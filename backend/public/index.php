<?php
declare(strict_types=1);

use FixIt\AuthMiddleware;
use FixIt\Database;
use FixIt\JwtService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/bootstrap.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$env = load_env(__DIR__ . '/../.env');
$displayErrors = env_bool($env, 'APP_DEBUG', false);
ini_set('display_errors', $displayErrors ? '1' : '0');
ini_set('log_errors', '1');

$registerErrorMiddleware = function () use ($app, $displayErrors): void {
    $errorMiddleware = $app->addErrorMiddleware($displayErrors, true, true);
    $errorMiddleware->setDefaultErrorHandler(
        function (
            Request $request,
            Throwable $exception,
            bool $displayErrorDetails,
            bool $logErrors,
            bool $logErrorDetails
        ) {
            $status = $exception instanceof HttpException ? $exception->getCode() : 500;
            if ($status < 400 || $status > 599) {
                $status = 500;
            }

            $payload = [
                'error' => $status >= 500 ? 'Internal server error' : $exception->getMessage(),
            ];

            if ($displayErrorDetails) {
                $payload['details'] = [
                    'type' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];
            }

            return json_response($payload, $status);
        }
    );
};

$app->options('/', fn(Request $request, Response $response) => $response);
$app->options('/{routes:.+}', fn(Request $request, Response $response) => $response);

try {
    $pdo = Database::connect($env);
    $jwt = new JwtService($env);
    $auth = new AuthMiddleware($jwt);

    require __DIR__ . '/../src/routes.php';
} catch (Throwable $exception) {
    error_log($exception->getMessage());

    $app->get('/health', fn() => json_response([
        'status' => 'error',
        'database' => 'unavailable',
        'service' => 'Arcade FixIt API',
    ], 503));

    $app->any('/', fn() => json_response(['error' => 'Service unavailable'], 503));
    $app->any('/{routes:.+}', fn() => json_response(['error' => 'Service unavailable'], 503));
}

$registerErrorMiddleware();

$app->add(function (Request $request, $handler) use ($env) {
    $response = $handler->handle($request);
    return with_cors_headers($response, $request, $env)
        ->withHeader('Content-Type', 'application/json');
});

$app->run();
