<?php
session_start();

//If the user is logged and verified
if ((isset($_SESSION['username'])) && (isset($_SESSION['verified'])) && ($_SESSION['verified'] == true)) {
  require_once('./include/dbconnect.php');
  //Select the list of verified users
  if ($query = mysqli_prepare($db, "SELECT username FROM users WHERE verified=1 ORDER BY username ASC")){
    mysqli_stmt_execute($query);
    $list = mysqli_stmt_get_result($query);
    $numr = mysqli_num_rows($list);
  } else {
    die(mysqli_error($db));
  } ?>

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

<?php

//If we have results (verified users)
if ($numr > 0) {

  echo "<b>".$_SESSION['username']."</b>, here's the list of verified members <br /><br />";

  while ($usr = mysqli_fetch_array($list)){
      $u = $usr['username'];
      ?><a href="send.php?to=<?php echo htmlentities($u, ENT_QUOTES, 'UTF-8'); ?>"><? echo $u; ?></a><br /><?php
  }
} else {
  echo "There is currently no verified user <br />";
}

} else {

  $_SESSION['msg'] = "<div class=\"error\">You must log in first</div><br />";
  header('location: login.php');
}
?>

<br />
<a href="index.php">Index page</a><br />
<a href="index.php?logout='1'" style="color: red;">Logout</a>

</div>
<div class="footer" >
  <span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
</div>

</body>
</html>
