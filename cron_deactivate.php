<?php

	define("ROOT", __DIR__ ."/");
	header('Content-Type: text/html; charset=utf-8');
	
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
	date_default_timezone_set('Europe/Budapest');
	
	require_once (ROOT . "define.php");
	require_once (ROOT . "engine/functions.php");
	require_once (ROOT . "engine/magictemplate.class.php");
	require_once (ROOT . "engine/connection.class.php");
	
	$sql = new Connection($_setting['sql_hostname'], $_setting['sql_username'], $_setting['sql_password'], $_setting['sql_database']);
	
	$users = $sql->getArray("select * from user where account_exp!='0'");
	
	$oneday = 60*60*24;
	
	foreach ($users as $user){
		/*echo date("Y.m.d. H:i:s", time()) . "<br />";
		echo date("Y.m.d. H:i:s", $user["account_exp"] - $oneday * 3) . "<br />";
		echo date("Y.m.d. H:i:s", $user["account_exp"] - $oneday * 2) . "<br />";*/
		
		//alert user expire
		if (
			$user["account_exp"] > time() &&
			time() > $user["account_exp"] - $oneday * 3 &&
			time() < $user["account_exp"] - $oneday * 2
		){
			$body = file_get_contents(ROOT . "front/mail/alert_user_expire.html");
			$body = bulk_replace(array(
				"name"=>$user["name"]
			),$body);
			systemmail($user["name"], $user["mail"], "A Trading Assistant előfizetésed hamarosan lejár", $body);
		}
		
		//cancel subscription
		else if (time() > $user["account_exp"]){
			
			//alert user
			$body = file_get_contents(ROOT . "front/mail/alert_user_expired.html");
			$body = bulk_replace(array(
				"name"=>$user["name"]
			),$body);
			systemmail($user["name"], $user["mail"], "A Trading Assistant előfizetésed LEJÁRT", $body);
			
			//alert admin
			$body = file_get_contents(ROOT . "front/mail/alert_admin_expired.html");
			$body = bulk_replace(array(
				"name"=>$user["name"],
				"mail"=>$user["mail"],
				"discord_name"=>$user["discord_name"]
			),$body);
			adminmail("Lejárt előfizetés: " . $user["name"] . " / " . $user["discord_name"], $body);
			
			//update records
			$sql->update("user", array("account_type"=>$_account_types[0], "account_exp"=>0), "id", $user["id"]);
			
			//log
			userLog(0,$user["id"],0,"account_expired","a user előfizetése lejárt, emailben kiértesítve és táblázat lekapcsolva");
		
		}
	}