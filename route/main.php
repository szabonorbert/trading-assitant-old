<?php
	
	login_required();
	$r = array();
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/main.html"));
	$_html["content"]($r);
	
?>