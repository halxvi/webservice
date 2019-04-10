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
        
        $stmt = $pdo->prepare("SELECT * FROM Users,Tasks WHERE User_Id = ? AND Users.User_Id = Tasks.Task_Id AND End_Flag != 1");
        $stmt->execute(array($_SESSION["ID"]));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $mokuhyo = sprintf("現在の目標は'%s'です\n",$row["Goal"]);
        preg_match("/[0-9０－９]+/",$row["Task"],$today_task_num); //Taskから正規表現で数字を抜く
        preg_match_all("/[^0-9]+/",$row["Task"],$today_task_stmt,PREG_SET_ORDER);//Taskから正規表現で数字以外を抜く
        $today_task_num[0] = ceil($today_task_num[0]/$row["Period"]); 
        $tasks = sprintf("今日は'%s'をやりましょう",$today_task_stmt[0][0].$today_task_num[0].$today_task_stmt[1][0]);

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
        <input type="submit" name="end_task" value="今日の分終了！">
        <input type="submit" name="making_goal" value="目標作成">
    </form>
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>