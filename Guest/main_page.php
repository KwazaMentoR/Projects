<?php 
	error_reporting(-1);
	session_start(); 
	header('Content-Type: text/html; charset=utf-8');
	require_once 'connect.php';
	require_once 'func.php'; //подключаем функции
	require_once 'personal_account.php';
?>


<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>MelangerBegin</title>
<link rel="stylesheet" type="text/css" href="Melanger.css"/>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="Script/jparallax-master/js/jquery.parallax.min.js"></script>
</head>

<body>
<header class="Header">Здесь располагается содержимое  class "Header"</header>
<script type="text/javascript">
jQuery( '.parallax-layer' ).parallax( options );
	</script>

<div class="pers_account">
	<?php if(isset($_SESSION['inf'])):?>
		<ul>
            <?=$_SESSION['inf']?>
		</ul>
		<?php unset($_SESSION['inf'])?>
	<?php endif;?>
	<?php if(!isset($_COOKIE['nickname'])):?>
		<a href="registration_page_mysql_2.php">Регистрация</a>
		<form action = "main_page.php" method="post" enctype="multipart/form-data">
			<input type = "text" name = "nickname" placeholder = "nickname" value="<?php if(isset($_SESSION['nickname'])): echo  htmlspecialchars($_SESSION['nickname']);   endif; ?>"> <br>
			<input type = "text" name = "password" placeholder = "password"> <br>
			<button type = "submit" name = "submit">Submit</button>
		</form>
	<?php else: echo 'вы зарегестрированы';?>
        <a href="cabinet.php">Перейти в личный кабинет</a>
		<a href = "main_page.php?do=exit">Выйти</a>
	<?php endif;?>
	<?php if(isset($_SESSION['nickname'])) :?>
		<?php unset($_SESSION['nickname']); ?>
	<?php endif;?>
</div>

<main class="MainContent">
	<div class="section1">
		<div class="left_capture">
        <img src="del.png" width="800" alt=""/> </div>
		<div class="center_capture">
          <video controls width="100%">
          	<source src="Clip 03.mp4" type="video/mp4">
          </video>
        </div>
		<div class="big_words">
		  <h1 class="vash">Ваш</h1><h1 class="product">Продукт</h1>
    	</div>
   	  <div class="gate1">Узнать больше</div>
	</div>
	<div class="section2">
        <div class="left_capture">
            <img src="del.png" width="800" alt=""/> 
        </div>
		<div class="center_capture">
            <div class="video">
                <video controls width="100%">
                	<source src="Clip 03.mp4" type="video/mp4">
                </video>
            </div>
        	<div class="vash1">Ваш</div>	
		    <div class="product1">Продукт</div>
        </div>
   	  	<div class="gate">Узнать больше</div>
    </div>
</main>
</body>
</html>

