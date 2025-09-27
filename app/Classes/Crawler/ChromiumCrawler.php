<?php

namespace App\Classes\Crawler;

use Dom\HTMLDocument;
use Exception;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\BrowserConnectionFailed;
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

        if (! file_exists($socketFile)) {
            file_put_contents($socketFile, '');
        }

        $socket = file_get_contents($socketFile);

        $options = [
            //
            'connectionDelay' => 1,
            'headless' => false,
            'noSandbox' => true,
            "headers" => $extra_headers,
            'customFlags' => [
                '--lang=en-US',
                '--disable-blink-features=AutomationControlled',
                '--deny-permission-prompts=true',
                '--disable-blink-features=AutomationControlled',
                '--disable-web-security',
                '--disable-features=IsolateOrigins,site-per-process',
                '--disable-site-isolation-trials',
                '--ignore-certificate-errors',
                '--no-first-run',
                '--no-default-browser-check',
                '--no-sandbox',
                '--test-type',
                '--enable-features=NetworkService,NetworkServiceInProcess',
                '--window-size=1920,1080',
            ],
            'disableNotifications' => true,
            'keepAlive' => true,
            'ignoreHTTPSErrors' => true,
            'ignoreCertificateErrors' => true,
            'stealth' => true,
            'bypassCSP' => true,
        ];

        try {
            $browser = BrowserFactory::connectToBrowser($socket);
        } catch (BrowserConnectionFailed|InvalidArgumentException $e) {
            Log::info('New Chrome instance');
            Log::info($e->getMessage());
            $browserType = app()->isProduction() ? 'chromium' : null;

            // just in case there is a chromium process running on the server
            Process::run("killall chromium");

            // The browser was probably closed, start it again
            $browser_factory = new BrowserFactory($browserType);

            $browser = $browser_factory
                ->createBrowser($options);
            // save the uri to be able to connect again to the browser
            file_put_contents($socketFile, $browser->getSocketUri(), LOCK_EX);
        }

        $page = $browser->createPage();

        try {
            $page->navigate($url)
                ->waitForNavigation($page_event, $timeout_ms);

            if (Str::contains($url, "homedepot.ca", true)) {
                $this->home_depot_process($page);
            }

            $this->dom = HTMLDocument::createFromString($page->getHtml(), LIBXML_NOERROR);
            $page->close();
        } catch (OperationTimedOut $e) {
            $this->dom = HTMLDocument::createFromString($page->getHtml(), LIBXML_NOERROR);
            $page->close();
        } catch (Exception $exception) {
            Log::error("Crawling using chrome");
            Log::error($exception->getMessage());
            $page->close();
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
