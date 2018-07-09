<?php

namespace Fusonic\RateLimitBundle\Model;

interface RouteLimitConfigInterface
{
    public function getLimit(): int;

    public function getPeriod(): int;

    public function getRoute(): string;
}
