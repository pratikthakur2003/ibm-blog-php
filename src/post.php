<?php
include 'db_connection.php';
session_start();

# Setup
$post_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$current_user_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
$current_user_name = isset($_SESSION['username']) ? $_SESSION['username'] : "Anonymous";

# Fetch post
$post_sql = "SELECT p.id, p.user_id, p.title, p.content, p.image, p.created_at, u.username 
             FROM posts p 
             JOIN users u ON p.user_id = u.id 
             WHERE p.id = ?";
$stmt = $conn->prepare($post_sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post_result = $stmt->get_result();

if ($post_result->num_rows > 0) {
    $post = $post_result->fetch_assoc();
} else {
    die("No post found.");
}

# Insert comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && $current_user_id != 0) {
    $comment = trim($_POST['comment']);
    if ($comment !== "") {
        $insert_sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iis", $post_id, $current_user_id, $comment);
        $insert_stmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $post_id . "#comments");
    exit;
}

# Fetch comments with pagination
$comments_per_page = 5;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $comments_per_page;

$comment_sql = "SELECT c.id, c.content, c.created_at, u.username 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.post_id = ? 
                ORDER BY c.created_at DESC 
                LIMIT ? OFFSET ?";
$stmt = $conn->prepare($comment_sql);
$stmt->bind_param("iii", $post_id, $comments_per_page, $offset);
$stmt->execute();
$comment_result = $stmt->get_result();

# Total comments count
$count_sql = "SELECT COUNT(*) AS total FROM comments WHERE post_id = ?";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$count_result = $stmt->get_result();
$total_comments = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_comments / $comments_per_page);

# Prepare post image URL
$displayImage = !empty($post['image'])
    ? $post['image']
    : 'https://static.vecteezy.com/system/resources/thumbnails/008/818/339/large/the-background-of-the-technological-process-of-the-computer-system-light-blue-lines-on-a-dark-background-the-animation-has-a-cyclic-video.jpg';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - IBM Blog</title>
    <link rel="stylesheet" href="../assets/css/post.css">
</head>

<body>

    <header class="header">
        <div class="header-container">
            <div class="header-logo"><a href="home.php">
                    <img src="../assets/data/logo-cropped.png" style="height: 3em; width: 8em;" />
                </a></div>
            <nav class="header-links">
                <a href="home.php">Home</a>
                <?php if ($current_user_id != 0): ?>
                    <a href="create_post.php">Create Blog</a>
                    <a href="profile.php">Hello, <?= htmlspecialchars($current_user_name) ?></a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="register.php">Register</a>
                    <a href="login.php">Sign in</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="content">
        <article class="post-container">
            <h2><?= htmlspecialchars($post['title']) ?></h2>
            <p class="meta">By <?= htmlspecialchars($post['username']) ?> | <?= $post['created_at'] ?></p>

            <?php if ($displayImage): ?>
                <div class="post-img">
                    <img src="<?= htmlspecialchars($displayImage) ?>" alt="Blog Image">
                </div>
            <?php endif; ?>

            <div class="post-body">
                <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
            </div>
        </article>

        <section id="comments" class="comments-section">
            <h3>Comments (<?= $total_comments ?>)</h3>

            <div class="comment-list">
                <?php if ($comment_result->num_rows > 0): ?>
                    <?php while ($row = $comment_result->fetch_assoc()): ?>
                        <div class="comment-card">
                            <strong><?= htmlspecialchars($row['username']) ?></strong>
                            <p><?= htmlspecialchars($row['content']) ?></p>
                            <small>Posted on <?= $row['created_at'] ?></small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No comments yet. Be the first to comment!</p>
                <?php endif; ?>
            </div>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?id=<?= $post_id ?>&page=<?= $page - 1 ?>#comments">&laquo;</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?id=<?= $post_id ?>&page=<?= $i ?>#comments"
                        class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?id=<?= $post_id ?>&page=<?= $page + 1 ?>#comments">&raquo;</a>
                <?php endif; ?>
            </div>

            <?php if ($current_user_id != 0): ?>
                <form action="post.php?id=<?= $post_id ?>#comments" method="post" class="comment-form">
                    <label for="comment">Leave a Comment:</label>
                    <textarea id="comment" name="comment" rows="4" required></textarea>
                    <button type="submit">Submit</button>
                </form>
            <?php else: ?>
                <p><em>You must <a href="login.php">login</a> to leave a comment.</em></p>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2025 IBM Blog.</p>
    </footer>

</body>

</html>