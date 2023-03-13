<?php

	
	session_start();
	
	/*if (!isset($_SESSION["tester"])){
		echo "Az oldalon ma este karbantartást végzünk, a megértésedet köszönjük. Az időzített levelek kiküldése továbbra is működik";
		die();
	}*/
	
	
	
	define("ROOT", __DIR__ ."/");
	header('Content-Type: text/html; charset=utf-8');
	
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('allow_url_fopen', 1);
	
	date_default_timezone_set('Europe/Budapest');
	
	//
	//  step-by-step code generation by define.php
	//
	
	require_once (ROOT . "define.php");
	require_once (ROOT . "engine/functions.php");
	require_once (ROOT . "engine/magictemplate.class.php");
	require_once (ROOT . "engine/connection.class.php");
	require_once (ROOT . "init.php");
	require_once (ROOT . "route/" . getRoutingPath());
	require_once (ROOT . "engine/generators.php");


	$_SESSION['last_url'] = CURRENT_URL;
	
	
	//systemmail("Norbert", "detenyleg.com@gmail.com", "Hello!", "ez itt egy teszt üzenet, mmsg. ");
	//systemmail("Norbert", "test-6fqx9ydd2@srv1.mail-tester.com", "teszt", "msg");
	//echo "mail send";die();
	
	
	//
	//  ...and finally the magic happens
	//
	
	if (isset($url[0]) && ($url[0] == "login" || $url[0] == "newpass" || $url[0] == "reg")){
		$_end = new MagicTemplate(file_get_contents(ROOT . "front/base_public.html"));
	} else {
		$_end = new MagicTemplate(file_get_contents(ROOT . "front/base.html"));
	}
	
	
	$_end($_html);
	echo $_end -> cleared();
?>
