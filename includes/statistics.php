<?php

/* Require configuration */
require_once("includes/config.php");
/* Require database */
require_once("includes/db.php");

class Statistics {
    
    function getLastPerformedSuck($type = "post") {
        global $db;
        $query = "SELECT * FROM ".DB_PREFIX."statistics WHERE type='".$type."' ORDER BY performed DESC LIMIT 10";
        $result = $db->query($query);
        $stats = array();
        if(mysql_num_rows($result)) {
            while($stat = mysql_fetch_array($result)) {
                $stats []= $stat;
            }
        }  
        return $stats;  
    }
    
    function performSuck($type) {
        global $db;
        $query = "INSERT into ".DB_PREFIX."statistics (error, type) values (1, '".$type."')";
        $result = $db->query($query);
        if ($result) {
            return mysql_insert_id();
        }
        return 0;
    }
    function completeSuck($id, $count, $log) {
        global $db;
        $query = "UPDATE ".DB_PREFIX."statistics set completed=NOW(), count=".$count.", log=".$log." error=0 WHERE id=".$id;
        $result = $db->query($query);
        if ($result) {
            return 1;
        }
        return 0;
    }
    
    function writeLog($type) {
        global $db;
        $query = "INSERT into ".DB_PREFIX."log (log) values ('".$log."')";
        $result = $db->query($query);
        if ($result) {
            return mysql_insert_id();
        }
        return 0;
    }
}
?>