<?php

namespace App\Application\Ports\Inbound;

interface IAuthPort
{
    public function generateJWT(string $username, string $password): string;
    public function validateJWT(string $token): bool;
}