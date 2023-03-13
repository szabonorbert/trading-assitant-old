<?php

	login_required();
	admin_required();
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/admin_trans_x.html"));
	$r = array();
	
	$trans = $sql->getArray("select * from trans where id='" . $sql->escape($url[2]) . "'");
	if (count($trans) == 0){
		echo "missing trans";
		die();
	}
	$trans = $trans[0];
	$user = $sql->getArray("select * from user where id='" . $trans["user_id"] . "'");
	$user = $user[0];
	$log = $sql->getArray("select * from log where trans_id='" . $trans["id"] . "'");
	$log = $log[0];
	
	if ($trans["trans_id"] == "") $trans["trans_id"] = "(még?) nem adta meg";
	
	
	
	
	
	$r["trans_account_type_translated"] = $_trans["account_type"][$trans["account_type"]];
	$r["user_account_type_translated"] = $_trans["account_type"][$user["account_type"]];
	
	//hide change panel
	if ($trans["current_state"] != "payed"){
		$r["hide_if_no_payed"] = "hide";
	}
	
	//dates
	$date = new DateTime();
	if ($user["account_exp"] > time()){
		$date->setTimestamp($user["account_exp"]);
	}
	$date->modify('next month');
	
	$r["default_date_value"] = $date->format('Y-m-d');
	$r["today_date_value"] = date("Y-m-d", time());
	
	$r["trans_states"] = json_encode($_trans_states);
	
	//dates to show
	$r["start_date"] = date("Y.m.d. H:i:s", $trans["start_date"]);
	if ($trans["start_date"] == 0) $r["start_date"] = "-";
	$r["cancelled_date"] = date("Y.m.d. H:i:s", $trans["cancelled_date"]);
	if ($trans["cancelled_date"] == 0) $r["cancelled_date"] = "-";
	$r["fulfilled_date"] = date("Y.m.d. H:i:s", $trans["fulfilled_date"]);
	if ($trans["fulfilled_date"] == 0) $r["fulfilled_date"] = "-";
	$r["trans_id_date"] = date("Y.m.d. H:i:s", $trans["trans_id_date"]);
	if ($trans["trans_id_date"] == 0) $r["trans_id_date"] = "-";
	
	//options
	$r["options"] = "";
	foreach ($_avaliable_account_types as $account_type){
		$r["options"] .= '<option value="' . $account_type . '"';
		if ($trans["account_type"] == $account_type) $r["options"] .= ' selected';
		$r["options"] .= '>' . $_trans["account_type"][$account_type] . '</option>';
		
	}
	
	//history
	$r["history"] = "";
	$history = $sql->getArray("select * from log where trans_id='" . $trans["id"] . "' order by timestamp desc");
	foreach ($history as $hist){
		$r["history"] .= "<li><b>" . date("Y.m.d. H:i:s", $hist["timestamp"]) . "<br />" . $hist["event_type"] . "</b><br />" . $hist["comment"] . "</li>";
	}
	
	
	
	
	//other stuff for html
	if ($user["account_exp"] == 0){
		$user["account_exp"] = "-";
	} else {
		$user["account_exp"] = date("Y.m.d. H:i:s", $user["account_exp"]);
	}
	foreach ($user as $key => $u) $r["user_" . $key] = $u;
	foreach ($trans as $key => $u) $r["trans_" . $key] = $u;
	$r["address"] = $_address;
	
	$_html["content"]($r);
	
	
	
	