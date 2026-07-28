<?php

namespace App\EventSubscriber;

use App\Repository\CountryVisitRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SiteVisitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CountryVisitRepository $countryVisitRepository,
        private HttpClientInterface $httpClient
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Skip administrative, profiler, and static asset routes
        if (
            str_starts_with($path, '/admin') ||
            str_starts_with($path, '/_') ||
            preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i', $path)
        ) {
            return;
        }

        // Throttle visit logging per session to avoid duplicate hits per page click
        $session = $request->hasSession() ? $request->getSession() : null;
        if ($session && $session->has('last_visit_logged')) {
            $lastLogged = $session->get('last_visit_logged');
            if (time() - (int)$lastLogged < 900) { // 15 minutes throttle
                return;
            }
        }

        $rawIp = $request->getClientIp() ?? '127.0.0.1';
        $countryCode = 'PH';
        $countryName = 'Philippines';

        // Check if IP is local/private
        if ($this->isLocalOrPrivateIp($rawIp)) {
            $countryCode = 'PH';
            $countryName = 'Philippines';
        } else {
            try {
                $response = $this->httpClient->request('GET', sprintf('http://ip-api.com/json/%s?fields=status,country,countryCode', $rawIp), [
                    'timeout' => 1.5,
                ]);

                if (200 === $response->getStatusCode()) {
                    /** @var array{status?: string, countryCode?: string, country?: string} $data */
                    $data = $response->toArray(false);
                    if (isset($data['status']) && 'success' === $data['status']) {
                        $countryCode = $data['countryCode'] ?? 'PH';
                        $countryName = $data['country'] ?? 'Philippines';
                    }
                }
            } catch (\Throwable) {
                // Fallback to default Philippines on network timeout or offline
                $countryCode = 'PH';
                $countryName = 'Philippines';
            }
        }

        // Safely increment country count in DB (Zero IP logs stored)
        $this->countryVisitRepository->incrementCountryVisit($countryName, $countryCode);

        if ($session) {
            $session->set('last_visit_logged', time());
        }
    }

    private function isLocalOrPrivateIp(string $ip): bool
    {
        if ('127.0.0.1' === $ip || '::1' === $ip) {
            return true;
        }

        return false === filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
