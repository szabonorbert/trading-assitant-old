<?php

	login_required();
	
	//check trans state
	if (ongoingTransactionNum() == 0){
		go(URL."subscribe");
	}
	
	$trans = getLastTransaction();
	
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/subscribe_currentorder.html"));
	$r = array();
	if ($trans["current_state"] == "started"){
		$r["hide_for_started"] = "hide";
	}
	
	$r["address"] = $_address;
	$r["network"] = $_network;
	$r["account_type"] = $trans["account_type"];
	$r["account_type_translated"] = $_trans["account_type"][$trans["account_type"]];
	$r["curr"] = $trans["curr"];
	$r["sum"] = $trans["sum"];
	$r["trans_id"] = $trans["trans_id"];
	
	
	
	
	$_html['content']($r);