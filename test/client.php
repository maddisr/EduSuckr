<?php
    require_once("../includes/config.php");
    require_once("../includes/nusoap/nusoap.php");
    $client = new nusoap_client(SERVER_ROOT.'/ws.php?wsdl', TRUE);
    $err = $client->getError();
    if ($err) {
	    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
    }
    $result = false;
    $result = $client->call('listEduCources', array());

    if ($client->fault) {
        echo "<strong>Fault:</strong>".
        print_r($callResult);    
    } else {
        $err = $client->getError();
        if ($err) {
            echo "<strong>Error:</strong>".$err;
        }
    }
    foreach ($result as $feed) {
        echo $feed."<br />";    
    }
    echo '<h2>Request</h2>';
    echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    echo '<h2>Response</h2>';
    echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';

?>
