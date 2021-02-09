<?php

namespace system\core\base;


class View
{
    public $route;
    public $controller;
    public $model;
    public $view;
    public $layout;
    public $prefix;
    public $data = [];
    public $meta = [];

    public function __construct($route, $layout = '', $view = '', $meta)
        // шаблон $layout, для того что бы отрендерить - сформировать страницу, html код. Шаблон это обертка вида - динамичной части
    {
        //debug($route);
        $this->route = $route;
        $this->controller = $route['controller'];
        $this->model = $route['controller'];
        $this->view = $view;
        $this->prefix = $route['prefix'];
        $this->meta = $meta;
        if($layout === false){ // если пустой шаблон, может быть передана пустая строка а она не false
            $this->layout = false;
        }else{
            $this->layout = $layout?: LAYOUT; // если передан шаблон, то мы возьмем его, если пустая строка, то значение константы из init LAYOUT = default
        }
    }

    public function render($data)
    {
        //echo '<b>Переменная $data в render($data)</b>';
        //debug($data);

        if (is_array($data)){ // извлекаем данные обычно записанные через set(compact('data')).
            extract($data); // извлекает ключи из массива или объекта
        }

        $viewFile = APP . "/views/{$this->prefix}{$this->controller}/{$this->view}.php"; // указываем путь к виду
        //echo '<b>Переменная $viewFile в class View</b>';
        //debug($viewFile);

        if (is_file($viewFile)){ // если файл существует, то подключаем его
            ob_start();
            require_once $viewFile;
            $content = ob_get_clean(); // все что находится после запуска буфера ob_start и до ob_get_clean записывается в буфер а последняя функция выводит данные и очищает буфер. Буферизированные данные теперь записаны в перменную $content. Буфер не дает выводится документу в браузер
            //echo $content;
        }else{
            throw new \Exception("Не найден вид $viewFile", 500);
        }

        if(false !== $this->layout){
            $layoutFile = APP . "/views/layouts/{$this->layout}.php";
            //echo '<b>Переменная $layoutFile в class View</b>';
            //debug($layoutFile);
            if(is_file($layoutFile)){
                require_once $layoutFile;
            }else{
                throw new \Exception("На найден шаблон {$this->layout}", 500);
            }
        }
    }

    public function getMeta()
    {
        $output = '<title>' . $this->meta['title'] . '</title>' . PHP_EOL; //PHP_EOL перенос строк
        $output .= '<meta name="description" content="' . $this->meta['desc'] . '">' . PHP_EOL;
        $output .= '<meta name="keywords" content="' . $this->meta['keywords'] . '">' . PHP_EOL;
        return $output;
    }
}