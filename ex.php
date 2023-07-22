<?php
 
 
     
    $conn = new PDO("mysql:host=localhost;dbname=cms;charset=utf8;", "myuser", 12345);
     
    $categoryId = 3;
    $active = 1;
    $numRows = 5;  
    $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) AS publicationDate FROM articles WHERE categoryId = :categoryId AND active = :active ORDER BY publicationDate DESC LIMIT :numRows";
     
    $st = $conn->prepare($sql);      
 
    $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
    if ($categoryId)
        $st->bindValue( ":categoryId", $categoryId, PDO::PARAM_INT);
    if ($active)
        $st->bindValue( ":active", $active, PDO::PARAM_INT);
     
    echo $sql . "<br>". "<br>";
     
    $st->execute(); // выполняем запрос к базе данных
 
    while ($result = $st->fetch(PDO::FETCH_ASSOC)) {
        echo '<pre>';
        print_r($result);
        echo '</pre>';
    }