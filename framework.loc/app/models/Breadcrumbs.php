<?php

namespace app\models;

use system\core\App;

class Breadcrumbs
{
    public static function getBreadCrumbs($category_id, $name = '')
    {
        $cats = App::$app->getProperty('cats');
        $breadcrumbs_array = self::getPart($cats, $category_id);
        $breadcrumbs = "<li><a href='" . PATH . "'>Главная</a></li>";
        if ($breadcrumbs_array){
            foreach ($breadcrumbs_array as $alias => $title){
                $breadcrumbs .= "<li><a href='" . PATH . "/category/{$alias}'>$title</a></li>";
            }
        }
        if ($name){
            $breadcrumbs .= "<li>$name</li>";
        }
        return $breadcrumbs;
    }

    public static function getPart($cats, $id)
    {
        if (!$id) return false;
        $breadcrumbs = [];
        foreach ($cats as $cat){
            if (isset($cats[$id])) {
                $breadcrumbs[$cats[$id]['alias']] = $cats[$id]['title'];
                $id = $cats[$id]['parent_id'];
            }else break;
        }
        return array_reverse($breadcrumbs);
    }


}