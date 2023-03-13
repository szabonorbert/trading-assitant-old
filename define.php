<?php
	
	////////// company settings
	$_setting['company'] = 'Trading Assistant';
	$_setting['domain_base'] = 'ta.binarych.com';
	$_setting['app_folder'] = '';
	$_setting['app_name'] = 'Trading Assistant';
	$_setting['robots'] = 'noindex, nofollow';
	
	$_setting['api_log'] = false;
	$_setting['api_key'] = "viv874K9n2";
	
	////////// basic settings
	
	$_setting['https'] = true;
	$_setting['www'] = false;
	$_setting['recaptcha_public'] = "6LcGPp4eAAAAAI8ICyzG2WhUKcMvpE1jnPcomrap";
	$_setting['recaptcha_secret'] = "6LcGPp4eAAAAAI5jZSwaCTvsHBFhHHnMEp_9_mmK";
	
	////////// database settings
	
	$_setting['sql_hostname'] = 'localhost';
	$_setting['sql_username'] = 'binarych_ta';
	$_setting['sql_password'] = 'Z9ztn{W@[~a8';
	$_setting['sql_database'] = 'binarych_ta';
	
	////////// admins
	
	$_devops = array();
	$_devops[] = array("name"=>"Szabó Norbert", "mail"=>"detenyleg.com@gmail.com");
	$_david = array("name" => "Budai Dávid", "mail" => "info@davidbudai.com");
	
	////////// mail settings
	
	$_mail['smtp'] = true;
	$_mail['host'] = 'mail.binarych.com';
	$_mail['user'] = 'ta@binarych.com';
	$_mail['pass'] = '!.~RbgO@XNsd';
	$_mail['secure'] = 'ssl';
	$_mail['port'] = 465;
	
	$_mail['from_address'] = 'ta@binarych.com';
	$_mail['from_name'] = 'Trading Assistant';
	
	////////// for assets binance
	
	$_setting["binance_futures_api"] = "https://www.binance.com/fapi/v1/exchangeInfo";
	$_setting["binance_futures_csv_first_line"] = "Name,Price,Spread,RollLong,RollShort,PIP,PIPCost,MarginCost,Leverage,LotAmount,Commission";
	$_setting["binance_futures_csv_line"] = "{{instrument}},0.01,0.0001,0,0,0.000001,0.000001,0,1,1,-0.1";
	
	////////// language settings (url-based)
	
	/*$_lang['multilang'] = false;
	$_lang['default'] = 'hu';*/
	
	////////// router settings
	
	//functions
	$_route["function/user"] = "function/user.php";
	$_route["function/user/?"] = "function/user.php";
	$_route["function/user/?/?"] = "function/user.php";
	$_route["function/admin"] = "function/admin.php";
	$_route["function/admin/?"] = "function/admin.php";
	$_route["function/admin/?/?"] = "function/admin.php";
	$_route["function/public"] = "function/public.php";
	$_route["function/public/?"] = "function/public.php";
	$_route["function/public/?/?"] = "function/public.php";
	$_route["function/public/?/?/?"] = "function/public.php";
	$_route["function/subscribe/?"] = "function/subscribe.php";
	$_route["function/img"] = "function/img.php";
	$_route["function/img/?"] = "function/img.php";
	$_route["function/img/?/?"] = "function/img.php";
	
	$_route["api/update"] = "function/api.php";
	
	//pages
	$_route[""] = "main.php";
	$_route["settings"] = "settings.php";
	$_route["login"] = "login.php";
	$_route["reg"] = "reg.php";
	$_route["newpass"] = "newpass.php";
	$_route["table"] = "table.php";
	$_route["table/?"] = "table.php";
	$_route["table/?/?"] = "table.php";
	$_route["profile"] = "profile.php";
	$_route["screenshot"] = "soon.php";
	$_route["alert"] = "soon.php";
	$_route["backtest"] = "backtest.php";
	$_route["help"] = "help.php";
	$_route["admin"] = "admin.php";
	$_route["admin/user"] = "admin_user.php";
	$_route["admin/user/?"] = "admin_user_x.php";
	$_route["admin/trans"] = "admin_trans.php";
	$_route["admin/trans/?"] = "admin_trans_x.php";
	
	$_route["subscribe"] = "subscribe.php";
	$_route["subscribe/history"] = "subscribe_history.php";
	$_route["subscribe/history/?"] = "subscribe_history_x.php";
	$_route["subscribe/table"] = "subscribe_table.php";
	//$_route["subscribe/form"] = "subscribe_form.php";
	$_route["subscribe/currentorder"] = "subscribe_currentorder.php";
	
	$_route["admin"] = "admin.php";
	$_route["admin/add"] = "admin_add.php";
	$_route["admin/?"] = "admin_x.php";
	
	//Validators
	$_exchanges = array("test", "admiral", "ftmo", "binance");
	$_timeframes = array(1, 5, 15, 60, 240);
	$_directions = array(0, 1, 2);
	$_states = array(0, 1, 2, 3);
	$_account_types = array("betekinto", "tanulo", "swingtrader", "daytrader", "protrader");
	$_prices = array(
		"betekinto" => 0,
		"tanulo" => 10,
		"swingtrader" => 40,
		"daytrader" => 120,
		"protrader" => 9999,
	);
	$_currency = "BUSD";
	$_network = "BSC Binance Smart Chain (BEP20)";
	$_address = "0xec130C7Ea176D73e64423bf5115f3F652ecaFb2d";
	$_avaliable_account_types = array("tanulo", "swingtrader", "daytrader");
	$_trans_states = array("started", "payed", "ok", "cancelled");
	
	//Translator
	$_trans = array();
	
	$_trans["direction"][0] = "unknown direction";
	$_trans["direction"][1] = "long ↗";
	$_trans["direction"][2] = "short ↘";
	
	$_trans["direction_text"][0] = "unknown direction";
	$_trans["direction_text"][1] = "long";
	$_trans["direction_text"][2] = "short";
	
	$_trans["state"][0] = "neutral";
	$_trans["state"][1] = "trending";
	$_trans["state"][2] = "in correction";
	$_trans["state"][3] = "trend break";
	
	$_trans["exchange"]["admiral"] = "Admiral Markets";
	$_trans["exchange"]["ftmo"] = "FTMO";
	$_trans["exchange"]["binance"] = "Binanace Futures";
	$_trans["exchange"]["test"] = "Test Exchange";
	
	$_trans["timeframe"][1] = "M1";
	$_trans["timeframe"][5] = "M5";
	$_trans["timeframe"][15] = "M15";
	$_trans["timeframe"][60] = "H1";
	$_trans["timeframe"][240] = "H4";
	
	$_trans["account_type"]["betekinto"] = "betekintő";
	$_trans["account_type"]["tanulo"] = "tanuló";
	$_trans["account_type"]["swingtrader"] = "swing trader";
	$_trans["account_type"]["daytrader"] = "daytrader";
	$_trans["account_type"]["protrader"] = "pro trader";
	
	
?>