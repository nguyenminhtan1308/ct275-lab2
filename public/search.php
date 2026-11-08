<?php
// Kết nối CSDL SQLite
try {
    $db = new PDO('sqlite:../ct275_lab2.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

// Lấy danh sách tác giả cho Combobox
$authors = [];
$stmt = $db->query('SELECT DISTINCT author FROM quotes ORDER BY author');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $authors[] = $row['author'];
}

// Xử lý tìm kiếm khi có request GET
$keyword = $_GET['keyword'] ?? '';
$selectedAuthor = $_GET['author'] ?? '';
$results = [];

if (!empty($keyword) || !empty($selectedAuthor)) {
    $sql = "SELECT * FROM quotes WHERE 1=1";
    $params = [];

    if (!empty($keyword)) {
        $sql .= " AND quote_text LIKE :keyword";
        $params[':keyword'] = "%$keyword%";
    }
    if (!empty($selectedAuthor)) {
        $sql .= " AND author = :author";
        $params[':author'] = $selectedAuthor;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm trích dẫn</title>
    <style>body { font-family: sans-serif; margin: 20px; } table { border-collapse: collapse; width: 100%; margin-top: 20px;} th, td { border: 1px solid #ddd; padding: 8px; text-align: left;} th { background-color: #f2f2f2;}</style>
</head>
<body>
    <h2>Tìm kiếm Trích dẫn (Lab 2)</h2>
    <form action="search.php" method="GET">
        <label for="keyword">Từ khóa:</label>
        <input type="text" id="keyword" name="keyword" value="<?= htmlspecialchars($keyword) ?>">

        <label for="author">Tác giả:</label>
        <select id="author" name="author">
            <option value="">-- Tất cả --</option>
            <?php foreach ($authors as $auth): ?>
                <option value="<?= htmlspecialchars($auth) ?>" <?= ($auth === $selectedAuthor) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($auth) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Tìm kiếm</button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && (!empty($keyword) || !empty($selectedAuthor))): ?>
        <h3>Kết quả tìm kiếm:</h3>
        <?php if (count($results) > 0): ?>
            <table>
                <tr><th>ID</th><th>Trích dẫn</th><th>Tác giả</th></tr>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['quote_text']) ?></td>
                        <td><?= htmlspecialchars($row['author']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Không tìm thấy trích dẫn nào phù hợp.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
