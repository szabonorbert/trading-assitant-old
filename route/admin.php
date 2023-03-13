<?php

	login_required();
	admin_required();
	
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/admin.html"));
	$r = array();
	
	
	
	
	
	
	$_html["content"]($r);