<?php

namespace system\core;

class ErrorHandler{

    public function __construct()
    {
        if(DEBUG){
            error_reporting(-1); // если true, то есть DEBUG 1, значит режим разработки и выводятся сообщения об ошибке
        }else{
            error_reporting(0); // не показывать ошибки
        }
        set_exception_handler([$this, 'exceptionHandler']);
        // Задает пользовательский обработчик исключений. Позволит назачить для обработки ошибок собственную функцию. Передается в виде массива
        // https://habr.com/ru/post/161483/   Про ошибки

        set_error_handler([$this, 'errorHandler']);
        /*
        Задает пользовательский обработчик ошибок. Работает для обычных ошибок
        В errorHandler $errno, $errstr, $errfile, $errline первые два обязательных аргумента,  остальные необяз. Назвать эти параметры можно как угодно
        Error - базовый класс для всех внутренних ошибок PHP.
        */

        ob_start(); // включаем буферизацию, что бы убрать ошибку error_reporting обрабатываемую fatalErrorHandler

        register_shutdown_function([$this, 'fatalErrorHandler']);
        // Регистрирует функцию, которая выполнится при завершении работы скрипта. Работает для фатальной ошибки
    }

    public function errorHandler($type, $message, $file, $line)
    {
        $err = ['type' => $type, 'message' => $message, 'file' => $file, 'line' => $line,];
        //var_dump($err);
        //debug($err);

        $this->logErrors($message, $file, $line);
        if (DEBUG || in_array($type, [E_USER_ERROR, E_RECOVERABLE_ERROR])){
            $this->displayError($type, $message, $file, $line);
        }
    }

    public function fatalErrorHandler()
    {
        $error = error_get_last(); //Получение информации о последней произошедшей ошибке
        if (!empty($error) && $error['type'] & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_WARNING)){
            // если не пусто сообщение об ошибке и при этом тип ошибки включает в себя перечисленные
            ob_end_clean(); // Очистить (стереть) буфер вывода и отключить буферизацию вывода
            $this->logErrors($error['message'], $error['file'], $error['line']);
            $this->displayError($error['type'], $error['message'], $error['file'], $error['line']);
        }else{
            ob_end_flush(); // Сбросить (отправить) буфер вывод и отключить буферизацию вывода.
        }
        //var_dump($error);
        //debug($error);
    }

    public function exceptionHandler($e)
       /*
       set_exception_handler перехватывает данные Error Object или Exception Object и помещает их в exceptionHandler. Тот логирует и выводит эти данные
       Exception — это базовый класс для всех исключений в PHP 5 и базовый класс для всех пользовательских исключений в PHP 7.
       getMessage и прочие - методы этого класса
       */
    {
        //var_dump($e);
        //debug($e); // в $e помещается Error Object или Exception Object

        /*
        getCode() передает int(0) при любой ошибке, где не задан код вручную, например throw new Exception('Страница не найдена', 404);
        Приходит всегда int, за исключением ошибок PDO. Они str
        Когда приходит значение, то проверяется значение int(0) или str, отправляется код ошибки 500. Иначе отправляется код пришедший $e->getCode()
        Код не отправляется если ранее выведен debug echo var_dump
        */

        $response = ($e->getCode() == 0 || is_string($e->getCode())) ? 500 : $e->getCode();
        $error = (is_string($e->getCode())) ? "Ошибка (str значение): " . $e->getCode() : get_class($e);
        if ($response != 404){
            $this->logErrors($e->getMessage(), $e->getFile(), $e->getLine());
        }
        $this->displayError($error, $e->getMessage(), $e->getFile(), $e->getLine(), $response);
    }

    protected function logErrors($message = '', $file = '', $line = '') // метод для логирования ошибок
    {
        //Отправляет сообщение об ошибке заданному обработчику ошибок

        error_log("[" . date('Y-m-d H:i:s') . "] Текст ошибки: {$message} | Файл: {$file} | Строка: {$line}\n=================\n", 3, ROOT . '/storage/errors.log');
    }

    protected function displayError($name, $message, $file, $line, $response = 500)
    {
        /*
        Метод для вывода ошибок.
        задаваемое имя ошибки $name, текст ошибки $message, файл в котором ошибка $file, строка в котором произошла ошибка $line
        и http код который должен быть отправлен браузеру $response.
        По дефолту $response установлен 500, для errorHandler он отправляется.
        Для exceptionHandler отправляется за счет $e->getCode().
        */
        http_response_code($response); //Получает или устанавливает код ответа HTTP. Отправит код переданный параметром $response.

        if (!DEBUG){
            if ($response == 404){
                require WWW . '/errors/404.php';
                exit;
            }
            require WWW . '/errors/prod.php'; // файл продакшн
        }else{
            require WWW . '/errors/dev.php'; // файл разработки
        }
    }

}