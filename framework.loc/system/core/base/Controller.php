<?php

namespace system\core\base;
use system\core\App;

abstract class Controller // не нужно создавать объект класса. Является базисом основных методов и свойств для наследования другим классам контроллеров
{
    public $route;
    public $controller;
    public $model;
    public $view;
    public $layout;
    public $prefix;
    public $data = [];
    public $meta = ['title' => '', 'desc' => '', 'keywords' => '']; // по дефолту будут пустые значения, если не задано ничего в setMeta

    public function __construct($route)
    { // Так как класс родительский, то $controllerObject = new $controller(self::$route) в классе Router помещает $route в данный __construct
        //echo '<b>Переменная $route в abstract class Controller</b>';
        $this->route = $route;
        $this->controller = $route['controller'];
        $this->model = $route['controller'];
        $this->view = $route['action'];
        $this->prefix = $route['prefix'];
    }

    public function getView()
    {
        $viewObject = new View($this->route, $this->layout, $this->view, $this->meta); // создаем объект класса View с заданными в конструкте свойствами
        $viewObject->render($this->data); //вызываем его метод
    }

    public function set($data) // данные методы можно использовать в дочерних классах для записи данных
    {
        $this->data = $data;
    }

    public function setMeta($title, $desc, $keywords)
    {
        $this->meta['title'] = $title;
        $this->meta['desc'] = $desc;
        $this->meta['keywords'] = $keywords;
    }

    public function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function loadView($view, $vars = []){
        extract($vars);
        require APP . "/views/{$this->prefix}{$this->controller}/{$view}.php";
        exit;
    }
}