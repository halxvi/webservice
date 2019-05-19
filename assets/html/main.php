<?php
    session_start();
    $db['host'] = "192.168.99.100:13306";
    $db["user"] = "root";
    $db["pass"] = "root";
    $db["dbname"] = "my_system";

    $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8',$db['host'],$db['dbname']);

    $_SESSION["userMessage"]= null;
    $_SESSION["DateOutMessage"]= null;
    $_SESSION["tasks"]="";
    $_SESSION["mokuhyo"] ="";

    try{
        $pdo = new PDO($dsn,$db['user'],$db['pass'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
        $stmt = $pdo->prepare("SELECT * FROM Users,Tasks WHERE User_Id = ? AND Users.User_Id = Tasks.Task_User_Id AND Tasks.End_Flag =0");
        $stmt->execute(array($_SESSION["ID"]));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if(isset($row["Goal"])){
          $_SESSION["mokuhyo"] = sprintf("現在の目標は%sです\n",$row["Goal"]);
        }else {
          $_SESSION["userMessage"] = sprintf("ようこそ%sさん\n目標を作りましょう",$_SESSION["Name"]);
        }
        if(isset($row["Task"])){
            preg_match("/[0-9０－９]+/",$row["Task"],$today_task_num);  //Taskから正規表現で数字を抜く
            preg_match_all("/[^0-9]+/",$row["Task"],$today_task_stmt,PREG_SET_ORDER); //Taskから正規表現で数字以外を抜く
            $today_task_num[0] = ceil($today_task_num[0]/$row["Period"]); 
            $_SESSION["tasks"] = sprintf("お疲れ様です%sさん\n今日は%sをやりましょう",$_SESSION["Name"],$today_task_stmt[0][0].$today_task_num[0].$today_task_stmt[1][0]);  
        }
    }catch(PDOException $e){
         $_SESSION["userMessage"] = $e->getmessage();
    }
    
    if(isset($_POST["end_task"])){
        $DateNow = (int)date("Ymd");
        $koushin = ($DateNow-(int)$row["Task_Counter"])-(int)$row["Start_Date"];
        $DateOut = (int)$row["End_Date"] - $DateNow;
        $_SESSION["userMessage"] = $DateNow;
        if($koushin == 1){
            try{
                $koushin_flag = (int)$row["Task_Counter"] + 1;
                $stmt = $pdo->prepare("UPDATE Tasks SET Task_Counter =? WHERE End_Flag =0");
                $stmt->execute(array($koushin_flag));
                $_SESSION["userMessage"]= "今日もお疲れ様です！";
            }catch(PDOException $e){
                 $_SESSION["userMessage"] = $e->getmessage();
            }
        }
        elseif($koushin == 0){
            $_SESSION["userMessage"] = "今日の分は終わっています";
        }
        elseif($koushin > 1){
            $_SESSION["userMessage"] = "予定より遅れています<br>続行しますか？<br><button type='button' class='btn btn-outline-primary'>Yes</button>";
        }
      }
      if($row["Task_Counter"]==$row["Period"]){
          $_SESSION["mokuhyo"] = "おめでとうございます！目標を達成しました！";
          $stmt = $pdo->prepare("UPDATE Tasks SET End_Flag = 1 WHERE End_Flag = 0");
          $stmt->execute();
      }
      if($DateOut > 3 && $row["Task_Counter"] < $row["Period"]){
          $_SESSION["DateOutMessage"] = "予定より".$DateOut."日遅れています 現在の目標を削除しますか？";
      }
?>

<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/simple-sidebar.css" rel="stylesheet">
</head>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<body>

    <?php if(isset($_SESSION["userMessage"])){
      echo "<div class='alert alert-primary' role='alert'>".$_SESSION["userMessage"]."</div>";
    }?>
    <?php if(isset($_SESSION["DateOutMessage"])){
      echo "<script>
        var result = window.confirm('". $_SESSION["DateOutMessage"] ."');
        if(result){
          $.post('main.php',
          {End_Flag: 1},
          function(data){
            alert('削除しました');
          })
        }else{
          return;
        }
        </script>";
    }?>
    
    <div class="d-flex" id="wrapper">

    <div id="page-content-wrapper">

      <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">

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

        <form method="POST">
          <input type="submit" name="end_task" class="btn btn-primary" value="今日の分終了！">
        </form>
        
      </nav>
      
      <div class="container-fluid">
         <?php
            echo htmlspecialchars($_SESSION["mokuhyo"]);
            echo htmlspecialchars($_SESSION["tasks"]);
          ?>
      </div>
    </div>
  </div>
  
  
</body>
</html>