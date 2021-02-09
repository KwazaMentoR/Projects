<?php

use system\core\Router;

Router::add('^product/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Product', 'action' => 'view']);
// [a-z0-9-]разрешаются не только буквы и тире но и цифры

//default routes
Router::add('^admin$', ['controller' => 'Main', 'action' => 'index', 'prefix' => 'admin']); // маршруты для админки
Router::add('^admin/?(?P<controller>[a-z-]+)/?(?P<action>[a-z-]+)?$', ['prefix' => 'admin']);

Router::add('^$', ['controller' => 'Main', 'action' => 'index']); // соответствует пустой строке, то еть главной странице сайта в $query
Router::add('^(?P<controller>[a-z-]+)/?(?P<action>[a-z-]+)?$');