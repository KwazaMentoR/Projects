<?php

namespace system\core;

class Router
/*вся фишка - создаем классы и методы классов по данным, входящим из строки браузера. Например http://framework.loc/page/view
 делаем класс PageController с методом viewAction.
 */
{
    protected static $routes = []; //здесь будет хранится таблица маршрутов
    protected static $route = []; //здесь хранится текущий маршрут

    public static function add($regexp, $route = [])  // записывает правила в таблицу маршрутов. Шаблон регулярного выражения и шаблон соответствия
    {
        self::$routes[$regexp] = $route;
    }

    public static function getRoutes()
    {
        return self::$routes;
    }

    public static function getRoute()
    {
        return self::$route;
    }

    public static function  dispatch($url)
    {
        //echo '<b>Метод removeQueryString</b>';
        //var_dump($url);
        $url = self::removeQueryString($url);
        //var_dump($url);
        if (self::matchRoute($url)){
            $controller = 'app\controllers\\' . self::$route['prefix'] . self::$route['controller'] . 'Controller';
            // записывает путь класса app\controllers\pageController, но должно быть PageController, а если page-newController то PageNewController. Для этого создадим метод upperCamelCase
            //echo '<b>Переменная $controller</b>';
            //debug($controller);
            //debug(self::$route);
            if (class_exists($controller)){ //если класс существует, то создадим его объект
                $controllerObject = new $controller(self::$route); // в конструктор класса заносятся данные текущего маршрута
                $action = self::lowerCamelCase(self::$route['action']) . 'Action'; // создаем метод класса контроллера например indexAction
                if (method_exists($controllerObject, $action)){ // если метод существет, то вызываем этот метод
                    //echo '<b>Переменная $action</b>';
                    //debug($action);
                    $controllerObject->$action(); // вызывается метод например indexAction
                    $controllerObject->getView();
                    /* вызывается метод класса Controller который создает класс View передает данные в его конструкт и
                    вызывает метод render для рендеринга страницы */
                }else{
                    throw new \Exception("Метод $controller::$action не найден", 404);
                }
            }else{
                throw new \Exception("Контроллер $controller не найден", 404);
            }
        }else{
            throw new \Exception('Страница не найдена', 404);
        }
    }

    public static function  matchRoute($url)
    {
        foreach (self::$routes as $pattern => $route){ // извлекаем Шаблон регулярного выражения и шаблон соответствия
            //echo '<b>Переменная $route</b>';
            //debug($route);
            if (preg_match("#{$pattern}#", $url, $matches)){
                //функцией preg_match Выполняем проверку строки браузера $query на соответствие регулярному выражению и записываем в $matches
                //echo '<b>Переменная $matches</b>';
                //debug($matches);
                foreach ($matches as $k => $v) {  // пересоздаем массив выкидывая числовые значения ключей
                    if (is_string($k)){
                        $route[$k] = $v;
                        // Для главной страницы $matches пустой массив и соответственно остаются [controller] => Main [action] => index [prefix] => admin
                    }
                }
                if (empty($route['action'])){
                    $route['action'] = 'index';
                }
                if (!isset($route['prefix'])){
                    $route['prefix'] = '';
                }else{
                    $route['prefix'] .= '\\';
                }
                $route['controller'] = self::upperCamelCase($route['controller']); // превращаем page-newController в PageNewController
                self::$route = $route; // записываем в текущий маршрут значение строки пришедшее из $query в matchRoute($url)
                //echo '<b>Переменная $route</b>';
                //debug($route);
                return true;
            }
        }
        return false;
    }

    protected static function upperCamelCase($name)
    {
        $name = ucwords(str_replace('-', ' ', $name));
        // str_replace меняет - на пробел что бы получить два слова, ucwords делает заглавную букву в каждом слове
        $name = str_replace(' ', '', $name); // убирает пробел между словами
        //echo 'Переменная $name';
        //debug($name);
        return $name;
    }

    protected static function lowerCamelCase($name)
    {
        return lcfirst(self::upperCamelCase($name)); // делаем первую букву строчной pageNewController
    }

    public static function removeQueryString($url) // метод убирает из $url GET параметры например http://framework.loc/page/view/?id=1 оставит page/view
    {
        if ($url){
            $params = explode('&', str_replace('amp;', '', $url), 2); // разбивает строку по разделителю и помещает в массив
            //debug($params);
            if (strpos($params['0'], '=') === false){ // ещет в ключе = и если есть, то выводит пустую строку
                return rtrim($params['0'], '/');
            }else{
                return '';
            }
        }
    }


}