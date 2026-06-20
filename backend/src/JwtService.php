<?php
declare(strict_types=1);

namespace FixIt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JwtService
{
    private string $secret;
    private string $issuer;
    private int $ttl;

    public function __construct(array $env)
    {
        $this->secret = $env['JWT_SECRET'] ?? 'dev-secret';
        $this->issuer = $env['JWT_ISSUER'] ?? 'arcade-fixit';
        $this->ttl = (int)($env['JWT_TTL'] ?? 86400);
    }

    public function issue(array $user): string
    {
        $now = time();

        return JWT::encode([
            'iss' => $this->issuer,
            'iat' => $now,
            'exp' => $now + $this->ttl,
            'sub' => (int)$user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'name' => $user['name'],
        ], $this->secret, 'HS256');
    }

    public function verify(string $token): array
    {
        return (array)JWT::decode($token, new Key($this->secret, 'HS256'));
    }
}

