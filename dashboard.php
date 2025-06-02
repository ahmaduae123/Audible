<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>redirect('login.php');</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Save book to library
if (isset($_POST['save_book'])) {
    $book_id = (int)$_POST['save_book'];
    $stmt = $pdo->prepare("INSERT INTO user_library (user_id, audiobook_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE saved_at = CURRENT_TIMESTAMP");
    $stmt->execute([$user_id, $book_id]);
}

// Save progress
if (isset($_POST['progress']) && isset($_POST['book_id'])) {
    $progress = (int)$_POST['progress'];
    $book_id = (int)$_POST['book_id'];
    $stmt = $pdo->prepare("INSERT INTO listening_progress (user_id, audiobook_id, progress) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE progress = ?, updated_at = CURRENT_TIMESTAMP");
    $stmt->execute([$user_id, $book_id, $progress, $progress]);
}

// Fetch saved books
$stmt = $pdo->prepare("SELECT a.*, lp.progress FROM user_library ul JOIN audiobooks a ON ul.audiobook_id = a.id LEFT JOIN listening_progress lp ON a.id = lp.audiobook_id AND lp.user_id = ?");
$stmt->execute([$user_id]);
$saved_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Audible Clone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        header {
            background: linear-gradient(90deg, #ff8c00, #ff4500);
            padding: 20px;
            text-align: center;
            color: white;
        }
        nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .audiobook-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .audiobook-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .audiobook-card:hover {
            transform: scale(1.05);
        }
        .audiobook-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .audiobook-card h3, .audiobook-card p {
            padding: 0 10px;
        }
        button {
            background: #ff4500;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
        }
        button:hover {
            background: #ff8c00;
        }
        @media (max-width: 768px) {
            .audiobook-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Audible Clone</h1>
        <nav>
            <a href="#" onclick="redirect('index.php')">Home</a>
            <a href="#" onclick="redirect('library.php')">Library</a>
            <a href="#" onclick="redirect('dashboard.php')">Dashboard</a>
            <a href="#" onclick="redirect('logout.php')">Logout</a>
        </nav>
    </header>
    <div class="container">
        <h2>Your Library</h2>
        <div class="audiobook-grid">
            <?php foreach ($saved_books as $book): ?>
                <div class="audiobook-card">
                    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                    <p>By <?php echo htmlspecialchars($book['author']); ?></p>
                    <p>Progress: <?php echo ($book['progress'] ? floor($book['progress'] / 60) . ' min' : 'Not started'); ?></p>
                    <button onclick="redirect('player.php?id=<?php echo $book['id']; ?>')">Listen Now</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
