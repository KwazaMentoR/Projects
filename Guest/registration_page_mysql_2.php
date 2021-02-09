<?php 
	error_reporting(-1);
	session_start(); 
	header('Content-Type: text/html; charset=utf-8');
	require_once 'func.php'; //подключаем функцию
	
	require_once 'connect.php';
		
	if(isset($_POST['submit'])){
		$_SESSION['inf'] = '';
		if(empty($_POST['email'])){
			$_SESSION['nickname'] = $_POST['nickname'];
			$_SESSION['inf'] .= '<li>Почта не заполнена</li>';
		}
		if(empty($_POST['nickname'])){
			$_SESSION['email'] = $_POST['email'];
			$_SESSION['inf'] .= '<li>Nickname не заполнен</li>';
		}
		if(empty($_POST['password'])){
			$_SESSION['nickname'] = $_POST['nickname'];
			$_SESSION['email'] = $_POST['email'];
			$_SESSION['inf'] .= '<li>Пароль не заполнен</li>';
		}
			
		if(!empty($_POST['email']) && !empty($_POST['nickname']) && !empty($_POST['password'])){
			$email = mysqli_real_escape_string($link, $_POST['email']);
			$nickname = mysqli_real_escape_string($link, $_POST['nickname']);
			$password = mysqli_real_escape_string($link, $_POST['password']);
			$query = "SELECT email, nickname FROM tabl_registration WHERE email = '$email' OR nickname = '$nickname' LIMIT 2";
			$res = mysqli_query($link, $query) or exit(mysqli_error($link));
			$res_arr = mysqli_fetch_all($res, MYSQLI_ASSOC);
			
			foreach($res_arr as $res_arr2){
				$res_inter = array_intersect($res_arr2, $_POST); //выявляет сходимость массивов
				
				if(isset($res_inter['email'])){
					$_SESSION['email'] = $_POST['email'];
					$_SESSION['nickname'] = $_POST['nickname'];
					$_SESSION['inf'] .= '<li>Почта уже занята</li>';
				}
				if(isset($res_inter['nickname'])){
					$_SESSION['email'] = $_POST['email'];
					$_SESSION['nickname'] = $_POST['nickname'];
					$_SESSION['inf'] .= '<li>Nickname уже занят</li>';
				}
			}

			if(!isset($res_inter)){
				$insert = "INSERT INTO `tabl_registration`(`email`, `nickname`, `password`) VALUES ('$email', '$nickname', '$password')";
				$res_insert = mysqli_query($link, $insert) or exit(mysqli_error($link)); // передает данные в базу	
				$_SESSION['inf'] = 'Вы успешно зарегистрировались';
			}
									
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

	<form action = "registration_page_mysql_2.php" method="post" enctype="multipart/form-data">
		<input type = "text" name = "email" placeholder = "email" value="<?php if(isset($_SESSION['email'])) echo htmlspecialchars($_SESSION['email']); ?>" > <br>
		<input type = "text" name = "nickname" placeholder = "nickname" value="<?php if(isset($_SESSION['nickname'])) echo  htmlspecialchars($_SESSION['nickname']); ?>"> <br>
		<input type = "text" name = "password" placeholder = "password"> <br>
		<button type = "submit" name = "submit">Submit</button>
	</form>
	<a href = "main_page.php?do=exit">Вернуться</a>
<?php if(isset($_SESSION['email']) or isset($_SESSION['nickname'])):?>
	<?php unset($_SESSION['email']); unset($_SESSION['nickname']);?>
<?php endif;?>

<body>
</body>
</html>