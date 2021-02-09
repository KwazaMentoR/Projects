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
    }
}