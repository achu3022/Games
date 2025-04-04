<?php
session_start(); // Start session to access stored email
include 'config.php';

if (!isset($_SESSION['email'])) {
    //Redirect to registration page if no email is stored in the session
   header("Location: register.php");
   exit();
}


?>




<!doctype html>
<html lang="en">
  <head>
  	<title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<link rel="stylesheet" href="css/style.css">
<style>

</style>
	</head>
	<body>
	<section class="ftco-section">
		<div class="container">
			
			<div class="row justify-content-center">
				<div class="col-md-12 col-lg-10">
					<div class="wrap d-md-flex">
						<div class="img" style="background-image: url(images/background.png);">
						<script type='module' src="app.js"></script>
			      </div>
						<div class="login-wrap p-4 p-md-5">
			      	<div class="d-flex">
			      		<div class="w-100">
			      			<h3 class="mb-4">OTP Verification</h3>
                            <p>OTP send to your email address.</p>
			      		</div>
								
			      	</div>
							<form method="POST" class="signin-form">
                            <?php
                            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                $email = $_SESSION['email']; // Retrieve email from session
                                $otp = $_POST["otp"];
                            
                                // Correct query to check OTP without expiry time
                                $query = $conn->prepare("SELECT * FROM users WHERE email = ? AND otp_code = ?");
                                $query->bind_param("ss", $email, $otp);
                                $query->execute();
                                $result = $query->get_result();
                                if ($result->num_rows > 0) {
                                    // OTP Verified - Activate Account
                                   $conn->query("UPDATE users SET otp_code = NULL WHERE email = '$email'");
                                   echo '<div class="text-success">OTP Verified! You can now <a href="index.php">Login</a></div>';
                                   unset($_SESSION['email']); // Clear session after successful verification
                                   } else {
                                       echo '<div class="text-danger">Invalid OTP. Try again!</div>';
                                   }
                            }
                            ?>
								
			      		<div class="form-group mb-3">
			      			<label class="label" for="name">Enter OTP</label>
			      			<input type="text" class="form-control" name="otp" placeholder="Enter OTP" required>
			      		</div>
		           
		            <div class="form-group">
		            	<button type="submit" class="form-control btn btn-primary rounded submit px-3">Verify OTP</button>
		            </div>
		            
		          </form>
		          <p class="text-center">Not a member? <a href="register.php">Sign Up</a></p>
                  <p class="text-center"><a href="terms.php">Terms & Conditions Applied</a></p>
		        </div>
		      </div>
				</div>
			</div>
		</div>
	</section>

	<script src="js/jquery.min.js"></script>
  <script src="js/popper.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/main.js"></script>
  

	</body>
</html>

