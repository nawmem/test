<?php
session_start();
setcookie('auth', isset($_COOKIE['auth']) == true ? true : false, time() + 3600);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
use App\Controllers\HomeController;
use App\Request\Request;
use App\Routers\Router;
spl_autoload_register(function ($class_name){
    include __DIR__.'/..'.'/'.str_replace('\\', '/', $class_name).'.php';
});
// подключаем автолоадер бутстрап чтобы подгрузить плагин для парсинга юзер агета
require __DIR__.'/../vendor/autoload.php';
// поделючаем роуты
include __DIR__.'/../App/Routers/URI.php';
$request = new Request();
// запскаем поиск роута
Router::searhUri($_SERVER['REQUEST_URI'],$_SERVER['REQUEST_METHOD']);
