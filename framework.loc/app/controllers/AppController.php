<?php

namespace app\controllers;

use app\models\AppModel;
use app\widgets\currency\Currency;
use system\core\App;
use system\core\base\Controller;
use system\core\Cache;
use system\core\Db;

class AppController extends Controller
{
    protected $cache;
    protected $currobject;
    protected $appmodel;

    public function __construct($route)
    {
        parent::__construct($route);
        $this->appmodel = new AppModel();
        $this->currobject = new Currency();

        App::$app->setProperty('currencies', $this->currobject->getCurrencies());
        $curr = $this->currobject->getCurrency(App::$app->getProperty('currencies'));
        //debug($curr);
        App::$app->setProperty('currency', $curr); // проверяется наличие куки и записывается в currency если есть совпадение значения

        App::$app->setProperty('cats', self::cacheCategory()); //записывает в кэш данные из таблицы Category
        //debug(App::$app->getProperties());
        //debug(self::cacheCategory());
    }

    public static function cacheCategory()
    {
        $cache = Cache::instance();
        $cats = $cache->get('cats'); //если получили данные из кэша то вернем
        if (!$cats){
            $cats = Db::findAssoc("SELECT * FROM category");
            //если нет, то получим из базы данных и запишем в кэш. Каждый элемент массива проименован id
            $cache->set('cats', $cats);
        }
        return $cats;
    }
}