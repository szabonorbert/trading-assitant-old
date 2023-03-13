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
		
		//is timeframe valid for seeking context and generating tbic signals (trend break in correction)
		$_context_check = false;
		$_context = 0;
		if ($post["timeframe"] == 1){
			$_context_check = true;
			$_context = 15;
		}
		if ($post["timeframe"] == 5){
			$_context_check = true;
			$_context = 60;
		}
		if ($post["timeframe"] == 15){
			$_context_check = true;
			$_context = 240;
		}
		
		//02.: Get all existing data
		
		$table = array();
		$table_tmp = $sql->getArray("select * from data where exchange='" . $post["exchange"] . "' and timeframe='" . $post["timeframe"] . "'");
		foreach ($table_tmp as $t) $table[$t["instrument"]] = $t;
		
		//+: check if context table needed & load
		
		if ($_context_check){
			$table_context = array();
			$table_tmp = $sql->getArray("select * from data where exchange='" . $post["exchange"] . "' and timeframe='" . $_context . "'");
			foreach ($table_tmp as $t) $table_context[$t["instrument"]] = $t;
		}
		
		//03.: Prepare for insert & update & alert
		
		$updater = array();
		$inserter = array();
		$alert_in_correction = array();
		$alert_trendbreak = array();
		
		foreach ($new_data as $nd){
			
			$instrument = $nd["instrument"];
			
			//instrument already exists
			if (isset($table[$instrument])){
				$updater[] = array(
					"direction" => $nd["direction"],
					"state0" => $nd["state"],
					"state1" => $table[$instrument]["state0"],
					"id" => $table[$instrument]["id"],
					"last_update" => $_time
				);
				
				//alerts
				if ($nd["state"] == 2 && $table[$instrument]["state0"] != 2){
					$alert_in_correction[$instrument] = $nd;
				} else if ($nd["state"] == 3 && $table[$instrument]["state0"] != 3){
					$alert_trendbreak[$instrument] = $nd;
				}
				
			//if not exists
			} else {
				$inserter[] = array(
					"exchange" => $post["exchange"],
					"timeframe" => $post["timeframe"],
					"instrument" => $instrument,
					"direction" => $nd["direction"],
					"state0" => $nd["state"],
					"state1" => "0",
					"last_update" => $_time
				);
				
				//alerts
				if ($nd["state"] == 2){
					$alert_in_correction[$instrument] = $nd;
				} else if ($nd["state"] == 3){
					$alert_trendbreak[$instrument] = $nd;
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
		
		//get simple webhooks
		$webhooks = array();
		$webhooks_tmp = $sql->getArray("select alias, url from webhook where exchange='" . $post["exchange"] . "' and alias like 'tf" . $post["timeframe"] . "%'");
		foreach ($webhooks_tmp as $whtmp){
			$state = explode("-",$whtmp["alias"]);
			$state = $state[1];
			$state = str_replace("s", "", $state);
			$webhooks["state".$state] = $whtmp["url"];
		}
		
		//get the special webhook
		if ($_context_check){
			$webhooks_tmp = $sql->getArray("select alias, url from webhook where exchange='" . $post["exchange"] . "' and alias = '" . $post["timeframe"] . "-" . $_context . "-tbic'");
			$webhooks["tbic"] = $webhooks_tmp[0]["url"];
		}
		
		//aprint($webhooks);die();
		
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
		
		// 07.: Alert context in correction & trend break (tbic)
		if ($_context_check && isset($webhooks["tbic"]) && count($alert_trendbreak) > 0){
			
			$webhook_url = $webhooks["tbic"];
			
			foreach ($alert_trendbreak as $instrument){
				if(
					isset($table_context[$instrument["instrument"]]) &&
					$table_context[$instrument["instrument"]]["state0"] == 2 &&
					$table_context[$instrument["instrument"]]["direction"] != $instrument["direction"]
				){
					
					$opposite_direction = 1;
					if ($instrument["direction"] == 1) $opposite_direction = 2;
					
					$sender = $timeframe;
					$message = $instrument["instrument"] . " " . $timeframe . " " . $_trans["direction_text"][$instrument["direction"]] . " trend break while context (" . $_trans["timeframe"][$_context] . ") is " . $_trans["direction_text"][$opposite_direction] . " and in correction\\n";
					sendWebhookMessage($webhook_url, $sender, $message);
					sleep(1);
				}
			}
		}
		
		echo "ok";
		die();
	}
	
	
	
	echo "unknown API route";
	die();