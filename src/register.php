<?php
include "db_connection.php";
include "Auth.php";
session_start();

class Register implements Auth
{
  private $conn;
  private $username;
  private $email;
  private $password;

  public function __construct($conn, $username, $email, $password)
  {
    $this->conn = $conn;
    $this->username = $username;
    $this->email = $email;
    $this->password = $password;
  }

  public function validateInput($data)
  {
    return htmlspecialchars(trim($data));
  }

  public function process()
  {
    $username = $this->validateInput($this->username);
    $email = $this->validateInput($this->email);
    $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

    $stmt = $this->conn->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
      header('Location: login.php');
      exit;
    } else {
      echo "User not registered: " . $stmt->error;
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $register = new Register($conn, $_POST['username'], $_POST['email'], $_POST['password']);
  $register->process();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="stylesheet" href="../assets/css/register.css">
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
          <a href="register.php" class="active">Register</a>
          <a href="login.php">Sign in</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <section class="register-section">
    <div class="register-container">
      <h2>Create Your Account</h2>
      <form action="register.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Register</button>
      </form>

      <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
      </div>
    </div>
  </section>

  <footer class="footer">
    <p>© 2025 IBM Blog.</p>
  </footer>
</body>

</html>