<?php
<<<<<<< HEAD
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

try {
  $stmt = $pdo->prepare("SELECT * FROM Users,Tasks WHERE User_Id = ? AND Users.User_Id = Tasks.Task_User_Id AND Tasks.End_Flag =0");
  $stmt->execute(array($_SESSION["ID"]));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
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
=======
//require_once("config.php");

class DBContoller
{
  private $UserMessage = null;
  private $task = null;
  private $goal = null;
  private $days = null;
  private $day  = null;
  private $dsn = null;
  private $pdo = null;
  private $row = null;

  function __construct()
  {
    session_start();
    $dbhostname = getenv('DBHOSTNAME');
    $dbname = getenv('DBNAME');
    $dbusername = getenv('DBUSERNAME');
    $dbpassword = getenv('DBPASSWORD');
    $this->day  = (int) date("Ymd");
    $this->dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', $dbhostname, $dbname);
    $this->pdo = new PDO($this->dsn, $dbusername, $dbpassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $this->getTable();
    if (isset($this->row["Goal"])) {
      $this->goal = sprintf("現在の目標は%sです", $this->row["Goal"]);
      if ($this->row["TaskCounter"] != 0) {
        $this->days = sprintf("%s日継続中です", $this->row["TaskCounter"]);
      }
    } else {
      $this->UserMessage = sprintf("ようこそ%sさん\n目標を作りましょう", $_SESSION["Name"]);
    }
    if (isset($this->row["Task"])) {
      $this->task = sprintf("今日やること：%s", $this->row["Task"]);
    }
>>>>>>> 90058cbad6c9ac33a8f59249d96a084ae93d4f5c
  }

<<<<<<< HEAD
if (isset($_POST["end_task"]) && isset($row["Task_No"])) {
  //現在のタスクがある状態で押すと動く
  if ($koushin > 1) {
    $_SESSION["AlertMessage"] = "予定より" . $koushin . "日遅れています　継続しますか？";
  } elseif ($koushin == 1) {
=======
  function endTask()
  {
    if ($this->row["LastAccessDay"] != $this->day) {
      $this->commitTask();
    } else {
      $this->UserMessage = "今日の分は終わっています";
    }
  }

  function checkGoal()
  {
    if ($this->getRow("TaskCounter") === $this->getRow("Period")) {
      $this->deleteGoal();
      $this->UserMessage = "おめでとうございます！目標を達成しました！";
    }
  }

  private function deleteGoal()
  {
    try {
      $stmt = $this->pdo->prepare("UPDATE Tasks SET EndFlag = 1 WHERE EndFlag = 0 AND TaskUserId = ?");
      $stmt->execute(array($_SESSION["ID"]));
    } catch (PDOException $e) {
      $this->UserMessage = $e->getmessage();
    }
  }

  private function getTable()
  {
>>>>>>> 90058cbad6c9ac33a8f59249d96a084ae93d4f5c
    try {
      $stmt = $this->pdo->prepare("SELECT * FROM Users,Tasks WHERE UserId = ? AND Users.UserId = Tasks.TaskUserId AND Tasks.EndFlag = 0");
      $stmt->execute(array($_SESSION["ID"]));
      $this->row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $this->UserMessage = $e->getmessage();
    }
  }

  private function setCounter()
  {
    $AddCounter = $this->row["TaskCounter"] + 1;
    $stmt = $this->pdo->prepare("UPDATE Tasks SET TaskCounter = ? WHERE EndFlag = 0");
    $stmt->bindValue(1, $AddCounter, PDO::PARAM_INT);
    $stmt->execute();
    $stmt = $this->pdo->prepare("UPDATE Tasks SET LastAccessDay = ? WHERE EndFlag = 0");
    $stmt->bindValue(1, $this->day, PDO::PARAM_INT);
    $stmt->execute();
  }

  private function commitTask()
  {
    $this->UserMessage = "今日もお疲れ様です！";
    $this->setCounter();
    $this->getTable();
    $this->days = sprintf("%s日継続中です", $this->row["TaskCounter"]);
  }

  function getUserMessage()
  {
    return $this->UserMessage;
  }

  function getGoal()
  {
    return $this->goal;
  }

  function getTask()
  {
    return $this->task;
  }

  function getDays()
  {
    return $this->days;
  }

  function getRow($str)
  {
    return $this->row[$str];
  }

  function getProgress()
  {
    $this->getTable();
    $per =  $this->row["TaskCounter"] / $this->row["Period"] * 100;
    return ceil($per);
  }

  function hsc($str)
  {
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8", false);
  }
}

$db = new DBContoller();

if ($db->getRow("TaskNo")) {
  filter_input(INPUT_POST, "endTask") ? $db->endTask() : false;
}

if ($db->getRow("TaskCounter") != null && $db->getRow("Period") != null) {
  $db->checkGoal();
}

if (!isset($_SESSION["ID"])) {
  header("location:login.php");
}
?>

<html>

<head>
  <title>もくひょうくん</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/simple-sidebar.css" rel="stylesheet">
</head>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

<body>
  <?php if ($db->getUserMessage()) {
    echo "<div class='alert alert-primary alert-dismissible fade show' role='alert'>" . $db->hsc($db->getUserMessage()) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
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
              <a class="nav-link" href="setgoal.php">目標設定</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="modal" data-target="#logoutModal">ログアウト</a>
            </li>
          </ul>
        </div>
        <div class="d-flex align-items-center justify-content-center m-1">
          <form class="m-0" method="POST">
            <input type="submit" name="endTask" class="btn btn-primary" value="今日の分終了！">
          </form>
        </div>
      </nav>
    </div>
  </div>

<<<<<<< HEAD
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
=======
  <div id="carouselExampleControls" class="carousel slide" data-ride="carousel" style="height:100%">
    <div class="carousel-inner" style="height:100%">
      <?php
      if ($db->getGoal()) {
        echo "<div class='carousel-item active' style='height:100%' alt='first'><label class='font-weight-light goaltxt'>" . $db->hsc($db->getGoal()) . "</label></div>";
      }
      if ($db->getTask()) {
        echo "<div class='carousel-item' style='height:100%' alt='second'><label class='font-weight-light tasktxt'>" . $db->hsc($db->getTask()) . "</label></div>";
      }
      if ($db->getDays()) {
        echo "<div class='carousel-item' style='height:100%' alt='third'><label class='font-weight-light daystxt'>" . $db->hsc($db->getDays()) . "</label><br><div class='progress w-75'><div class='progress-bar' role='progressbar' style='width:" . $db->hsc($db->getProgress()) . "%' aria-valuenow='" . $db->hsc($db->getProgress()) . "' aria-valuemin='0' aria-valuemax='100'>" . $db->hsc($db->getProgress()) . "%</div></div></div>";
      }
      ?>
    </div>
    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>
  </div>
>>>>>>> 90058cbad6c9ac33a8f59249d96a084ae93d4f5c

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