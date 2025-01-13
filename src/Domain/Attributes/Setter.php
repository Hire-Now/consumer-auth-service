<?php

namespace App\Domain\Attributes;

use App\Domain\Enums\Modifier;
use Attribute;

#[Attribute]
class Setter
{
    public function __construct(public Modifier $modifier = Modifier::PUBLIC )
    {
    }
}
