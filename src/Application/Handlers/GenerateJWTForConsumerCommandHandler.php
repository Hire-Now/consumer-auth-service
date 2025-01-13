<?php

namespace App\Application\Handlers;

use App\Application\Commands\GenerateJWTForConsumerCommand;
use App\Application\UseCases\AuthUseCase;

class GenerateJWTForConsumerCommandHandler
{
    public function __construct(private AuthUseCase $useCase)
    {
    }

    public function handle(GenerateJWTForConsumerCommand $command): ?string
    {
        return $this->useCase->generateJWT($command->getConsumerEntity());
    }
}
