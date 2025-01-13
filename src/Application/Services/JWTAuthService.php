<?php

namespace App\Infrastructure\Services;

use App\Application\Contracts\AuthInterface;
use App\Application\Ports\Inbound\IAuthPort;
use Firebase\JWT\JWT;

class JWTAuthService
{
    private string $secretKey = 'your-secret-key';

    public function generateToken(Consumer $entity): string
    {//todo" cambiar tiempo y llaves de crifrado dependiendo de la entidad
        try {
            $issuedAt = Carbon::now()->timestamp;
            $expirationTime = Carbon::now()->add((int) config('app.user_auth.jwt_validity_time'), config('app.user_auth.jwt_type_time'))->timestamp;
            $jwtId = Str::uuid()->toString();

            $payload = [
                'sub'   => $entity->getId(),
                'roles' => ($entity instanceof User) ? $this->userRoles($entity->getRoles()) : [],
                'iat'   => $issuedAt,
                'exp'   => $expirationTime,
                'aud'   => ($entity instanceof User) ? '' : config('app.url'),
                'iss'   => config('app.url'),
                'jti'   => $jwtId,
            ];

            $jwt = JWT::encode(
                $payload,
                openssl_pkey_get_private($this->privateKey, config('app.user_auth.private_key_passphrase')),
                'RS256'
            );

            $this->jwtTokenRepository->create(new JwtToken(
                null,
                $payload['sub'],
                $jwtId,
                config('app.user_auth.jwt_validity_time'),
                config('app.user_auth.jwt_type_time'),
                ($entity instanceof User) ? 'User' : 'Consumer'
            ));

            return $jwt;
        } catch (\Throwable $th) {
            throw new \RuntimeException('JWT could not be generated!', 0, $th);
        }
    }

    public function validateJWT(string $token): bool
    {
        try {
            JWT::decode($token, $this->secretKey, [ 'HS256' ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
