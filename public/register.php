<?php include('server.php') ?>
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

  <form method="post" action="register.php">
  	<?php include('errors.php'); ?>
  	<div class="input-group">
      <h3>Registration</h3>
      <br>
  	  <label>Username</label>
  	  <input type="text" name="username" value="<?php echo $username; ?>">
  	</div>
  	<div class="input-group">
  	  <label>Email</label>
  	  <input type="email" name="email" value="<?php echo $email; ?>">
  	</div>
  	<div class="input-group">
  	  <label>Password</label>
  	  <input type="password" id="password_1" name="password_1" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, at least one special character and should be at least 8 characters long" required>
  	</div>
  	<div class="input-group">
  	  <label>Confirm password</label>
  	  <input type="password" name="password_2" required>
  	</div>
  	<div class="input-group">
  	  <button type="submit" class="btn" name="reg_user">Register</button>
  	</div>

    <div id="message">
      <h3>Password must contain the following:</h3>
      <p class="validate-result">A <b>lowercase</b> letter</p>
      <p class="validate-result">A <b>capital (uppercase)</b> letter</p>
      <p class="validate-result">A <b>number</b></p>
      <p class="validate-result">A <b>special</b> charachter</p>
      <p class="validate-result">Minimum <b>8 characters</b></p>
    </div>
  	<p>
  		Already a member? <a href="login.php">Sign in</a>
  	</p>
  </form>
  <div class="footer" >
    <span>Developed by <a href="https://albertoradice.com" target="_blank">Alberto Radice</a></span>
  </div>


<script>
var myInput = document.getElementById("password_1");

//When the user clicks on the password input field, show the message box
myInput.onfocus = function() {
    document.getElementById("message").style.display = "block";
}

// When the user clicks outside of the password field, hide the message box
myInput.onblur = function() {
  document.getElementById("message").style.display = "none";
}

//Validation function below
  const pswEl = document.querySelector('#password_1'),
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
