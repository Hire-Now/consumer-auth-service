<?php

namespace App\Application\Commands;

use App\Domain\Traits\AccessorTrait;
use App\Domain\Attributes\Getter;
use App\Domain\Attributes\Setter;
use App\Domain\Entities\Consumer;

class GenerateJWTForConsumerCommand
{
    use AccessorTrait;

    public function __construct(
        #[Getter] #[Setter]
        private Consumer $consumerEntity,
    ) {
    }
}
