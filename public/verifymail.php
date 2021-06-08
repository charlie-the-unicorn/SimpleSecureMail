<?php require_once('./include/dbconnect.php'); ?>

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


<? if (!empty($_GET['usr']) && isset($_GET['usr']) && !empty($_GET['tok']) && isset($_GET['tok'] )){

    $u = $_GET['usr'];
    $t = $_GET['tok'];

    if ($up_stmt = mysqli_prepare($db, "UPDATE users SET verified=1 WHERE username=? AND token=?")){

      mysqli_stmt_bind_param($up_stmt, "ss", $u, $t);
      mysqli_stmt_execute($up_stmt);

      $affected_rows = mysqli_stmt_affected_rows($up_stmt);
        if ($affected_rows == 1) {
          echo "<div class=\"success\" >Your account has been activated, please <a href=\"./login.php\">click here</a> to login</div>";
          mysqli_stmt_close($up_stmt);
        } else {
          echo "<div class=\"error\" >Error occurred, you may have provided wrong data</div>";
          mysqli_stmt_close($up_stmt);
        }
    } else {
      echo "<div class=\"error\" >Couldn't compete the request</div>";
    }


  } else {
  echo "<div class=\"error\" >The link you provided seems incomplete</div>";
  }

?>
</div>

<div class="footer" >
  <span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
</div>

</body>
</html>
