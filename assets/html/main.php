<?php
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
    $dbENV = parse_url($_SERVER["CLEARDB_DATABASE_URL"]);
    $dbENV['dbname'] = ltrim($dbENV['path'], '/');
    $this->dsn = sprintf('mysql:host=%s; dbname=%s; charset=utf8', $dbENV['host'], $dbENV['dbname']);
    $this->pdo = new PDO($this->dsn, $dbENV['user'], $dbENV['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $this->day  = (int) date("Ymd");
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
  }

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
  <link href="css/simple-sidebar.css" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
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