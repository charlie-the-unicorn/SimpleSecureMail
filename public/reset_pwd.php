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

/* Add a green text color and a checkmark when the requirements are right */
.validate-result{
  color: red;
}

.validate-result:before{
  position: relative;
  left: -35px;
   content: "✖";
}

.valid {
    color: green;
}

.valid:before {
  position: relative;
  left: -35px;
  content: "✔";
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
  if (isset($_GET["q"])) {
    $t=$_GET["q"];

    require_once('./include/dbconnect.php');

  if ($query = mysqli_prepare($db, "SELECT expdate FROM password_reset WHERE token=?")){
    mysqli_stmt_bind_param($query, 's', $t);
    mysqli_stmt_execute($query);
    mysqli_stmt_bind_result($query, $expdate);
    mysqli_stmt_store_result($query);
      if (mysqli_stmt_fetch($query)) { // if a matching token exists
        mysqli_stmt_free_result($query);
        mysqli_stmt_close($query);

        $timestamp = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
        $curDate = date("Y-m-d H:i:s", $timestamp);

        if ($expdate >= $curDate) {
          $res_allow = 1; //you can reset the pwd
        } else {
          if ($query1 = mysqli_prepare($db, "DELETE FROM password_reset WHERE token=?")) {
            mysqli_stmt_execute($query1);
            $msg = "<div class=\"content\"><div class=\"error\">The token has expired</div><br /><br /><a href=\"forgot_pwd.php\">Request another token</a><br /><a href=\"login.php\">Back to the login page</a></div>";
          }
        }

      } else {
       $msg =  "<div class=\"content\"><div class=\"error\">The link is wrong or incomplete</div><br /><br /><a href=\"login.php\">Back to the login page</a></div>";
      }


  } else {
    die(mysqli_error($db));
  }

} else {
  $msg =  "<div class=\"content\"><div class=\"error\">The link is wrong or incomplete</div><br /><br /><a href=\"login.php\">Back to the login page</a></div>";
}

if (isset($res_allow)) {
?>
    <form action="reset.php" method="POST">
      <div class="input-group">
        <h3>Reset password</h3>
        <br/>
        E-mail Address: <input type="text" name="email" required><br/>
        New Password: <input type="password" name="password" id="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, at least one special character and should be at least 8 characters long" required><br/>
        Confirm Password: <input type="password" name="confirmpassword" required><br/>
      </div>
      <input type="hidden" name="t" value="<?php echo $t ?>">
      <button type="submit" class="btn" name="ResetPasswordForm">Reset Password</button>

      <div id="message">
        <h3>Password must contain the following:</h3>
        <p class="validate-result">A <b>lowercase</b> letter</p>
        <p class="validate-result">A <b>capital (uppercase)</b> letter</p>
        <p class="validate-result">A <b>number</b></p>
        <p class="validate-result">A <b>special</b> charachter</p>
        <p class="validate-result">Minimum <b>8 characters</b></p>
      </div>
    </form>

<?php } else {
      echo $msg; //write the error msg
    }
?>

      <div class="footer" >
            <span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
      </div>

    <script>
      var myInput = document.getElementById("password");

      //When the user clicks on the password input field, show the message box
      myInput.onfocus = function() {
        document.getElementById("message").style.display = "block";
      }

      // When the user clicks outside of the password field, hide the message box
      myInput.onblur = function() {
        document.getElementById("message").style.display = "none";
      }

      //Validation function below
        const pswEl = document.querySelector('#password'),
          warnings = document.querySelectorAll('.validate-result'),
          tests = [/[a-z]/, /[A-Z]/, /[0-9]/, /\W/, /.{8,}/];

          pswEl.addEventListener('input', e => {
            const psw = e.target.value;
            tests.forEach((test, n) => {
              if (test.test(psw)) {
                warnings[n].classList.add('valid');
              } else {
                warnings[n].classList.remove('valid');
              }
            });
          });

    </script>

</body>
</html>
