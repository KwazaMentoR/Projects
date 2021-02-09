<?php require_once "func.php"?>
<form method="get" action="">
    <textarea name="words" cols="30" rows="5"></textarea>
    <input type="submit" value="Отсортировать">
</form>

<?php

//$str = 'яблоко стул машина компьютер школа арбуз пельмени ключ душ мангал квартира сырость ананас';

$str = $_GET['words'];
debug($str);
$words = preg_split('/\n+/', $str);
debug($words);
sort($words);

$values = [];
foreach($words as $word){
    $values[mb_substr($word, 0, 1)][]=$word;
}

foreach($values as $letter => $words) {
    echo '<strong>Слова на букву ' .mb_strtoupper($letter). ':</strong><br>';
    foreach($words as $word) {
        echo $word . '<br>';
    }
    echo '<br>';
}
?>