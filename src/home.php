<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "db_connection.php";
session_start();

$blogs_per_page = 9;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $blogs_per_page;

$count_sql = "SELECT COUNT(*) AS total FROM posts";
$count_result = $conn->query($count_sql);
$total_blogs = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_blogs / $blogs_per_page);

// Featured blog
$featured_sql = "SELECT p.id, p.title, p.content, p.created_at, p.image, u.username
                 FROM posts p
                 JOIN users u ON p.user_id = u.id
                 ORDER BY p.created_at DESC
                 LIMIT 1";
$featured_result = $conn->query($featured_sql);
$featured_blog = $featured_result->fetch_assoc();

// Blogs for current page
$sql = "SELECT p.id, p.title, p.content, p.created_at, p.image, u.username
        FROM posts p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT $start, $blogs_per_page";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IBM Blog</title>
  <link rel="stylesheet" href="../assets/css/home.css">
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
        <a href="create_post.php">Create Blog</a>
        <?php if (isset($_SESSION['username'])): ?>
          <a href="profile.php">Hello, <?= ucwords(strtolower(htmlspecialchars($_SESSION['username']))) ?></a>
          <a href="logout.php">Logout</a>
        <?php else: ?>
          <a href="register.php">Register</a>
          <a href="login.php">Sign in</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <section class="content">
    <div class="content-container">
      <div class="content-up">
        <h1>Featured Blog</h1>
        <!-- <div class="content-up-logo">
          <img src="../assets/data/logo-cropped.png" alt="IBM Logo">
        </div>
        <div class="content-up-search">
          <input type="text" id="content-up-search-text" placeholder="Search blog here" />
        </div> -->
      </div>

      <?php
      $bg_url = !empty($featured_blog['image'])
        ? $featured_blog['image']
        : 'https://static.vecteezy.com/system/resources/thumbnails/008/818/339/large/the-background-of-the-technological-process-of-the-computer-system-light-blue-lines-on-a-dark-background-the-animation-has-a-cyclic-video.jpg';
      ?>
      <div class="content-down" style="
        background-image: url('<?= $bg_url ?>'); 
        background-size: cover; 
        background-position: center; 
        color: white; 
        padding: 30px; 
        border-radius: 12px; 
        min-height: 250px; 
        display: flex; 
        flex-direction: column; 
        justify-content: flex-end; 
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
        background-attachment: fixed;
        position: relative;
    ">

        <div style="
        position: absolute; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0), rgba(0, 0, 0, 1));
        border-radius: 12px;"></div>

        <div class="content-down-featured" style="z-index: 1; color: white;">
          <?php if ($featured_blog): ?>
            <h2>
              <a href="post.php?id=<?= $featured_blog['id'] ?>" style="color:white; text-decoration:none;">
                <?= htmlspecialchars($featured_blog['title']) ?>
              </a>
            </h2>
            <p><?= htmlspecialchars(substr($featured_blog['content'], 0, 200)) ?>...</p>
            <small>By <?= ucwords(strtolower(htmlspecialchars($featured_blog['username']))) ?> |
              <?= $featured_blog['created_at'] ?></small>
          <?php else: ?>
            <p>No featured blog available.</p>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </section>

  <section class="blogs">
    <div class="blogs-container">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
          $blog_img = !empty($row['image'])
            ? $row['image']
            : 'https://img.freepik.com/premium-photo/yellow-blue-abstract-creative-background-blue-abstract-background-geometric-background_481527-28134.jpg?semt=ais_hybrid&w=740&q=80';
          ?>
          <div class="blog-tile">
            <div class="blog-img" style="background-image : url('<?=  htmlspecialchars($blog_img) ?>'); background-size: cover; background-repeat: no-repeat;">
            <!-- <div class="blog-img" style="background-image : url('https://img.freepik.com/premium-photo/yellow-blue-abstract-creative-background-blue-abstract-background-geometric-background_481527-28134.jpg?semt=ais_hybrid&w=740&q=80"> -->
              <!-- <img src="<?= htmlspecialchars($blog_img) ?>"
                style="width:100%; height:200px; object-fit:cover; border-radius:8px;"> -->
            </div>
            <div class="blog-content">
              <h3>
                <a href="post.php?id=<?= $row['id'] ?>">
                  <?= htmlspecialchars($row['title']) ?>
                </a>
              </h3>
              <p><?= htmlspecialchars(substr($row['content'], 0, 120)) ?>...</p>
              <small>By <?= ucwords(strtolower(htmlspecialchars($row['username']))) ?> | <?= $row['created_at'] ?></small>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No blogs found.</p>
      <?php endif; ?>
    </div>

    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>">&laquo;</a>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>

      <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>">&raquo;</a>
      <?php endif; ?>
    </div>
  </section>

  <footer class="footer">
    <p>© 2025 IBM Blog.</p>
  </footer>
</body>

</html>