<?php

namespace app\models;

class ProductModel extends AppModel
{
    public function setRecentlyViewed($id) //создает куки просмотренных товаров если их еще нет
    {
        $recentlyViewed = $this->getAllRecentlyViewed();
        var_dump($this->getAllRecentlyViewed());
        if (!$recentlyViewed){
            setcookie('recentlyViewed', $id, time() + 3600*24, '/');
        }else{
            $recentlyViewed = explode('.', $recentlyViewed); //строковое значение превращается в массив
            if (!in_array($id, $recentlyViewed)){
                $recentlyViewed[] = $id; // дописываем значение id в массив для каждой открытой странички
                $recentlyViewed = implode('.', $recentlyViewed);
                setcookie('recentlyViewed', $recentlyViewed, time() + 3600*24, '/');
            };
        }
    }

    public function getRecentlyViewed()
    {
        if (!empty($_COOKIE['recentlyViewed'])){
            $recentlyViewed = $_COOKIE['recentlyViewed'];
            $recentlyViewed = explode('.', $recentlyViewed);
            return array_slice($recentlyViewed, -3); // выводится массив с 3 последними id.
        }
        return false;
    }

    public function getAllRecentlyViewed()
    {
        if (!empty($_COOKIE['recentlyViewed'])){
            return $_COOKIE['recentlyViewed'];
        }
        return false;
    }
}