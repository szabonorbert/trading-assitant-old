<?php

	login_required();

	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/table.html"));
	$r = array();
	
	//default data
	if (!isset($url[1])) $url[1] = "admiral";
	if (!isset($url[2])) $url[2] = "60";
	$exchange = strtolower($url[1]);
	$timeframe = strtolower($url[2]);
	
	//golden signal hack
	if ($url[2] == "golden"){
		$query1 = "
			SELECT data1.instrument, data1.last_update last_update_fast, data2.last_update last_update_slow FROM data data1, data data2 where
			data1.exchange='admiral' and
			data2.exchange='admiral' and
			data1.timeframe=1 and
			data2.timeframe=5 and
			data1.state0=3 and 
			data2.state0=2 and
			data1.direction != data2.direction and
			data1.instrument = data2.instrument
		";
		
		$query2 = "
			SELECT data1.instrument, data1.last_update last_update_fast, data2.last_update last_update_slow FROM data data1, data data2 where
			data1.exchange='admiral' and
			data2.exchange='admiral' and
			data1.timeframe=5 and
			data2.timeframe=60 and
			data1.state0=3 and 
			data2.state0=2 and
			data1.direction != data2.direction and
			data1.instrument = data2.instrument
		";
		
		echo "<h1>M1 <small><small>trend break</small></small> + M15 <small><small>in correction</small></small></h1>";
		$data = $sql->getArray($query1);
		foreach ($data as $k=>$v){
			$data[$k]["last_update_fast"] = date("Y.m.d. H:i", $data[$k]["last_update_fast"]);
			$data[$k]["last_update_slow"] = date("Y.m.d. H:i", $data[$k]["last_update_slow"]);
		}
		
		aprint($data);
		echo "<h1>M5 <small><small>trend break</small></small> + H1 <small><small>in correction</small></small></h1>";
		$data = $sql->getArray($query2);
		foreach ($data as $k=>$v){
			$data[$k]["last_update_fast"] = date("Y.m.d. H:i", $data[$k]["last_update_fast"]);
			$data[$k]["last_update_slow"] = date("Y.m.d. H:i", $data[$k]["last_update_slow"]);
		}
		aprint($data);
		die();
	}
	
	
	
	
	//checks
	if (!in_array($exchange, $_exchanges)) go404();
	if (!in_array($timeframe, $_timeframes)) go404();
	
	//permission check: exchanges
	if ($exchange == "ftmo"){
		if ($_SESSION["user"]["ftmo"] != 1){
			msgError("Nincs jogosultságod.");
			go(URL);
		}
	}
	
	if ($exchange == "binance"){
		if ($_SESSION["user"]["binance"] != 1){
			msgError("Nincs jogosultságod.");
			go(URL);
		}
	}
	
	//permission check: timeframes
	
	$denied = false;
	if ($_SESSION["user"]["account_type"] == "betekinto" && $timeframe != "60"){
		$denied = true;
	} else if ($_SESSION["user"]["account_type"] == "tanulo" && $timeframe != "60"){
		$denied = true;
	} else if ($_SESSION["user"]["account_type"] == "swingtrader" && ($timeframe != "60" && $timeframe != "240")){
		$denied = true;
	}
	
	if ($denied){
		msgError("Nincs jogosultságod.");
		go(URL . "table/admiral");
	}
	
	// load data
	$data = $sql->getArray("select * from data where exchange='" . $exchange . "' and timeframe='" . $timeframe . "' order by state0 desc");
	
	//obfuscating: betekinto
	// OPEN WEEKS
	/*if ($_SESSION["user"]["account_type"] == $_account_types[0]){
		$type_counter = 0;
		$pre_type = "";
		foreach ($data as $key => $value){
			if ($data[$key]["state0"] != $pre_type){
				$pre_type = $data[$key]["state0"];
				$type_counter = 0;
			}
			if ($type_counter != 0){
				$data[$key]["instrument"] = ". . .";
				$data[$key]["direction"] = ". . .";
			}
			$type_counter++;
		}
	}*/
	
	$r["table_data"] = "";
	foreach ($data as $d){
		$r["table_data"] .= '<tr class="state' . $d["state0"] .'">';
		$r["table_data"] .= '<td><i class="fa-solid fa-circle icon"></i></td>';
		$r["table_data"] .= "<td>" . $d["instrument"] . "</td>";
		if ($d["state0"] == 0){
			$r["table_data"] .= "<td>-</td>";
		} else {
			$r["table_data"] .= '<td class="';
			if ($d["state0"] == 3) $r["table_data"] .= "strike";
			$r["table_data"] .= '">' . translate("direction", $d["direction"]) . "</td>";
		}
		$r["table_data"] .= "<td>" . translate("state", $d["state0"]) . "</td>";
		$r["table_data"] .= "<!--<td>" . translate("state", $d["state1"]) . "</td>-->";
		$r["table_data"] .= "<td><small><small>" . date("Y.m.d.", $d["last_update"]) . " <small>" . date("H:i", $d["last_update"])  . "</small></small></small></td>";
		$r["table_data"] .= "</tr>";
	}
	
	
	
	
	$r["exchange"] = $exchange;
	$r[$timeframe."_selected"] = "selected";
	
	$_html['content']($r);