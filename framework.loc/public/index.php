<?php

use system\core\App; //для того что бы ниже использовать укороченное название App
use system\core\Router;

require_once dirname(__DIR__) . '/config/init.php'; // здесь мы подключили в том числе композер который автоматом будет подключать классы
require_once LIBS . '/functions.php';
require_once CONF . '/routes.php';

//echo "Это фронт-контроллер страница";
//echo dirname(__DIR__) . '/config/init.php';

new App();
//echo '<b>Метод getRoutes</b>';
//debug(Router::getRoutes());



//test11();
//echo $test;
//echo $test
//new Pus;
//throw new Exception('Страница не найдена', 404); // выбросим новое исключение
//trigger_error("E_USER_ERROR", E_USER_ERROR); // вызов ошибки

//var_dump(App::$app->getProperties());
//debug(App::$app->getProperties());
//$ser = $_SERVER;
//debug($ser);
//debug(App::$app->request);

//debug(App::$app->getProperty('add')->get);
//debug(App::$app->request->test);

