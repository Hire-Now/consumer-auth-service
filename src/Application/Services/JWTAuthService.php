<?php

namespace App\Application\Services;

use App\Domain\Entities\Consumer;
use App\Domain\Entities\JwtToken;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use OpenSSLAsymmetricKey;

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

            $privateKey = $this->extractPrivateKey();

            $jwt = JWT::encode($payload, $privateKey, 'RS256');

            return new JwtToken(
                null,
                $payload['sub'],
                $jwtId,
                $this->config['app']['consumer_auth']['jwt_validity_time'],
                $this->config['app']['consumer_auth']['jwt_type_time'],
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

    private function extractPrivateKey(): OpenSSLAsymmetricKey
    {
        $privateKeyPath = realpath(__DIR__ . '/../../') . '/Infrastructure/storage/app/private/keys/private_key.pem';

        if (!file_exists($privateKeyPath)) {
            throw new \RuntimeException("Private key file does not exist: {$privateKeyPath}");
        }

        $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath), $this->config['app']['consumer_auth']['private_key_passphrase']);

        if (!$privateKey) {
            throw new \RuntimeException("Failed to load private key: " . openssl_error_string());
        }

        return $privateKey;
    }
}
