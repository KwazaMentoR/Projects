<?php

namespace app\controllers;

use system\core\App;
use app\controllers\AppController;

class CurrencyController extends AppController
{
    public function changeAction()
        // при смене валюты на основной странице передается значение валюты в main.js через gеt параметр и направляется на данный контроллер с запуском метода currency/change?curr=RUB. Метод создает куки и далее AppController проверяет наличие куки и записывает setProperty('currency') как установленную валюту
    {
        $currency = !empty($_GET['curr']) ? $_GET['curr'] : null;
        $cur = App::$app->getProperty('currencies');
        if ($currency){
            $curr = $cur[$currency];
            if (!empty($curr)){
                setcookie('currency', $currency, time()+3600*24*7, '/');
            }
        }
        redirect();
    }
}