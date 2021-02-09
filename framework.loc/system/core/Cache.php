<?php

namespace system\core;

use system\core\TSingleton;

class Cache
{
    use TSingleton;

    protected function getFile($key)
    {
        $file = CACHE . '/' . md5($key) . '.txt'; //хэширует данные и указывает путь
        return $file;
    }

    public function set($key, $data, $seconds = 3600)
    {
        if ($seconds){
            $content['data'] = $data;
            $content['end_time'] = time() + $seconds;
            if (file_put_contents(self::getFile($key), serialize($content))){ // Генерирует пригодное для хранения представление переменной и записывает данные в файл
                return true;
            }
        }
        return false;
    }

    public function get($key)
    {
        if(file_exists(self::getFile($key))){
            $content = unserialize(file_get_contents(self::getFile($key)));
            if (time() <= $content['end_time']){
                return $content['data']; // если контент не устарел, то вернем файл
            }
            unlink(self::getFile($key)); //удаляем файл
        }
        return false;
    }

    public function delete($key)
    {
        if(file_exists(self::getFile($key))){
            unlink(self::getFile($key)); //удаляем файл
        }
    }





}