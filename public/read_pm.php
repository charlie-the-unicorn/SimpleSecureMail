<?php
  session_start();

  if (isset($_SESSION['username']) && isset($_SESSION['verified']) && ($_SESSION['verified'] == true)) {
  	require_once('./include/dbconnect.php');

    $user = $_SESSION['username'];
    $allow = false; //initializing

    //Checking the id of the current user
    if ($query = mysqli_prepare($db, "SELECT id from users WHERE username=?")){
      mysqli_stmt_bind_param($query, 's', $user);
      mysqli_stmt_execute($query);
      mysqli_stmt_bind_result($query, $current_uid);
      $user_res=mysqli_stmt_fetch($query);
      mysqli_stmt_free_result($query);
      mysqli_stmt_close($query);
    } else {
      die(mysqli_error($db));
    }

    //if we received a message id to read
    if (isset($_GET['id']) && !empty($_GET['id'])) {
      $mid = $_GET['id'];
      if ($mquery = mysqli_prepare($db, "SELECT users.username as sender, rid, title, message, timestamp FROM pm INNER JOIN users ON pm.sid = users.id WHERE pm.id=?")){
        mysqli_stmt_bind_param($mquery, 'i',$mid);
        mysqli_stmt_execute($mquery);
        mysqli_stmt_bind_result($mquery, $sender, $rid, $title, $message, $timestamp);
        $mex = mysqli_stmt_fetch($mquery);
        mysqli_stmt_free_result($mquery);
        mysqli_stmt_close($mquery);
      } else {
        die(mysqli_error($db));
      }
    } else {
      $err = "<div class=\"error\">The link is incomplete</div><br />";
    }



    if ($mex){ //There is a result
      if ($current_uid == $rid) { //if the requested message belongs to the logged in user
        $allow = true;
        //Variables for decryption
        $enc_key = getenv('ENC_KEY');
        $iv = getenv('IV');
        $ciphering = getenv('CIPHERING');

        //Decipher the message
        $dec_msg = openssl_decrypt(htmlentities($message, ENT_QUOTES, 'UTF-8'),$ciphering,$enc_key,0,$iv);
        $msg_nl2br = str_replace(array("\\r\\n","\\r", "\\n"), "<br />", $dec_msg);

        //Set to read
        if ($uquery = mysqli_prepare($db, "UPDATE pm SET `read`=1 WHERE id=?")) {
          mysqli_stmt_bind_param($uquery, 'i', $mid);
          mysqli_stmt_execute($uquery);
        } else {
          die(mysqli_error($db));
        }
      } else {
        $err = "<div class=\"error\">Unauthorized action</div><br />";
      }
    } else {
      $err = "<div class=\"error\">Message not found</div><br />";
    }




  } else {
    $_SESSION['msg'] = "<div class=\"error\">You must log in first</div><br />";
    header('location: login.php');
  }

?>
<!DOCTYPE html>
<html>
<head>
<style>
table {
  border: 1px outset black;
}

th{
  border: 20px solid black;
}

td{
  border: 20px solid black;
}

</style>
	<title>SimpleSecureMail</title>
	<meta name="author" content="Alberto Radice">
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div class="header">
	<h2><a href="index.php" style="text-decoration:none; color:white">SimpleSecureMail</a></h2>
</div>

<div class="content">

<?php if ($allow == true) { ?>
<!-- message -->
<table border-style="outset" >
  <tr>
    <th>From</th>
    <td><?php echo htmlentities($sender, ENT_QUOTES, 'UTF-8'); ?></td>
  </tr>
  <tr>
    <th style="background-color:#c1c2c1">Title</th>
    <td style="background-color:#c1c2c1"><?php echo openssl_decrypt(htmlentities($title, ENT_QUOTES, 'UTF-8'),$ciphering,$enc_key,0,$iv); ?></td>
  </tr>
  <tr>
    <td colspan="2"><?php echo $msg_nl2br; ?></td>
  </tr>
  <tr>
    <th style="background-color:#c1c2c1">Date (UTC)</th>
    <td style="background-color:#c1c2c1"><?php echo $timestamp; ?></td>
  </tr>
</table><br />
<a href="send.php?to=<?php echo htmlentities($sender, ENT_QUOTES, 'UTF-8'); ?>">Reply</a><br />
<a href="inbox.php?del=<?php echo $mid ?>">Delete message</a><br />
<?php } else { echo $err; }?>

<a href="inbox.php">Back to the Inbox</a><br />
<a href="index.php?logout='1'" style="color: red;">Logout</a>

</div>
<div class="footer" >
<span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
</div>

</body>
</html>
