<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        //  API routy (modul ApiModule)
        $apiRouter = new RouteList('Api');
        //Generátory
        $apiRouter->addRoute('api/generators', 'Generator:create'); // Api:Generator:create
        $apiRouter->addRoute('api/generators/delete', 'Generator:delete'); // Api:Generator:delete
        $apiRouter->addRoute('api/generators/update', 'Generator:update'); // Api:Generator:update
        $apiRouter->addRoute('api/generators/list', 'Generator:list'); // Api:Generator:list
        //Sessions
        $apiRouter->addRoute('api/sessions', 'Session:create'); // Api:Session:create
        $apiRouter->addRoute('api/sessions/stop', 'Session:stop'); // Api:Session:stop
        $apiRouter->addRoute('api/sessions/update-load', 'Session:updateLoad'); // Api:Session:updateLoad
        $apiRouter->addRoute('api/sessions/current', 'Session:current'); // Api:Session:current

        $router[] = $apiRouter;

        //  Webové routy (klasické)
        $router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');

        return $router;
    }
}