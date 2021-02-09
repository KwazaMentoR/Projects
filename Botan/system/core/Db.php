<?php

namespace system\core;

class Db //для подключения к базе данных
{
    use TSingleton; // используем что бы потом подключить один раз

    protected static $connection = null;
    protected static $statement = null;
    public static $countSql = 0; // для создания счетчика запросов
    public static $count = 0;
    public static $queries = []; // для создания списка запросов

/*
        require_once LIBS. '/rb.php'; // подключение ORM RedBeanPHP
        \R::setup($db['dsn'], $db['user'], $db['pass']);

        if( !\R::testConnection()){
            throw new \Exception("Нет соединения с БД", 500);
        }
*/

    public function __construct()
    {
        self::$count++;
        $db = require_once CONF . '/config_db.php';

        // https://www.php.net/manual/ru/ref.pdo-mysql.connection.php  про кодировки в зависимости от версий

        $dsn = 'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'];
        $options =
            [ // задаем дополнительные опции по умолчанию для подключния PDO::setAttribute
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                //\PDO::ATTR_PERSISTENT => true, // постоянное соединение
                //\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ];

        if( version_compare(PHP_VERSION, '5.3.6', '<') ){
            if( defined('PDO::MYSQL_ATTR_INIT_COMMAND') ){
                $options[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $db['charset'];
            }
        }else{
            $dsn .= ';charset=' . $db['charset'];
        }

        self::$connection = new \PDO($dsn, $db['user'], $db['pass'], $options); // создаем класс PDO Object

        if( version_compare(PHP_VERSION, '5.3.6', '<') && !defined('PDO::MYSQL_ATTR_INIT_COMMAND') ){
            $sql = 'SET NAMES ' . $db['charset'];
            self::$connection->exec($sql);
        }
    }

    public static function myExecute($sql, $params = [])
    {
        self::$countSql++;
        self::$queries[] = $sql;

        self::$statement = self::$connection->prepare($sql);;  // вызывая свойство создается объект PDOStatement Object
        return self::$statement->execute($params); // свойство объекта PDOStatement Object
    }

    public static function query($sql, $params = []) //params передает подготовленный запрос и вставляет переданные данные на выходе вместо ? заданного в $sql
    {
        self::$countSql++;
        self::$queries[] = $sql;

        self::$statement = self::$connection->prepare($sql);
        $result = self::$statement->execute($params);
        if ($result !== false){
            return self::$statement->fetchAll(\PDO::FETCH_ASSOC);
        }
        return [];
    }

    public static function genSlots($data)  //генерирует плэйсхолдеры(ззнаки вопроса) для подготовленного запроса
    {
        $holders = implode(',', array_fill(0, count($data), '?'));
        return $holders;
    }

    public static function findAssoc($sql, $params = []) //работает как query только имя массив имеет по значению первого ключа массива
    {
        $rows  = self::query( $sql, $params );

        if ( !$rows ) {
            return array();
        }

        $assoc = array();

        foreach ( $rows as $row ) {
            if ( empty( $row ) ) continue;
            $keys = array_keys($row); //создает массив из ключей
            $key = array_shift($row);
            // извлекает значение первого ключа массива и записывает в $key и $row перезаписывается и остается без первого ключа
            switch (count($row)){
                case 0:
                    $value = $key;
                    //$value[$keys[0]] = $key;
                    break;
                default:
                    $value = $row;
                    $value[$keys[0]] = $key;
            }
            $assoc[$key] = $value;
        }
        return $assoc;
    }

    public static function findAll($table, $limit = false) //извлекает все данные
    {
        if ($limit){
            $sql = "SELECT * FROM $table LIMIT $limit";
            return self::query($sql);
        }else{
            $sql = "SELECT * FROM $table";
            return self::query($sql);
        }
    }

    public static function findWhere($table, $field, $plchold = []) //извлекает заданные данные
    {
        $sql = "SELECT * FROM $table WHERE $field"; //защита от sql инъекций ?
        //debug($sql);
        return self::query($sql, $plchold);
    }

    public static function findLike($table, $str, $field)
    {
        $sql = "SELECT * FROM $table WHERE $field LIKE ?";
        return self::query($sql, ['%' . $str . '%']);
    }

    public static function insertPostData($table, $data, $fields) //какие значения вставить $data в какие поля $fields
    {
        $set = '';
        //debug($data);
        $holders = self::genSlots($data);
        //debug($holders);
        $fields_arr = explode(',', $fields);
        //debug($fields_arr);
        foreach ($fields_arr as $field) { //проверка, существует ли переданная информация в массиве POST
            $field = trim($field);
            //debug($field);
            if (isset(App::$app->request->post[$field])) {
                $set .= "$field,";
                //$set .= "`".str_replace("`","``",$field)."`". ",";
                //$set .= "`" . $field . "`" . ",";
            }
        }
        $set = rtrim($set, ",");
        //debug($test);
        $sql = "INSERT INTO $table ($set) VALUES ($holders)";
        //debug($sql);
        return self::myExecute($sql, $data); //[$data]
    }


    public static function insertData($table, $data, $fields)
    {
        $holders = self::genSlots($data);
        $sql = "INSERT INTO $table ($fields) VALUES ($holders)";
        //debug($sql);
        return self::myExecute($sql, $data);
    }


    public function __destruct()
    {
        self::$connection = null;
    }
}