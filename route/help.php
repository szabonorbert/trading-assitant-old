<?php
	
	login_required();
	$r = array();
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/help.html"));
	$_html["content"]($r);