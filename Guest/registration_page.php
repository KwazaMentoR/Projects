<?php 
	error_reporting(-1);
	session_start(); 
	header('Content-Type: text/html; charset=utf-8');
	require_once 'func.php'; //подключаем функцию

		if(isset($_POST['submit'])){
			
			if(!empty($_POST['email']) && !empty($_POST['nickname']) && !empty($_POST['password'])){
				$direct = 'userdata.txt'; // создаем буферный файл, куда записываются все данные введенные
				if(file_exists($direct)){
					$data_str = get_data($direct); //Читает содержимое файла в строку
					if(!empty($data_str)){
						$data_arr = arr_data($data_str); //разбивает строку по разделителю  "\n***\n" и превращает в массив				
						foreach($data_arr as $data_str1){ // выводит значения массива по строкам
							$data_str2 = explode_data($data_str1); //разбивает строку по разделителю ' | ' и превращает в массив
							if($_POST['email'] == $data_str2[0] || $_POST['nickname'] == $data_str2[1]){ //проверка существует ли почта или логин
								$_SESSION['inf'] = 'Имя или email уже занято';
								$yes = 0;
								break;
							}else{						
								$_SESSION['inf'] = 'Вы успешно зарегистрировались';
								$yes = 1;
							}
						}
						if(isset($yes) && $yes){
							save_user($direct);// используем собственную функцию для записи данных о пользователе в файл
						}
					}else{
						save_user($direct);
						$_SESSION['inf'] = 'Вы успешно зарегистрировались';
					}

				}else{
					save_user($direct);
					$_SESSION['inf'] = 'Вы успешно зарегистрировались';
				}

	
			}else{
				$_SESSION['inf'] = 'Одно или несколько полей не заполнены';
			} 
			header("Location: {$_SERVER['PHP_SELF']}"); 
			//создает редирект на текущую страницу, более универсальный. Если имя страницы поменяется, то не нужно вручную переписывать
			exit;
		}
?>




<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Registration page</title>
</head>
<?php 
	if(isset($_SESSION['inf'])){
		echo $_SESSION['inf'];
		unset($_SESSION['inf']);
	}
?>
	<form action = "registration_page.php" method="post" enctype="multipart/form-data">
		<input  type = "text"   name = "email"       placeholder = "email">    <br>
		<input  type = "text"    name = "nickname"   placeholder = "nickname"> <br>
		<input  type = "text"    name = "password"   placeholder = "password"> <br>
		<button type = "submit"  name = "submit">Submit</button>
	</form>

<body>
</body>
</html>