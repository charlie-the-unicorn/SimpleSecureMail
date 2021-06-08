<!DOCTYPE html>
<html>

<style>
/* Stylesheet for password strength checker
/* The message box is shown when the user clicks on the password field */
#message {
    display:none;
    background: #f1f1f1;
    color: #000;
    position: relative;
    padding: 20px;
    margin-top: 10px;
}

#message p {
    padding: 10px 35px;
    font-size: 18px;
}

</style>

<head>
  <title>SimpleSecureMail</title>
  <meta name="author" content="Alberto Radice">
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
  <div class="header">
  	<h2><a href="index.php" style="text-decoration:none; color:white">SimpleSecureMail</a></h2>
  </div>

<?php

require_once('./include/dbconnect.php');

// Was the form submitted?
if (isset($_POST["ResetPasswordForm"])){
	// Gather the post data
	$email = $_POST["email"];
	$password_1 = $_POST["password"];
	$confirmpassword = $_POST["confirmpassword"];
	$hash = $_POST["t"];

	// Use the same salt as the change procedure
	$salt = getenv('SALT');

	// Generate the reset key
	$resetkey = hash('sha512', $salt.$email);

	// Does the new reset key match the old one?
	if ($resetkey == $hash){
		if ($password_1 == $confirmpassword){
			//hash and secure the password
      $password = password_hash($password_1, PASSWORD_DEFAULT); //encrypt the password before saving in the database

			// Update the user's password
			if ($update_q = mysqli_prepare($db, "UPDATE users SET password = ? WHERE email = ?")){
				mysqli_stmt_bind_param($update_q, 'ss', $password, $email);
				mysqli_stmt_execute($update_q);
				echo "<div class=\"content\"><div class=\"success\">The password has been successfully reset</div><br /><br /><a href=\"login.php\">Login</a></div>";
			} else {
	      die(mysqli_error($db));
	    }


		} else {
			echo "<div class=\"content\"><div class=\"error\">The passwords do not match</div><br /><br /><a href=\"login.php\">Back to the Login page</a></div>";
		}
	} else {
		echo "<div class=\"content\"><div class=\"error\">Your password reset key is invalid. Make sure the token link and the email you used are correct.</div><br /><br /><a href=\"login.php\">Back to the Login page</a></div>";
	}
}

?>
	<div class="footer" >
			<span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
	</div>
</body>
</html>
