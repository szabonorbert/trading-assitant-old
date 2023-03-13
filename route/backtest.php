<?php

	login_required();
	
	$_pagination = 50;
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/backtest.html"));
	$r = array();
	
	//permission check
	
	$denied = false;
	if ($_SESSION["user"]["account_type"] == "betekinto") $denied = true;
	if ($denied){
		msgError("Nincs jogosultságod.");
		go(URL . "");
	}
	
	//default data & validations
	
	$instrument = "all";
	if (isset($_GET["instrument"])) $instrument = $sql->escape($_GET["instrument"]);
	$timeframe = "60";
	if (isset($_GET["timeframe"])){
		$timeframe = $sql->escape($_GET["timeframe"]);
		if (!in_array($timeframe, $_timeframes)){
			echo "invalid timeframe";
			die();
		}
	}
	$state = "all";
	if (isset($_GET["state"])){
		$state = $sql->escape($_GET["state"]);
		if (!in_array($state, $_states)){
			echo "invalid state";
			die();
		}
	}
	$y = "all";
	if (isset($_GET["y"]) && is_numeric($_GET["y"])) $y = $sql->escape($_GET["y"]);
	$m = "all";
	if (isset($_GET["m"]) && is_numeric($_GET["m"])) $m = $sql->escape($_GET["m"]);
	$d = "all";
	if (isset($_GET["d"]) && is_numeric($_GET["d"])) $d = $sql->escape($_GET["d"]);
	
	$h_from = 0;
	if (isset($_GET["h_from"]) && is_numeric($_GET["h_from"])) $h_from = $sql->escape($_GET["h_from"]);
	$h_to = 24;
	if (isset($_GET["h_to"]) && is_numeric($_GET["h_to"])) $h_to = $sql->escape($_GET["h_to"]);
	
	$page = 1;
	if (isset($_GET["page"]) && is_numeric($_GET["page"])) $page = $sql->escape($_GET["page"]);
	$page = (int)$page;
	if ($page < 1) $page = 1;
	if ($page > 10000){
		echo "page error";
		die();
	}
	
	//query building
	$query_cond = array();
	if ($instrument != "all") $query_cond[] = "instrument='" . $instrument . "'";
	if ($timeframe != "all") $query_cond[] = "timeframe='" . $timeframe . "'";
	if ($state != "all") $query_cond[] = "state='" . $state . "'";
	if ($y != "all") $query_cond[] = "y='" . $y . "'";
	if ($m != "all") $query_cond[] = "m='" . $m . "'";
	if ($d != "all") $query_cond[] = "d='" . $d . "'";
	if ($h_from != 0 || $h_to != 24){
		if ($h_from < $h_to){
			$query_cond[] = "h>='" . $h_from . "'";
			$query_cond[] = "h<='" . $h_to . "'";
		} else if ($h_from == $h_to){
			$query_cond[] = "h='" . $h_from . "'";
		} else {
			$query_cond[] = "(h>='" . $h_from . "' or h<='" . $h_to . "')";
		}
	}
	
	// get the paginator details
	$query_c = "select count(*) c from history_data";
	if (count($query_cond) > 0) $query_c .= " where " . implode(" and ", $query_cond);
	$query_c = $sql->getArray($query_c);
	$all_data_num = $query_c[0]["c"];
	$all_page_num = floor ($all_data_num / $_pagination) + 1;
	
	if ($page > $all_page_num) $page = $all_page_num;
	
	$r["prev_page"] = $page - 1;
	if ($r["prev_page"] < 1) $r["prev_page_hide"] = "hide";
	$r["next_page"] = $page + 1;
	$r["all_page"] = $all_page_num;
	if ($r["next_page"] > $all_page_num) $r["next_page_hide"] = "hide";
	
	$r["current_page"] = $page;
	$r["paginator_options"] = "";
	for ($i=1; $i <= $all_page_num; $i++){
		$r["paginator_options"] .= '<option ';
		if ($page == $i) $r["paginator_options"] .= ' selected ';
		$r["paginator_options"] .= 'value="' . $i . '">' . $i . '</option>';
	}
	
	//echo $all_data_num . " - " . $all_page_num . " - " . $page;
	
	// build the real select query
	$query = "select * from history_data";
	if (count($query_cond) > 0) $query .= " where " . implode(" and ", $query_cond);
	$query .= " order by y, m, d, h, i";
	$query .= " limit " . $_pagination . " offset " . ($page-1) * $_pagination;
	
	//load data
	$datas = $sql->getArray($query);
	
	//build html
	$r["table_data"] = "";
	foreach ($datas as $data){
		$r["table_data"] .= '<tr>';
		$r["table_data"] .= '<td class="clickable"><i class="fa-solid fa-circle icon"></i></td>';
		$r["table_data"] .= '<td class="clickable">' . $data["instrument"] . "</td>";
		$r["table_data"] .= '<td class="clickable">' . translate("timeframe", $data["timeframe"]) . "</td>";
		if ($data["state"] == 0){
			$r["table_data"] .= '<td class="clickable">-</td>';
		} else {
			$r["table_data"] .= '<td class="clickable';
			if ($data["state"] == 3) $r["table_data"] .= ' strike';
			$r["table_data"] .= '">' . translate("direction", $data["direction"]) . '</td>';
		}
		$r["table_data"] .= '<td class="clickable">' . translate("state", $data["state"]) . "</td>";
		$r["table_data"] .= '<td class="clickable">' . $data["y"] . "." . sprintf("%02d", $data["m"]) . "." . sprintf("%02d", $data["d"]) . ".</td>";
		$r["table_data"] .= '<td class="clickable">' . sprintf("%02d", $data["h"]) . ":" . sprintf("%02d", $data["i"]) . "</td>";
		$r["table_data"] .= '<td><a target="_blank" href="' . URL . 'function/img/get/' . $data["id"] . '"><i class="fa-solid fa-eye open"></i></a></td>';
		$r["table_data"] .= "</tr>";
	}
	
	//build selectors
	/*$r["state_select"] = "";
	$r["state_select"] .= '<option value="all"';
	$r["state_select"] .= '>összes</option>';
	if ($state == "all") $r["state_select"] .= " selected";
	foreach ($_states as $s){
		$r["state_select"] .= '<option value="' . $s . '"';
		if ($s == $state) $r["state_select"] .= ' selected';
		$r["state_select"] .= '>' . translate("state", $s) . "</option>";
	}*/
	
	$r["state_select"] = '<option value="3">trend break</option>';
	
	$r["instrument_select"] = "";
	$r["instrument_select"] .= '<option value="all"';
	if ($instrument == "all") $r["instrument_select"] .= " selected";
	$r["instrument_select"] .= '></option>';
	$tmp = $sql->getArray("select distinct instrument from history_data");
	foreach ($tmp as $i){
		$r["instrument_select"] .= '<option value="' . $i["instrument"] . '"';
		if ($i["instrument"] == $instrument) $r["instrument_select"] .= ' selected';
		$r["instrument_select"] .= '>' . $i["instrument"] . "</option>";
	}
	
	$r["timeframe_select"] = "";
	$tmp = $sql->getArray("select distinct timeframe from history_data");
	foreach ($tmp as $i){
		$r["timeframe_select"] .= '<option value="' . $i["timeframe"] . '"';
		if ($i["timeframe"] == $timeframe) $r["timeframe_select"] .= ' selected';
		$r["timeframe_select"] .= '>' . translate("timeframe", $i["timeframe"]) . "</option>";
	}
	
	$r["year_select"] = "";
	$r["year_select"] .= '<option value="all"';
	if ($y == "all") $r["year_select"] .= " selected";
	$r["year_select"] .= '></option>';
	$tmp = $sql->getArray("select distinct y from history_data");
	foreach ($tmp as $i){
		$r["year_select"] .= '<option value="' . $i["y"] . '"';
		if ($i["y"] == $y) $r["year_select"] .= ' selected';
		$r["year_select"] .= '>' . $i["y"] . "</option>";
	}
	
	$r["month_select"] = "";
	$r["month_select"] .= '<option value="all"';
	if ($m == "all") $r["month_select"] .= " selected";
	$r["month_select"] .= '></option>';
	for ($i=1; $i <= 12; $i++){
		$r["month_select"] .= '<option value="' . $i . '"';
		if ($i == $m) $r["month_select"] .= ' selected';
		$r["month_select"] .= '>' . sprintf("%02d", $i) . "</option>";
	}
	
	$r["day_select"] = "";
	$r["day_select"] .= '<option value="all"';
	if ($d == "all") $r["day_select"] .= " selected";
	$r["day_select"] .= '></option>';
	for ($i=1; $i <= 31; $i++){
		$r["day_select"] .= '<option value="' . $i . '"';
		if ($i == $d) $r["day_select"] .= ' selected';
		$r["day_select"] .= '>' . sprintf("%02d", $i) . "</option>";
	}
	
	$r["h_from_select"] = "";
	/*$r["h_from_select"] .= '<option value="0"';
	if ($h_from == 0) $r["h_from_select"] .= " selected";
	$r["h_from_select"] .= '></option>';*/
	for ($i=0; $i <= 24; $i++){
		$r["h_from_select"] .= '<option value="' . $i . '"';
		if ($i == $h_from) $r["h_from_select"] .= ' selected';
		$r["h_from_select"] .= '>' . sprintf("%02d", $i) . ":00</option>";
	}
	
	$r["h_to_select"] = "";
	/*$r["h_to_select"] .= '<option value="24"';
	if ($h_to == 24) $r["h_to_select"] .= " selected";
	$r["h_to_select"] .= '></option>';*/
	for ($i=0; $i <= 24; $i++){
		$r["h_to_select"] .= '<option value="' . $i . '"';
		if ($i == $h_to) $r["h_to_select"] .= ' selected';
		$r["h_to_select"] .= '>' . sprintf("%02d", $i) . ":00</option>";
	}
	
	//all filtered data num on front
	$r["all_data_num"] = number_format($all_data_num, 0, "", " ");
	
	$r["backtest_info"] = file_get_contents(ROOT . "backtest_info.html");
	
	//full db num on front
	$full_db_num = $sql->getArray("select count(*) c from history_data");
	$full_db_num = $full_db_num[0]["c"];
	$r["full_db_num"] = number_format($full_db_num, 0, "", " ");
	
	$_html['content']($r);