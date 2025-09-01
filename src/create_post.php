<?php
include "db_connection.php";
// require 'vendor/autoload.php';

require __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

session_start();

if (!isset($_SESSION['id'])) {
  header("Location: login.php");
  exit();
}

function cloudInit()
{

  // Cloudinary config
  return new Cloudinary([
    'cloud' => [
      'cloud_name' => 'dqbi6kzt9',
      'api_key' => '519775242154421',
      'api_secret' => 'ZhHdBncxWD8lAqH8mtiPEtnJqac',
    ],
    'url' => [
      'secure' => true
    ]
  ]);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $title = trim($_POST['title']);
  $content = trim($_POST['content']);
  $user_id = $_SESSION['id'];
  $imageUrl = null;
  $error = '';

  // Upload image to Cloudinary
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($_FILES['image']['tmp_name']);

    if (in_array($fileType, $allowedTypes)) {
      if ($_FILES['image']['size'] <= 5 * 1024 * 1024) { // Max 5MB
        try {
          $cloudinary = cloudInit();
          $uploadResult = $cloudinary->uploadApi()->upload($_FILES['image']['tmp_name'], [
            'folder' => 'blog_posts'
          ]);
          $imageUrl = $uploadResult['secure_url']; // Save this URL to DB
        } catch (Exception $e) {
          $error = "Image upload failed: " . $e->getMessage();
        }
      } else {
        $error = "Image size must be less than 5MB.";
      }
    } else {
      $error = "Only JPG, PNG, GIF, and WEBP images are allowed.";
    }
  }

  if (!empty($title) && !empty($content) && empty($error)) {
    $stmt = $conn->prepare("INSERT INTO posts (title, content, image, user_id, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssi", $title, $content, $imageUrl, $user_id);

    if ($stmt->execute()) {
      header("Location: home.php?msg=Post+created+successfully");
      exit();
    } else {
      $error = "Error: " . $stmt->error;
    }
    $stmt->close();
  } elseif (empty($title) || empty($content)) {
    $error = "Both Title and Content are required.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Blog - IBM Blog</title>
  <link rel="stylesheet" href="../assets/css/create_post.css">
</head>

<body>
  <header class="header">
    <div class="header-container">
      <div class="header-logo"><a href="home.php">
          <img src="../assets/data/logo-cropped.png" style="height: 3em; width: 8em;" />
        </a></div>
      <nav class="header-links">
        <a href="home.php">Home</a>
        <a href="profile.php">Hello, <?= ucwords(strtolower(htmlspecialchars($_SESSION['username']))) ?></a>
        <a href="logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="content">
    <div class="form-container">
      <h2>Create a New Blog</h2>

      <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
          <label for="title">Title</label>
          <input type="text" id="title" name="title" required>
        </div>

        <div class="form-group">
          <label for="content">Content</label>
          <textarea id="content" name="content" rows="8" required></textarea>
        </div>

        <div class="form-group">
          <label for="image">Upload Image (optional)</label>
          <input type="file" id="image" name="image" accept="image/*">
        </div>

        <button type="submit">Publish</button>
      </form>
    </div>
  </main>
</body>

</html>