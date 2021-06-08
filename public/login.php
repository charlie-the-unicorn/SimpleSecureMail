<?php include('server.php') ?>
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

  <form method="post" action="login.php">
    <?php
    include('errors.php');
    if (isset($_SESSION['msg'])) {
        echo $_SESSION['msg'];
    }
    ?>
  	<div class="input-group">

      <h3>Login</h3>
      <br>
  		<label>Username</label>
  		<input type="text" name="username" >
  	</div>
  	<div class="input-group">
  		<label>Password</label>
  		<input type="password" name="password">
  	</div>
  	<div class="input-group">
  		<button type="submit" class="btn" name="login_user">Login</button>
  	</div>
  	<p>
  		Not yet a member? <a href="register.php">Sign up</a><br>
      Forgot your password? <a href="forgot_pwd.php">Reset it</a>
  	</p>
  </form>
  <div class="footer" >
    <span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
  </div>
</body>
</html>
