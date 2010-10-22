<?php
    /* Require statistic functions */
    require_once("includes/statistics.php");
    $statistics = new Statistics;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>EduSuckr Statistics</title>
		<style type="text/css">
		    body { margin: 0; padding: 0; }
		</style>
	</head>
	<body>
    <h1>EduSuckr Statistics</h1>
    <table>
        <tr><th></th><th>performed</th><th>completed</th><th>lasts</th><th>count</th><th>log</th></tr>
<?php
    foreach ($statistics->getLastPerformedSuck("post") as $lps) {
        $lasts="";
        $completed="not completed";
        if ($lps['completed']!='0000-00-00 00:00:00') {
            $completed = $lps['completed'];
            $lasts = strtotime($lps['completed'])-strtotime($lps['performed']);
        }
        echo '<tr><td>Posts</td><td>'.$lps['performed'].'</td><td>'.$completed.'</td><td>'.$lasts.'</td><td>'.$lps['count'].'</td><td><a href="log.php?id='.$lps['log'].'">view</a></td></tr>';
    }
    foreach ($statistics->getLastPerformedSuck("comment") as $lps) {
        $lasts="";
        $completed="not completed";
        if ($lps['completed']!='0000-00-00 00:00:00') {
            $completed = $lps['completed'];
            $lasts = strtotime($lps['completed'])-strtotime($lps['performed']);
        }
        echo '<tr><td>Comments</td><td>'.$lps['performed'].'</td><td>'.$completed.'</td><td>'.$lasts.'</td><td>'.$lps['count'].'</td><td><a href="log.php?id='.$lps['log'].'">view</a></td></tr>';
    }
?>
    </table>
	</body>
</html>