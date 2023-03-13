<?php

	login_required();
	admin_required();
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/admin_user.html"));
	$r = array();
	
	//user list
	$r["user_list"] = "";
	$users = $sql->getArray("select * from user");
	foreach ($users as $user){
		$r["user_list"] .= '<tr>';
		$r["user_list"] .= "<td>#" . $user["id"] . "</td>";
		$r["user_list"] .= "<td>" . $user["discord_name"] . "</td>";
		$r["user_list"] .= "<td>" . $user["mail"] . "</td>";
		$r["user_list"] .= "<td>" . $_trans["account_type"][$user["account_type"]] . "</td>";
		if ($user["account_exp"] == 0){
			$r["user_list"] .= "<td>-</td>";
		} else {
			$r["user_list"] .= "<td>" . date("Y.m.d.", $user["account_exp"]) . "</td>";
		}
		$r["user_list"] .= '<td><a href="' . URL . 'admin/user/' . $user["id"] . '"><i class="fa-solid fa-user"></i></i></td>';
	}
	
	$_html["content"]($r);