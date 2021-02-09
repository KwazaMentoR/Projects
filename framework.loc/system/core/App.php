<?php

namespace system\core;

use system\core\ErrorHandler;
use system\core\Request;

class App
{
    public static $app;

    public function __construct()
    {
        session_start();
        self::$app = Registry::instance(); // записываем в свойство объект класса реестр из TSingleton. Объект Registry создался, дальше мы можем обращаться к его методам. Грубо говоря в переменную один раз записался объект Registry, $app = new Registry. Статический метод можно вызвать у любого класса
        new ErrorHandler();
        self::$app->setProperty('request', new Request());
        $query = trim(self::$app->request->server['QUERY_STRING'], '/'); // теперь $_GET $_POST и прочие использовать через очиститель request
        self::$app->setProperty('Tiltle', 'Magazine'); // все данные должны заносится до роутера Router, что бы ему были доступны данные до того как он отработает, иначе данных не увидит
        $this->getParams();
        Db::instance();
        Router::dispatch($query); //можем обратиться к методу так как он статический и не требует создания экземпляра класса
    }

    protected function getParams() // записываем в объект $properties = [] данные, в цикле присваиваем каждому ключу значение
    {
        $params = require_once CONF . '/params.php';
        if(!empty($params)){
            foreach ($params as $k => $v){
                self::$app->setProperty($k, $v); //обращаемся к методу выше созданного Registry
            }
        }
    }
}
