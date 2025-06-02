<?php
session_start();
require 'db.php';

// Fetch featured audiobooks
$stmt = $pdo->query("SELECT a.*, c.name AS category FROM audiobooks a JOIN categories c ON a.category_id = c.id LIMIT 4");
$audiobooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audible Clone - Home</title>
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
        .featured, .categories {
            margin-bottom: 40px;
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
        .categories a {
            display: inline-block;
            background: #ff4500;
            color: white;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            text-decoration: none;
        }
        .categories a:hover {
            background: #ff8c00;
        }
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: relative;
            bottom: 0;
            width: 100%;
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" onclick="redirect('dashboard.php')">Dashboard</a>
                <a href="#" onclick="redirect('logout.php')">Logout</a>
            <?php else: ?>
                <a href="#" onclick="redirect('login.php')">Login</a>
                <a href="#" onclick="redirect('signup.php')">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">
        <div class="featured">
            <h2>Featured Audiobooks</h2>
            <div class="audiobook-grid">
                <?php foreach ($audiobooks as $book): ?>
                    <div class="audiobook-card">
                        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p>By <?php echo htmlspecialchars($book['author']); ?></p>
                        <p><?php echo htmlspecialchars($book['category']); ?></p>
                        <button onclick="redirect('player.php?id=<?php echo $book['id']; ?>')">Listen Now</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="categories">
            <h2>Categories</h2>
            <?php foreach ($categories as $category): ?>
                <a href="#" onclick="redirect('library.php?category=<?php echo $category['id']; ?>')"><?php echo htmlspecialchars($category['name']); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Audible Clone. All rights reserved.</p>
    </footer>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
