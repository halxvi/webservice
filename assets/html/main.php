<?php
    session_start();
    $db['host'] = "192.168.99.100:13306";
    $db["user"] = "root";
    $db["pass"] = "root";
    $db["dbname"] = "my_system";

    $errorMessage = "";
    $tasks="";
    $mokuhyo ="";

    $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8',$db['host'],$db['dbname']);

    try{
        $pdo = new PDO($dsn,$db['user'],$db['pass'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
        
        $stmt = $pdo->prepare("SELECT * FROM Users,Tasks WHERE User_Id = ? AND Users.User_Id = Tasks.Task_Id AND End_Flag != 1");
        $stmt->execute(array($_SESSION["ID"]));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $mokuhyo = sprintf("現在の目標は'%s'です\n",$row["Goal"]);
        if(isset($row["Task"])){
            preg_match("/[0-9０－９]+/",$row["Task"],$today_task_num); //Taskから正規表現で数字を抜く
            preg_match_all("/[^0-9]+/",$row["Task"],$today_task_stmt,PREG_SET_ORDER);//Taskから正規表現で数字以外を抜く
            $today_task_num[0] = ceil($today_task_num[0]/$row["Period"]); 
            if($tasks !=0){
                $tasks = sprintf("今日は'%s'をやりましょう",$today_task_stmt[0][0].$today_task_num[0].$today_task_stmt[1][0]);
                echo htmlspecialchars($tasks,ENT_QUOTES);
            }      
        }
    }catch(PDOException $e){
        $errorMessage = $e->getmessage();
        echo htmlspecialchars($errorMessage,ENT_QUOTES);
    }
    echo htmlspecialchars($mokuhyo,ENT_QUOTES);
    
    if(isset($_POST["end_task"])){
        $DateNow = (int)date("Ymd");
        $koushin = $DateNow+(int)$row["Task_Counter"]-(int)$row["Start_Date"];
        if($koushin == 0){
            try{
                $koushin_flag = (int)$row["Task_Counter"] + 1;
                $pdo = new PDO($dsn,$db['user'],$db['pass'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
                $stmt = $pdo->prepare("UPDATE Tasks SET Task_Counter =?");
                $stmt->execute(array($koushin_flag));
            }catch(PDOException $e){
                $errorMessage = $e->getmessage();
                echo htmlspecialchars($errorMessage,ENT_QUOTES);
            }
        }
        elseif($koushin >= 0){
            echo "今日の文は終わっている";
        }
    }
?>

<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
    body{
        background-color:lightblue
    }
    </style>
</head>
<body>
    <nav class="navbar navbar-static-top">
    <div class="container">
        <ul class="nav navbar-nav">
        <li class="active"><a href="main.php">HOME</a></li>
        <li><a href="set_goal.php">目標設定</a></li>
        </ul>
    </div>
    </nav>
    <div class="container">
        <div class="row">
            <h1　class="font-weight-light">メイン</h1>
            <div class="col-xs-12">
                <form method="POST">
                    <input type="submit" name="end_task" class="btn btn-primary" value="今日の分終了！">
                </form>
            </div>
        </div>
    </div>
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>