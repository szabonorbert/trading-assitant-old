<?php
	
	if (isset($_SESSION['user'])) go(URL);
	
	
	$_html['content'] = new MagicTemplate(file_get_contents(ROOT.'front/page/reg.html'));
	$_html['content'](array("recaptcha_public"=>$_setting["recaptcha_public"]));
	
?>