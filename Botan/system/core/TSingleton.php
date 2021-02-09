<?php

namespace system\core;

trait TSingleton
{
    private static $insatance; //создаем приватное статическое свойство. Заполняем его объектом, если его там нет

    public static function instance()
    {
        if(self::$insatance === null){  // если свойство пусто, то мы в него положим объект
            self::$insatance = new self;
        }
        return self::$insatance;
    }
}