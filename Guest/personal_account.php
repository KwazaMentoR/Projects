<?php 
	if(isset($_GET['do']) && $_GET['do'] == 'exit'){
		setcookie('nickname', '', time() - 3600); //важно оставить пустыми '' что бы удалилась кука
		header("Location: main_page.php"); 
		exit;
	}
	if(isset($_POST['submit'])){
		$_SESSION['inf'] = '';
		if(empty($_POST['nickname'])){
			$_SESSION['inf'] .= '<li>Nickname не заполнен</li>';
		}
		if(empty($_POST['password'])){
			$_SESSION['nickname'] = $_POST['nickname'];
			$_SESSION['inf'] .= '<li>Пароль не заполнен</li>';
		}
			
		if(!empty($_POST['nickname']) && !empty($_POST['password'])){
			$nickname = mysqli_real_escape_string($link, $_POST['nickname']);
			$password = mysqli_real_escape_string($link, $_POST['password']);
			$query = "SELECT nickname, password FROM tabl_registration WHERE nickname = '$nickname' AND password = '$password' LIMIT 1";
			$res = mysqli_query($link, $query) or exit(mysqli_error($link));
			$res_arr = mysqli_fetch_all($res, MYSQLI_ASSOC);
			
			foreach($res_arr as $res_arr2){
				$res_inter = array_intersect($res_arr2, $_POST);
			}
			//echo "<pre>"; print_r ($res_inter); echo "</pre>";
			
			if(isset($res_inter)){
				setcookie('nickname', $_POST['nickname'], time()+3600*24, '/');
				$_SESSION['inf'] = 'Вы успешно зарегистрировались';
			}else{
				$_SESSION['nickname'] = $_POST['nickname'];
				$_SESSION['inf'] .= '<li>Пароль неверный</li>';
			}
								
		}
		
		header("Location: main_page.php"); 
		//создает редирект на текущую страницу, более универсальный. Если имя страницы поменяется, то не нужно вручную переписывать
		exit; 
	}
?>
