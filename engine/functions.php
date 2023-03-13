<?php
	
	
	
	function ongoingTransactionNum(){
		global $sql;
		if (!isset($_SESSION["user"])){
			echo "missing user";
			die();
		}
		$res = $sql->getArray("select count(*) c from trans where user_id='" . $_SESSION["user"]["id"] . "' and (current_state='started' or current_state='payed')");
		return $res[0]['c'];
	}
	
	function getLastTransaction(){
		global $sql;
		if (!isset($_SESSION["user"])){
			echo "missing user";
			die();
		}
		$res = $sql->getArray("select * from trans where user_id='" . $_SESSION["user"]["id"] . "' order by start_date desc limit 1");
		if (count($res) == 0){
			echo "missing transaction";
			die();
		}
		return $res[0];
	}
	
	function userLog($actor_id, $subject_id, $trans_id, $event_type, $comment){
		global $sql;
		$sql->insert("log", array(
			"actor_id" => $actor_id,
			"subject_id" => $subject_id,
			"trans_id" => $trans_id,
			"event_type" => $event_type,
			"comment" => $comment,
			"timestamp" => time()
		));
	}
	
	
	function sendWebhookMessage($webhook_url, $sender, $message){
		$content = file_get_contents (ROOT . "front/discord_message_template.json");
		
		$content = str_replace("{{sender}}", $sender, $content);
		$content = str_replace("{{message}}", $message, $content);
		$ch = curl_init($webhook_url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		
		curl_close($ch);
	}
	
	function translate($key, $alias){
		global $_trans;
		if (isset($_trans[$key][$alias])) return $_trans[$key][$alias];
		return $alias;
	}
	
	function translated($key, $alias){
		return translate($key, $alias);
	}
	
	
	
	
	
	//
	//	User & password & msg
	//
	
	function requires_login(){
		if (!isset($_SESSION['user'])) go(URL . 'login');
	}
	function login_required(){requires_login();}
	
	function loginUser($id){
		global $sql;
		$user = $sql->getArray("select * from user where id='" . $sql->escape($id) . "'");
		if (count($user) == 0){
			echo "missing user";
			die();
		}
		$_SESSION["user"] = $user[0];
	}
	
	function requires_admin(){
		if (!isset($_SESSION['user'])) go(URL . 'login');
		if (!isset($_SESSION['user']['admin'])) go(URL);
		if ($_SESSION['user']['admin'] == 0){
			echo "admin feature";
			die();
		}
	}
	function admin_required(){requires_admin();}
	
	
	
	
	// get Page with cookie
	function getPageWithCookie($url, $cookie, $postArray=array()){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		if (count($postArray) > 0){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POST, count($postArray));
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postArray));
		} else {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		}

		$headers = array();
		$headers[] = 'Connection: keep-alive';
		$headers[] = 'Cache-Control: max-age=0';
		$headers[] = 'Upgrade-Insecure-Requests: 1';
		$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36';
		$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
		$headers[] = 'Accept-Encoding: gzip, deflate';
		$headers[] = 'Accept-Language: hu-HU,hu;q=0.9,en-US;q=0.8,en;q=0.7';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		$return = curl_getinfo($ch);
		$return["content"] = $result;
		curl_close ($ch);

		return $return;
	}
	
	
	// Selector
	function getDataBySelector($site, $selector){
		require_once ROOT . "engine/simpledom/simple_html_dom.php";
		$html = str_get_html(getHtml($site));		
		$e = $html->find($selector, 0);
		
		//get result
		if ($e == NULL) return false;
		$return = $e->outertext;
		
		//clean up
		$html -> clear();
		unset($html);
		
		//return
		return $return;
	}
	
	function getHtml($url){
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url
		]);
		$resp = curl_exec($curl);
		curl_close($curl);
		return $resp;
	}
	
	
	//Change OK state
	function checkOK($id, $type){
		global $sql;
		$id = $sql->escape($id);
		
		if ($type == "tender"){
			
			$c = $sql->getArray("select id from tender where id='" . $id . "'");
			if (count($c) == 0){
				echo "checkOK: ismeretlen tender";
				die();
			}
			
			$ok = 0;
			
			//test1
			
			$tz = $sql->getArray("
			select id
			from tender
			where
				name != '' and
				o_name != '' and
				support_type_id != '0' and
				ft_min != '0' and
				ft_max != '0' and
				p_min != '0' and
				p_max != '0' and
				subm_start != '0000-00-00' and
				subm_end != '0000-00-00' and
				site != '' and
				id = '" . $id . "'
			");
			if (count($tz) > 0) $ok++;
			
			//test2
			
			$labelcount = $sql->getArray("select count(*) c from tender_label where tender_id='" . $id . "'");
			if ($labelcount[0]["c"] > 0) $ok++;
			
			//test3
			
			$regioncount = $sql->getArray("select count(*) c from tender_region where tender_id='" . $id . "'");
			if ($regioncount[0]["c"] > 0) $ok++;
			
			//set
			if ($ok == 3){
				$sql->update("tender", array("ok"=>"1"), "id", $id);
			} else {
				$sql->update("tender", array("ok"=>"0"), "id", $id);
			}
			
		} else if ($type == "client"){
			
			$c = $sql->getArray("select ok from client where id='" . $id . "'");
			if (count($c) == 0){
				echo "checkOK: ismeretlen client";
				die();
			}
			$ok = 0; 
			
			//test 1
			$tz = $sql->getArray("
				select id
				from client
				where
					name != '' and
					fullname != '' and
					found != '0' and
					legal_id != '0' and
					region_id != '0' and
					gfo_id != '0' and
					(d1 != '0' or last_net != '0') and
					id = '" . $id . "'
			");
			
			if (count($tz) > 0) $ok++;
			
			//test2
			
			$labelcount = $sql->getArray("select count(*) c from client_label where client_id='" . $id . "'");
			if ($labelcount[0]["c"] > 0) $ok++;
			
			//test3
			$tcount = $sql->getArray("select count(*) c from client_first_teaor where client_id='" . $id . "'");
			if ($tcount[0]["c"] > 0) $ok++;
		
			
			//set
			if ($ok == 3){
				$sql->update("client", array("ok"=>"1"), "id", $id);
			} else {
				$sql->update("client", array("ok"=>"0"), "id", $id);
			}
			
		} else {
			echo "invalid checkOK type";
			die();
		}
	}
	
	//Evaluation of tender and client
	function evalConn($tender_id, $client_id, $labels_count = true){
		global $sql;
		
		//get client & tender
		$tender = $sql->getArray("select name, id from tender where id='" . $sql->escape($tender_id) . "'");
		if (count($tender) == 0){
			echo "evalConn error: unknown tender";
			die();
		}
		$tender = $tender[0];
		$client = $sql->getArray("select name, id, legal_id, gfo_id, region_id from client where id='" . $sql->escape($client_id) . "'");
		if (count($client) == 0){
			echo "evalConn error: unknown client";
			die();
		}
		$client = $client[0];
		
		
		$ret = array();
		$ret["client"]["id"] = $client["id"];
		$ret["client"]["name"] = $client["name"];
		$ret["tender"]["id"] = $tender["id"];
		$ret["tender"]["name"] = $tender["name"];
		//
		// List matching labels
		//
		
		$ret["label"]["ok"] = false;
		$ret["label"]["list"] = array();
		
		$tlabels = array();
		$tls = $sql->getArray("select label.name, label.id from label, tender_label where tender_label.tender_id='" . $tender["id"] . "' and tender_label.label_id = label.id");
		foreach ($tls as $tl){
			$tlabels[$tl["id"]] = $tl["name"];
		}
		$clabels = $sql->getArray("select label_id id from client_label where client_id='" . $client_id . "'");
		foreach ($clabels as $cl){
			if (isset($tlabels[$cl["id"]])){
				$ret["label"]["list"][$cl["id"]] = $tlabels[$cl["id"]];
			}
		}
		
		if (count($ret["label"]["list"]) != 0) $ret["label"]["ok"] = true;
		
		//if not important
		if (!$labels_count) $ret["label"]["ok"] = true;

		//aprint($ret);
		
		//
		// CHECK RESTRICTIONS
		//
		
		//01.: Legal
		$legals = $sql->getArray("select legal_id from tender_legal where tender_id='" . $tender["id"] . "'");
		$ret["legal"]["ok"] = false;
		$ret["legal"]["msg"] = "";
		
		foreach ($legals as $legal) if ($client["legal_id"] == $legal["legal_id"]) $ret["legal"]["ok"] = true;
		if ($ret["legal"]["ok"]){
			$ret["legal"]["msg"] = $sql->getArray("select name from legal where id='" . $client["legal_id"] . "'");
			$ret["legal"]["msg"] = $ret["legal"]["msg"][0]["name"];
		}
		
		//aprint($ret);
		
		//02.: GFO
		$ret["gfo"]["ok"] = false;
		$ret["gfo"]["msg"] = "";
		$gfos = $sql->getArray("select gfo_id from tender_gfo where tender_id='" . $tender["id"] . "'");
		foreach ($gfos as $gfo) if ($client["gfo_id"] == $gfo["gfo_id"]) $ret["gfo"]["ok"] = true;
		if ($ret["gfo"]["ok"]){
			$ret["gfo"]["msg"] = $sql->getArray("select name from gfo where id='" . $client["gfo_id"] . "'");
			$ret["gfo"]["msg"] = $ret["gfo"]["msg"][0]["name"];
		}
		
		//aprint($ret);
		
		//03.: REGION
		$ret["region"]["ok"] = false;
		$ret["region"]["msg"] = "";
		$regions = $sql->getArray("select region_id from tender_region where tender_id='" . $tender["id"] . "'");
		foreach ($regions as $region) if ($client["region_id"] == $region["region_id"]) $ret["region"]["ok"] = true;
		if ($ret["region"]["ok"]){
			$ret["region"]["msg"] = $sql->getArray("select name from region where id='" . $client["region_id"] . "'");
			$ret["region"]["msg"] = $ret["region"]["msg"][0]["name"];
		}
		
		//aprint($ret);
		
		//03.: FIRST TEÁOR
		$ret["first_teaor"]["ok"] = false;
		$ret["first_teaor"]["msg"] = "";
		$client_first_teaor = $sql->getArray("select teaor_id from client_first_teaor where client_id='" . $client_id . "'");
		if (count($client_first_teaor) == 0){
			$ret["first_teaor"]["msg"] = "Az ügyfélhez nincs megadva elsődleges TEÁOR";
		} else {
			$tender_first_teaors = $sql->getArray("select teaor_id from tender_first_teaor where tender_id='" . $tender_id . "'");
			foreach ($tender_first_teaors as $tft){
				foreach ($client_first_teaor as $cft){
					if ($cft["teaor_id"] == $tft["teaor_id"]) $ret["first_teaor"]["ok"] = true;
				}
			}
			
			if ($ret["first_teaor"]["ok"]){
				$ret["first_teaor"]["msg"] = $sql->getArray("select name from teaor where id='" . $client_first_teaor[0]["teaor_id"] . "'");
				$ret["first_teaor"]["msg"] = $ret["first_teaor"]["msg"][0]["name"];
			}
			
			if (count ($tender_first_teaors) == 0){
				$ret["first_teaor"]["ok"] = true;
				$ret["first_teaor"]["msg"] = "A pályázatnál nem számít, hogy mi a főtevékenység.";
			}
		}
		
		//aprint($ret);
		
		//04.: SECOND TEÁOR
		$ret["second_teaor"]["ok"] = false;
		$ret["second_teaor"]["msg"] = "";
		
		$client_second_teaor = $sql->getArray("select teaor_id from client_second_teaor where client_id='" . $client_id . "'");
		$tender_second_teaor = $sql->getArray("select teaor_id from tender_second_teaor where tender_id='" . $tender_id . "'");
		
		//plus: add client first teaor
		$add_teaor = $sql->getArray("select teaor_id from client_first_teaor where client_id='" . $client_id . "'");
		if (count($add_teaor) > 0) $client_second_teaor[]["teaor_id"] = $add_teaor[0]["teaor_id"];
		
		$match_array = array();
		foreach ($tender_second_teaor as $tst){
			foreach ($client_second_teaor as $cst){
				if ($tst["teaor_id"] == $cst["teaor_id"]) $match_array[] = $cst["teaor_id"];
			}
		}
		
		if (count($match_array) == 0){
			if (count($tender_second_teaor) == 0){
				$ret["second_teaor"]["ok"] = true;
				$ret["second_teaor"]["msg"] = "A pályázatnál nincs megjelölve a megvalósítható tevékenységek listája.";
			}
		} else {
			$ret["second_teaor"]["ok"] = true;
			$m = array();
			foreach ($match_array as $ma) $m[] = 'id="' . $ma . '"';
			$teaor_names = $sql->getArray("select name, id from teaor where " . implode(' or ', $m));
			$names = array();
			foreach ($teaor_names as $n){
				//$names[] = $n["name"];
				$ret["second_teaor"]["msg"] .= "<li>" . $n["name"] . "</li>";
			}
			$ret["second_teaor"]["msg"].="</ul>";
			//$ret["second_teaor"]["msg"] = "A partner számára a pályázat által megvalósítható tevékenységek:<ul>" . implode(", ", $names) . ;
		}
		
		//percent
		$c = 0;
		if ($ret["label"]["ok"]) $c++;
		if ($ret["legal"]["ok"]) $c++;
		if ($ret["gfo"]["ok"]) $c++;
		if ($ret["region"]["ok"]) $c++;
		if ($ret["first_teaor"]["ok"]) $c++;
		if ($ret["second_teaor"]["ok"]) $c++;
		
		$ret["ratio"] = 0;
		$ret["percent"] = 0;
		if ($c > 0){
			$ret["ratio"] = $c/6;
			$ret["percent"] = round($ret["ratio"]*100);
		}
		
		//aprint($ret);
		
		return $ret;
		
	}
	
	
	function goodSort(&$z, $field = ""){
		if (count($z) < 2) return;
		if ($field==""){
			uksort($z, function($v1, $v2){return strnatcmp($v1, $v2);});
		} else {
			foreach ($z as $firstkey=>$v) break;
			if (!isset($z[$firstkey][$field])){
				echo "goodSort error, milling field (" . $field . ")";
				die();
			}
			uasort($z, function($v1, $v2) use ($field){return strnatcmp($v1[$field], $v2[$field]);});
		}
	}
	
	function evalHtml($tender_id, $client_id, $show_actions = true, $labels_count = true){
		$eval = evalConn($tender_id, $client_id, $labels_count);
		$html = file_get_contents(ROOT . "front/template/evaluation.html");
		
		$r = array();
		$r["cname"] = $eval["client"]["name"];
		$r["cid"] = $client_id;
		$r["tname"] = $eval["tender"]["name"];
		$r["tid"] = $tender_id;
		$r["percent"] = $eval["percent"];
		$r["url"] = URL;
		
		$r["show_admin"] = "";
		if ($_SESSION["user"]["p_conn"] < 2 || !$show_actions) $r["show_admin"] = "hide";
		
		$r["percent_class"] = "bg-warning";
		if ($eval["percent"] == 100){
			$r["percent_class"] = "bg-success";
			$r["text-white"] = "text-white";
		}
		
		//label
		if (!$labels_count){
			$r["label_class"] = "alert-success";
			if (count($eval["label"]["list"]) > 0){
				$r["label_msg"] = "Ennél a kiértékelésnél nem számít a címkeegyezés, de közös címkék:<br />" . implode(", ", $eval["label"]["list"]);
			} else {
				$r["label_msg"] = "Ennél a kiértékelésnél nem számít a címkeegyezés.";
			}
		} else {
			if ($eval["label"]["ok"]){
				$r["label_class"] = "alert-success";
				$r["label_msg"] = implode(", ", $eval["label"]["list"]);
			} else {
				$r["label_class"] = "alert-danger";
				$r["label_msg"] = "Nincs címkeegyezés.";
			}
		}
		/*
		
		if ($eval["label"]["ok"]){
			$r["label_class"] = "alert-success";
			if (count($eval["label"]["list"]) > 0){
				$r["label_msg"] = implode(", ", $eval["label"]["list"]);
			} else {
				$r["label_msg"] = "Ennél a kiértékelésnél nem számít a címkeegyezés.";
			}
			
		} else {
			$r["label_class"] = "alert-danger";
			$r["label_msg"] = "Nincs címkeegyezés.";
		}
		*/
		//legal
		if ($eval["legal"]["ok"]){
			$r["legal_class"] = "alert-success";
			$r["legal_msg"] = $eval["legal"]["msg"];
		} else {
			$r["legal_class"] = "alert-danger";
			$r["legal_msg"] = "Nincs egyezés.";
		}
		
		//gfo
		if ($eval["gfo"]["ok"]){
			$r["gfo_class"] = "alert-success";
			$r["gfo_msg"] = $eval["gfo"]["msg"];
		} else {
			$r["gfo_class"] = "alert-danger";
			$r["gfo_msg"] = "Nincs egyezés.";
		}
		
		//region
		if ($eval["region"]["ok"]){
			$r["region_class"] = "alert-success";
			$r["region_msg"] = $eval["region"]["msg"];
		} else {
			$r["region_class"] = "alert-danger";
			$r["region_msg"] = "Nincs egyezés.";
		}
		
		//first_teaor
		if ($eval["first_teaor"]["ok"]){
			$r["first_teaor_class"] = "alert-success";
			$r["first_teaor_msg"] = $eval["first_teaor"]["msg"];
		} else {
			$r["first_teaor_class"] = "alert-danger";
			$r["first_teaor_msg"] = "Nincs egyezés.";
		}
		
		//second_teaor
		if ($eval["second_teaor"]["ok"]){
			$r["second_teaor_class"] = "alert-success";
			$r["second_teaor_msg"] = $eval["second_teaor"]["msg"];
		} else {
			$r["second_teaor_class"] = "alert-danger";
			$r["second_teaor_msg"] = "Nincs egyezés.";
		}
		
		return bulk_replace($r, $html);
	}
	
	
	
	
	function api($array, $url){
		global $sql;
		$ch = curl_init($url);
		$jsonDataEncoded = json_encode($array);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
		$result = curl_exec($ch);
		if (curl_getinfo($ch)['http_code'] != 200){
			echo "api server error (response not 200); please contact the system administrators";
			die();
		}
		$result = json_decode($result, true);
		foreach ($result as $k => $v) $result[$k] = trim($sql->escape($v));
		return $result;
	}
	
	function updatePermissions($username){
		global $sql, $_setting;
		$user_id = $sql->getArray("select id from user where username='" . $sql->escape($username) . "'");
		
		if (count($user_id) == 0) return;
		

		$user_id = $user_id[0]["id"];
		
		$result = api(array('loginuser' => $username), $_setting["status_api"]);
		unset($result["name"]);
		unset($result["email"]);
		unset($result["senID"]);
		unset($result["status"]);
		
		//"false" after json parse become "", so change to "0"
		foreach ($result as $k=>$v) if ($v == "") $result[$k] = "0";
		
		$sql->update("user", $result, "id", $user_id);
	}
	
	function pwdHash($pwd){
		return password_hash($pwd, PASSWORD_DEFAULT);
	}
	
	function pwdCheck($pwd, $hash){
		return password_verify($pwd, $hash);
	}
	
	function msg($msg){
		$_SESSION['msg'] = $msg;
	}
	
	function msgError($msg){
		$_SESSION['msg'] = $msg;
		$_SESSION['msgerror'] = "";
	}
	
	function errorMsg($msg){msgError($msg);}
	
	function generateLang($section, $lang=""){
		global $sql;
		if ($lang == ""){
			if (!isset($_SESSION['lang']['lang'])) $_SESSION['lang']['lang'] = "en";
			$lang = $_SESSION['lang']['lang'];
		}
		$return = array();
		$z = $sql->getArray("select * from lang where section='" . $section . "'");
		foreach ($z as $v){
			$return['l_'.$v['alias']] = $v[$lang];
		}
		return $return;
	}
	
	function getLang($s, $b=""){return generateLang($s, $b);}
	
	//
	// Developing functions
	// 
	
	function getForm($id, $bs = false){
		global $sql;
		$id = $sql->escape($id);
		$list = $sql->getArray("select * from list where id='" . $id . "'");
		$list = $list[0];
		
		if ($bs){
			$form_html = file_get_contents(ROOT . 'front/template/userform_bs.html');
			$field_html = file_get_contents(ROOT . 'front/template/userform_field_bs.html');
		} else {
			$form_html = file_get_contents(ROOT . 'front/template/userform.html');
			$field_html = file_get_contents(ROOT . 'front/template/userform_field.html');
		}
		$fields = "";
		
		$lang = getLang("subscribe_form", $list['lang']);
		
		// name & mail
		$fields .= bulk_replace(array("id"=>'name', "name"=>$lang['l_name'], "type"=>"text", "required"=>" required"), $field_html);
		$fields .= bulk_replace(array("id"=>'mail', "name"=>$lang['l_mail'], "type"=>"email", "required"=>" required"), $field_html);
		
		//others
		for ($i = 1; $i<6; $i++){
			if ($list['d'.$i.'_active'] == 1){
				$req = "";
				if ($list['d'.$i.'_req'] == 1) $req = " required";
				$fields .= bulk_replace(array("id"=>"data".$i, "name"=>$list['d'.$i.'_name'], "type"=>"text", "required"=>$req), $field_html);
			}
		}
		
		$form_html = bulk_replace(array("list_id"=>$list['id'], "fields"=>$fields, "url"=>URL, "l_subscribe"=>$lang['l_subscribe']), $form_html);
		
		//colors
		//$form_html = bulk_replace(array("color1"=>$list["color1"],"color2"=>$list["color2"],"color3"=>$list["color3"],"color4"=>$list["color4"],"color5"=>$list["color5"],"color6"=>$list["color6"]), $form_html);
		
		//echo $form_html;die();
		return $form_html;
	}
	
	//$today = date("Y-m-d H:i:s"); 
	function time2date($time){
		return date("Y.m.d.", $time);
	}
	function time2time($time, $precise = false){
		if ($precise) return date("H:i:s", $time);
		return date("H:i", $time);
	}
	function time2($time){
		return time2date($time)." ".time2time($time);
	}
	
	function getLangTitles(){
		global $sql;
		$r = $sql->getArray("select * from lang where alias='title'");
		$ret = array();
		foreach ($r as $b){
			$ret[$b['section']] = $b[$_SESSION['lang']['lang']];
		}
		return $ret;
	}
	
	function getMaxFilesize(){
		$temp = array();
		$temp[] = ini_get('memory_limit');
		$temp[] = ini_get('post_max_size');
		$temp[] = ini_get('upload_max_filesize');
		foreach ($temp as $k => $v){
			$r = substr($v, -1);
			if (!is_numeric($r)){
				$v = substr($v, 0, -1);
				switch(strtolower($r)){
					case 'g':
						$v = $v * 1024 * 1024 * 1024;
						break;
					case 'm':
						$v = $v * 1024 * 1024;
						break;
					case 'k':
						$v = $v * 1024;
						break;
				}
				$temp[$k] = $v;
			}
		}
		return min($temp);
	}
	
	function trimEscapeForce($array, $fieldset){
		if (!is_array($array) || !is_array($fieldset)){
			echo "trimEscapeForce error: need two arrays";
			die();
		}
		//switch arrays
		if (isset($array[0])){
			$tmp = $array;
			$array = $fieldset;
			$fieldset = $tmp;
		}
		global $sql;
		$ret = array();
		foreach ($fieldset as $field){
			if (!isset($array[$field])){
				echo "trimEscapeForce: missing field (" . $field . ")";
				die();
			}
			$ret[$field] = trim($sql->escape($array[$field]));
		}
		return $ret;
	}
	
	function aprint($array){
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	}
	function printa($array){aprint($array);}
	
	function bulk_replace($a, $b, $clear = false, $left = "{{", $right = "}}"){
		if (is_array($a) && is_string($b)){
			$array = $a;
			$string = $b;
		} else if (is_array($b) && is_string($a)){
			$array = $b;
			$string = $a;
		} else {
			echo "bulk replace error: not an array-string pair";
			die();
		}
		foreach ($array as $k=>$v) $string = str_replace ($left.$k.$right, $v, $string);
		if ($clear) $string = preg_replace("#".$left.".*?".$right."#", "", $string);
		return $string;
	}
	
	function isMobile(){
		if (stripos($_SERVER['HTTP_USER_AGENT'],"iphone")) return true;
		if (stripos($_SERVER['HTTP_USER_AGENT'],"android")) return true;
		if (stripos($_SERVER['HTTP_USER_AGENT'],"webos")) return true;
		if (stripos($_SERVER['HTTP_USER_AGENT'],"blackberry")) return true;
		if (stripos($_SERVER['HTTP_USER_AGENT'],"ipod")) return true;
		if (stripos($_SERVER['HTTP_USER_AGENT'],"ipad")) return true;
		return false;
	}
	
	function genRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) $randomString .= $characters[rand(0, $charactersLength - 1)];
		return $randomString;
	}
	
	function getMailHtml($list){
		$ret = file_get_contents(ROOT . "front/mail/base.html");
		return bulk_replace(array("color1"=>$list['color1'], "color2"=>$list['color2'], "color3"=>$list['color3'], "color4"=>$list['color4'], "color5"=>$list['color5'], "color6"=>$list['color6']), $ret);
	}
	
	
	//
	// Sending mail actually
	//
    use PHPMailer\PHPMailer\PHPMailer;

	function systemmail($toname, $tomail, $subject, $message){
		require_once ROOT . "engine/phpmailer.class.php";
		require_once ROOT . "engine/phpmailer.smtp.class.php";

		global $_mail;
		
		$mail = new PHPMailer;
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
		$mail->isSMTP();
		$mail->Host = $_mail["host"];
		$mail->SMTPAuth = true;
		$mail->Username = $_mail["user"];
		$mail->Password = $_mail["pass"];
		$mail->SMTPSecure = $_mail["secure"];
		$mail->Port = $_mail["port"];
		
		
		$mail->isHTML(true);
		$mail->setFrom($_mail['from_address'], $_mail['from_name']);
		$mail->addAddress($tomail, $toname);
		$mail->Subject = $subject;
		$design = file_get_contents(ROOT . "front/mail/base.html");
		$design = str_replace("{{text}}", $message, $design);
		$mail->Body = $design;
		
		$mail->AddEmbeddedImage(ROOT . "front/mail/logo.png", "logo");
		if ($mail->send()){
			//echo "done";
			return true;
		} else {
			echo "error: " . $mail->ErrorInfo;
			return false;
		}
	}
	
	//
	// Sending mail actually to admin
	//
	
	function adminmail($subject, $message){
		global $_devops;
		foreach ($_devops as $admin){
			systemmail($admin["name"], $admin["mail"], $subject, $message);
		}
	}
	
	//
	// Basic site working
	//
	
	function go404(){
		header("HTTP/1.0 404 Not Found");
		global $_html, $_setting, $_menu, $_crumb, $sql;
		require_once (ROOT . "engine/generators.php");
		$_end = new MagicTemplate(file_get_contents(ROOT . "front/base_public.html"));
		
		$_html['title'] = '404 | ' . $_html['title'];
		$_html['content'] = file_get_contents(ROOT . 'front/page/404.html');
		
		$_end($_html);
		echo $_end -> cleared();
		die();
	}
	
	function go301($where){
		header("HTTP/1.1 301 Moved Permanently"); 
		go($where);
	}
	
	function go($where){
		header("Location: " . $where);
		die();
	}
	
	function testPreferredUrl(){
		global $_setting, $url;
		$ishttps = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);
		$iswww = preg_match("#www\..*#", $_SERVER['HTTP_HOST']);
		if ($ishttps != $_setting['https'] || $iswww != $_setting['www']) go301(CURRENT_URL);
	}
	
	function goJS($where){
		echo '<script type="text/javascript">window.location.replace("' . $where . '");</script>';
		die();
	}
	
	function urlExplode(){
		function explodeToArray($a){
			$a = preg_replace('/\?.*/','',$a);
			$a = trim($a,"/");
			$ret = explode("/", $a);
			if (count($ret) == 1 && $ret[0] == "") return array();
			return $ret;
		}
		
		global $_setting;
		$app_folder = explodeToArray($_setting['app_folder']);
		$request_uri = explodeToArray($_SERVER['REQUEST_URI']);
		$request_uri = array_slice($request_uri, count($app_folder));
		return $request_uri;
	}
	
	function getRoutingPath(){
		global $url, $_route;
		if (count($url) == 0) return $_route[""];
		$actual_route;
		$found = false;
		foreach ($_route as $r => $include_php){
			$actual_route = explode("/", $r);
			if (count($url) == count($actual_route)){
				$found = true;
				for($i=0; $i<count($url) && $found; $i++){
					$found = ($actual_route[$i] == "?" || $actual_route[$i] == $url[$i]);
				}
				if ($found) return $include_php;
			}
		}
		if (!$found) go404();
	}
	
?>
