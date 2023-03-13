<?php
	
	//
	// UPDATE the data tables
	// api/update
	//
	
	if (isset($url[1]) && $url[1] == "update"){
		
		$_time = time();
		if ($_setting['api_log']) file_put_contents(ROOT . "apilog/".$_time."_".getmypid().".log", json_encode($_POST));
		
		$post = trimEscapeForce($_POST, array("key", "exchange", "timeframe", "data"));
		
		//01.: Validations & preloading
		
		if ($_POST["key"] != $_setting['api_key']){
			echo "key mismatch";
			die();
		}
		
		$post["exchange"] = preg_replace("/[0-9]/", "", $post["exchange"]);
		
		if (
			!in_array($post["exchange"], $_exchanges) ||
			!in_array($post["timeframe"], $_timeframes)
		){
			echo "invalid data in exchange or timeframe";
			die();
		}
		
		$new_data = array();
		$instruments = array();
		$all_cycle_valid = true;
		
		$data = explode(" ", $post["data"]);
		foreach ($data as $d){
			$d = trim($d);
			if ($d != ""){
				$d = explode("-", $d);
				if (count($d) == 3 && $d[0] != "" && $d[1] != "" && $d[2] != ""){
					
					if (in_array($d[1], $_directions) && in_array($d[2], $_states)){
					
						$new_data[] = array(
							"instrument" => $d[0],
							"direction" => $d[1],
							"state" => $d[2]
						);
						
						$instruments[] = $d[0];
					
					} else {
						$all_cycle_valid = false;
					}
				}
			}
			if (!$all_cycle_valid){
				echo "invalid data";
				die();
			}
		}
		
		//02.: Get all existing data
		
		$table_tmp = $sql->getArray("select * from data where exchange='" . $post["exchange"] . "' and timeframe='" . $post["timeframe"] . "'");
		$table = array();
		foreach ($table_tmp as $t) $table[$t["instrument"]] = $t;
		
		//03.: Prepare for insert & update & alert
		
		$updater = array();
		$inserter = array();
		$alert_in_correction = array();
		$alert_trendbreak = array();
		
		foreach ($new_data as $nd){
			
			//instrument already exists
			if (isset($table[$nd["instrument"]])){
				$updater[] = array(
					"direction" => $nd["direction"],
					"state0" => $nd["state"],
					"state1" => $table[$nd["instrument"]]["state0"],
					"id" => $table[$nd["instrument"]]["id"],
					"last_update" => $_time
				);
				
				//alerts
				if ($nd["state"] == 2 && $table[$nd["instrument"]]["state0"] != 2){
					$alert_in_correction[] = $nd;
				} else if ($nd["state"] == 3 && $table[$nd["instrument"]]["state0"] != 3){
					$alert_trendbreak[] = $nd;
				}
				
			//if not exists
			} else {
				$inserter[] = array(
					"exchange" => $post["exchange"],
					"timeframe" => $post["timeframe"],
					"instrument" => $nd["instrument"],
					"direction" => $nd["direction"],
					"state0" => $nd["state"],
					"state1" => "0",
					"last_update" => $_time
				);
				
				//alerts
				if ($nd["state"] == 2){
					$alert_in_correction[] = $nd;
				} else if ($nd["state"] == 3){
					$alert_trendbreak[] = $nd;
				}
			}
		}
		
		// 04.: Insert
		
		foreach ($inserter as $insert){
			$sql->insert("data", $insert);
		}
		
		// 05.: Update
		
		foreach ($updater as $update){
			$id = $update["id"];
			unset($update["id"]);
			$sql->update("data", $update, "id", $id);
		}
		
		
		// 06.: Alert preload
		
		$webhooks = array();
		$webhooks_tmp = $sql->getArray("select state, url from webhook where exchange='" . $post["exchange"] . "' and timeframe='" . $post["timeframe"] . "'");
		foreach ($webhooks_tmp as $whtmp){
			$webhooks["state".$whtmp["state"]] = $whtmp["url"];
		}
		
		// 06.: Variables for sending messages
		
		//$exchange = translated("exchange", $post["exchange"]);
		$timeframe = translated("timeframe", $post["timeframe"]);
		
		// 07.: Alert in correction
		
		if (isset($webhooks["state2"]) && count($alert_in_correction) > 0){
			
			$webhook_url = $webhooks["state2"];
			
			foreach ($alert_in_correction as $instrument){
				$sender = $timeframe;
				$message = $instrument["instrument"] . " " . $timeframe . " " . $_trans["direction_text"][$instrument["direction"]] . " trend in correction\\n";
				sendWebhookMessage($webhook_url, $sender, $message);
				sleep(1);
			}
		}
		
		// 06.: Alert trend break
		
		if (isset($webhooks["state3"]) && count($alert_trendbreak) > 0){
			
			$webhook_url = $webhooks["state3"];
			
			foreach ($alert_trendbreak as $instrument){
				$sender = $timeframe;
				$message = $instrument["instrument"] . " " . $timeframe . " " . $_trans["direction_text"][$instrument["direction"]] . " trend break\\n";
				sendWebhookMessage($webhook_url, $sender, $message);
				sleep(1);
			}
		}
		
		echo "ok";
		die();
	}
	
	
	
	echo "unknown API route";
	die();