<?php

	login_required();
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/settings.html"));
	
	$r = array();
	$r["name"] = $_SESSION["user"]["name"];
	$r["mail"] = $_SESSION["user"]["mail"];
	$r["discord_name"] = $_SESSION["user"]["discord_name"];
	
	$_html['content']($r);
?>