<?php

namespace App\Application\Handlers;

use App\Application\Commands\AuthenticateConsumerCommand;
use App\Application\UseCases\AuthUseCase;
use App\Domain\Entities\Consumer;

class AuthenticateConsumerCommandHandler
{
    public function __construct(private AuthUseCase $useCase)
    {
    }

    public function handle(AuthenticateConsumerCommand $command): ?Consumer
    {
        return $this->useCase->authenticate($command->getBasicAuthorization());
    }
}
