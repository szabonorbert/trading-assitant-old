<?php

	define("ROOT", __DIR__ ."/");
	header('Content-Type: text/html; charset=utf-8');
	
	session_start();
	
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
	date_default_timezone_set('Europe/Budapest');
	
	require_once (ROOT . "define.php");
	require_once (ROOT . "engine/functions.php");
	require_once (ROOT . "engine/magictemplate.class.php");
	require_once (ROOT . "engine/connection.class.php");
	
	$sql = new Connection($_setting['sql_hostname'], $_setting['sql_username'], $_setting['sql_password'], $_setting['sql_database']);
	
	if (!isset($_SESSION["user"]) || $_SESSION["user"]["id"] != 2){
		echo "no perm";
		die();
	}
	
	//SQL truncate
	if (isset($_GET["force"])){
		echo "truncate by force  ... ";
		$sql->query("truncate history_data");
		echo "done<br>";
	}
	
	$dir = scandir(ROOT . "backtest-db/");
	//aprint($dir);
	
	$inserted = 0;
	
	foreach ($dir as $d){
		$data = explode("-", $d);
		
		//check data count
		if (count($data) != 10) continue;
		
		//check exist
		$md5 = md5($d);
		$res = $sql->getArray("select count(*) c from history_data where md5='" . $md5 . "'");
		if ($res[0]["c"] != 0) continue;
		
		//insert
		$d[9] == str_replace(".png", "", $data[9]);
		
		$i = array();
		$i["version"] = $data[0];
		$i["instrument"] = $data[1];
		$i["timeframe"] = $data[2];
		$i["direction"] = $data[3];
		$i["state"] = $data[4];
		$i["y"] = $data[5];
		$i["m"] = $data[6];
		$i["d"] = $data[7];
		$i["h"] = $data[8];
		$i["i"] = $data[9];
		$i["md5"] = $md5;
		
		//aprint($i);
		//die();
		$sql->insert("history_data", $i);
		$inserted++;
	}
	
	echo "import finished.<br>new insert: " . $inserted . "<br>" . date("Y.m.d. H:i:s");
	
	die();