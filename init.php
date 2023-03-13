<?php
	
	// url basics
	
	$url = urlExplode();
	define("DOMAIN",($_setting['https'] ? 'https' : 'http') . '://' . ($_setting['www'] ? 'www.' : '') . $_setting['domain_base'] . '/');
	define("FOLDER", DOMAIN . ($_setting['app_folder'] != "" ? $_setting['app_folder'] : ''));
	define ("URL", FOLDER);
	if (count($url) != 0){
		define ("CURRENT_URL", URL . implode('/', $url) . '/');
	} else {
		define ("CURRENT_URL", URL);
	}
	
	// connect
	
	$sql = new Connection($_setting['sql_hostname'], $_setting['sql_username'], $_setting['sql_password'], $_setting['sql_database']);
	
	//echo session_id();die();
	//check user modifications always
	if (isset($_SESSION["user"]["id"])){
		$u = $sql->getArray("select * from user where id='" . $_SESSION["user"]["id"] . "'");
		$_SESSION["user"] = $u[0];
		
		//check session & logout intruder
		if ($_SESSION["user"]["session_id"] != session_id()){
			unset($_SESSION["user"]);
			msgError("Egy másik eszközről beléptek a fiókba, így ez a munkamenet lezárult.");
			go(URL."login");
		}
	}
	
	//if logged in, set the profile stuff
	if (isset($_SESSION["user"]["id"])){
		
		//account basic settings
		$_html['betekinto_blub_color'] = 'betekinto';
		$_html['tanulo_blub_color'] = 'tanulo';
		$_html['swingtrader_blub_color'] = 'swingtrader';
		$_html['daytrader_blub_color'] = 'daytrader';
		$_html['protrader_blub_color'] = 'protrader';
		$_html['line_percent'] = '20';
		$_html['main_color'] = $_SESSION["user"]["account_type"];
		$_html['discord_name'] = $_SESSION["user"]["discord_name"];
		$_html['ftmo_active'] = "";
		$_html['binance_active'] = "";
		$_html['screenshot_active'] = "";
		$_html['account_exp'] = $_SESSION["user"]["account_exp"];
		$_html['account_exp_alerted'] = "";
		$_html['account_exp_formatted'] = date("Y.m.d.", $_SESSION["user"]["account_exp"]);
		
		//not yet activated
		if ($_html['account_exp'] == 0){
			$_html['account_exp_hide'] = "hide";
			//betekinto, default settings are OK
		}
		
		//activated but nearly expired
		else if ($_html['account_exp'] < time() + 60*60*24*3){
			$_html['account_exp_alerted'] = "alerted";
		}
		
		
		$account_type = $_SESSION["user"]["account_type"];
		
		if ($account_type == "betekinto"){
			$_html["betekinto_extraclass"] = "active";
			
		} else if ($account_type == "tanulo"){
			$_html["betekinto_extraclass"] = "before";
			$_html["tanulo_extraclass"] = "active";
			$_html['line_percent'] = '40';
			
		} else if ($account_type == "swingtrader"){
			$_html["betekinto_extraclass"] = "before";
			$_html["tanulo_extraclass"] = "before";
			$_html["swingtrader_extraclass"] = "active";
			$_html['line_percent'] = '60';
			
		} else if ($account_type == "daytrader"){
			$_html["betekinto_extraclass"] = "before";
			$_html["tanulo_extraclass"] = "before";
			$_html["swingtrader_extraclass"] = "before";
			$_html["daytrader_extraclass"] = "active";
			$_html['line_percent'] = '80';
			
		} else if ($account_type == "protrader"){
			$_html["betekinto_extraclass"] = "before";
			$_html["tanulo_extraclass"] = "before";
			$_html["swingtrader_extraclass"] = "before";
			$_html["daytrader_extraclass"] = "before";
			$_html["protrader_extraclass"] = "active";
			$_html['line_percent'] = '100';
		} else {
			echo "valami hiba történt: nincs ilyen csomag.";die();
		}
		
		//others
		if ($_SESSION["user"]["ftmo"] == 1){
			$_html["ftmo_active"] = "active";
		}
		
		if ($_SESSION["user"]["binance"] == 1){
			$_html["binance_active"] = "active";
		}
		
		if ($_SESSION["user"]["screenshot"] == 1){
			$_html["screenshot_active"] = "active";
		}
	
	}
	
	//selected menu element
	if (isset($url[0])) $_html[$url[0]."_selected"] = "selected";
	if (isset($url[1]) && $url[0] == "table") $_html[$url[1]."_selected"] = "selected";
	
	
	if (isset($_SESSION["user"]["admin"]) && $_SESSION["user"]["admin"] == 1){
		//
	} else {
		$_html["noadmin_hide"] = "hide";
	}
	
	
	// language: session-based
	
	/*if (!isset($_SESSION['lang']) || !isset($_SESSION['langs'])){
		$_SESSION['langs'] = $sql->getArray("select * from lang_avaliable order by weight");
		$_SESSION['lang'] = $_SESSION['langs'][0];
	}*/
	
	// language: url-based
	
	/*if ($_lang['multilang']){
		if (!isset($url[0])) go301(FOLDER . $_lang['default']);
		if (!isset($_SESSION['lang'])) $_SESSION['lang'] = $_lang['default'];
		$_lang['all'] = $sql -> getArray("select * from lang");
		if ($url[0] != $_SESSION['lang']){
			$l = false;
			for ($i = 0; $i<count($_lang['all']) && !$l; $i++){
				$l = ($_lang['all'][$i]['lang'] == $url[0]);
			}
			if (!$l){
				go404();
			} else {
				$_SESSION['lang'] = $url[0];
			}
		}
		$_lang['selected'] = $_SESSION['lang'];
		$url = array_slice($url, 1);
		define ("LANG", $_lang['selected']);
	}*/
	
	//URL
	
	testPreferredUrl();
	
	// basic vars for html
	
	$_html['content'] = "";
	$_html['name'] = $_setting['app_name'];
	$_html['title'] = $_setting['app_name'];
	$_html['company'] = $_setting['company'];
	$_html['description'] = "";
	$_html['robots'] = $_setting['robots'];
	$_html['domain'] = DOMAIN;
	$_html['folder'] = FOLDER;
	$_html['url'] = URL;
	$_html['current_url'] = CURRENT_URL;
	$_html['year'] = date('Y', time());
	$_html['recaptcha_public'] = $_setting['recaptcha_public'];
	if (!isset($_SESSION['user'])){
		$_html['logged_out'] = "logged_out";
		$_html['logged_out_hide'] = "hide";
	}
	
	//sort the routes
	krsort($_route);
	
	//other useful vars
	
	$_crumb = array();
	$_menu = array();
?>