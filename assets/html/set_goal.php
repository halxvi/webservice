<?php
    session_start();
    $db['host'] = "192.168.99.100:13306";
    $db["user"] = "root";
    $db["pass"] = "root";
    $db["dbname"] = "my_system";

    $_SESSION["errorMessage"] = "";
    $_SESSION["userMessage"] = null;

    $dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8',$db['host'],$db['dbname']);

    if(isset($_POST["send_data"])){
        try{
            $Start_Date = (int)date("Ymd");
            $Alter_Term = (int)$_POST["term"];
            $End_Date = time()+($Alter_Term*24*60*60);
            $End_Date = (int)date("Ymd",$End_Date);
            $pdo = new PDO($dsn,$db['user'],$db['pass'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Tasks WHERE Task_User_Id = ? AND End_Flag != 1");
            $stmt->execute(array($_SESSION["ID"]));
            $Recode_Check = $stmt->fetch(PDO::FETCH_NUM);
            if($Recode_Check[0] == 0){
                $stmt = $pdo->prepare("INSERT INTO Tasks(Task_User_Id, Goal, Task,Way, Period, Start_Date, End_Date) value(?,?,?,?,?,?,?)");
                $stmt->execute(array($_SESSION["ID"],$_POST["object"],$_POST["quantitiy"],$_POST["way"],$_POST["term"],$Start_Date,$End_Date));
                $_SESSION["userMessage"] = "登録しました！";
            }else{
                $_SESSION["userMessage"] = "既に目標があります";
            }
        }catch(PDOException $e){
            $errorMessage = $e->getmessage();
            echo htmlspecialchars($errorMessage,ENT_QUOTES);
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
    <?php if(isset($_SESSION["userMessage"])){
      echo "<div class='alert alert-primary' role='alert'>".$_SESSION["userMessage"]."</div>";
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
      </nav>

      <div class="container-fluid">
        <form method="POST">
            <p>目標</p><input type='text' name='object' required>
            <p>達成期間</p><select name="term">
                <option value="7">1週間</option>
                <option value="14">2週間</option>
                <option value="30">1ヶ月</option>
            </select>
            <p>達成手段</p><input type="text" name="quantitiy" >
            <p>達成手順</p><input type="text" name="way" >
            <input type="submit" name="send_data" class="btn btn-primary"value="完了">
        </form>
      </div>
    </div>
  </div>
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>