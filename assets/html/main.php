<?php
require_once("config.php");
class DBContoller
{
  private $host = dbhostname;
  private $user = dbusername;
  private $pass = dbpassword;
  private $dbname = dbname;

  function getDBHost()
  {
    return $this->host;
  }
  function getDBUser()
  {
    return $this->user;
  }
  function getDBPass()
  {
    return $this->pass;
  }
  function getDBName()
  {
    return $this->dbname;
  }
}

$db = new DBContoller();

$dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', $db->getDBHost(), $db->getDBName());
$pdo = new PDO($dsn, $db->getDBUser(), $db->getDBPass(), array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

session_start();
$userMessage = null;
$_SESSION["DateOutMessage"] = null;
$_SESSION["tasks"] = null;
$_SESSION["mokuhyo"] = null;

try {
  $stmt = $pdo->prepare("SELECT * FROM Users,Tasks WHERE UserId = ? AND Users.UserId = Tasks.TaskUserId AND Tasks.EndFlag =0");
  $stmt->execute(array($_SESSION["ID"]));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (isset($row["Goal"])) {
    $_SESSION["mokuhyo"] = sprintf("現在の目標は%sです\n", $row["Goal"]);
  } else {
    $_SESSION["userMessage"] = sprintf("ようこそ%sさん\n目標を作りましょう", $_SESSION["Name"]);
  }
  if (isset($row["Task"])) {
    preg_match("/[0-9０－９]+/", $row["Task"], $today_task_num);
    preg_match_all("/[^0-9]+/", $row["Task"], $today_task_stmt, PREG_SET_ORDER);
    $today_task_num[0] = ceil($today_task_num[0] / $row["Period"]);
    $_SESSION["tasks"] = sprintf("お疲れ様です%sさん\nやるべきこと：%s", $_SESSION["Name"], $today_task_stmt[0][0] . $today_task_num[0] . $today_task_stmt[1][0]);
  }
} catch (PDOException $e) {
  $_SESSION["userMessage"] = $e->getmessage();
}

$DateNow = (int) date("Ymd");
$koushin = $DateNow + 1 - $row["TaskCounter"] - $row["StartDate"];
$DateOut = $DateNow - $row["EndDate"];

if (isset($_POST["end_task"]) && isset($row["TaskNo"])) {
  if ($DateOut > 1) {
    $_SESSION["userMessage"] = "目標達成予定日を過ぎてしまいました 新しい目標を設定してください";
    $_POST["Delete_Flag"] = 1;
  }
  if ($koushin > 1) {
    $_SESSION["AlertMessage"] = "予定より" . $koushin . "日遅れています　継続しますか？";
  } elseif ($koushin == 1) {
    try {
      $AddCounter = $koushin;
      $stmt = $pdo->prepare("UPDATE Tasks SET TaskCounter =? WHERE EndFlag =0");
      $stmt->execute(array($AddCounter));
      $_SESSION["userMessage"] = "今日もお疲れ様です！";
    } catch (PDOException $e) {
      $_SESSION["userMessage"] = $e->getmessage();
    }
  } elseif ($koushin == 0) {
    $_SESSION["userMessage"] = "今日の分は終わっています";
  }
}

if ($row["TaskCounter"] == $row["Period"] && $koushin == 0) {
  $_SESSION["userMessage"] = "おめでとうございます！目標を達成しました！";
  $stmt = $pdo->prepare("UPDATE Tasks SET EndFlag = 1 WHERE EndFlag = 0 AND TaskUserId = ?");
  $stmt->execute(array($_SESSION["ID"]));
}

if ($_POST["Delete_Flag"] == 1) {
  $stmt = $pdo->prepare("UPDATE Tasks SET EndFlag = 1 WHERE EndFlag = 0 AND TaskUserId = ?");
  $stmt->execute(array($_SESSION["ID"]));
}
if ($_POST["Keep_Flag"] == 1) {
  try {
    $AddCounter = $koushin - 1;
    $stmt = $pdo->prepare("UPDATE Tasks SET TaskCounter =? WHERE EndFlag =0");
    $stmt->execute(array($AddCounter));
  } catch (PDOException $e) {
    $_SESSION["userMessage"] = $e->getmessage();
  }
}
?>

<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/simple-sidebar.css" rel="stylesheet">
</head>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

<body>
  <?php if (isset($userMessage)) {
    echo "<div class='alert alert-primary alert-dismissible fade show' role='alert'>" . h($userMessage) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
  } ?>
  <?php if (isset($_SESSION["AlertMessage"])) {
    echo "<script>
        var result = window.confirm('" . htmlspecialchars($_SESSION["AlertMessage"]) . "');
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
            <li class="nav-item">
              <a class="nav-link" data-toggle="modal" data-target="#logoutModal">ログアウト</a>
            </li>
          </ul>
        </div>
        <div class="d-flex align-items-center justify-content-center m-1">
          <form class="m-0" method="POST">
            <input type="submit" name="end_task" class="btn btn-primary" value="今日の分終了！">
          </form>
        </div>
      </nav>

      <div class="container">
        <?php
        echo htmlspecialchars($_SESSION["mokuhyo"]);
        echo htmlspecialchars($_SESSION["tasks"]);
        ?>
      </div>
    </div>
  </div>

  <div class="modal" id="logoutModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>ログアウトしますか？</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="logout()">はい</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ</button>
        </div>
      </div>
    </div>
  </div>

</body>

</html>