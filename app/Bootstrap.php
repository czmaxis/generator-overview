<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;
use Nette\Http\IRequest;
use Nette\Http\IResponse;

class Bootstrap
{
    public function boot(): \Nette\DI\Container
    {
        // ⚙️ Preflight OPTIONS request pro CORS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: http://127.0.0.1:8080');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');
            header('Access-Control-Allow-Credentials: true');
            http_response_code(204); // No Content
            exit;
        }

        $configurator = new Configurator;

        $configurator->setDebugMode(true);
        $configurator->enableTracy(__DIR__ . '/../log');
        $configurator->setTempDirectory(__DIR__ . '/../temp');
        $configurator->addConfig(__DIR__ . '/../config/common.neon');
        $configurator->addConfig(__DIR__ . '/../config/local.neon');

        $container = $configurator->createContainer();

        // ⚙️ Nastavení CORS hlaviček pro každou odpověď
        /** @var IRequest $httpRequest */
        $httpRequest = $container->getByType(IRequest::class);
        /** @var IResponse $httpResponse */
        $httpResponse = $container->getByType(IResponse::class);

        $origin = $httpRequest->getHeader('Origin');
        // povolíme jen frontend na http://127.0.0.1:8080
        if ($origin === 'http://127.0.0.1:8080') {
            $httpResponse->setHeader('Access-Control-Allow-Origin', $origin);
            $httpResponse->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $httpResponse->setHeader('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization');
            $httpResponse->setHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $container;
    }
}