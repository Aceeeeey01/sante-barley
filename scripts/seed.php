<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../config/database.php';

$sql     = file_get_contents(__DIR__ . '/../database/sante_barley_db.sql');
$catalog = json_decode(file_get_contents(__DIR__ . '/../database/catalog.json'), true);

if (!is_array($catalog)) {
    fwrite(STDERR, "Invalid catalog.json.\n");
    exit(1);
}

try {
    $db->exec($sql);
    $db->beginTransaction();

    $q = $db->prepare(
        'INSERT INTO products(category_id, product_name, slug, description, price, stock, image, size, sku)
         VALUES ((SELECT id FROM categories WHERE name=?),?,?,?,?,?,?,?,?)
         ON CONFLICT(slug) DO NOTHING'
    );

    foreach ($catalog as $i => $p) {
        if (!is_array($p) || count($p) < 7) {
            continue;
        }
        $q->execute([
            $p[1],
            $p[0],
            $p[6],
            $p[4],
            $p[2],
            $p[3],
            '/assets/images/products/' . $p[6] . '.svg',
            $p[5],
            'SB-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    $db->commit();
    echo "Schema and sample catalog ready. Prices and pack sizes are illustrative; verify before launch.\n";
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Seed failed: " . $e->getMessage() . "\n");
    exit(1);
}