<?php
include 'config.php';
session_start();
$conflicting_user = $_SESSION['warning_user'] ?? "Unknown";
$mac_address = $_SESSION['mac_address'];
$ip_address = $_SESSION['ip_address'] ?? "Unknown IP";
$emp_id = $_SESSION['emp_id'] ?? null;

if (!$conflicting_user || !$mac_address) {
    echo "Unauthorized access!";
    exit;
}

$query = "SELECT name FROM staffs WHERE emp_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$uname = $user_data['name'] ?? "Unknown User";
?>

<head>
  <title>Warning - Device Already Used</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    @import url('https://fonts.googleapis.com/css?family=Playfair+Display:400,400i,700,700i,900,900i');
    @import url('https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i');

    * {
      box-sizing: border-box;
    }

    html, body {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #eaeaea;
    }

    .carousel {
      width: 100%;
      height: 100%;
      display: flex;
      max-width: 1000px;
      max-height: 550px;
      overflow: hidden;
      position: relative;
      flex-direction: row;
      background: #fff;
    }

    .carousel-item {
      display: flex;
      width: 100%;
      height: 100%;
      align-items: center;
      justify-content: flex-end;
      position: relative;
      z-index: 0;
      flex-shrink: 0;
      transition: 0.6s all linear;
    }

    .carousel-item__info {
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 40px;
      width: 50%;
      text-align: center;
    }

    .carousel-item__image {
      width: 50%;
      height: 100%;
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
      position: relative;
      transition: 0.6s all ease-in-out;
      background-image: url('assets/images/warning.jpg');
    }

    .carousel-item__subtitle {
      font-family: 'Open Sans', sans-serif;
      letter-spacing: 3px;
      font-size: 16px;
      text-transform: uppercase;
      color: #7E7E7E;
      font-weight: 700;
      color:rgb(216, 39, 7);
    }

    .carousel-item__title {
      font-family: 'Playfair Display', serif;
      font-size: 26px;
      font-weight: 700;
      color:rgb(38, 0, 173);
      margin-top: 10px;
    }

    .carousel-item__description {
      font-family: 'Open Sans', sans-serif;
      font-size: 14px;
      color: #7e7e7e;
      line-height: 22px;
      margin-top: 10px;
    }

    .carousel-item__btn {
      display: inline-block;
      color: #2C2C2C;
      font-family: 'Open Sans', sans-serif;
      letter-spacing: 2px;
      font-size: 13px;
      text-transform: uppercase;
      font-weight: 700;
      text-decoration: none;
      margin-top: 10px;
    }

    .logout-btn {
      color: red;
      font-size: 14px;
      font-weight: bold;
      margin-top: 20px;
      display: block;
    }

    /* Mobile Responsive - Change layout on small screens */
    @media screen and (max-width: 768px) {
      .carousel {
        flex-direction: column;
        height: auto;
        max-height: none;
      }

      .carousel-item {
        flex-direction: column;
        height: auto;
      }

      .carousel-item__image {
        width: 100%;
        height: 250px;
        transform: none;
      }

      .carousel-item__info {
        width: 100%;
        padding: 20px;
      }

      .carousel-item__title {
        font-size: 22px;
      }

      .carousel-item__description {
        font-size: 13px;
      }

      .carousel-item__btn {
        font-size: 12px;
      }

      .logout-btn {
        font-size: 13px;
      }
    }
  </style>
</head>

<body>
  <div class="carousel">
    <div class="carousel-item carousel-item--1 active">
      <div class="carousel-item__image"></div>
      <div class="carousel-item__info">
        <h2 class="carousel-item__subtitle">Access Denied</h2>
        <h1 class="carousel-item__title">ഇറങ്ങിപ്പോടെ </h1>
        <p class="carousel-item__description">
    This device is already used by <strong><span style="color:#FF0000;"><?php echo $uname; ?></span></strong>.
</p>

        <a class="carousel-item__btn">
          Your IP Address: <?php echo $ip_address; ?><br>
          MAC Address: <?php echo $mac_address; ?><br>
          One device can be used for only one user.
        </a>
        <br>
        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </div>
  </div>
</body>
