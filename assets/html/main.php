<?php
session_start();
$db['host'] = "192.168.99.100:13306";
$db["user"] = "root";
$db["pass"] = "root";
$db["dbname"] = "my_system";

$dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
$pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

$_SESSION["userMessage"] = null;
$_SESSION["DateOutMessage"] = null;
$_SESSION["tasks"] = "";
$_SESSION["mokuhyo"] = "";

$DateNow = (int)date("Ymd");
$koushin = $DateNow + 1 - $row["Task_Counter"] - $row["Start_Date"];
$DateOut = $DateNow - $row["End_Date"];

try {
  $stmt = $pdo->prepare("SELECT * FROM Users,Tasks WHERE User_Id = ? AND Users.User_Id = Tasks.Task_User_Id AND Tasks.End_Flag =0");
  $stmt->execute(array($_SESSION["ID"]));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($DateOut > 1 && $row["Goal"]) {
    //予定日を過ぎていないか確認
    $_SESSION["userMessage"] = "目標達成予定日を過ぎてしまいました 新しい目標を設定してください";
    $_POST["Delete_Flag"] = 1;
  }
  if (isset($row["Goal"])) {
    $_SESSION["mokuhyo"] = sprintf("現在の目標は%sです\n", $row["Goal"]);
  } else {
    $_SESSION["userMessage"] = sprintf("ようこそ%sさん\n目標を作りましょう", $_SESSION["Name"]);
  }
  if (isset($row["Task"])) {
    preg_match("/[0-9０－９]+/", $row["Task"], $today_task_num);  //Taskから正規表現で数字を抜く
    preg_match_all("/[^0-9]+/", $row["Task"], $today_task_stmt, PREG_SET_ORDER); //Taskから正規表現で数字以外を抜く
    $today_task_num[0] = ceil($today_task_num[0] / $row["Period"]);
    $_SESSION["tasks"] = sprintf("お疲れ様です%sさん\n%s日継続しています\nやるべきこと：%s", $_SESSION["Name"], $row["Task_Counter"], $today_task_stmt[0][0] . $today_task_num[0] . $today_task_stmt[1][0]);
  }
} catch (PDOException $e) {
  $_SESSION["userMessage"] = $e->getmessage();
}

if (isset($_POST["end_task"]) && isset($row["Task_No"])) {
  //現在のタスクがある状態で押すと動く
  if ($koushin > 1) {
    $_SESSION["AlertMessage"] = "予定より" . $koushin . "日遅れています　継続しますか？";
  } elseif ($koushin == 1) {
    try {
      $AddCounter = $koushin;
      $stmt = $pdo->prepare("UPDATE Tasks SET Task_Counter =? WHERE End_Flag =0");
      $stmt->execute(array($AddCounter));
      $_SESSION["userMessage"] = "今日もお疲れ様です！";
    } catch (PDOException $e) {
      $_SESSION["userMessage"] = $e->getmessage();
    }
  } elseif ($koushin == 0) {
    $_SESSION["userMessage"] = "今日の分は終わっています";
  }
}

if ($row["Task_Counter"] == $row["Period"] && $koushin == 0) {
  $_SESSION["userMessage"] = "おめでとうございます！目標を達成しました！";
  $stmt = $pdo->prepare("UPDATE Tasks SET End_Flag = 1 WHERE End_Flag = 0 AND Task_User_Id = ?");
  $stmt->execute(array($_SESSION["ID"]));
}

if ($_POST["Delete_Flag"] == 1) {
  $stmt = $pdo->prepare("UPDATE Tasks SET End_Flag = 1 WHERE End_Flag = 0 AND Task_User_Id = ?");
  $stmt->execute(array($_SESSION["ID"]));
}
if ($_POST["Keep_Flag"] == 1) {
  try {
    $AddCounter = $koushin - 1;
    $stmt = $pdo->prepare("UPDATE Tasks SET Task_Counter =? WHERE End_Flag =0");
    $stmt->execute(array($AddCounter));
  } catch (PDOException $e) {
    $_SESSION["userMessage"] = $e->getmessage();
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
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<body>

  <?php if (isset($_SESSION["userMessage"])) {
    echo "<div class='alert alert-primary' role='alert'>" . $_SESSION["userMessage"] . "</div>";
  } ?>
  <?php if (isset($_SESSION["AlertMessage"])) {
    echo "<script>
        var result = window.confirm('" . $_SESSION["AlertMessage"] . "');
        if(result){
          $.post('main.php',
          {Delete_Flag: 1},
          function(data){
            alert('目標を削除しました');
          })
        }else{
          $.post('main.php',
          {Keep_Flag: 1},
          function(data){
            alert('遅れていても大丈夫です！　継続していきましょう！');
          })
        }
        </script>";
  } ?>

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
        <div class="cl_mokuhyo">
          <?php
          echo htmlspecialchars($_SESSION["mokuhyo"]);
          ?>
        </div>
        <div class="cl_tasks">
          <?php
          echo htmlspecialchars($_SESSION["tasks"]);
          ?>
        </div>
      </div>
    </div>


</body>

</html>