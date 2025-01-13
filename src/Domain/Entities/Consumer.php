<?php

namespace App\Domain\Entities;

use App\Domain\Traits\AccessorTrait;
use App\Domain\Attributes\Getter;
use App\Domain\Attributes\Setter;
use Carbon\Carbon;

class Consumer
{
    use AccessorTrait;

    public function __construct(
        #[Getter] #[Setter]
        private ?string $id,
        #[Getter] #[Setter]
        private ?string $name,
        #[Getter] #[Setter]
        private ?string $clientId,
        #[Getter]
        private ?string $clientSecret,
        #[Getter] #[Setter]
        private ?string $description,
        #[Getter] #[Setter]
        private ?bool $isActive,
        #[Getter] #[Setter]
        private ?Carbon $lastAccessAt
    ) {
    }

    /**
     * Updates the last access timestamp to the current date and time.
     */
    public function updateLastAccess(): void
    {
        $this->lastAccessAt = new \DateTimeImmutable();
    }

    /**
     * Deactivates the consumer.
     */
    public function deactivate(): void
    {
        $this->isActive = false;
    }

    /**
     * Activates the consumer.
     */
    public function activate(): void
    {
        $this->isActive = true;
    }

    /**
     * Validates the client secret.
     */
    public function validateClientSecret(string $secret): bool
    {
        return password_verify($secret, $this->clientSecret);
    }
}
