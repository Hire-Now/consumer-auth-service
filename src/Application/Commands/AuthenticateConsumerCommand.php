<?php

namespace App\Application\Commands;

use App\Domain\Traits\AccessorTrait;
use App\Domain\Attributes\Getter;
use App\Domain\Attributes\Setter;

class AuthenticateConsumerCommand
{
    use AccessorTrait;

    public function __construct(
        #[Getter] #[Setter]
        private string $basicAuthorization,
    ) {
    }
}
