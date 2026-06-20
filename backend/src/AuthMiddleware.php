<?php
declare(strict_types=1);

namespace FixIt;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthMiddleware
{
    public function __construct(private JwtService $jwt)
    {
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine('Authorization');

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $match)) {
            return json_response(['error' => 'Missing bearer token'], 401);
        }

        try {
            $payload = $this->jwt->verify($match[1]);
        } catch (\Throwable $e) {
            return json_response(['error' => 'Invalid or expired token'], 401);
        }

        return $handler->handle($request->withAttribute('auth', $payload));
    }
}

