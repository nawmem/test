<?php
use \App\Controllers\HomeController;
use App\Routers\Router;

Router::routeGet(['Route' => '/', 'Controller' => [HomeController::class, 'createHome']]);
// регистрируемся
Router::routeGet(['Route' => '/registration', 'Controller' => [HomeController::class, 'createRegistration']]);
Router::routePost(['Route' => '/registration', 'Controller' => [HomeController::class, 'storeRegistration']]);
// логинимся
Router::routeGet(['Route' => '/login', 'Controller' => [HomeController::class, 'createLogin']]);
Router::routePost(['Route' => '/login', 'Controller' => [HomeController::class, 'storeLogin']]);

Router::routePost(['Route' => '/generate-uri', 'Controller' => [HomeController::class, 'storeGenerateUri']]);
Router::routeGet(['Route' => '/generate-uri', 'Controller' => [HomeController::class, 'storeGenerateUri']]);
Router::routeGet(['Route' => '/ln/{:url}', 'Controller' => [HomeController::class, 'createRedirectUri']]);

Router::routeGet(['Route' => '/logout', 'Controller' => [HomeController::class, 'createLogout']]);

Router::routeGet(['Route' => '/dashboard', 'Controller' => [HomeController::class, 'createDashboard']]);

Router::routeGet(['Route' => '/dashboard/link-info/{:id}', 'Controller' => [HomeController::class, 'createDashboardLinkInfo']]);



