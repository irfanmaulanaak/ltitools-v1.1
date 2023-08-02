<?php
phpversion();
require_once "vendor/autoload.php";

use IMSGlobal\LTI\ToolProvider\DataConnector;
use IMSGlobal\LTI\ToolProvider;

$db = mysql_connect("localhost:3307", "root", "");
if (!$db) {
    die('Not connected : ' . mysql_error());
}
$db_selected = mysql_select_db("ltitoolsdb", $db);
if (!$db_selected) {
    die('Can\'t use ltitools : ' . mysql_error());
}

$db_connector = DataConnector\DataConnector::getDataConnector('', $db, "mysql");

$consumer_key_1 = 'toolstest1';

$consumer = new ToolProvider\ToolConsumer($consumer_key_1, $db_connector);
$consumer->name = 'toolstest1 LTI Tools';
$consumer->secret = 'toolstest1secret';
$consumer->enabled = TRUE;
if ($consumer->save()) {
    echo 'Consumer saved successfully with ID: ' . $consumer->getRecordId();
} else {
    die('Error saving consumer: ' . mysql_error());
}
