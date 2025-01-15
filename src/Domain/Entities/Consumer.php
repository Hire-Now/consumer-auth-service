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
        private ?Carbon $lastActivity,
        #[Getter] #[Setter]
        private ?Carbon $createdAt,
        #[Getter] #[Setter]
        private ?Carbon $updatedAt,
    ) {
    }

    /**
     * Updates the last access timestamp to the current date and time.
     */
    public function updateLastAccess(): void
    {
        $this->lastActivity = new \DateTimeImmutable();
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

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'client_id'     => $this->clientId,
            'description'   => $this->description,
            'is_active'     => $this->isActive,
            'last_activity' => $this->lastActivity?->format('Y-m-d H:i:s'),
            'created_at'    => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
