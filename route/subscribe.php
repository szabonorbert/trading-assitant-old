<?php

	login_required();
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/subscribe.html"));
	
	$r = array();
	$r["account_type"] = $_trans["account_type"][$_SESSION["user"]["account_type"]];
	$r["account_exp"] = date("Y.m.d.", $_SESSION["user"]["account_exp"]);
	if ($_SESSION["user"]["account_exp"] == 0) $r["account_exp"] = "-";
	
	if (ongoingTransactionNum() > 0){
		$r["table_hide"] = "hide";
	} else {
		$r["currentorder_hide"] = "hide";
	}
	
	$_html["content"]($r);