<?php 
	
	include "define.php";
	include "engine/recaptcha.class.php";
	
	if (isset($_POST['g-recaptcha-response'])){
		$recaptcha = $_POST['g-recaptcha-response'];
		$object = new Recaptcha();
		$response = $object->verifyResponse($recaptcha);

		if(isset($response['success']) and $response['success'] != true) {
			echo "An Error Occured and Error code is :".$response['error-codes'];
		}else {
			echo "Correct Recaptcha";
		}
	}
	
	?>
<html>
  <head>
    <title>reCAPTCHA demo</title>
    <script src='https://www.google.com/recaptcha/api.js'></script>
	 <script>
       function onSubmit(token) {
         document.getElementById("demo-form").submit();
       }
     </script>
  </head>
  <body>
    <form action="captchatest.php" method="POST" id="demo-form">
      <br>
	  <button class="g-recaptcha"
		data-sitekey="6Lc_GD0UAAAAAB0ZDFmvFJdId_phzBzPR2zqX58C"
		data-callback="onSubmit">Submit
      </button>
    </form>
  </body>
</html>