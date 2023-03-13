<?php


	login_required();
	
	//
	//	Get an img
	//	function/img/get
	//
	
	if ($url[2] == "get" && isset($url[3])){
		
		//OPEN WEEKS
		/*
		if ($_SESSION["user"]["account_type"] == "betekinto"){
			msgError("Nincs jogosultságod.");
			go(URL);
		}*/
		
		$id = $sql->escape($url[3]);
		
		$data = $sql->getArray("select * from history_data where id='" . $id . "'");
		if (count($data) == 0){
			echo "missing img by ID";
			die();
		}
		$data = $data[0];
		
		$imgroute = array();
		$imgroute[] = $data["version"];
		$imgroute[] = $data["instrument"];
		$imgroute[] = $data["timeframe"];
		$imgroute[] = $data["direction"];
		$imgroute[] = $data["state"];
		$imgroute[] = $data["y"];
		$imgroute[] = sprintf("%02d", $data["m"]);
		$imgroute[] = sprintf("%02d", $data["d"]);
		$imgroute[] = sprintf("%02d", $data["h"]);
		$imgroute[] = sprintf("%02d", $data["i"]);
		
		$final_route = ROOT . "backtest-db/" . implode("-", $imgroute) . ".png";
		if (!file_exists($final_route)){
			echo "the file " . $final_route . " is not exists";
			die();
		}
		
		header("Content-type: image/png");
		header('Content-Length: ' . filesize($final_route));
		readfile($final_route);
		
		die();
	}
	
	
	echo "unknown img function";
	die();