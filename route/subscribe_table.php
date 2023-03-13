<?php

	login_required();
	
	msgError("Jelenleg nem lehet előfizetni.");
	go(URL."subscribe");
	
	//check if ongoing trans
	if (ongoingTransactionNum() != 0){
		msgError("Addig nem indíthatsz új tranzakciót, amíg folyamatban van egy másik.");
		go(URL."subscribe");
	}
	
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/subscribe_table.html"));
	
	$r = array();
	
	foreach ($_account_types as $ac){
		$r[$ac."_price"] = $_prices[$ac];
	}
	$r["currency"] = $_currency;
	
	$_html["content"]($r);