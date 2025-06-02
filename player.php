<?php
require 'db.php';
session_start();

if (!isset($_GET['id'])) {
    echo "<script>redirect('library.php');</script>";
    exit;
}

$book_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM audiobooks WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    echo "<script>redirect('library.php');</script>";
    exit;
}

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT progress FROM listening_progress WHERE user_id = ? AND audiobook_id = ?");
    $stmt->execute([$_SESSION['user_id'], $book_id]);
    $progress = $stmt->fetchColumn() ?: 0;
} else {
    $progress = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - Audible Clone</title>
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
        .player-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .player-container img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .controls {
            margin: 20px 0;
        }
        button {
            background: #ff4500;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #ff8c00;
        }
        select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
        }
        audio {
            width: 100%;
            margin-top: 20px;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        @media (max-width: 480px) {
            .player-container {
                padding: 10px;
            }
            .player-container img {
                width: 150px;
                height: 150px;
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
    <div class="player-container">
        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
        <h2><?php echo htmlspecialchars($book['title']); ?></h2>
        <p>By <?php echo htmlspecialchars($book['author']); ?></p>
        <p><?php echo htmlspecialchars($book['description']); ?></p>
        <audio id="audioPlayer" src="<?php echo htmlspecialchars($book['audio_file']); ?>" controls></audio>
        <div class="controls">
            <button onclick="playPause()">Play/Pause</button>
            <button onclick="rewind()">Rewind 10s</button>
            <button onclick="forward()">Forward 10s</button>
            <select id="speedControl" onchange="changeSpeed()">
                <option value="1">1x</option>
                <option value="1.5">1.5x</option>
                <option value="2">2x</option>
            </select>
        </div>
        <div id="error" class="error"></div>
    </div>
    <script>
        const audio = document.getElementById('audioPlayer');
        let progress = <?php echo $progress; ?>;
        audio.currentTime = progress;

        console.log("Audio Source: " + audio.src);

        audio.addEventListener('error', (e) => {
            const errorDiv = document.getElementById('error');
            errorDiv.textContent = "Error loading audio. Check if the file exists or is accessible.";
            console.error("Audio Error: ", e);
        });

        function playPause() {
            if (audio.paused) {
                audio.play().catch(e => {
                    document.getElementById('error').textContent = "Playback failed: " + e.message;
                });
            } else {
                audio.pause();
            }
        }

        function rewind() {
            audio.currentTime = Math.max(0, audio.currentTime - 10);
            saveProgress();
        }

        function forward() {
            audio.currentTime = Math.min(audio.duration, audio.currentTime + 10);
            saveProgress();
        }

        function changeSpeed() {
            audio.playbackRate = document.getElementById('speedControl').value;
        }

        function saveProgress() {
            <?php if (isset($_SESSION['user_id'])): ?>
                fetch('dashboard.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'progress=' + audio.currentTime + '&book_id=<?php echo $book_id; ?>'
                });
            <?php endif; ?>
        }

        audio.addEventListener('timeupdate', saveProgress);

        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
