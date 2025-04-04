<?php
session_start();
include 'config.php';

// Admin credentials (hardcoded)
$fixed_username = "admin_smec_quiz";
$fixed_password = "403035Abhi#";

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($username === $fixed_username && $password === $fixed_password) {
        $_SESSION["admin_logged_in"] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error_message = "Invalid username or password!";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

// Redirect to login if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    </head>
    <body class="bg-light">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header text-center">
                            <h3>Admin Login</h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error_message)) { ?>
                                <div class="alert alert-danger"><?= $error_message ?></div>
                            <?php } ?>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
<?php
    exit();
}

// Pagination setup
$limit = 10; // Users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total user count
$total_query = "SELECT COUNT(*) as total FROM users";
$total_result = $conn->query($total_query);
$total_users = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// Fetch users with scores and pagination
$query = "SELECT users.id, users.name, users.email, users.phone, COALESCE(results.correct_answers, 0) AS score
          FROM users
          LEFT JOIN results ON users.id = results.user_id
          LIMIT $limit OFFSET $offset";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - User Scores</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 50px; }
        .table { background-color: white; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">Admin Panel - User Scores</h2>
    <div class="text-right mb-3">
        <a href="admin.php?logout=true" class="btn btn-danger">Logout</a>
    </div>

    <table class="table table-bordered text-center">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['score']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1) { ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
            <?php } ?>
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php } ?>
            <?php if ($page < $total_pages) { ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
            <?php } ?>
        </ul>
    </nav>
</div>

</body>
</html>
