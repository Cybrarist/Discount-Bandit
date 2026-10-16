<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ChromiumCrawlerLockTest extends TestCase
{
    private string $socketFile;
    private string $lockFile;
    private string $counterFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->socketFile = tempnam(sys_get_temp_dir(), 'chrome_socket_test_');
        $this->lockFile = $this->socketFile . '.lock';
        $this->counterFile = $this->socketFile . '.counter';

        file_put_contents($this->socketFile, '');
        file_put_contents($this->counterFile, '0');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->socketFile)) unlink($this->socketFile);
        if (file_exists($this->lockFile)) unlink($this->lockFile);
        if (file_exists($this->counterFile)) unlink($this->counterFile);
        parent::tearDown();
    }

    /**
     * Simulates the ChromiumCrawler connection logic with file locking.
     * Instead of actually starting a browser, it increments a counter file
     * to track how many times "browser creation" would have occurred.
     */
    private function simulateBrowserConnectionWithLock(): string
    {
        // Fast path: try to read existing socket
        $socket = file_get_contents($this->socketFile);

        if ($socket) {
            return 'reused';
        }

        // Slow path: acquire lock
        $lockHandle = fopen($this->lockFile, 'c');
        flock($lockHandle, LOCK_EX);

        try {
            // Re-check after acquiring lock (another process may have written)
            $socket = file_get_contents($this->socketFile);

            if ($socket) {
                return 'reused';
            }

            // Simulate browser startup time
            usleep(50000); // 50ms

            // Atomically increment the creation counter
            $counterHandle = fopen($this->counterFile, 'c+');
            flock($counterHandle, LOCK_EX);
            $count = (int) fread($counterHandle, 100);
            fseek($counterHandle, 0);
            ftruncate($counterHandle, 0);
            fwrite($counterHandle, (string) ($count + 1));
            flock($counterHandle, LOCK_UN);
            fclose($counterHandle);

            // Write the socket URI
            file_put_contents($this->socketFile, 'ws://127.0.0.1:9222/devtools/browser/fake-id', LOCK_EX);

            return 'created';
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    public function test_with_lock_only_one_process_creates_browser(): void
    {
        if (! function_exists('pcntl_fork')) {
            $this->markTestSkipped('pcntl extension is required for this test');
        }

        $numProcesses = 5;
        $pids = [];

        for ($i = 0; $i < $numProcesses; $i++) {
            $pid = pcntl_fork();

            if ($pid === -1) {
                $this->fail('Could not fork process');
            }

            if ($pid === 0) {
                // Child process
                $this->simulateBrowserConnectionWithLock();
                exit(0);
            }

            $pids[] = $pid;
        }

        // Wait for all children
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
        }

        $creationCount = (int) file_get_contents($this->counterFile);

        $this->assertEquals(1, $creationCount,
            "Expected exactly 1 browser creation with locking, but got {$creationCount}");
    }

    public function test_double_check_reuses_socket_written_by_another_process(): void
    {
        if (! function_exists('pcntl_fork')) {
            $this->markTestSkipped('pcntl extension is required for this test');
        }

        // Fork a child that holds the lock and writes the socket after a delay
        $pid = pcntl_fork();

        if ($pid === 0) {
            // Child: acquire lock, simulate slow browser start, then write socket
            $lockHandle = fopen($this->lockFile, 'c');
            flock($lockHandle, LOCK_EX);
            usleep(100000); // 100ms — hold the lock while "starting browser"
            file_put_contents($this->socketFile, 'ws://127.0.0.1:9222/devtools/browser/child-created', LOCK_EX);
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
            exit(0);
        }

        // Parent: small delay to ensure child grabs lock first, then try the locked path
        usleep(20000); // 20ms
        $result = $this->simulateBrowserConnectionWithLock();

        pcntl_waitpid($pid, $status);

        // The parent should have reused the socket the child wrote (double-check after lock)
        $this->assertEquals('reused', $result,
            'Process should reuse the socket written by the first process after acquiring the lock');

        $this->assertEquals(0, (int) file_get_contents($this->counterFile),
            'No additional browser should be created — the double-check should catch it');
    }

    public function test_socket_file_contains_valid_uri_after_concurrent_access(): void
    {
        if (! function_exists('pcntl_fork')) {
            $this->markTestSkipped('pcntl extension is required for this test');
        }

        $numProcesses = 5;
        $pids = [];

        for ($i = 0; $i < $numProcesses; $i++) {
            $pid = pcntl_fork();

            if ($pid === -1) {
                $this->fail('Could not fork process');
            }

            if ($pid === 0) {
                $this->simulateBrowserConnectionWithLock();
                exit(0);
            }

            $pids[] = $pid;
        }

        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
        }

        $socket = file_get_contents($this->socketFile);

        $this->assertNotEmpty($socket, 'Socket file should contain a URI after concurrent access');
        $this->assertStringStartsWith('ws://', $socket, 'Socket file should contain a valid websocket URI');
    }

    public function test_existing_socket_is_reused_without_lock(): void
    {
        // Pre-populate socket file (simulating an already-running browser)
        file_put_contents($this->socketFile, 'ws://127.0.0.1:9222/devtools/browser/existing-id');

        $result = $this->simulateBrowserConnectionWithLock();

        $this->assertEquals('reused', $result, 'Should reuse existing browser via fast path');
        $this->assertEquals(0, (int) file_get_contents($this->counterFile),
            'No new browser should be created when one already exists');
    }
}
