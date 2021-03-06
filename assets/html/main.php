<?php
require_once("db.php");
require_once("calendar.php");

class Main
{
  private $UserMessage = null;
  private $pdo = null;
  private $row = null;

  function __construct()
  {
    session_start();
    $db = new DB();
    $this->pdo = $db->getPDO();
    $this->getTable();
    $this->checkGoal();
  }

  function endTask()
  {
    $stmt = $this->pdo->prepare("SELECT Date FROM Counter WHERE TaskNo = ?");
    $stmt->execute(array($this->row['TaskNo']));
    $counter = $stmt->fetchAll(PDO::FETCH_COLUMN);
    for ($i = 0; $i < count($counter) + 1; $i++) {
      if ($counter[$i] == date("Y-m-d")) {
        $this->UserMessage = "今日の分は終わっています";
        return;
      }
    }
    $this->UserMessage = "今日もお疲れ様です！";
    $this->setCounter();
    $this->getTable();
    $this->date = sprintf("%s日継続中です", count($counter));
  }


  private function checkGoal()
  {
    $this->getTable();
    if ($this->getCount() == $this->getRow("Period")) {
      $this->UserMessage = "おめでとうございます！   目標を達成しました！";
      $this->deleteGoal();
    } elseif ($this->getRow('EndDate') && $this->getRow('EndDate')  <= date("Y-m-d")) {
      $this->UserMessage = "目標設定から時間が経っています    新しく目標を設定してみてはいかがですか?";
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
      $stmt = $this->pdo->prepare("SELECT * FROM Users,Tasks WHERE Users.UserId = ? AND Users.UserId = Tasks.TaskUserId  AND Tasks.EndFlag = 0");
      $stmt->execute(array($_SESSION["ID"]));
      $this->row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $this->UserMessage = $e->getmessage();
    }
  }

  private function setCounter()
  {
    $stmt = $this->pdo->prepare("INSERT INTO Counter(TaskNo,Date) value(:TaskNo,:Date)");
    $stmt->bindValue(':TaskNo', $this->row["TaskNo"], PDO::PARAM_INT);
    $stmt->bindValue(':Date', date("Y-m-d"));
    $stmt->execute();
  }

  function getPeriod()
  {
    if ($this->getCount() != 0) {
      return sprintf("%s日継続中です", $this->getCount());
    }
  }

  function getProgress()
  {
    if ($this->getCount() != 0) {
      $per = $this->getCount()  / $this->row["Period"] * 100;
      return ceil($per);
    }
  }

  private function getCount()
  {
    $this->getTable();
    $stmt = $this->pdo->prepare("SELECT count(*) from Counter WHERE TaskNo = :Id");
    $stmt->bindValue(':Id', $this->row["TaskNo"], PDO::PARAM_INT);
    $stmt->execute();
    $num = $stmt->fetch(PDO::FETCH_ASSOC);
    return $num['count(*)'];
  }

  function getUserMessage()
  {
    return $this->UserMessage;
  }

  function getGoal()
  {
    if ($this->row["Goal"]) {
      return sprintf("現在の目標は%sです", $this->row["Goal"]);
    } else {
      return sprintf("ようこそ%sさん\n目標を作りましょう", $_SESSION["Name"]);
    }
  }

  function getTask()
  {
    if ($this->row["Task"]) {
      return sprintf("今日やること：%s", $this->row["Task"]);
    }
  }

  function getRow($str)
  {
    return $this->row[$str];
  }

  function hsc($str)
  {
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8", false);
  }
}

$main = new Main();

if ($main->getRow("TaskNo")) {
  filter_input(INPUT_POST, "endTask") ? $main->endTask() : false;
}

// if ($main->getRow("TaskCounter") != null && $main->getRow("Period") != null) {
//   $main->checkGoal();
// }

if (!isset($_SESSION["ID"])) {
  header("location:login.php");
}

$calendar = new Calendar();

$calendar->makeCalender(null);

if (isset($_REQUEST['previousMonth'])) {
  $calendar->makeCalender('p');
}

if (isset($_REQUEST['nextMonth'])) {
  $calendar->makeCalender('n');
}
?>

<html>

<head>
  <title>もくひょうくん</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="../css/simple-sidebar.css" rel="stylesheet">
  <link href="../css/calendar-style.css" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../js/main.js"></script>

<body>


  <div class="d-flex" id="wrapper">
    <div id="page-content-wrapper" class="w-100">
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

  <?php if ($main->getUserMessage()) {
    echo "<div class='alert alert-primary alert-dismissible fade show' role='alert'>" . $main->hsc($main->getUserMessage()) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
  }
  ?>

  <?php
  if ($main->getGoal()) {
    echo "<div><label class='font-weight-light goaltxt'>" . $main->hsc($main->getGoal()) . "</label></div>";
  }
  if ($main->getTask()) {
    echo "<div><label class='font-weight-light tasktxt'>" . $main->hsc($main->getTask()) . "</label></div>";
  }
  if ($main->getPeriod()) {
    echo "<div><label class='font-weight-light daystxt'>" . $main->hsc($main->getPeriod()) . "</label><br><div class='progress w-50'><div class='progress-bar' role='progressbar' style='width:" . $main->hsc($main->getProgress()) . "%' aria-valuenow='" . $main->hsc($main->getProgress()) . "' aria-valuemin='0' aria-valuemax='100'>" . $main->hsc($main->getProgress()) . "%</div></div></div>";
  }
  ?>

  <?php echo $main->hsc($calendar->getYear()) . '年' . $main->hsc($calendar->getMonth()) . '月' ?>
  <form class="m-0" method="POST">
    <input type="submit" name="previousMonth" class="btn btn-secondary" value="<">
    <input type="submit" name="nextMonth" class="btn btn-secondary" value=">">
  </form>
  <br>

  <table>
    <tr>
      <th>日</th>
      <th>月</th>
      <th>火</th>
      <th>水</th>
      <th>木</th>
      <th>金</th>
      <th>土</th>
    </tr>
    <tr>
      <?php $cend = 0; ?>
      <?php foreach ($calendar->getCalendar() as $key => $value) : ?>
        <td <?php if ($main->hsc($value['check'])) {
                echo 'class="calendar-color"';
              } ?>>
          <?php
            $cend++;
            echo $main->hsc($value['day']);
            ?>
        </td>
        <?php if ($cend == 7) : ?>
    </tr>
    <tr>
      <?php $cend = 0; ?>
    <?php endif; ?>

  <?php endforeach; ?>
    </tr>
  </table>

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