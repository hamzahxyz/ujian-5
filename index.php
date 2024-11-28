<?php
// Koneksi ke database
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

// Konfigurasi pagination
$articles_per_page = 3; // Jumlah artikel per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $articles_per_page;

// Cek apakah ada kata kunci pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT * FROM articles";

if ($search) {
    $query .= " WHERE title LIKE :search OR content LIKE :search ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $statement = $pdo->prepare($query);
    $statement->bindValue(':search', "%$search%");
    $statement->bindValue(':limit', $articles_per_page, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
} else {
    $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $statement = $pdo->prepare($query);
    $statement->bindValue(':limit', $articles_per_page, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
}

// Eksekusi query dan ambil data artikel
$statement->execute();
$articles = $statement->fetchAll(PDO::FETCH_ASSOC);

// Hitung total artikel untuk pagination
$total_query = "SELECT COUNT(*) FROM articles";
$total_statement = $pdo->prepare($total_query);
$total_statement->execute();
$total_articles = $total_statement->fetchColumn();
$total_pages = ceil($total_articles / $articles_per_page);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Saya - Hamzah Fakhrudin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap');
        body {
            background-color: #f8f9fa;
            color: #343a40;
        }
        .header {
            background-color: #ffc107;
            padding: 20px;
            text-align: center;
            font-family: "Merriweather", serif;
            font-style: normal;
            font-weight: 300;
}
        .card {
            transition: 0.3s all ease-in-out;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .pagination .page-item.active .page-link {
            background-color: #ff9800;
            border-color: #ffc107;
            color: #fff;
        }
        .pagination .page-link:hover {
            background-color: #ffc107;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Hamzah Fakhrudin</h1>
        <p>Welcom To My Blog</p>
    </div>
    <div class="container mt-4">
        <h2>Artikel Terbaru</h2>
        <form method="GET" action="" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari artikel..." value="<?= htmlspecialchars($search); ?>">
                <button class="btn btn-warning" type="submit">Cari</button>
            </div>
        </form>
        <div class="row">
            <?php if ($articles): ?>
                <?php foreach ($articles as $article): ?>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <img src="<?= htmlspecialchars($article['image_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($article['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($article['title']); ?></h5>
                                <p class="card-text"><?= substr(htmlspecialchars($article['content']), 0, 100); ?>...</p>
                                <p><small class="text-muted">Diterbitkan: <?= htmlspecialchars($article['created_at']); ?></small></p>
                                <a href="detail.php?id=<?= $article['id']; ?>" class="btn btn-warning">Baca Selengkapnya</a>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
               <?php 
                $remaining = 4 - count($articles); 
                for ($i = 0; $i < $remaining; $i++): 
            ?>
                <div class="col-md-4">
                    <div class="card mb-4" style="visibility: hidden;">
                        <img src="#" class="card-img-top" alt="">
                        <div class="card-body">
                            <h5 class="card-title">Placeholder</h5>
                            <p class="card-text">Lorem ipsum dolor sit amet...</p>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
            <?php else: ?>
                <p class="text-center">Tidak ada artikel yang ditemukan.</p>
            <?php endif; ?>
            </div>
        </div>
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" aria-label="Previous">&laquo;</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" aria-label="Next">&raquo;</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <p>&copy; <?= date('Y'); ?> Hamzah Fakhrudin. Semua Hak Dilindungi.</p>
    </footer>
</body>
</html>
