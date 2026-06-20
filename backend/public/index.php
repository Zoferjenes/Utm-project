<?php
declare(strict_types=1);

use FixIt\AuthMiddleware;
use FixIt\Database;
use FixIt\JwtService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/bootstrap.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$env = load_env(__DIR__ . '/../.env');
$pdo = Database::connect($env);
$jwt = new JwtService($env);
$auth = new AuthMiddleware($jwt);

$app->add(function (Request $request, $handler) use ($env) {
    $response = $handler->handle($request);
    $origin = $request->getHeaderLine('Origin');
    $allowed = array_map('trim', explode(',', $env['CORS_ALLOWED_ORIGINS'] ?? ''));

    if ($origin && in_array($origin, $allowed, true)) {
        $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
    }

    return $response
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Content-Type', 'application/json');
});

$app->options('/{routes:.+}', fn(Request $request, Response $response) => $response);

require __DIR__ . '/../src/routes.php';

$app->run();

