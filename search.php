<?php
require __DIR__ . '/config/config.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$keyword = '%' . $q . '%';

$sql = "
SELECT id, title, slug,
  (
    CASE
      WHEN title LIKE ? THEN 3
      WHEN title LIKE ? THEN 2
      WHEN title LIKE ? THEN 1
      ELSE 0
    END
  ) AS relevance
FROM products
WHERE title LIKE ?
ORDER BY relevance DESC, title ASC
LIMIT 8
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $q . '%',        
    '% ' . $q . '%', 
    '%' . $q . '%', 
    '%' . $q . '%'
]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
