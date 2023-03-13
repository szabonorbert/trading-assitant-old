<?php

	
	
	//
	// Login
	// function/public/login
	//
	
	if (isset($url[2]) && $url[2] == "login"){
		
		//get post fields
		$post = trimEscapeForce($_POST, array("mail", "pass", "g-recaptcha-response"));
		
		//tests
		require_once (ROOT . "engine/recaptcha.class.php");
		$recaptcha = new Recaptcha();
		$response = $recaptcha->verifyResponse($post['g-recaptcha-response']);
		
		if(isset($response['success']) and $response['success'] != 1) {
			msgError("login captcha error");
			go(URL.'login');
		}
		
		$user = $sql->getArray("select id, pass from user where mail='" . $post["mail"] . "'");
		if (count($user) == 0){
			msgError("Ismeretlen email cím.");
			go(URL."login");
		}
		
		if (!pwdCheck($post["pass"], $user[0]["pass"])){
			msgError("Hibás jelszó");
			go(URL."login");
		}
		
		//update
		$sql->update("user", array("session_id"=>session_id(), "lastlogindate"=>time()), "id", $user[0]["id"]);

		//login
		loginUser($user[0]["id"]);
		msg("Sikeres bejelentkezés.");
		go(URL);
	}
	
	//
	// Registration
	// function/public/reg
	//
	
	else if (isset($url[2]) && $url[2] == "reg"){
		
		//get post fields
		$post = trimEscapeForce($_POST, array("name", "mail", "pass", "pass2", "discord_name", "g-recaptcha-response"));
		
		//captcha test
		require_once (ROOT . "engine/recaptcha.class.php");
		$recaptcha = new Recaptcha();
		$response = $recaptcha->verifyResponse($post['g-recaptcha-response']);
		
		if(isset($response['success']) and $response['success'] != 1) {
			msgError("reg captcha error");
			go(URL.'reg');
		}
		
		//validation
		if ($post["name"] == "" || $post["mail"] == "" || $post["pass"] == "" || $post["discord_name"] == ""){
			echo "empty";
			die();
		}
		
		if (!filter_var($post["mail"], FILTER_VALIDATE_EMAIL)){
			msgError("Hibás email cím.");
			go(URL."reg");
		}
		
		if ($post["pass"] != $post["pass2"]){
			msgError("A két jelszó nem egyezik.");
			go(URL."reg");
		}
		
		//user check
		$user = $sql->getArray("select * from user where mail='" . $post["mail"] . "'");
		if (count($user) != 0){
			msgError("Már van ilyen email cím.");
			go(URL."reg");
		}
		
		//add user
		$time = time();
		$user = array();
		$user["name"] = $post["name"];
		$user["mail"] = $post["mail"];
		$user["pass"] = pwdHash($post["pass"]);
		$user["discord_name"] = $post["discord_name"];
		$user["regdate"] = $time;
		$user["moddate"] = $time;
		$user["lastlogindate"] = $time;
		$user["session_id"] = session_id();
		
		$id = $sql->insert("user", $user);
		
		//login
		loginUser($id);
		msg("Sikeres regisztráció!");
		go(URL);
		
	}
	
	//
	// Newpass
	// function/public/newpass
	//
	
	else if (isset($url[2]) && $url[2] == "newpass"){
		
		//get post fields
		$post = trimEscapeForce($_POST, array("mail", "g-recaptcha-response"));
		
		//captcha test
		require_once (ROOT . "engine/recaptcha.class.php");
		$recaptcha = new Recaptcha();
		$response = $recaptcha->verifyResponse($post['g-recaptcha-response']);
		
		if(isset($response['success']) and $response['success'] != 1) {
			msgError("newpass captcha error");
			go(URL.'newpass');
		}
		
		//validation
		if ($post["mail"] == ""){
			echo "empty";
			die();
		}
		
		if (!filter_var($post["mail"], FILTER_VALIDATE_EMAIL)){
			msgError("Hibás email cím.");
			go(URL."newpass");
		}
		
		//user check
		$user = $sql->getArray("select * from user where mail='" . $post["mail"] . "'");
		if (count($user) == 0){
			msgError("Nincs ilyen email cím.");
			go(URL."newpass");
		}
		$user = $user[0];
		
		$newpass = genRandomString();
		$hash = pwdHash($newpass);
		
		$sql->update("user", array("pass"=>$hash), "id", $user["id"]);
		
	
		
		//send mail
		$mailbody = file_get_contents(ROOT . "front/mail/newpass.html");
		$mailbody = bulk_replace(array("name"=>$user["name"], "mail"=>$user["mail"], "pass"=>$newpass, "url"=>URL), $mailbody);
		
		//function systemmail($toname, $tomail, $subject, $message)
		systemmail($user["name"], $user["mail"], "Új jelszó", $mailbody);
	
		msg("Nézd meg az email címedet!");
		go(URL."login");
	}
	
	//
	// Generate AssetsBinance CSV
	// function/public/assetsbinance
	//
	
	else if (isset($url[2]) && $url[2] == "assetsbinance"){
		
		global $_setting;
		
		//get assets from Binance
		$curl = curl_init($_setting["binance_futures_api"]);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		curl_close($curl);
		
		$result = json_decode($result, true);
		$result = $result["symbols"];
		
		//clear tmp directory
		$dir = "tmp/AssetsBinance/";
		
		//remove tmp files
		$files = scandir(ROOT . $dir);
		foreach ($files as $file){
			if ($file != "." && $file != ".."){
				unlink(ROOT . $dir . $file);
			}
		}
		
		//generate CSV files
		$record_counter = 0;
		$file_counter = 0;
		$final_links = "<ul>";
		
		$csv = "";
		
		foreach ($result as $r){
			
			$record_counter++;
			
			//begin CSV
			if ($record_counter == 1){
				$csv = "";
				$csv .= $_setting["binance_futures_csv_first_line"];
			}
			
			//add a line
			$csv .= "\n" . str_replace("{{instrument}}", $r["symbol"], $_setting["binance_futures_csv_line"]);
			
			//finish csv
			if ($record_counter == 60){
				$record_counter = 0;
				$file_counter++;
				file_put_contents(ROOT . $dir . "AssetsBinance" . $file_counter . ".csv", $csv);
				$final_links .= '<li><a target="_blank" href="' . URL . $dir . "AssetsBinance" . $file_counter . '.csv">AssetsBinance' . $file_counter . '.csv</a></li>';
			}
		}
		
		//write last data
		if ($record_counter > 0){
			$file_counter++;
			file_put_contents(ROOT . $dir . "AssetsBinance" . $file_counter . ".csv", $csv);
			$final_links .= '<li><a target="_blank" href="' . URL . $dir . "AssetsBinance" . $file_counter . '.csv">AssetsBinance' . $file_counter . '.csv</a></li>';
		}
		
		$final_links .= "</ul>";
		
		
		//tadamm
		echo $final_links;
		die();
		
	}
	
	//
	// Session
	// function/public/session
	//
	
	else if (isset($url[2]) && $url[2] == "session"){
		aprint($_SESSION);
		die();
	}
	
	
	msgError("unknown public function");
	go(URL);

	
