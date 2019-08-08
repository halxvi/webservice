<?php
require_once("config.php");
$db['host'] = "db:3306";
$db["user"] = "root";
$db["pass"] = "root";
$db["dbname"] = "my_system";

session_start();
$_SESSION["errorMessage"] = null;
$_SESSION["userMessage"] = null;
$_SESSION["Task_Delete_Message"] = null;

$dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);
$pdo = new PDO($dsn, $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

$StartDate = (int) date("Ymd");
$Alter_Term = (int) $_POST["term"];
$EndDate = time() + ($Alter_Term * 24 * 60 * 60);
$EndDate = (int) date("Ymd", $EndDate);

if (isset($_POST["send_data"])) {
  try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Tasks WHERE TaskUserId = ? AND EndFlag = 0");
    $stmt->execute(array($_SESSION["ID"]));
    $Recode_Check = $stmt->fetch(PDO::FETCH_NUM);
    if ($Recode_Check[0] == 0) {
      $stmt = $pdo->prepare("INSERT INTO Tasks(TaskUserId, Goal, Task,Way, Period, StartDate, EndDate) value(?,?,?,?,?,?,?)");
      $stmt->execute(array($_SESSION["ID"], $_POST["object"], $_POST["quantitiy"], $_POST["way"], $_POST["term"], $StartDate, $EndDate));
      $_SESSION["userMessage"] = "登録しました！";
    } else {
      $_SESSION["userMessage"] = "既に目標があります";
    }
  } catch (PDOException $e) {
    $errorMessage = $e->getmessage();
    echo htmlspecialchars($errorMessage, ENT_QUOTES);
  }
}
if (isset($_POST["task_delete_opt"])) {
  $_SESSION["Task_Delete_Message"] = "目標を削除しますか？";
}
if ($_POST["Task_Delete_Flag"] == 1) {
  $stmt = $pdo->prepare("UPDATE Tasks SET EndFlag = 1 WHERE EndFlag = 0 AND TaskUserId = ? ");
  $stmt->execute(array($_SESSION["ID"]));
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
  <?php if (isset($_SESSION["Task_Delete_Message"])) {
    echo "<script>
        var result = window.confirm('" . $_SESSION["Task_Delete_Message"] . "');
        if(result){
          $.post('set_goal.php',
          {Task_Delete_Flag: 1},
          function(data){
            alert('目標を削除しました');
          })
        }
        </script>";
  }
  ?>

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
            <li class="nav-item">
              <a class="nav-link" href="logout.php">ログアウト</a>
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
          <p>達成手段<span class="text-muted">具体的な数字を含めるようにしてください　例：テキストを１００ページやる</span></p>
          <input type="text" name="quantitiy">
          <p>達成手順</p><input type="text" name="way">
          <input type="submit" name="send_data" class="btn btn-primary" value="完了">
        </form>
        <form method="POST">
          <input type="submit" name="task_delete_opt" class="btn btn-primary" value="現在の目標を削除する">
        </form>
      </div>
    </div>
  </div>

</body>

</html>