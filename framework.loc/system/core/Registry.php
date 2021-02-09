<?php
/**
 * Created by PhpStorm.
 * User: BeastMachine
 * Date: 02.05.2019
 * Time: 14:25
 */

namespace system\core;


class Registry
{
    use TSingleton; // подключаем TSingleton, пракстически как копирование работает

    public static $properties = [];

    public function setProperty($name, $value)
    {
        if (!isset(self::$properties[$name])) {  //если свойство не существует, то даем значение ключам объекта
            self::$properties[$name] = $value;
        }
    }

    public function getProperty($name)
    {
        if (isset(self::$properties[$name])) { // если существует свойство, то возвращает
            return self::$properties[$name];
        }
        return null;
    }

    public function __get($name)
        // позволяет обращаться напрямую к свойству объекта, например не _getProperty('request')->get['test'] а просто request->get['test']
    {
        if (isset(self::$properties[$name])) { // если существует свойство, то возвращает
            return self::$properties[$name];
        }
        return null;
    }

    public function getProperties()
    {
        return self::$properties;
    }

}