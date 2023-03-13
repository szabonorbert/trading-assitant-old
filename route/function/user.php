<?php
	
	login_required();
	
	//
	// Settings
	// function/user/settings
	//
	
	if (isset($url[2]) && $url[2] == "settings"){
		
		$post = trimEscapeForce($_POST, array("name", "discord_name", "mail", "pass"));
		if ($post["pass"] == ""){
			unset($post["pass"]);
		} else {
			$post["pass"] = pwdHash($post["pass"]);
		}
		
		if (!filter_var($post["mail"], FILTER_VALIDATE_EMAIL)){
			msgError("Hibás email cím");
			go(URL."settings");
		}
		
		//check mail exist
		$z = $sql->getArray("select * from user where mail='" . $post["mail"] . "' and id!='" . $_SESSION["user"]["id"] . "'");
		if (count($z) > 0){
			msgError("Ez az email cím már foglalt");
			go(URL."settings");
		}
		
		$sql->update("user", $post, "id", $_SESSION["user"]["id"]);
		
		//change immediately
		$user = $sql->getArray("select * from user where id='" . $_SESSION["user"]["id"] . "'");
		$_SESSION["user"] = $user[0];
		
		msg("Sikeres mentés");
		go(URL."settings");
	}
	
	//
	// Logout
	// function/user/logout
	//
	
	else if (isset($url[2]) && $url[2] == "logout"){
		unset($_SESSION['user']);
		msg("Sikeres kijelentkezés");
		go(URL.'login');
	}
	
	
	msgError("unknown user function");
	go(URL);

?>