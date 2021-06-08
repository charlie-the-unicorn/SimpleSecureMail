<?php
  session_start();

  if (isset($_SESSION['username']) && isset($_SESSION['verified']) && ($_SESSION['verified'] == true)) {
  	require_once('./include/dbconnect.php');

    $user = $_SESSION['username'];
    $msg = "";

    //Variables for encryption
    $enc_key = getenv('ENC_KEY');
    $iv = getenv('IV');
    $ciphering = getenv('CIPHERING');

    //Checking the id of the current user
    if ($cquery = mysqli_prepare($db, "SELECT id from users WHERE username=?")){
      mysqli_stmt_bind_param($cquery, 's', $user);
      mysqli_stmt_execute($cquery);
      mysqli_stmt_bind_result($cquery, $current_id);
      $user_res=mysqli_stmt_fetch($cquery);
      mysqli_stmt_free_result($cquery);
      mysqli_stmt_close($cquery);
    } else {
      die(mysqli_error($db));
    }

    //if we received the id of a message to delete
    if (isset($_GET['del']) && !empty($_GET['del'])) {
      $did = $_GET['del'];
      if ($dr_query = mysqli_prepare($db, "SELECT rid FROM pm WHERE id=?")) {
        mysqli_stmt_bind_param($dr_query, 'i',$did);
        mysqli_stmt_execute($dr_query);
        mysqli_stmt_bind_result($dr_query, $dr_id);
        mysqli_stmt_fetch($dr_query);
        mysqli_stmt_close($dr_query);
      } else {
        die(mysqli_error($db));
      }

      //if the recipient of the message that the user wants to delete is the logged in user
      if ($current_id == $dr_id) {
        if ($dquery = mysqli_prepare($db, "DELETE FROM pm WHERE id=?")){
          mysqli_stmt_bind_param($dquery, 'i',$did);
          mysqli_stmt_execute($dquery);
          mysqli_stmt_close($dquery);
          $msg ="<div class=\"success\">The message was deleted</div><br />";
        } else {
          die(mysqli_error($db));
        }
      } else {
        $msg ="<div class=\"error\">Unauthorized action</div><br />";
      }
    }

    //Pulling all messages
    if ($query = mysqli_prepare($db, "SELECT pm.id, users.username AS sender, pm.title, pm.message, pm.timestamp, pm.read FROM pm INNER JOIN users ON pm.sid = users.id WHERE pm.rid=(SELECT users.id FROM users WHERE users.username=?)")){
        mysqli_stmt_bind_param($query, 's', $user);
        mysqli_stmt_execute($query);
        $res = mysqli_stmt_get_result($query);
        $numr = mysqli_num_rows($res);
    } else {
      die(mysqli_error($db));
    }

  } else {
    $_SESSION['msg'] = "<div class=\"error\">You must log in first</div><br />";
    header('location: login.php');
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
  <?php echo $msg ?><br />
  <?php if ($numr > 0) { ?>
  <b><?php echo $user; ?></b>, here's the list of your messages<br />

<!-- messages and stuff -->
<table>
  <tr>
    <th>From</th>
    <th>Title</th>
    <th>Received on (UTC)</th>
  </tr>
<?php
while($mex = mysqli_fetch_array($res)){
  $bold_open = "";
  $bold_closed = "";
  if ($mex['read']==0) {
    $bold_open = "<b>";
    $bold_closed = "</b>";
  }
?>
  <tr>
    <td><?php echo $bold_open.htmlentities($mex['sender'], ENT_QUOTES, 'UTF-8').$bold_closed; ?></td>
    <td><?php echo $bold_open; ?><a href="read_pm.php?id=<?php echo $mex['id']; ?>"><?php echo openssl_decrypt(htmlentities($mex['title'], ENT_QUOTES, 'UTF-8'),$ciphering,$enc_key,0,$iv); ?></a><?php echo $bold_closed; ?></td>
    <td><?php echo $bold_open.$mex['timestamp'].$bold_closed; ?></td>
  </tr>
<?php
}
?>
</table><br />
<?php } else {
  echo "<b>".$user."</b>, your inbox is empty <br /><br />";
} ?>
<a href="index.php">Index page</a><br />
<a href="index.php?logout='1'" style="color: red;">Logout</a>

</div>
<div class="footer" >
  <span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
</div>

</body>
</html>
