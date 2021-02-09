<?php
use app\widgets\currency\Currency;
use app\widgets\menu\Menu;
use system\core\Db;

?>
<!--A Design by W3layouts
Author: W3layout
Author URL: http://w3layouts.com
License: Creative Commons Attribution 3.0 Unported
License URL: http://creativecommons.org/licenses/by/3.0/
-->
<!DOCTYPE html>
<html>
<head>
    <base href="/"> <!--Для того что бы прописать ко всем ссылкам путь к корню-->
    <?=$this->getMeta();?>
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <!--Custom-Theme-files-->
    <!--theme-style-->
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
    <!--//theme-style-->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="css/Chart.min.css" rel="stylesheet" />
</head>

</body>

<?php debug(Db::$queries); ?>
<div class="logo">
    <a href="<?= PATH; ?>"><h1>Bot</h1></a>
    <h5><a href="bot" class="bot">Торговый бот</a></h5>
</div>

<div class="content">
    <?php //debug($_SESSION);?>
    <?= $content; // подключаемый вид из render View?>
</div>

<body>
<script src="js/Chart.min.js"></script>
<script src="js/jquery-1.11.0.min.js"></script>
<script src="js/jquery-1.12.4.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>

<!--End-slider-script-->

</html>