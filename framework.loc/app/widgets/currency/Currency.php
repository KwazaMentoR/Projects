<?php
namespace app\widgets\currency;
use system\core\App;
use system\core\base\Model;
use system\core\Db;

class Currency // у преподв в 5 уроке используются статические классы и используется редбин в этих классах и объект Currency не создается
{
    protected static $tpl;
    protected static $currencies;
    protected static $currency;

    public function getCurrencies()
    {
        //return $this->db->query("SELECT code, title, symbol_left, symbol_right, value, base FROM currency ORDER BY base DESC");
        return Db::findAssoc("SELECT code, title, symbol_left, symbol_right, value, base FROM currency ORDER BY base DESC");
    }

    public function getCurrency($currencies)
    {
        if (isset($_COOKIE['currency']) && array_key_exists($_COOKIE['currency'], $currencies)){ //сверяет значение куки с ключом массива
            $key = $_COOKIE['currency'];
        }else{
            $key = key($currencies); //берет первый ключ массива, который устанавливается по дефолту в базе данных первым
        }
        $currency = $currencies[$key];
        return $currency;
    }

    public static function getHtml()
    {
        self::$tpl = __DIR__ . '/currency_tpl/currency.php';
        self::$currencies = App::$app->getProperty('currencies');
        self::$currency = App::$app->getProperty('currency');
        ob_start();
        require_once self::$tpl;
        return ob_get_clean();
    }



}