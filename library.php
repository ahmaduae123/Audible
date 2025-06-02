<?php
require 'db.php';
session_start();

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$query = "SELECT a.*, c.name AS category FROM audiobooks a JOIN categories c ON a.category_id = c.id";
if ($category_id) {
    $query .= " WHERE a.category_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$category_id]);
} else {
    $stmt = $pdo->query($query);
}
$audiobooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library - Audible Clone</title>
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
        .filters {
            margin-bottom: 20px;
        }
        .filters select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
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
        <div class="filters">
            <select onchange="redirect('library.php?category=' + this.value)">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="audiobook-grid">
            <?php foreach ($audiobooks as $book): ?>
                <div class="audiobook-card">
                    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                    <p>By <?php echo htmlspecialchars($book['author']); ?></p>
                    <p><?php echo htmlspecialchars($book['category']); ?></p>
                    <button onclick="redirect('player.php?id=<?php echo $book['id']; ?>')">Listen Now</button>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button onclick="saveBook(<?php echo $book['id']; ?>)">Save to Library</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
        function saveBook(bookId) {
            fetch('dashboard.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'save_book=' + bookId
            }).then(() => alert('Book saved to your library!'));
        }
    </script>
</body>
</html>
