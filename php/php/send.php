<?php
	require_once "Mail.php";
	require_once "recaptchalib.php";
	
	// Captcha
	$privatekey = "6Lfr9u4SAAAAAAOdw2GkNegPb6MvURtiSGnvmT8l";
  	$resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

  	if (!$resp->is_valid) {
		echo ('captcha');
	}
	else {
		$from = $_POST['email'];
		$subject = '[www.weso.es] ' . $_POST['subject'];
		$body = '[' . $from . '] ' . $_POST['description'];
		$account = json_decode(file_get_contents('../data/mail.json'), true);

		$to = $account['username'];

		$headers = array(
			'From' => $from,
			'To' => $to,
			'Subject' => $subject
		);

		$smtp = Mail::factory('smtp', array(
				'host' => 'ssl://' . $account['host'],
				'port' => $account['port'],
				'auth' => $account['auth'],
				'username' => $account['username'],
				'password' => $account['password']
			));

		$mail = $smtp->send($to, $headers, $body);

		if (PEAR::isError($mail))
			echo ('Error ' . $mail->getMessage());
		else
			echo ('OK');
	}
?>
