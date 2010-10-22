<?php
    /* Require statistic functions */
    require_once("includes/statistics.php");
    $statistics = new Statistics;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>EduSuckr Log</title>
		<style type="text/css">
		    body { margin: 0; padding: 0; }
		</style>
	</head>
	<body>
    <h1>EduSuckr Log</h1>
    <?php
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        echo $statistics->readLog($_GET['id']);
    } else {
        echo "No log with that id!";    
    }
    ?>
</html>