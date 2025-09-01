<?php
include "db_connection.php";
session_start();

// redirecting to login if not logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$current_user_name = $_SESSION['username'];

// fetching user info
$user_sql = "SELECT username, bio FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// handling bio submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bio'])) {
    $bio = trim($_POST['bio']);
    $update_sql = "UPDATE users SET bio = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $bio, $user_id);
    $update_stmt->execute();

    header("Location: profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Additional styles to support dynamic button addition */
        .selected-buttons {
            display: inline-block;
            margin-left: 10px;
            color: #6c757d;
            font-size: 1.2em;
        }
    </style>
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
                <a href="profile.php">Hello, <?= ucwords(strtolower(htmlspecialchars($current_user_name))) ?></a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="profile-container">
        <div class="cover-photo"></div>

        <div class="profile-info">
            <img src="../assets/data/profile.jpg" alt="Profile Picture" class="profile-pic">
            <h1><?= ucwords(strtolower(htmlspecialchars($user['username']))) ?>
                <a href="#" id="edit-bio-icon" class="edit-icon"><i class="fa-solid fa-pen"></i></a>
            </h1>
            <h3>Developer</h3>
            <span id="selected-buttons-container" class="selected-buttons"></span>

            <div class="social-icons">
                <a href="#"><i class="fa-brands fa-dribbble"></i></a>
                <a href="#"><i class="fa-brands fa-twitter"></i></a>
                <a href="#"><i class="fa-brands fa-pinterest"></i></a>
            </div>

            <div class="bio">
                <?php if (empty($user['bio'])): ?>
                    <form action="" method="post" id="bio-form">
                        <textarea name="bio" rows="5" cols="50" placeholder="Add your bio here..." required></textarea><br>
                        <button type="submit">Save Bio</button>
                    </form>
                <?php else: ?>
                    <p id="bio-text"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                    <form action="" method="post" id="bio-form" style="display:none;">
                        <textarea name="bio" rows="5" cols="50"
                            required><?= htmlspecialchars($user['bio']) ?></textarea><br>
                        <button type="submit">Update Bio</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="buttons">
                <a href="#" class="btn purple" onclick="addButton('Studio')"><i class="fa-solid fa-camera"></i>
                    Studio</a>
                <a href="#" class="btn purple" onclick="addButton('Work')"><i class="fa-solid fa-palette"></i> Work</a>
                <a href="#" class="btn purple" onclick="addButton('Favorite')"><i class="fa-solid fa-heart"></i>
                    Favorite</a>
            </div>
        </div>
    </div>

    <script>
        // toggling edit bio form
        const editIcon = document.getElementById('edit-bio-icon');
        const bioText = document.getElementById('bio-text');
        const bioForm = document.getElementById('bio-form');

        if (editIcon) {
            editIcon.addEventListener('click', (e) => {
                e.preventDefault();
                if (bioText) bioText.style.display = 'none';
                bioForm.style.display = 'block';
            });
        }

        function addButton(buttonName) {
            const container = document.getElementById('selected-buttons-container');
            container.innerHTML = '';
            const newButton = document.createElement('span');
            newButton.textContent = buttonName;
            container.appendChild(newButton);
        }

    </script>

</body>

</html>