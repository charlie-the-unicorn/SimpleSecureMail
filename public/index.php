<?php
  session_start();

//if the user isn't logged, take them to the login page
  if (!isset($_SESSION['username'])) {
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
	<h2><a href="index.php" style="text-decoration:none; color:white">SimpleSecureMail</a></h2>
</div>
<div class="content">
  	<!-- notification message -->
  	<?php if (isset($_SESSION['success'])) : ?>
      <div class="error success" >
      	<h3>
          <?php
          	echo $_SESSION['success'];
          	unset($_SESSION['success']);
          ?>
      	</h3>
      </div>
  	<?php endif ?>

    <!-- Check if the user is registered  -->
    <?php  if ((isset($_SESSION['username'])) && (isset($_SESSION['verified']))) :

    // Check if the registered user has validated their email
    if ($_SESSION['verified'] === true) : ?>
    <!--information for user logged in and validated -->
      <p>Welcome <strong><?php echo $_SESSION['username']; ?></strong></p><br />
      <p> <a href="userlist.php"><img src="./img/users.png" width="50" height="50" alt="Users"><h4>Users List</h4></a></p><br />
      <p> <a href="inbox.php"><img src="./img/inbox.png" width="50" height="50" alt="Inbox"><h4>Check your Inbox</h4></a></p><br />
      <p> <a href="send.php"><img src="./img/send.png" width="50" height="50" alt="Compose message"><h4>Compose a message</h4></a></p><br />
      <p> <a href="index.php?logout='1'" style="color: red;">Logout</a> </p>
    <?php else : ?>

    <!--information for user registered but not validated -->
    <p>Thanks for signing up <strong><?php echo $_SESSION['username']; ?></strong></p>
    <p> <a href="index.php?logout='1'" style="color: red;">Go back to the login page</a> </p>
  <?php endif; endif; ?>

</div>
<div class="footer" >
  <span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
</div>
</body>
</html>
