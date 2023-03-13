<?php

	login_required();
	admin_required();
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/admin_trans.html"));
	$r = array();
	
	
	//todo list
	$r["todo_trans_list"] = "";
	$trans = $sql->getArray("select trans.*, user.discord_name from trans, user where current_state='payed' and trans.user_id = user.id");
	foreach ($trans as $tran){
		$r["todo_trans_list"] .= "<tr>";
		$r["todo_trans_list"] .= '<td>#' . $tran["id"] . '</td>';
		$r["todo_trans_list"] .= '<td><a target="_blank" href="' . URL . 'admin/user/' . $tran["user_id"] . '">'. $tran["discord_name"] .  '</td>';
		$r["todo_trans_list"] .= '<td>' . date("Y.m.d. H:i:s", $tran["start_date"]) . '</td>';
		$r["todo_trans_list"] .= '<td>' . date("Y.m.d. H:i:s", $tran["trans_id_date"]) . '</td>';
		$r["todo_trans_list"] .= '<td>' . $tran["current_state"] . '</td>';
		$r["todo_trans_list"] .= '<td>' . $_trans["account_type"][$tran["account_type"]] . '</td>';
		$r["todo_trans_list"] .= '<td>' . $tran["sum"] . ' ' . $tran["curr"] . '</td>';
		$r["todo_trans_list"] .= '<td><a href="' . URL . 'admin/trans/' . $tran["id"] . '"><i class="fa-solid fa-money-bill-1-wave"></i></a></td>';
		$r["todo_trans_list"] .= "</tr>";
	}
	
	if (count($trans) == 0){
		$r["hide_if_no_todo"] = "hide";
	}
	
	
	//other list
	$r["other_trans_list"] = "";
	$trans = $sql->getArray("select trans.*, user.discord_name from trans, user where current_state!='payed' and trans.user_id = user.id order by id desc limit 30");
	foreach ($trans as $tran){
		$r["other_trans_list"] .= "<tr>";
		$r["other_trans_list"] .= '<td>#' . $tran["id"] . '</td>';
		$r["other_trans_list"] .= '<td><a target="_blank" href="' . URL . 'admin/user/' . $tran["user_id"] . '">'. $tran["discord_name"] .  '</td>';
		$r["other_trans_list"] .= '<td>' . date("Y.m.d. H:i:s", $tran["start_date"]) . '</td>';
		$r["other_trans_list"] .= '<td>' . $tran["current_state"] . '</td>';
		$r["other_trans_list"] .= '<td>' . $_trans["account_type"][$tran["account_type"]] . '</td>';
		$r["other_trans_list"] .= '<td>' . $tran["sum"] . ' ' . $tran["curr"] . '</td>';
		$r["other_trans_list"] .= '<td><a href="' . URL . 'admin/trans/' . $tran["id"] . '"><i class="fa-solid fa-money-bill-1-wave"></i></a></td>';
		$r["other_trans_list"] .= "</tr>";
	}
	
	$_html["content"]($r);