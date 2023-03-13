<?php


	login_required();
	
	//
	//	Start transaction
	//	function/subscribe/start
	//
	
	if ($url[2] == "start"){
		
		$post = trimEscapeForce($_GET, array("account_type"));
		if (!in_array($post["account_type"], $_avaliable_account_types)){
			echo "invalid account type";
			die();
		}
		
		//check if ongoing trans
		if (ongoingTransactionNum()){
			msgError("Addig nem indíthatsz új tranzakciót, amíg folyamatban van egy másik.");
			go(URL."subscribe");
		}
		
		//no backwards
		$new_index = array_search($post["account_type"], $_account_types);
		$old_index = array_search($_SESSION['user']["account_type"], $_account_types);
		if ($new_index < $old_index){
			msgError("Kisebb csomagot nem választhatsz, mint a jelenlegi. Várd meg míg a mostani lejár, utána bármelyikre elő tudsz fizetni.");
			go(URL."subscribe/table");
		}
		
		//add transaction
		$trans = array();
		$trans["user_id"] = $_SESSION["user"]["id"];
		$trans["account_type"] = $post["account_type"];
		$trans["binance"] = 0;
		$trans["ftmo"] = 0;
		$trans["screenshot"] = 0;
		$trans["current_state"] = "started";
		$trans["sum"] = $_prices[$post["account_type"]];
		$trans["curr"] = $_currency;
		$trans["start_date"] = time();
		
		$trid = $sql->insert("trans", $trans);
		
		userLog($_SESSION["user"]["id"],$_SESSION["user"]["id"],$trid,"trans_start","#" . $trid . " tranzakció indítása: " . json_encode($trans));
		
		//go
		go(URL."subscribe/currentorder");
	}
	
	//
	//	Save transaction id
	//	function/subscribe/save
	//
	
	else if ($url[2] == "save"){
		
		if (ongoingTransactionNum() == 0){
			go(URL."subscribe");
		}
		
		
		//check existence
		$post = trimEscapeForce($_POST, array("trans_id"));

		$json =
		'{
			"jsonrpc": "2.0",
			"method": "eth_getTransactionByHash",
			"params": [
				"' . $post["trans_id"] . '"
			],
			"id": 0
		}';
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL,"https://bsc-dataseed.binance.org/");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		$result = curl_exec($curl);
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		//checks
		if ($statusCode != 200){
			msgError("Valami hiba történt, nem elérhető a blokklánc (status code " . $statusCode . ")");
			go(URL."subscribe/currentorder");
		}
		
		$result = json_decode($result, true);
		
		if (isset($result["error"])){
			msgError("Hiba történt, ellenőrizd a tranzakció számát!<br />A blokklánc véleménye:<br />" . json_encode($result["error"]));
			go(URL."subscribe/currentorder");
		}
		
		if (!isset($result["result"]["to"])){
			msgError("Nincs meg a tranzakció, ellenőrizd a számát!");
			go(URL."subscribe/currentorder");
		}
		
		$trans = getLastTransaction();
		
		//check DB for existence: ok and cancelled
		$tmp = $sql->getArray("select id from trans where trans_id='" . $post["trans_id"] . "' and current_state != 'cancelled'");
		if (count($tmp)>0){
			if ($tmp[0]["id"] == $trans["id"]){
				msg("Már értesítettük az admint.");
				go(URL."subscribe/currentorder");
			} else {
				msgError("Ezt a tranzakció ID-t már valaki felhasználta.");
				go(URL."subscribe/currentorder");
			}
		}
		
		//update trans
		$sql->update("trans", array("trans_id"=>$post["trans_id"], "trans_id_date"=>time(), "current_state"=>"payed"), "id", $trans["id"]);
		
		//log
		userLog($_SESSION["user"]["id"],$_SESSION["user"]["id"],$trans["id"],"trans_save","#" . $trans["id"] . " tranzakcióhoz új ID elmentve: " . $post["trans_id"]);
		
		//mail
		$body = file_get_contents(ROOT . "front/mail/admin_newtrans.html");
		$body = bulk_replace(array(
			"discord_name"=>$_SESSION["user"]["discord_name"],
			"mail"=>$_SESSION["user"]["mail"],
			"account_type"=>$_trans["account_type"][$trans["account_type"]],
			"sum" => $trans["sum"],
			"curr" => $trans["curr"],
			"trans_id"=>$trans["id"]
		),$body);
		$subject = "Új kifizetés: " . $_trans["account_type"][$trans["account_type"]] . " #" . $trans["id"];
		
		//send
		adminmail($subject, $body);
		systemmail($_david["name"], $_david["mail"], $subject, $body);
		
		msg("Sikeres mentés");
		go(URL."subscribe/currentorder");
		
	}
	
	
	
	//
	//	Cancel transaction
	//	function/subscribe/cancel
	//
	
	else if ($url[2] == "cancel"){
		
		if (ongoingTransactionNum() == 0){
			go(URL."subscribe");
		}
		
		$trans = getLastTransaction();
		
		$sql->update("trans", array("current_state"=>"cancelled", "cancelled_by"=>$_SESSION["user"]["id"], "cancelled_date"=>time()), "id", $trans["id"]);
		
		userLog($_SESSION["user"]["id"],$_SESSION["user"]["id"],$trans["id"],"trans_cancel","#" . $trans["id"] . " tranzakció visszavonva");
		
		msg("Tranzakció visszavonva.");
		go(URL."subscribe");
	
	}
	
	//
	//	Check transaction
	//	function/subscribe/check
	//
	
	else if ($url[2] == "check"){
		
		die();
		
		
		$post = trimEscapeForce($_POST, array("id"));
		//$post["id"] = "";
		$json =
		'{
			"jsonrpc": "2.0",
			"method": "eth_getTransactionByHash",
			"params": [
				"' . $post["id"] . '"
			],
			"id": 0
		}';
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL,"https://bsc-dataseed.binance.org/");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		$result = curl_exec($curl);
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		//checks
		if ($statusCode != 200){
			msgError("Valami hiba történt, nem elérhető a blokklánc (status code " . $statusCode . ")");
			go(URL."subscribe/currentorder");
		}
		
		$result = json_decode($result, true);
		
		if (isset($result["error"])){
			msgError("Hiba történt, ellenőrizd a tranzakció számát!<br />A BSC lánc ezt mondja:<br />" . json_encode($result["error"]));
			go(URL."subscribe/currentorder");
		}
		
		if (!isset($result["result"]["to"])){
			msgError("Nincs meg a tranzakció, ellenőrizd a számát!");
			go(URL."subscribe/currentorder");
		}
		
		$result = $result["result"];
		//if (!isset($result["to"]) || !isset($result[""]
		aprint($result);
		
		//todo: check to, check amount, check transaction status
		
		die();
	}
	
	
	echo "unknown subscribe function";
	die();