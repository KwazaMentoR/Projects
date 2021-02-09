<?php

namespace system\core\base;
use system\core\Db;

abstract class Model
{
    public $attributes = [];
    public $errors = [];
    public $rules = [];
    public $db;

    public function __construct()
    {

    }

    public function insertPostData($table, $fields)
    {
        $post = App::$app->request->post;
        //debug($post);
        $data = [$post['title'],$post['file']];
        //debug($posts);
        Db::insertData($table, $data, $fields);
        redirect();
    }

}