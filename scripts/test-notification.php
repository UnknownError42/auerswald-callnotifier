<?php
namespace callnotifier;
require_once __DIR__ . '/../tests/bootstrap.php';

//dependencies for the config file
$config = new Config();
$log = new Log();
$log->debug = true;

$callMonitor = new CallMonitor($config, $log);

$cli = new CLI();
$configFile = $cli->getConfigFile();
if ($configFile === null) {
    echo "No config file found\n";
    exit(1);
}
require $configFile;



$call = new CallMonitor_Call();
$call->type  = 'i';
$call->from  = '03411234567';
//$call->fromName = 'Foo Bar';
$call->fromLocation = 'Leipzig';
$call->to    = '12345';
$call->start = strtotime('2013-08-03 20:11');
$call->end   = strtotime('2013-08-03 20:12');

$log->log('startingCall', array('call' => $call));

?>
