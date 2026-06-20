<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;

function load_env(string $path): array
{
    $env = $_ENV;

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

