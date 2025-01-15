<?php

namespace App\Application\Handlers;

use App\Application\Commands\GenerateJWTForConsumerCommand;
use App\Application\Commands\ValidateJWTCommand;
use App\Application\UseCases\AuthUseCase;
use App\Domain\Entities\Consumer;

class ValidateJWTCommandHandler
{
    public function __construct(private AuthUseCase $useCase)
    {
    }

    public function handle(ValidateJWTCommand $command): ?Consumer
    {
        return $this->useCase->validateJWT($command->getConsumerJWT());
    }
}
