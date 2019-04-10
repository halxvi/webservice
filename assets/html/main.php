<?php
    session_start();
    $db['host'] = "192.168.99.100:13306";
    $db["user"] = "root";
    $db["pass"] = "root";
    $db["dbname"] = "my_system";

    $errorMessage = "";

    if(isset($_POST["making_goal"])){
        header("Location:set_goal.php");
        exit();
    }

    $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8',$db['host'],$db['dbname']);

    try{
        $pdo = new PDO($dsn,$db['user'],$db['pass'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
        
        $stmt = $pdo->prepare("SELECT * FROM Users,Tasks WHERE User_Id = ? AND Users.User_Id = Tasks.Task_Id");
        $stmt->execute(array($_SESSION["ID"]));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $mokuhyo = sprintf("現在の目標は%sです\n",$row["Goal"]);
        $tasks = sprintf("今日は%sをやりましょう",$row["Goal"]);

    }catch(PDOException $e){
        $errorMessage = $e->getmessage();
        echo htmlspecialchars($errorMessage,ENT_QUOTES);
    }
    echo htmlspecialchars($mokuhyo,ENT_QUOTES);
    echo htmlspecialchars($tasks,ENT_QUOTES);
?>

<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <form method="POST">
        <input type="submit" name="end_task" value="作業終了">
        <input type="submit" name="making_goal" value="目標作成">
    </form>
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>