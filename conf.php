<?php
echo "Magic quotes is: ";
if (ini_get( 'magic_quotes_gpc' )) {
    echo "On";
} else { 
    echo "Off";
}
?>
