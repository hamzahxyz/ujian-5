<?php
$host = 'localhost';
$dbname = 'blog';
$username = 'root';
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id > 0) {
    $query = "SELECT * FROM articles WHERE id = :id";
    $statement = $pdo->prepare($query);
    $statement->bindValue(':id', $article_id, PDO::PARAM_INT);
    $statement->execute();
    $article = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        die("Artikel tidak ditemukan.");
    }

    $query_other_articles = "SELECT id, title, image_url FROM articles WHERE id != :current_id ORDER BY created_at DESC LIMIT 3";
    $statement_other_articles = $pdo->prepare($query_other_articles);
    $statement_other_articles->bindValue(':current_id', $article_id, PDO::PARAM_INT);
    $statement_other_articles->execute();
    $other_articles = $statement_other_articles->fetchAll(PDO::FETCH_ASSOC);
} else {
    die("ID artikel tidak valid.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']); ?> - Hamzah fakhrudin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
        }
        .header {
            background-color: #ffc107;
            padding: 20px;
            text-align: center;
        }
        .content img {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Hamzah Fakhrudin</h1>
        <p>blog pribadi & motivasi</p>
    </div>
    <div class="container mt-4">
        
        <h2><?= htmlspecialchars($article['title']); ?></h2>
        <p><small class="text-muted">Diterbitkan pada <?= htmlspecialchars($article['created_at']); ?> oleh <?= htmlspecialchars($article['author']); ?></small></p>

        <?php if (!empty($article['image_url'])): ?>
            <img src="<?= htmlspecialchars($article['image_url']); ?>" class="img-fluid mb-4" alt="<?= htmlspecialchars($article['title']); ?>">
        <?php endif; ?>

        <div class="content">
            <p><?= nl2br(htmlspecialchars($article['content'])); ?></p>
        </div>
        <a href="index.php" class="btn btn-warning mt-3">Kembali ke Beranda</a>

        <div class="mt-5">
            <h3>Artikel Lainnya</h3>
            <div class="row">
                <?php foreach ($other_articles as $other): ?>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <img src="<?= htmlspecialchars($other['image_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($other['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($other['title']); ?></h5>
                                <a href="detail.php?id=<?= $other['id']; ?>" class="btn btn-warning">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <p>&copy; <?= date('Y'); ?> Blog Saya. Semua Hak Dilindungi.</p>
    </footer>
</body>
</html>
