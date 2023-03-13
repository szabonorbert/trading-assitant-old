<?php

	login_required();
	admin_required();
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/admin_user_x.html"));
	$r = array();
	
	$user = $sql->getArray("select * from user where id='" . $sql->escape($url[2]) . "'");
	if (count($user) == 0){
		echo "missing user";
		die();
	}
	$user = $user[0];
	$user["pass"] = "***SECRET***";
	$user["session_id"] = "***SECRET***";
	$user["account_exp"] = date("Y.m.d. H:i:s", $user["account_exp"]) . " (" . $user["account_exp"] . ")";
	$user["regdate"] = date("Y.m.d. H:i:s", $user["regdate"]) . " (" . $user["regdate"] . ")";
	$user["moddate"] = date("Y.m.d. H:i:s", $user["moddate"]) . " (" . $user["moddate"] . ")";
	$user["lastlogindate"] = date("Y.m.d. H:i:s", $user["lastlogindate"]) . " (" . $user["lastlogindate"] . ")";
	//$user["account_exp"] = "";
	
	$r["id"] = $user["id"];
	$r["discord_name"] = $user["discord_name"];
	$r["user"] = print_r($user, true);
	
	$r["trans"] = $sql->getArray("select * from trans where user_id='" . $user["id"] . "' order by id desc");
	$r["trans"] = print_r($r["trans"], true);
	$_html["content"]($r);