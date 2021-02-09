<?php

namespace app\controllers;

use app\models\MainModel;
use system\core\App;
use system\core\Cache;
use system\core\Db;
use system\core\Router;

class MainController extends AppController
{
    public function indexAction()
    {
        $model = new MainModel();

        //echo '<b>Переменная $res в MainController</b>';
        //debug($res);

        $this->setMeta(App::$app->getProperty('Tiltle'), 'Описание', 'ключевые слова');

/*
        $posts = \R::findAll('test'); извлекаем данные из ДБ с помощью RedBeanPHP
        echo '<b>Переменная $posts в MainController</b>';
        debug($posts);

        //Cache::set('test', $names);
        //$data = Cache::get('test');
*/

        /*
        if(isset(App::$app->request->post['submit'])){
            $model->insertPost('title, file');
        }
        */
    }

}