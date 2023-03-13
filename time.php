<?php

	include "define.php";
	include "engine/functions.php";
	include "engine/connection.class.php";
	$sql = new Connection($_setting['sql_hostname'], $_setting['sql_username'], $_setting['sql_password'], $_setting['sql_database']);
	
	echo "<pre>";
	
	$string = "   \ \ 'Brian\ 's name";
	$string2 = '\"Hello bello\", mondta Sanyi';
	echo $string . " --> " . $sql->escape($string) . "<br />" . $string2 . " --> " . $sql->escape($string2) . "<br />";
	echo "<br />";
	
	check (" \"Bri\"en's n'ame   '");
	check ($string2);
	
	
	function check($string){
		$patterns = array();
		$replaces = array();
		$patterns[] = '/^\'|[^\\\\]\'/';
		$replaces[] = '\"';
		$patterns[] = '/^"|[^\\\\]"/';
		$replaces[] = '\"';
		
		echo "=========================<br />";
		echo "<u>ORIGINAL STRING:</u><br />";
		echo "|>>" . $string . "<<|";
		echo "<br /><br />";
		echo "<u>MATCHES 1 & 2:</u>";
		preg_match_all($patterns[0], $string, $matches, PREG_OFFSET_CAPTURE);
		$matches = $matches[0];
		aprint($matches);
		$c = 0;
		foreach ($matches as $m){
			if ($m[1] > 0){
				$string = substr_replace($string, "\\", $m[1]+1+$c, 0);
			} else {
				if (strlen($m[0]) == 1){
					$string = substr_replace($string, "\\", 0, 0);
				} else {
					$string = substr_replace($string, "\\", 1, 0);
				}
			}
			$c++;
		}
		
		preg_match_all($patterns[1], $string, $matches, PREG_OFFSET_CAPTURE);
		$matches = $matches[0];
		aprint($matches);
		$c = 0;
		foreach ($matches as $m){
			if ($m[1] > 0){
				$string = substr_replace($string, "\\", $m[1]+1+$c, 0);
			} else {
				if (strlen($m[0]) == 1){
					$string = substr_replace($string, "\\", 0, 0);
				} else {
					$string = substr_replace($string, "\\", 1, 0);
				}
			}
			$c++;
		}
		echo "<br /><br />";
		echo "<u>NEW STRING:</u><br />";
		echo $string;
		echo "<br />";
		
	}
	
	
	
	
	
	
	/*
	$re = '/[^\\\\]["\']/x';
	$str = 'hello\\"
	hello \\ "
	hello\\ \'
	hello\\ \'"\'
	hello\\"" \' "

	';

	preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

	// Print the entire match result
	var_dump($matches);
	*/
	
	function isEscaped($string){
		
	}
	
	die();
	
	




echo time(); ?>