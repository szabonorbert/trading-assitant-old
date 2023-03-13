<?php

	login_required();
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT . "front/page/profile.html"));
	
	$_html['content']($_html);