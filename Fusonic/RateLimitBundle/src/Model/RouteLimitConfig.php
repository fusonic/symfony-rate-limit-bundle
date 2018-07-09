<?php

namespace Fusonic\RateLimitBundle\Model;

class RouteLimitConfig implements RouteLimitConfigInterface
{
    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $period;

    /**
     * @var string
     */
    private $route;

    private function __construct()
    {
    }

    public static function fromRouteConfig(string $route, array $data): RouteLimitConfigInterface
    {
        $config = new RouteLimitConfig();
        $config->setLimit($data['limit']);
        $config->setPeriod($data['period']);
        $config->setRoute($route);

        return $config;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getPeriod(): int
    {
        return $this->period;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    private function setRoute(string $route): void
    {
        $this->route = $route;
    }

    private function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    private function setPeriod(int $period): void
    {
        $this->period = $period;
    }
}
