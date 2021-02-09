<?php

namespace app\controllers;

use app\models\Cart;
use system\core\Db;

class CartController extends AppController
{
    public function addAction() // вызывается с помощью js через ajax запрос
    {
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : null;
        $qty = !empty($_GET['qty']) ? (int)$_GET['qty'] : null;
        $mod_id = !empty($_GET['mod']) ? (int)$_GET['mod'] : null;
        $mod = null;
        if ($id){
            $product = Db::findWhere('product','id = ?', [$id]);
            if ($product) {
                foreach ($product as $prod){
                    $prodone = $prod;
                }
            }else{
                throw new \Exception("Продукт не найден");
            }
            if($mod_id){
                $mod = Db::findWhere('modification','id = ? AND product_id = ?', [$mod_id, $id]);
                if ($mod) {
                    foreach ($mod as $m){
                        $mod = $m;
                    }
                }else{
                    throw new \Exception("Модификация не найдена");
                }
            }
        }
        $cart = new Cart();
        $cart->addToCart($prodone, $qty, $mod);
        if ($this->isAjax()){ // если был произведен ассинхронный запрос через ajax, то выводится вид методом из Controller
            $this->loadView('cart_modal');
        }
        redirect();
    }

    public function showAction()
    {
        $this->loadView('cart_modal');
    }

    public function deleteAction()
    {
        $id = !empty($_GET['id']) ? $_GET['id'] : null;
        if (isset($_SESSION['cart'][$id])){
            $cart = new Cart();
            $cart->deleteItem($id);
        }
        if ($this->isAjax()){
            $this->loadView('cart_modal');
        }
        redirect();
    }
}