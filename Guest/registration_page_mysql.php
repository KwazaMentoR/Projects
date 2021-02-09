<?php 
	error_reporting(-1);
	session_start(); 
	header('Content-Type: text/html; charset=utf-8');
	require_once 'func.php'; //подключаем функцию
	
	$link = @mysqli_connect('127.0.0.1:3306', 'root', '', 'registration') or exit('Ошибка соединения с БД'); //@ не выводит ошибку которую не следуют видеть пользователю
	//var_dump (mysqli_connect_error());
	mysqli_set_charset($link, "utf8") or exit('Неверная кодировка');
		
	if(isset($_POST['submit'])){
			
		if(!empty($_POST['email']) && !empty($_POST['nickname']) && !empty($_POST['password'])){
			$_SESSION['inf'] = '';
			$email = mysqli_real_escape_string($link, $_POST['email']);
			$nickname = mysqli_real_escape_string($link, $_POST['nickname']);
			$password = mysqli_real_escape_string($link, $_POST['password']);
			$query = "SELECT email FROM tabl_registration WHERE email = '$email' LIMIT 1"; // следить за знаками, долго не мог понять ошибку, не нужна была "," в "password, FROM"
			$res = mysqli_query($link, $query) or exit(mysqli_error($link));
			$query2 = "SELECT nickname FROM tabl_registration WHERE nickname = '$nickname' LIMIT 1";
			$res2 = mysqli_query($link, $query2) or exit(mysqli_error($link));
			//echo "<pre>"; print_r ($res); echo "</pre>";
			//$find_arr = find_value($link, $find); // выводит ассоциативный массив ряда с заданными полями по запросу из БД
			//$a = mysqli_query($link, $find) or exit(mysqli_error($link));
			//$b = mysqli_fetch_assoc($a);
			//echo "<pre>"; print_r ($find_arr); echo "</pre>";
			//foreach(find_value($link, $find) as $find_str){
			//	echo "<pre>"; print_r ($find_str); echo "</pre>";
			//}
			if($res->num_rows > 0){ // или mysqli_num_rows($res) > 0
				$_SESSION['inf'] .= '<li>Почта уже занята</li>';
			}
			//echo $res2->num_rows;
			if($res2->num_rows > 0){
				$_SESSION['inf'] .= '<li>Логин уже занят</li>';
			}
			if(mysqli_num_rows($res) == 0 && mysqli_num_rows($res2) == 0){
				$insert = "INSERT INTO `tabl_registration`(`email`, `nickname`, `password`) VALUES ('$email', '$nickname', '$password')";
				$res_insert = mysqli_query($link, $insert) or exit(mysqli_error($link)); // передает данные в базу	
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

<?php if(isset($_SESSION['inf'])):?>
	<ul>
		<?=$_SESSION['inf']?>
	</ul>
	<?php unset($_SESSION['inf'])?>
<?php endif;?>

	<form action = "registration_page_mysql.php" method="post" enctype="multipart/form-data">
		<input  type = "text"   name = "email"       placeholder = "email">    <br>
		<input  type = "text"    name = "nickname"   placeholder = "nickname"> <br>
		<input  type = "text"    name = "password"   placeholder = "password"> <br>
		<button type = "submit"  name = "submit">Submit</button>
	</form>

<body>
</body>
</html>