<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php echo $this->getMeta(); // все шаблоны теперь относятся к классу View и могут напрямую использовать его методы?>
</head>
<body>

<h2>Шаблон DEFAULT</h2>

<?php //echo $content; // подключает $viewFile вид?>
<?= $content; //так же выводит как и выше?>


</body>
</html>