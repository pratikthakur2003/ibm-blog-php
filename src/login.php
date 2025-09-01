<?php
include "db_connection.php";
include "Auth.php";
session_start();

class Login implements Auth
{
    private $conn;
    private $email;
    private $password;

    public function __construct($conn, $email, $password)
    {
        $this->conn = $conn;
        $this->email = $email;
        $this->password = $password;
    }

    public function validateInput($data)
    {
        return htmlspecialchars(trim($data));
    }

    public function process()
    {
        $email = $this->validateInput($this->email);
        $sql = "SELECT id,username,email,password FROM users WHERE email = '$email'";
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($this->password, $row['password'])) {
                $_SESSION['id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['email'] = $row['email'];
                header("Location: home.php");
                exit;
            } else {
                $_SESSION['login_error'] = "Incorrect password";
                header("Location: login.php");
                exit;
            }
        } else {
            $_SESSION['login_error'] = "No user found with this email";
            header("Location: login.php");
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $login = new Login($conn, $_POST['email'], $_POST['password']);
    $login->process();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <header class="header">
        <div class="header-container">
            <div class="header-logo">
                <a href="home.php">
                    <img src="../assets/data/logo-cropped.png" style="height: 3em; width: 8em;" />
                </a>
            </div>
            <nav class="header-links">
                <a href="home.php">Home</a>
                <a href="create_post.php">Create Blog</a>
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="profile.php">Hello, <?= ucwords(strtolower(htmlspecialchars($_SESSION['username']))) ?></a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="register.php">Register</a>
                    <a href="login.php" class="active">Sign in</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <section class="login-section">
        <div class="login-container">
            <h2>Sign In</h2>
            <?php if (isset($_SESSION['login_error'])): ?>
                <p class="error"><?= $_SESSION['login_error'];
                unset($_SESSION['login_error']); ?></p>
            <?php endif; ?>
            <form action="login.php" method="post">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Login</button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </section>

    <footer class="footer">
        <p>© 2025 IBM Blog.</p>
    </footer>
</body>

</html>