<?php

	require "engine/simpledom/simple_html_dom.php";
	
	
	$site = "http://pafi.hu/_pafi/palyazat.nsf/83c68255e917fff4c1256bd6006b51fa/db2f6a7b3932ce3cc1258371005cca34?OpenDocument";

	$selector = "table";
	$html = file_get_html($site);
	//echo $html->outertext;
	//die();
	
	$e = $html->find("/html/body/table[8]", 0);
	//echo count($e);
	//die();
	
	if ($e == NULL){
		echo "NULL";
	} else {
		echo $e->outertext;
	}


?>