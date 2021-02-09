<?php

define("DEBUG", 1);
define("ROOT", dirname(__DIR__));
define("WWW", ROOT . '/public');
define("APP", ROOT . '/app');
define("CORE", ROOT . '/system/core');
define("LIBS", ROOT . '/system/core/libs');
define("CACHE", ROOT . '/storage/cache');
define("CONF", ROOT . '/config');
define("LAYOUT", 'watches');
//  http://framework
$app_path = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
// http://ishop2.loc/public/
$app_path = preg_replace("#[^/]+$#", '', $app_path);
// http://ishop2.loc
$app_path = str_replace('/public/', '', $app_path);
define("PATH", $app_path);
define("ADMIN", PATH . '/admin');

function autoloader($class)
{
    $class = str_replace("\\", "/", $class);
    $file = dirname(__DIR__) . "/{$class}.php";
    if(file_exists($file)){
        require_once $file;
    }
}
spl_autoload_register('autoloader');

/*
require_once ROOT . '/vendor/autoload.php';

cd - для перехода на нужный путь
composer self-update - обновляет сам composer, т.е. его версию
composer install - установить composer, если он еще не был установлен
composer update - обновляет атозагрузчик composer (необходимо, когда изменил путь в имеющемся уже правиле в composer.json)
composer dump-autoload - обновляет атозагрузчик composer (необходимо, когда добавил новые правила в composer.json)
*/

