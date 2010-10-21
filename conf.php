<?php
/* Require configuration */
require_once("includes/config.php");
echo "EduSuckr silent mode is: ";
if (SILENT_MODE) {
    echo "On";
} else { 
    echo "Off";
}
echo "<br />";
echo "PHP magic quotes is: ";
if (ini_get( 'magic_quotes_gpc' )) {
    echo "On";
} else { 
    echo "Off";
}
?>
