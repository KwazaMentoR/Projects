<?php

namespace app\controllers;

use app\models\Breadcrumbs;
use app\models\ProductModel;
use system\core\Db;

class ProductController extends AppController
{
    public function viewAction()
    {
        $prodone = '';
        //debug($this->route); //данные с controller action и alias
        $alias = $this->route['alias']; // $route из класса Router передан в Controller
        $product = Db::findWhere('product', "alias = ? AND status = '1'", [$alias]); //данные о продукте
        if ($product) {
            foreach ($product as $prod){
                $prodone = $prod;
            }
        }else{
            throw new \Exception(" Продукт $alias не найден");
        }

        //связанные товары
        $related = Db::query("SELECT * FROM related_product JOIN product ON product.id = related_product.related_id WHERE related_product.product_id = ?", [$prodone['id']]);
        // debug($related); связанные товары
        // объединяет таблицы и оставляет только те массивы, где id = related_id
        // а потом выбирает те массивы, где product_id = prodone['id']
        // Таким образом выводятся  сопряженные товары

        //галерея
        $gallery = Db::findWhere('gallery', 'product_id = ?', [$prodone['id']]); //вставляет изображения товара из бд
        //debug($gallery);

        //запись в куки запрошенного товара
        $p_model = new ProductModel();
        $p_model->setRecentlyViewed($prodone['id']);

        //просмотренные товары
        $r_viewed = $p_model->getRecentlyViewed(); // дает куки всех просмотренных товаров
        //debug($r_viewed);
        $recentlyViewed = null;
        if ($r_viewed){ // 3 последние просмотренные товара
            $recentlyViewed = Db::findWhere('product', 'id IN (' . Db::genSlots($r_viewed) . ') LIMIT 3', $r_viewed);
        }

        // хлебные крошки
        $breadcrumbs = Breadcrumbs::getBreadCrumbs($prodone['category_id'], $prodone['title']);

        // mods
        $mods = Db::findWhere('modification', 'product_id = ?', [$prodone['id']]); // выводит модификаторы с задействованием main.js где

        $this->setMeta($prodone['title'], $prodone['description'], $prodone['keywords']);
        $this->set(compact('prodone', 'related', 'gallery', 'recentlyViewed', 'breadcrumbs', 'mods'));
    }
}