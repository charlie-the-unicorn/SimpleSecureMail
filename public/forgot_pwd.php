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
  <form action="change.php" method="POST">
    E-mail Address: <input type="text" name="email"/><br>
    <button type="submit" class="btn" name="ForgotPassword" value=" Request Reset ">Request Reset</button><br><br>
    <a href="login.php">Back<a/>
  </form>
  <div class="footer" >
    <span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
  </div>
</body>
</html>
