# RateLimitBundle

This bundle provides simple rate limiting based on routes.

## Getting started

1. Install bundle:

```
composer require fusonic/rate-limit-bundle
```

2. Add RateLimitBundle to kernel:

```PHP
    Fusonic\RateLimitBundle\RateLimitBundle::class => ['prod' => true],
```

3. Add cache config

```YAML
framework:
    cache:
        app: cache.adapter.array
```

4. Add rate limit config

```YAML
fusonic_rate_limit:
    cache_provider: "cache.app"
    enabled: true
    routes:
        foo:
            limit: 2
            period: 3600
```

## How does it work

The bundle makes use of Symfony's event system. Therefore some events exist under `Fusonic/RateLimitBundle/Event`:
- **RateLimitAttemptsUpdatedEvent** will be emitted when a request for a rate limited route is detected.
- **RateLimitExceededEvent** will be emitted when a route limit is exceeded.
- **RateLimitResetAttemptsEvent** can be used to reset the state for a specific route (e.g. after a successful login)

### Example

Create an event listener or subscriber:

```PHP
<?php

namespace AppBundle\EventListener;

use Fusonic\RateLimitBundle\Event\RateLimitEvents;
use Fusonic\RateLimitBundle\Event\RateLimitExceededEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

final class RateLimitSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            RateLimitEvents::ROUTE_LIMIT_EXCEEDED => 'onLimitExceeded',
        ];
    }

    public function onLimitExceeded(RateLimitExceededEvent $event): void
    {
        $config = $event->getRouteLimitConfig();
        $message = 'You sent too many requests for this endpoint.';
        throw new TooManyRequestsHttpException($config->getPeriod(), $message);
    }
}
```

And register it as service.
 
```YAML
    app.rate_limit_subscriber:
        class: AppBundle\EventListener\RateLimitSubscriber
        tags:
            - { name: kernel.event_subscriber }

```

## Execute tests

Run the the tests by executing:

```
vendor/bin/simple-phpunit
```
