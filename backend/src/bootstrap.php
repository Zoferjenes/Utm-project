<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;

function load_env(string $path): array
{
    $env = [];

    foreach (getenv() ?: [] as $key => $value) {
        if (is_string($key) && is_scalar($value)) {
            $env[$key] = (string)$value;
        }
    }

    foreach ($_SERVER as $key => $value) {
        if (is_string($key) && is_scalar($value)) {
            $env[$key] = (string)$value;
        }
    }

    foreach ($_ENV as $key => $value) {
        if (is_string($key) && is_scalar($value)) {
            $env[$key] = (string)$value;
        }
    }

    if (!is_file($path)) {
        return $env;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }

    return $env;
}

function env_bool(array $env, string $key, bool $default = false): bool
{
    if (!array_key_exists($key, $env)) {
        return $default;
    }

    $value = strtolower(trim((string)$env[$key]));
    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

function allowed_cors_origins(array $env): array
{
    $origins = explode(',', (string)($env['CORS_ALLOWED_ORIGINS'] ?? ''));
    return array_values(array_filter(array_map('trim', $origins), fn(string $origin) => $origin !== ''));
}

function with_cors_headers(Response $response, \Psr\Http\Message\ServerRequestInterface $request, array $env): Response
{
    $origin = $request->getHeaderLine('Origin');
    if ($origin !== '' && in_array($origin, allowed_cors_origins($env), true)) {
        $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
    }

    return $response
        ->withHeader('Vary', 'Origin')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true');
}

function json_response(array $data, int $status = 200): Response
{
    $response = new \Slim\Psr7\Response($status);
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES));
    return $response->withHeader('Content-Type', 'application/json');
}

function body_array(\Psr\Http\Message\ServerRequestInterface $request): array
{
    $body = $request->getParsedBody();
    return is_array($body) ? $body : [];
}

function require_role(array $auth, array $roles): ?Response
{
    if (!in_array($auth['role'] ?? '', $roles, true)) {
        return json_response(['error' => 'Forbidden for this role'], 403);
    }

    return null;
}

function require_fields(array $body, array $fields): array
{
    $errors = [];

    foreach ($fields as $field) {
        if (!isset($body[$field]) || trim((string)$body[$field]) === '') {
            $errors[$field] = "{$field} is required";
        }
    }

    return $errors;
}
