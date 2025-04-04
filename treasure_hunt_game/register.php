<?php
session_start();
include 'config.php';
include 'send_mail.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $qualification = $_POST["qualification"];
    $dob = $_POST["dob"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT); // Hash Password
    $referred_by = $_POST["referral_code"]; // Optional Referral Code

    // Check if email already exists
    $check_email = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($check_email->num_rows > 0) {
        die("Email already registered!");
    }

    // Generate OTP
    $otp = rand(100000, 999999);

    // Generate Unique Referral Code
    $referral_code = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 8);

    // Insert user with OTP
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, address, qualification, dob, password, otp_code, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $name, $email, $phone, $address, $qualification, $dob, $password, $otp, $referral_code, $referred_by);
    $stmt->execute();
	if ($stmt->affected_rows > 0) {
		$user_id = $stmt->insert_id; // Get the last inserted user ID
	
		// Function to generate a random 4-character alphanumeric passcode
		function generatePasscode() {
			return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 4);
		}
	
		// Insert passcodes for levels 1 to 5
		$password_stmt = $conn->prepare("INSERT INTO passwords (user_id, level, passcode) VALUES (?, ?, ?)");
		for ($level = 1; $level <= 5; $level++) {
			$passcode = generatePasscode();
			$password_stmt->bind_param("iis", $user_id, $level, $passcode);
			$password_stmt->execute();
		}
		$password_stmt->close();
	}
	

    // Send OTP Email
    $subject = "Your OTP Code for Quiz Registration";
    $body = "Your OTP is: <b>$otp</b>";
    sendMail($email, $subject, $body);

    $_SESSION['email'] = $email; // Store email for verification step
    header("Location: verify.php");
}
?>



<!-- New register form -->


<!doctype html>
<html lang="en">
  <head>
  	<title>Registrer</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<link rel="stylesheet" href="css/style.css">
<style>
.heading-section{
            background: #121FCF;
background: linear-gradient(to right, #121FCF 0%, #CF1512 100%);
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
            color: #fff;

        }
</style>
	</head>
	<body>
	<section class="ftco-section">
		<div class="container">
			
			<div class="row justify-content-center">
			<div class="col-md-6 text-center mb-5">
                <h2 class="heading-section">SMECLab’s Weekly Games</h2>
            </div>
			</div>
			<div class="row justify-content-center">
				<div class="col-md-12 col-xl">
					<div class="wrap d-md-flex">
						<div class="img" style="background-image: url(images/quiz.jpg);">
						
			      </div>
						<div class="login-wrap p-4 p-md-5">
			      	<div class="d-flex">
			      		<div class="w-100">
			      			<h3 class="mb-4">Sign Up</h3>
			      		</div>
								<div class="w-100">
									
								</div>
			      	</div>
							<form method="POST" class="signin-form">
			      		<div class="form-group mb-3">
			      			<label class="label" for="name">Name</label>
			      			<input type="text" class="form-control" name="name" placeholder="Full Name" required>
			      		</div>
                          <div class="form-group mb-3">
			      			<label class="label" for="name">Email</label>
			      			<input type="email" class="form-control" name="email" placeholder="Email Id" required>
			      		</div>
                          <div class="form-group mb-3">
			      			<label class="label" for="name">Phone</label>
			      			<input type="text" class="form-control" name="phone" placeholder="Phone Number" required>
			      		</div>
                          <div class="form-group mb-3">
			      			<label class="label" for="name">Place</label>
			      			<input type="text" class="form-control" name="address" placeholder="Place" required>
			      		</div>
                          <div class="form-group mb-3">
			      			<label class="label" for="name">Qualification</label>
			      			<input type="text" class="form-control" name="qualification" placeholder="Qualification" required>
			      		</div>
                          <div class="form-group mb-3">
			      			<label class="label" for="name">Date of Birth</label>
			      			<input type="date" class="form-control" name="dob"  required>
			      		</div>
                          <div class="form-group mb-3">
			      			<label class="label" for="name">Password</label>
			      			<input type="password" class="form-control" name="password"  required>
			      		</div>
                          

		            <div class="form-group">
		            	<button type="submit" class="form-control btn btn-primary rounded submit px-3">Register</button>
		            </div>
		            
		          </form>
		          <p class="text-center">Already Register? <a href="index.php">Sign In</a></p>
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

