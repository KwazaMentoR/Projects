<?php

use system\core\App;

function debug($arr){
    echo "<pre>" . print_r ($arr, true) . "</pre>";
}

function redirect($http = false){
    if($http){
        $redirect = $http;
    }else{
        $redirect = isset(App::$app->request->server['HTTP_REFERER']) ? App::$app->request->server['HTTP_REFERER'] : PATH;
    }
    header("Location: $redirect");
    exit;
}
