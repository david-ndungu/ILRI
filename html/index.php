<?php

namespace html {
	
	$start = microtime(true);
	ob_start();
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	ini_set('error_log', '../logs/messages.log');
	require_once '../base/Sandbox.php';
	require_once("../settings.php");
	$sandbox = new \base\Sandbox($settings);
	require_once '../base/Controller.php';
	require_once '../base/Response.php';
	$control = new \base\Controller($sandbox);
	$latency = (microtime(true) - $start)*1000;
	$control->log($latency);
	ob_flush();
	
}

?>