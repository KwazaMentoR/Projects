<?php

namespace app\widgets\menu;

use system\core\App;
use system\core\Cache;
use system\core\Db;

class Menu
{
    protected $data;
    protected $tree;
    protected $menuHtml;
    protected $tpl;
    protected $container = 'ul';
    protected $class = '';
    protected $table = 'category';
    protected $cache = 3600;
    protected $cacheKey = 'framework_menu';
    protected $attrs = [];
    protected $prepend = '';

    public function __construct($options = [])
    {
        $this->tpl = __DIR__ . 'menu_tpl/menu.php';
        $this->getOptions($options); //метод для передачи значений в свойства класса. Меняет tpl
        $this->run();
        //debug($this->attrs);

        // Если в $cache = Cache::instance() не содержится ничего по ключу $this->cacheKey, то в $data записывает 'cats' из бд category
        // который был записан в классе AppController через cacheCategory.
        // Из $data получает дерево $tree(зависимости массивов через parent_id).
        // Подключаясь через путь $this->tpl в $this->menuHtml записывается в строку наполнение html кода.
        // Далее если $this->cache не 0, то в $cache = Cache::instance() по ключу $this->cacheKey записывается $this->menuHtml
    }

    protected function getOptions($options)
    {
        foreach ($options as $k => $v){
            if (property_exists($this, $k)){ //существует ли имя свойства в указанном классе
                $this->$k = $v; // перезаписывает значение проверенной переменной класса
            }
        }
    }

    protected function run()
    {
        $cache = Cache::instance();
        $this->menuHtml = $cache->get($this->cacheKey);

        if (!$this->menuHtml){
            $this->data = App::$app->getProperty('cats'); //если в кэш не содержится framework_menu, то в $data записываем 'cats' из кэша
            if(!$this->data){
                $this->data = $cats = Db::findAssoc("SELECT * FROM {$this->table}");
            }
            $this->tree = $this->getTree();
            $this->menuHtml = $this->getMenuHtml($this->tree);
            if ($this->cache){
                $cache->set($this->cacheKey, $this->menuHtml, $this->cache);
            }
        }
        $this->output();
    }

    protected function output()
    {
        $attrs = '';
        if (!empty ($this->attrs)){
            foreach ($this->attrs as $k => $v){
                $attrs .= " $k='$v' "; // дополнительные атрибуты типа style = '' и т.д.
            }
        }
        echo "<{$this->container} class = '{$this->class}' $attrs >";
            echo $this->menuHtml;
        echo "</{$this->container}>";
    }

    protected function getTree()
    {
        $tree = [];
        $data = $this->data;
        foreach ($data as $id => &$node) {
            // в данном случае & указывает на оригинальные элементы массива, которые поле изменения останутся в $data.
            if (!$node['parent_id']){ //проверяются все элементы массива с parent_id = 0
                $tree[$id] = &$node;
            }else{
                $data[$node['parent_id']]['childs'][$id] = &$node;
            }
        }
        return $tree;
    }

    protected function getMenuHtml($tree, $tab = '')
    {
        $str = '';
        foreach ($tree as $id => $category) {
            $str .= $this->catToTemplate($category, $tab, $id);
        }
        return $str;
    }

    protected function catToTemplate($category, $tab, $id)
    {
        ob_start();
        require $this->tpl; // require_once не использовать, потому что не будет работать, 1 раз подключит и все.
        return ob_get_clean();

    }
}