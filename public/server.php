<?php
session_start();

// initializing variables
$username = "";
$email    = "";
$errors = array();

require_once('./include/dbconnect.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';

// REGISTER USER
if (isset($_POST['reg_user'])) {
  // receive all input values from the form
  $username = mysqli_real_escape_string($db, $_POST['username']);
  $email = mysqli_real_escape_string($db, $_POST['email']);
  $password_1 = $_POST['password_1']; //no need to escape password as it will be hashed
  $password_2 = $_POST['password_2'];


  // form validation: ensure that the form is correctly filled ...
  // by adding (array_push()) corresponding error unto $errors array
  if (empty($username)) { array_push($errors, "Username is required"); }
  if (empty($email)) { array_push($errors, "Email is required"); }
  if (empty($password_1)) { array_push($errors, "Password is required"); }
  if ($password_1 != $password_2) {
	array_push($errors, "The two passwords do not match");
  }

  // first check the database to make sure
  // a user does not already exist with the same username and/or email

  /* create a prepared statement */
  if ($stmt = mysqli_prepare($db, "SELECT username, email FROM users WHERE username=? OR email=? LIMIT 1")) {

      /* bind parameters for markers */
      mysqli_stmt_bind_param($stmt, "ss", $username, $email);

      /* execute query */
      mysqli_stmt_execute($stmt);

      /* bind result variables */
      mysqli_stmt_bind_result($stmt, $user_res, $email_res);

      /* fetch value */
      $user=mysqli_stmt_fetch($stmt);

  }


  if ($user) { // if user exists
    if ($user_res === $username) {  //username taken
      array_push($errors, "Username already exists");
    }

    if ($email_res === $email) {  //email already used
      array_push($errors, "Email already exists");
    }
  }

  // Finally, register user if there are no errors in the form
  if (count($errors) == 0) {
  	$password = password_hash($password_1, PASSWORD_DEFAULT);//encrypt the password before saving in the database (salt included)
    $token = bin2hex(random_bytes(50)); //generate unique token
    $verified = 0;
    /* Prepare the statement */
    $insert = mysqli_prepare($db, "INSERT INTO users (username, email, password, verified, token) VALUES(?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($insert, 'sssis', $username, $email, $password, $verified, $token);

    /* execute prepared statement */
    mysqli_stmt_execute($insert);

    //SEND verification email to user

    $mail = new PHPMailer(false); // Passing `true` enables exceptions


        //Server settings
        $mx = getenv('SMTP_SERVER');
        $mail_uname = getenv('MAIL_USER');
        $mail_pwd = getenv('MAIL_PWD');

        //$mail->SMTPDebug = 1;//Enable verbose debug output
        $mail->isSMTP();//Set mailer to use SMTP
        $mail->Host = $mx;//Specify main and backup SMTP servers
        $mail->SMTPAuth = true;//Enable SMTP authentication
        $mail->Username = $mail_uname;//SMTP username
        $mail->Password = $mail_pwd;//SMTP password
        $mail->SMTPSecure = 'tls';//Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;//TCP port to connect to


        //Recipients
        $mail->setFrom($mail_uname,'SSMail');
        $mail->addAddress($email);//Add a recipient

        //Content
        $mail->isHTML(true);//Set email format to HTML
        $mail->Subject = 'SimpleSecureMail Email Confirmation';

        $mail->Body    = 'Dear '.$username.',<br /><br />Thanks for registering to SimpleSecureMail, to complete the validation process please click on the below link or paste it in your browser:<br /> https://ssmail.herokuapp.com/verifymail.php?usr='.$username.'&tok='.$token.' <br /><br />SSMail Admin<br /><br />*** DO NOT REPLY TO THIS MESSAGE ***';

        $mail->send();

    //set session variables

  	$_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['verified'] = false;
  	$_SESSION['success'] = "You are almost done! Check your mailbox to complete the validation process. If you see nothing, check the SPAM folder!";
  	header('location: index.php');
  }

}


// LOGIN USER
if (isset($_POST['login_user'])) {
  $username = mysqli_real_escape_string($db, $_POST['username']);
  $password = $_POST['password'];

  if (empty($username)) {
  	array_push($errors, "Username is required");
  }
  if (empty($password)) {
  	array_push($errors, "Password is required");
  }

  if (count($errors) == 0) {

    if ($query = mysqli_prepare($db, "SELECT username, password, verified FROM users WHERE username=?")){
      mysqli_stmt_bind_param($query, 's', $username);
      mysqli_stmt_execute($query);

      /* bind result variables */
      mysqli_stmt_bind_result($query, $selected_user, $selected_user_pwd, $selected_user_verified);

      /* fetch value */
      $user_result=mysqli_stmt_fetch($query);
    } else {
      die(mysqli_error($db));
    }

  	if ($user_result && password_verify($password, $selected_user_pwd)) { //If the user exists and the password matches
      if (!$selected_user_verified) { //if the user hasn't verified their email
        array_push($errors, "Please check you mailbox and complete the validation process");
      } else {
        $_SESSION['username'] = $username;
  	    $_SESSION['success'] = "You are now logged in";
        $_SESSION['verified'] = true;
  	    header('location: index.php');
      }
  	} else {
  		array_push($errors, "Wrong username/password combination");
  	}
  }
}

?>
