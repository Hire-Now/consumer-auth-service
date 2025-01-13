<?php

namespace App\Application\Services;

use App\Domain\Entities\Consumer;
use App\Domain\Entities\JwtToken;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Firebase\JWT\JWT;

class JWTAuthService
{
    private string $secretKey = 'your-secret-key';

    // Inyectar configuración en el constructor
    public function __construct(private readonly array $config)
    {
    }

    public function generateJWT(Consumer $entity): JwtToken
    {
        try {
            $issuedAt = Carbon::now()->timestamp;
            $expirationTime = Carbon::now()->add((int) $this->config['app']['consumer_auth']['jwt_validity_time'], $this->config['app']['consumer_auth']['jwt_type_time'])->timestamp;
            $jwtId = Str::uuid()->toString();

            $payload = [
                'sub' => $entity->getId(),
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'aud' => $this->config['app']['url'],
                'iss' => $this->config['app']['url'],
                'jti' => $jwtId,
            ];

            $privateKey = openssl_pkey_get_private($this->config['app']['user_auth']['private_key_passphrase']);

            $jwt = JWT::encode($payload, $privateKey, 'RS256');

            return new JwtToken(
                null,
                $payload['sub'],
                $jwtId,
                $this->config['app']['user_auth']['jwt_validity_time'],
                $this->config['app']['user_auth']['jwt_type_time'],
                'Consumer',
                $jwt
            );
        } catch (\Throwable $th) {
            throw new \RuntimeException('JWT could not be generated!', 0, $th);
        }
    }

    public function validateJWT(string $token): bool
    {
        try {
            // JWT::decode($token, $this->secretKey, [ 'HS256' ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function extractCliendIDClientSecret($basicAuthorization): array
    {
        $decoded = base64_decode(substr($basicAuthorization, 6));
        return explode(':', $decoded, 2);
    }

    public function consumerCredentialsAreValid(string $clientSecret, Consumer $consumer): bool
    {
        return password_verify($clientSecret, $consumer->getClientSecret()) && $consumer->getIsActive();
    }
}
