<?php

namespace App\Contracts;

interface HealthServiceInterface
{
    /**
     * Perform health check and return status array
     *
     * @return array
     */
    public function getStatus(): array;
}
