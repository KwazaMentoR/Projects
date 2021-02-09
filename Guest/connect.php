<?php 
	$link = @mysqli_connect('127.0.0.1:3306', 'root', '', 'registration') or exit('Ошибка соединения с БД'); //@ не выводит ошибку которую не следуют видеть пользователю
	//var_dump (mysqli_connect_error());
	mysqli_set_charset($link, "utf8") or exit('Неверная кодировка');