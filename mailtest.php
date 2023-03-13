<?php
	
	include "engine/phpmailer.class.php";
	include "engine/phpmailer.smtp.class.php";
	
	$mail = new PHPMailer;
	$mail->SMTPOptions = array(
		'ssl' => array(
			'verify_peer' => false,
			'verify_peer_name' => false,
			'allow_self_signed' => true
		)
	);
	$mail->isSMTP();
	//$mail->Host = 'localhost';
	$mail->Host = 'smtp.detenyleg.com';
	$mail->SMTPAuth = true;
	$mail->Username = 'norbi';
	$mail->Password = 'Aenuy9ai';
	$mail->SMTPSecure = 'tls';
	$mail->Port = 587;
	$mail->isHTML(true);
	
	
	$mail->setFrom('noreply@tenderstruck.com', 'Tenderstruckkkkkkk');
	//$mail->addAddress("geribek@gmail.com", "Gergely");
	$mail->addAddress("detenyleg.com@gmail.com", "Norbert");
	$mail->Subject = "Áj em a pesszendzsör";
	$mail->Body = "https://www.youtube.com/watch?v=hLhN__oEHaw";
	if ($mail->send()){
		echo "done";
	} else {
		echo "error: " . $mail->ErrorInfo;
	}
	
	
	/*
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	define("ROOT", __DIR__ ."/");
	include "define.php";
	include "engine/functions.php";
	
	
	//sendmail("Szabó Norbert", "spm@detenyleg.com", "n", "aktivteszt@senletter.com", "SUBJECT", "BODY HELLO");
	
	
	require_once ROOT . "engine/phpmailer.class.php";
	require_once ROOT . "engine/phpmailer.smtp.class.php";
	
	$frommail = "aktivteszt@senletter.com";
	$fromname = "Norbert";
	$tomail = "spm@detenyleg.com";
	$toname = "Norbert Szabó";
	$subject="Tárgy";
	$message="Message body";
	$img="";
	
	
	$m = new PHPMailer();
	if ($_mail['smtp'] == true){
		$m->isSMTP();
		/*$m->SMTPAuth = false; 
		$m->SMTPSecure = false;
		//$m->Host = gethostbyname($_mail['host']);
		$m->Host = $_mail['host'];
		//$m->Username = $_mail['user'];
		//$m->Password = $_mail['pass'];
		$m->SMTPSecure = $_mail['secure'];
		$m->Port = $_mail['port'];
		$m->SMTPOptions = array('ssl' => array('verify_peer' => false,'verify_peer_name' => false,'allow_self_signed' => true));
	}
	$m->setFrom($frommail, $fromname);
	$m->addAddress($tomail, $toname);
	$m->isHTML(true);
	$m->Subject = $subject;
	if ($img == "" || !file_exists($img)) $img = ROOT . "upload/sen.png";
	$m->AddEmbeddedImage($img, 'logo');
	$m->Body = $message;
	if (!$m->send()){
		$aaa = time().": ".$m->ErrorInfo."\n";
		echo $aaa;
		file_put_contents(ROOT . "error/maillog.txt", $aaa, FILE_APPEND);
	}*/
	



?>