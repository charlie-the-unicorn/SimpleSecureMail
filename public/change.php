<?php


use PHPMailer\PHPMailer\PHPMailer;
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

require_once('./include/dbconnect.php');

//initialize
$sendmail = 0;
$addtoken = 0;

$msg = "";

date_default_timezone_set('UTC');

// Was the form submitted?
if (isset($_POST["ForgotPassword"])) {

	// Harvest submitted e-mail address
	if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
		$email = $_POST["email"];
	} else {
		$invalid = "<div class=\"error\">You entered an invalid email address</div><br /><br /><a href=\"forgot_pwd.php\">Try again</a>";
	}

	if (!isset($invalid)) { // if the email entered is valid
	// Check to see if a user exists with this e-mail
		if ($query = mysqli_prepare($db, "SELECT email, username, verified FROM users WHERE email = ?")) {
			mysqli_stmt_bind_param($query, 's', $email);
    	mysqli_stmt_execute($query);
			mysqli_stmt_bind_result($query, $selected_email, $selected_user, $selected_user_verified);
  		mysqli_stmt_store_result($query);
			if (mysqli_stmt_fetch($query)) { //if the user exists
				mysqli_stmt_free_result($query);
				mysqli_stmt_close($query);
				if ($selected_user_verified==1){ //if he has already verified his email
					$salt = getenv('SALT');
					// Create the unique user password reset token
					$token = hash('sha512', $salt.$selected_email);
					// calculate the current timestamp and the expiry one
					$timestamp = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
					$exp_time = mktime(date("H"), date("i"), date("s"), date("m"), date("d")+1, date("Y"));
					$curDate = date("Y-m-d H:i:s", $timestamp);

					$exp = date("Y-m-d H:i:s", $exp_time);


					//If the email is already present in the reset database
					if ($query1 = mysqli_prepare($db, "SELECT expdate FROM password_reset WHERE email=?")) {
						mysqli_stmt_bind_param($query1, 's', $selected_email);
						mysqli_stmt_execute($query1);
						mysqli_stmt_bind_result($query1, $expdate);
						mysqli_stmt_store_result($query1);
					} else {
						die(mysqli_error($db));
					}


					if (mysqli_stmt_fetch($query1)) { //if there is a result, then it means that the pwd reset for this email has already been requested
					//if the token is still valid, then do nothing
						if ($expdate >= $curDate) {

							mysqli_stmt_free_result($query1);
							mysqli_stmt_close($query1);
							$msg = "<div class=\"error\">A password reset was already requested. Please, check your mailbox</div>";
						} else {
							//if the token is expired, then replace it with the new one
							if ($query2 = mysqli_prepare($db, "UPDATE password_reset SET token = ?, expdate = ? WHERE email=?")){
								mysqli_stmt_bind_param($query2, 'sss', $token, $exp, $selected_email);
								mysqli_stmt_execute($query2);
								$sendmail = 1;
								$msg = "<div class=\"success\">We sent you a new email</div>";
							} else {
									die(mysqli_error($db));
							}
						}
					} else { //it means that a pwd reset hasn't been requested yet

						$msg = "<div class=\"success\">If a matching email exists, you're about to receive an email</div>";
						$addtoken = 1;
						$sendmail = 1;
					}

		 } else {
			 $msg = "<div class=\"error\">You haven't validated your email yet. Please, check your mailbox.</div>";
	   }

		} else { //there is no such user in the DB, so we do nothing
				$msg = "<div class=\"success\">If a matching email exists, you're about to receive an email</div>";
			}
		}
	}


	if ($addtoken==1){
		$insert = mysqli_prepare($db, "INSERT INTO password_reset (email, token, expdate) VALUES(?, ?, ?)");
		mysqli_stmt_bind_param($insert, 'sss', $selected_email, $token, $exp);
		mysqli_stmt_execute($insert);
	}


	if ($sendmail==1){ //if an email is needed
		// Create a url which will direct the user to reset their password
		$pwrurl = "https://ssmail.herokuapp.com/reset_pwd.php?q=".$token;
		// Mail them their token
		$mail = new PHPMailer(false); // Passing `true` enables exceptions
		//Server settings
		$mx = getenv('SMTP_SERVER');
		$mail_uname = getenv('MAIL_USER');
		$mail_pwd = getenv('MAIL_PWD');

		$mail->isSMTP();//Set mailer to use SMTP
		$mail->Host = $mx;//Specify main and backup SMTP servers
		$mail->SMTPAuth = true;//Enable SMTP authentication
		$mail->Username = $mail_uname;//SMTP username
		$mail->Password = $mail_pwd;//SMTP password
		$mail->SMTPSecure = 'tls';//Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;//TCP port to connect to

		//Recipients
		$mail->setFrom($mail_uname,'SSMail');
		$mail->addAddress($selected_email);//Add a recipient

		//Content
		$mail->isHTML(true);//Set email format to HTML
		$mail->Subject = 'SimpleSecureMail Password Reset';
		$mail->Body = "Dear ".$selected_user.",<br /><br />If this e-mail does not apply to you please ignore it. It appears that you have requested a password reset at our website ssmail.herokuapp.com<br />To reset your password, please click the link below. If you cannot click it, please paste it into your web browser's address bar.<br /><br />" . $pwrurl . "<br /><br />Thanks,<br /><br />SSMail Admin<br /><br />*** DO NOT REPLY TO THIS MESSAGE ***";
		$mail->send();
	}

}

?>
<!DOCTYPE html>
<html>
	<head>
    <title>SimpleSecureMail</title>
		<meta name="author" content="Alberto Radice">
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
		<div class="header">
    	<h2><a href="index.php" style="text-decoration:none; color:white">SimpleSecureMail</a></h2>
    </div>
    <div class="content">
      <?php if (isset($invalid)) {
				echo $invalid."<br />"; }
				else {
					echo $msg."<br /><br />";
				}	?>
        <a href="login.php">Back to the login page</a>
    </div>
		<div class="footer">
		  <span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
		</div>

  </body>
</html>
