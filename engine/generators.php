<?php

	//og
	if (!isset($_html['og:title'])) $_html['og:title'] = $_html['title'];
	if (!isset($_html['og:description'])) $_html['og:description'] = $_html['description'];
	if (!isset($_html['og:image'])) $_html['og:image'] = $_html['url'].'front/img/logo.png';
	if (!isset($_html['og:site_name'])) $_html['og:site_name'] = $_setting['app_name'];
	if (!isset($_html['og:type'])) $_html['og:type'] = 'website';
	if (!isset($_html['og:url'])) $_html['og:url'] = CURRENT_URL;
	
	//crumb
	if (count($_crumb) > 0){
		$c_html = file_get_contents(ROOT . "front/template/crumb_e.html");
		$_html['crumb'] = "";
		foreach ($_crumb as $c) $_html['crumb'] .= bulk_replace($c, $c_html, true);
	}
	
	//menu
	if (count($_menu) > 0){
		$c_html = file_get_contents(ROOT . "front/template/menu_a.html");
		$_html['menu'] = "";
		foreach ($_menu as $c) $_html['menu'] .= bulk_replace($c, $c_html, true);
	}
	
	//session msg
	if (isset($_SESSION['msg'])){
		$_html['session_msg'] = str_replace('{{msg}}', $_SESSION['msg'], file_get_contents(ROOT . 'front/template/session_msg.html'));
		unset($_SESSION['msg']);
		if (isset($_SESSION['msgerror'])){
			unset($_SESSION['msgerror']);
			$icon = 'times';
			$bg = 'danger';
		} else {
			$icon = 'check';
			$bg = 'success';
		}
		$_html['session_msg'] = str_replace('{{icon}}', $icon, $_html['session_msg']);
		$_html['session_msg'] = str_replace('{{bg}}', $bg, $_html['session_msg']);
		if ($bg == "success" && isset($_SESSION["user"]["andi"]) && $_SESSION["user"]["andi"] == 1) $_html["unicorn_go"] = "true";
	}
	
	if (isset($_SESSION["user"]["andi"]) && $_SESSION["user"]["andi"] == 1) $_html["unicornhead_go"] = "true";
	
	//body bg
	if (isset($_SESSION["user"]["andi"]) && $_SESSION["user"]["andi"] == 1){
		$_html["body_bg"] = rand(0,255) . "," . rand(0,255) . "," . rand(0,255);
	} else {
		$_html["body_bg"] = "239, 243, 246";
	}
	
	//rainbow number
	if (isset($_SESSION["user"]["andi"]) && $_SESSION["user"]["andi"] == 1){
		$_html["rainbow_counter"] = $sql->getArray("select * from var where alias='rainbow'");
		$_html["rainbow_counter"] = number_format($_html["rainbow_counter"][0]["content"], 0, "", " ");
	} else {
		$_html["hide_rainbow_counter"] = "hide";
	}
	
	//langs
	/*$_html['lang'] = $_SESSION['lang']['lang'];
	$_html['langs'] = "";
	foreach ($_SESSION['langs'] as $l){
		if ($l['id'] != $_SESSION['lang']['id']){
			$c_html = file_get_contents(ROOT . "front/template/a.html");
			$_html['langs'] .= bulk_replace($c_html, array("title"=>$l['lang'], "href"=>URL.'function/lang/'.$l['lang']));
			
		}
	}*/
?>