<?php

namespace App\Classes\Crawler;

use Dom\HTMLDocument;
use Exception;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\BrowserConnectionFailed;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Exception\NoResponseAvailable;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ChromiumCrawler
{
    public string $url;

    public HTMLDocument $dom;

    public function __construct(
        string $url,
        array $extra_headers = [],
        int $timeout_ms = 5000,
        string $page_event = Page::NETWORK_IDLE,
    ) {
        // path to the file to store websocket's uri
        $socketFile = '/tmp/chrome-php-demo-socket';
        $lockFile = '/tmp/chrome-php-demo-socket.lock';

        $options = [
            'connectionDelay' => 1.32,
            'headless' => true,
            'noSandbox' => true,
            "headers" => $extra_headers,
            'customFlags' => [
                '--lang=en-US',
                '--disable-blink-features=AutomationControlled',
                '--deny-permission-prompts=true',
                '--disable-web-security',
                '--disable-features=IsolateOrigins,site-per-process',
                '--disable-site-isolation-trials',
                '--ignore-certificate-errors',
                '--no-first-run',
                '--no-default-browser-check',
                '--no-sandbox',
                '--test-type',
                '--enable-features=NetworkService,NetworkServiceInProcess',
                '--window-size=1280,720',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--disable-extensions',
                '--disable-background-networking',
                '--disable-sync',
                '--disable-translate',
                '--mute-audio',
                '--renderer-process-limit=6',
                '--js-flags=--max-old-space-size=256',
            ],
            'disableNotifications' => true,
            'keepAlive' => true,
            'ignoreHTTPSErrors' => true,
            'ignoreCertificateErrors' => true,
            'stealth' => true,
            'bypassCSP' => true,
        ];

        // Try connecting to an existing browser without locking first (fast path)
        $socket = file_exists($socketFile) ? file_get_contents($socketFile) : '';
        $browser = null;

        if ($socket) {
            try {
                $browser = BrowserFactory::connectToBrowser($socket);
            } catch (BrowserConnectionFailed|InvalidArgumentException $e) {
                // Connection failed, will acquire lock and retry below
            }
        }

        // If fast path failed, acquire exclusive lock to start a new browser
        if (! $browser) {
            $lockHandle = fopen($lockFile, 'c');
            flock($lockHandle, LOCK_EX);

            try {
                // Re-read socket file — another process may have staexitrted the browser while we waited
                $socket = file_exists($socketFile) ? file_get_contents($socketFile) : '';

                if ($socket) {
                    try {
                        $browser = BrowserFactory::connectToBrowser($socket);
                    } catch (BrowserConnectionFailed|InvalidArgumentException $e) {
                        // Still can't connect, we need to start a new browser
                    }
                }

                if (! $browser) {
                    Log::info('New Chrome instance');
                    $browserType = app()->isProduction() ? 'chromium' : null;

                    // just in case there is a chromium process running on the server
                    Process::run("killall chromium");

                    // The browser was probably closed, start it again
                    $browser_factory = new BrowserFactory($browserType);

                    $browser = $browser_factory->createBrowser($options);
                    // save the uri to be able to connect again to the browser
                    file_put_contents($socketFile, $browser->getSocketUri(), LOCK_EX);
                }
            } finally {
                flock($lockHandle, LOCK_UN);
                fclose($lockHandle);
            }
        }

        $pageLockFile = '/tmp/chrome-php-page-semaphore.lock';
        $maxConcurrentPages = 4;

        // Block until a page slot is available
        $pageSlot = fopen($pageLockFile, 'c');
        $waited = 0;
        while (true) {
            flock($pageSlot, LOCK_EX);
            $count = (int) (file_exists($pageLockFile . '.count') ? file_get_contents($pageLockFile . '.count') : 0);
            if ($count < $maxConcurrentPages) {
                file_put_contents($pageLockFile . '.count', $count + 1);
                flock($pageSlot, LOCK_UN);
                break;
            }
            flock($pageSlot, LOCK_UN);
            usleep(200_000); // 200ms
            if (++$waited > 150) { // 30s timeout
                fclose($pageSlot);
                Log::error("Timed out waiting for a page slot");
                return;
            }
        }
        fclose($pageSlot);

        $page = null;
        try {
            $page = $browser->createPage();
            $page->navigate($url)
                ->waitForNavigation($page_event, $timeout_ms);

            if (Str::contains($url, "homedepot.ca", true)) {
                $this->home_depot_process($page);
            }

            $this->dom = HTMLDocument::createFromString($page->getHtml(), LIBXML_NOERROR);

        } catch (OperationTimedOut $e) {
            Log::error("Page navigation timed out: " . $url);
        } catch (CommunicationException $e) {
            Log::error("Couldn't connect to the page");
        } catch (NoResponseAvailable $e) {
            Log::error("No response available");
        } catch (Exception $exception) {
            Log::error("Crawling using chrome");
            Log::error($exception->getMessage());
        } finally {
            try {
                $page?->close();
            } catch (Throwable $e) {
                Log::error("Couldn't close the page");
            }

            // Release page slot
            $pageSlot = fopen($pageLockFile, 'c');
            flock($pageSlot, LOCK_EX);
            $count = (int) (file_exists($pageLockFile . '.count') ? file_get_contents($pageLockFile . '.count') : 1);
            file_put_contents($pageLockFile . '.count', max(0, $count - 1));
            flock($pageSlot, LOCK_UN);
            fclose($pageSlot);
        }
    }

    private function home_depot_process($page)
    {
        $page->waitUntilContainsElement('.hdca-modal__content , .hdca-product__description-pricing-price-value');

        $page->mouse()
            ->move(10, 10)
            ->click();

        $page->waitUntilContainsElement('.hdca-product__description-pricing-price-value');

    }
}
