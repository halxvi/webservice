<?php
class Calendar
{
    private $year = null;
    private $month = null;
    private $lastday = null;
    private $calendar = null;

    function __construct()
    {
        $this->year = date('Y');
        setcookie('year', $this->year);
        $this->month = date('n');
        setcookie('month', $this->month);
        $this->lastday = date('j', mktime(0, 0, 0, $this->month + 1, 0, $this->year));
        $this->makeCalender(null);
    }

    function makeCalender($stat)
    {
        if ($stat == 'p') {
            $month = $_COOKIE['month'] - 1;
            $year = $_COOKIE['year'];
            if ($month < 1) {
                $year = $_COOKIE['year'] - 1;
                $month = 12;
            }
            $this->month = $month;
            setcookie('month', $month);
            $this->year = $year;
            setcookie('year', $year);
            $lastday = date('j', mktime(0, 0, 0, $month + 1, 0, $year));
        } elseif ($stat == 'n') {
            $month = $_COOKIE['month'] + 1;
            $year = $_COOKIE['year'];
            if ($month > 12) {
                $year = $_COOKIE['year'] + 1;
                $month = 1;
            }
            $this->month = $month;
            setcookie('month', $month);
            $this->year = $year;
            setcookie('year', $year);
            $lastday = date('j', mktime(0, 0, 0, $month + 1, 0, $year));
        } else {
            $month = $this->getMonth();
            setcookie('month', $month);
            $year = $this->getYear();
            setcookie('year', $year);
            $lastday = $this->lastday;
        }

        $this->calendar = array();
        $j = 0;
        for ($i = 1; $i <  $lastday  + 1; $i++) {
            $week = date('w', mktime(0, 0, 0, $month, $i, $this->year));
            if ($i == 1) {
                for ($s = 1; $s <= $week; $s++) {
                    $this->calendar[$j]['day'] = '';
                    $j++;
                }
            }
            $this->calendar[$j]['day'] = $i;
            $j++;

            if ($i ==  $lastday) {
                for ($e = 1; $e <= 6 - $week; $e++) {
                    $this->calendar[$j]['day'] = '';
                    $j++;
                }
            }
        }
    }

    function getYear()
    {
        return $this->year;
    }
    function getMonth()
    {
        return $this->month;
    }
    function getCalendar()
    {
        return $this->calendar;
    }
}

$calendar = new Calendar();

if (isset($_REQUEST['previousMonth'])) {
    $calendar->makeCalender('p');
}

if (isset($_REQUEST['nextMonth'])) {
    $calendar->makeCalender('n');
}

?>
<html>
<style>
    table {
        border-collapse: collapse;
        border-spacing: 0;
    }

    table th {
        background: #EEEEEE;
    }

    table th,
    table td {
        border: 1px solid #CCCCCC;
        text-align: center;
        padding: 5px;
    }
</style>

<body>
    <?php echo $calendar->getYear() . '年' . $calendar->getMonth() . '月' ?>
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
                <td>
                    <?php $cend++;
                        echo $value['day'];
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
</body>

</html>