<?php

	admin_required();
	
	//
	//	Change user account type
	//	function/admin/change_account_type
	//
	if (isset($url[2]) && $url[2] == "change_account_type"){
		$post = trimEscapeForce($_POST, array("user_id", "trans_id", "account_type", "account_exp"));
		
		//check user, trans
		$trans = $sql->getArray("select * from trans where id='" . $post["trans_id"] . "'");
		if (count($trans) == 0){
			echo "missing trans";
			die();
		}
		$trans = $trans[0];
		
		$user = $sql->getArray("select * from user where id='" . $post["user_id"] . "'");
		if (count($user) == 0){
			echo "missing user";
			die();
		}
		$user = $user[0];
		if ($trans["user_id"] != $user["id"]){
			echo "usr mismatch to trans user id";
			die();
		}
		
		//validation
		if (!in_array($post["account_type"], $_avaliable_account_types)){
			echo "invaliud account type";
			die();
		}
		
		//calculate the end timestamp by date
		$date = DateTime::createFromFormat("Y-m-d", $post["account_exp"]);
		$date -> setTime(0,0,0);
		$date -> modify('+1 day');
		$date -> modify('-1 second');
		$post["account_exp"] = $date->getTimestamp();
		
		//setup trans
		$sql->update("trans", array("current_state"=>"ok", "fulfilled_by"=>$_SESSION["user"]["id"], "fulfilled_date"=>time()), "id", $trans["id"]);
		
		//change user
		$sql->update("user", array("account_type" => $post["account_type"], "account_exp"=>$post["account_exp"]), "id", $user["id"]);
		
		$account_type_text = $_trans["account_type"][$post["account_type"]];
		
		//send user mail
		$body = file_get_contents(ROOT . "front/mail/activate.html");
		$body = bulk_replace(array("name"=>$user["name"], "account_type" => $account_type_text, "account_exp" => date("Y.m.d. H:i:s", $post["account_exp"])), $body);
		systemmail($user["name"], $user["mail"], "A " . $account_type_text . " csomagod aktiválára került", $body);
		
		//log
		userLog($_SESSION["user"]["id"], $user["id"], $trans["id"], "trans_fulfilled","#" . $trans["id"] . " tranzakció elfogadva. Beállított új adatok (post tartalma): " . json_encode($post));
		
		$msg = 'Sikeres mentés, a felhasználót értesítettük.<br />Discord-on a következőt kell látni:<br /><b>' . $user["discord_name"] . ' => ' . $account_type_text . '</b>';
		if ($user["account_type"] == $post["account_type"]){
			$msg .= '<br /><b style="color: red; ">A user eddigi csomagja és az új között nincs eltérés, ezért nem kell a Discord-on semmit sem csinálnod!</b>';
		}
		msg($msg);
		go (URL."admin/trans");
	}
	
	
	
	
	
	echo "unknown admin function";
	die();

?>