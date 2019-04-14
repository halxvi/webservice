<?php
    session_start();
    $db['host'] = "192.168.99.100:13306";
    $db["user"] = "root";
    $db["pass"] = "root";
    $db["dbname"] = "my_system";

    $errorMessage = "";
    $_SESSION["userMessage"]="";
    $_SESSION["tasks"]="";
    $_SESSION["mokuhyo"] ="";

    $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8',$db['host'],$db['dbname']);

    try{
        $pdo = new PDO($dsn,$db['user'],$db['pass'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
        
        $stmt = $pdo->prepare("SELECT * FROM Users,Tasks WHERE User_Id = ? AND Users.User_Id = Tasks.Task_User_Id AND Tasks.End_Flag =0");
        $stmt->execute(array($_SESSION["ID"]));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if(isset($row["Goal"])){
          $_SESSION["mokuhyo"] = sprintf("現在の目標は%sです",$row["Goal"]);
        }else {
          $_SESSION["userMessage"] = sprintf("ようこそ%sさん\n目標を作りましょう",$_SESSION["Name"]);
        }
        if(isset($row["Task"])){
            preg_match("/[0-9０－９]+/",$row["Task"],$today_task_num);  //Taskから正規表現で数字を抜く
            preg_match_all("/[^0-9]+/",$row["Task"],$today_task_stmt,PREG_SET_ORDER); //Taskから正規表現で数字以外を抜く
            $today_task_num[0] = ceil($today_task_num[0]/$row["Period"]); 
            if($tasks !=0){
                $_SESSION["tasks"] = sprintf("今日は'%s'をやりましょう",$today_task_stmt[0][0].$today_task_num[0].$today_task_stmt[1][0]);
            }      
        }
    }catch(PDOException $e){
        $errorMessage = $e->getmessage();
        echo htmlspecialchars($errorMessage,ENT_QUOTES);
    }
    
    if(isset($_POST["end_task"])){
        $DateNow = (int)date("Ymd");
        $koushin = ($DateNow-(int)$row["Task_Counter"])-(int)$row["Start_Date"];
        if($koushin == 0){
            try{
                $koushin_flag = (int)$row["Task_Counter"] + 1;
                $stmt = $pdo->prepare("UPDATE Tasks SET Task_Counter =? WHERE End_Flag =0");
                $stmt->execute(array($koushin_flag));
                $_SESSION["userMessage"]= "今日もお疲れ様です！";
            }catch(PDOException $e){
                $errorMessage = $e->getmessage();
                echo htmlspecialchars($errorMessage,ENT_QUOTES);
            }
        }
        elseif($koushin < 0){
            $_SESSION["userMessage"] = "今日の分は終わっています";
        }
        if($row["Task_Counter"]==$row["Period"]){
        $_SESSION["mokuhyo"] = "おめでとうございます！目標を達成しました！";
        $stmt = $pdo->prepare("UPDATE Tasks SET End_Flag = 1 WHERE End_Flag = 0");
                $stmt->execute();
        }
    }
?>

<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/simple-sidebar.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">

    <div id="page-content-wrapper">

      <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">

        <form method="POST">
          <input type="submit" name="end_task" class="btn btn-primary" value="今日の分終了！">
        </form>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ml-auto mt-2 mt-lg-0">
            <li class="nav-item active">
              <a class="nav-link" href="main.php">ホーム <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="set_goal.php">目標作成</a>
            </li>
          </ul>
        </div>
      </nav>

      <div class="container-fluid">
        <h1 class="mt-4">目標くん</h1>
         <?php
            echo htmlspecialchars($_SESSION["mokuhyo"]);
            echo htmlspecialchars($_SESSION["userMessage"]);
            echo htmlspecialchars($_SESSION["tasks"]);
          ?>
      </div>
    </div>
  </div>

  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>