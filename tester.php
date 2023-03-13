<?php
	include "engine/simpledom/simple_html_dom.php";
	
	
	$html = str_get_html("<html>csőőőő</html>");
	echo $html->plaintext;
	
	
	echo "<pre>";
	print_r($vars);
	echo "</pre>";
	
	//ez működik:
	function destoryAllDomParser(){
		$vars = array_keys($GLOBALS);
		foreach ($vars as $v){
			if (is_object(${$v}) && get_class(${$v}) == "simple_html_dom"){
				${$v} -> clear();
				unset(${$v});
			}
		}
	}
	
	$vars = array_keys($GLOBALS);
	echo "<pre>";
	print_r($vars);
	echo "</pre>";
	
	echo (get_class($html));
	
	
	
	
	
	

?>