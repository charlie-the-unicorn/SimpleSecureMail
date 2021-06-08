<?php
  session_start();

  if (isset($_SESSION['username']) && isset($_SESSION['verified']) && ($_SESSION['verified'] == true)) {

    require_once('./include/dbconnect.php');

    date_default_timezone_set('UTC');

    $msg ="";
    $sender = $_SESSION['username'];

    //If they sent us a recipient to be used, let's add it
    if (isset($_GET['to']) && (!empty($_GET['to']))) {
      $to = $_GET['to'];
    } else {
      $to = "";
    }

  	$form = true;
    $otitle = "";
    $orecip = "";
    $omessage = "";
    //We check if the form has been sent
    if(isset($_POST['title'], $_POST['recip'], $_POST['message'])){
      $otitle = $_POST['title'];
      $orecip = $_POST['recip'];
      $omessage = $_POST['message'];


      //We check if all the fields were filled out
      if($_POST['title']!='' && $_POST['recip']!='' && $_POST['message']!=''){

        //Variables for encryption
        $enc_key = getenv('ENC_KEY');
        $iv = getenv('IV');
        $ciphering = getenv('CIPHERING');

        $timestamp = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
        $curDate = date("Y-m-d H:i:s", $timestamp);

        //Protecting the variables
        $title = stripslashes(mysqli_real_escape_string($db, $otitle));
        $recip = mysqli_real_escape_string($db, $orecip);
        $message = mysqli_real_escape_string ($db, nl2br(htmlentities($omessage, ENT_QUOTES, 'UTF-8')));


        //encrypt title and msg
        $etitle = openssl_encrypt($title, $ciphering, $enc_key, 0, $iv);
        $emessage = openssl_encrypt($message, $ciphering, $enc_key, 0, $iv);


        //Checking if the recipient user exists
        if ($query = mysqli_prepare($db, "SELECT id FROM users WHERE username =?")){
          mysqli_stmt_bind_param($query, 's', $recip);
          mysqli_stmt_execute($query);
          mysqli_stmt_bind_result($query, $rid);
          mysqli_stmt_store_result($query);
        } else {
          die(mysqli_error($db));
        }

        if(mysqli_stmt_fetch($query)) { //if recipient exists
          mysqli_stmt_free_result($query);
          mysqli_stmt_close($query);

          //Extract the sender id
          if ($sender_id = mysqli_prepare($db, "SELECT id FROM users WHERE username=?")){
            mysqli_stmt_bind_param($sender_id, 's', $sender);
            mysqli_stmt_execute($sender_id);
            mysqli_stmt_bind_result($sender_id, $sid);
            mysqli_stmt_fetch($sender_id);
            mysqli_stmt_free_result($sender_id);
            mysqli_stmt_close($sender_id);
          } else {
            die(mysqli_error($db));
          }

          //We send the message
          if ($send = mysqli_prepare($db,"INSERT into pm (sid, rid, title, message, timestamp) VALUES (?,?,?,?,?)" )) {
            mysqli_stmt_bind_param($send,'iisss',$sid,$rid,$etitle,$emessage,$curDate);
            mysqli_stmt_execute($send);
            $msg ="<div class=\"success\">The message was sent</div>";
          } else {
            die(mysqli_error($db));
            $msg ="<div class=\"error\">The message couldn't be sent</div>";
          }

        } else {
          //recipient doesn't exist
          $msg ="<div class=\"error\">Nonexistent user</div><br />";
        }
      } else {
        $msg ="<div class=\"error\">You must fill out all fields</div><br ?>";
      }

    }

  } else {
    $_SESSION['msg'] = "<div class=\"error\">You must log in first</div><br />";
    header('location: login.php');
  }
  if (isset($_GET['logout'])) {
  	session_destroy();
  	unset($_SESSION['username']);
  	header("location: login.php");
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
	<h2><a href="index.php" style="text-decoration:none; color:white">SimpleSecureMail<a/></h2>
</div>
<div class="content">
  <?php echo $msg ?>
<!-- Compose message -->
  <h5>New Personal Message</h5><br />
  <a href="index.php">Index page</a><br />
  <a href="index.php?logout='1'" style="color: red;">Logout</a>
</div>
<div>
    <form name="msgform" action="send.php" method="post">
      <b><? echo $sender ?></b>, please write your message below<br /><br />
      <label for="title">Title</label><br /><input type="text" id="title" name="title" required /><br />
      <label for="recip">Recipient (username)<br /><input type="text" value="<?php echo $to ?>" id="recip" name="recip" required /><br />
      <label for="message">Message</label><br /><textarea cols="40" rows="5" id="message" name="message" required></textarea><br />
      <div class="input-group">
    		<button type="submit" class="btn" name="send">Send</button>
    	</div>
    </form>
</div>
<div class="footer" >
<span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
</div>

</body>
</html>
