<?php

	function debug($name){
		echo "<pre>"; print_r ($name); echo "</pre>";
	}

	function save_user($direct){
		$str = $_POST['email'] . ' | ' . $_POST['nickname'] . ' | ' . $_POST['password'] . ' | ' . date('Y-m-d H:m') . "\n***\n";
		file_put_contents($direct, $str, FILE_APPEND); // можно имя файла указать константой, что бы не менять вручную, если имя поменяется
	}
	

	function get_data($direct){
		return file_get_contents($direct);
	}

	function arr_data($data_str){
		$data = explode("\n***\n", $data_str);
		array_pop($data);
		return array_reverse($data);
	}

	function explode_data($data_str1){
		$data = explode(' | ', $data_str1);
		return $data;
	}

	function find_value($link, $find){
		$a = mysqli_query($link, $find) or exit(mysqli_error($link));
		$b = mysqli_fetch_assoc($a);
		return $b; // ,без этой строки не работает, выводит значение
	}
		
	/*
	$str2 = [
		'email' => $_POST['email'],
		'nickname' => $_POST['nickname'],
		'password' => $_POST['password'],
		'date' => date('Y-m-d H:m'),
		];
	function save_user2(){
		file_put_contents('userdatamassive.txt', $str2, FILE_APPEND);
	}
	*/
?>
