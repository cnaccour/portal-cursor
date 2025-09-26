<?php
// Usage: php scripts/import-kb-json.php /absolute/path/to/jsonfile.json

if ($argc < 2) {
    fwrite(STDERR, "Usage: php scripts/import-kb-json.php /path/to/file.json\n");
    exit(1);
}

$jsonPath = $argv[1];
if (!is_file($jsonPath)) {
    fwrite(STDERR, "File not found: {$jsonPath}\n");
    exit(1);
}

// Load a DB connection compatible with cPanel first, fall back to local
$dbIncluded = false;
$dbPaths = [
    __DIR__ . '/../public/includes/db.php', // cPanel/public entrypoint creds
    __DIR__ . '/../includes/db.php',        // local dev creds
];
foreach ($dbPaths as $p) {
    if (file_exists($p)) {
        require_once $p;
        $dbIncluded = true;
        break;
    }
}
if (!$dbIncluded) {
    fwrite(STDERR, "Unable to load database configuration.\n");
    exit(1);
}

// Ensure kb_articles table exists (align with db/schema.sql)
$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS kb_articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(255) UNIQUE,
  title VARCHAR(255),
  category VARCHAR(100),
  content MEDIUMTEXT,
  status VARCHAR(50) DEFAULT 'published',
  allow_print TINYINT(1) DEFAULT 1,
  enable_sections TINYINT(1) DEFAULT 1,
  created_by INT NULL,
  updated_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL);

$raw = file_get_contents($jsonPath);
$data = json_decode($raw, true);
if (!$data || empty($data['document']) || empty($data['sections'])) {
    fwrite(STDERR, "Invalid JSON structure.\n");
    exit(1);
}

$doc = $data['document'];
$sections = $data['sections'];

$title = trim($doc['title'] ?? 'Untitled');
$category = trim($doc['category'] ?? '');
// Default to draft unless explicit flag provided
$isPublished = isset($doc['is_published']) ? (bool)$doc['is_published'] : false;
$status = $isPublished ? 'published' : 'draft';

// Build HTML content by concatenating sections in order
usort($sections, function($a, $b) {
    return (int)($a['section_number'] ?? 0) <=> (int)($b['section_number'] ?? 0);
});

$contentParts = [];
$contentParts[] = '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
if (!empty($doc['description'])) {
    $contentParts[] = '<p>' . htmlspecialchars($doc['description'], ENT_QUOTES, 'UTF-8') . '</p>';
}

foreach ($sections as $s) {
    $secTitle = trim($s['title'] ?? '');
    $html = (string)($s['content'] ?? '');
    $block = '<div class="kb-src-section" data-title="' . htmlspecialchars($secTitle, ENT_QUOTES, 'UTF-8') . '">';
    if ($secTitle !== '') {
        $block .= '<h2>' . htmlspecialchars($secTitle, ENT_QUOTES, 'UTF-8') . '</h2>';
    }
    $block .= $html;
    $block .= '</div>';
    $contentParts[] = $block;
}

$finalHtml = implode("\n\n", $contentParts);

// Generate unique slug
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = strtolower($text);
    $text = preg_replace('~[^-a-z0-9]+~', '', $text);
    if (empty($text)) { return 'article'; }
    return $text;
}

$baseSlug = slugify($title);
$requestedSlug = $baseSlug;
if (!empty($argv[2])) {
    // Optional third CLI arg to force a specific slug
    $requestedSlug = slugify($argv[2]);
}
$slug = $requestedSlug;
$forceSlug = !empty($argv[2]);

// Prefer updating an existing article with the same title (idempotent import)
$existingId = 0;
$existingByTitle = $pdo->prepare("SELECT id, slug FROM kb_articles WHERE title = ? LIMIT 1");
$existingByTitle->execute([$title]);
if (!$forceSlug) {
    if ($row = $existingByTitle->fetch(PDO::FETCH_ASSOC)) {
        $existingId = (int)$row['id'];
        $slug = $row['slug'];
    } else {
        // Ensure slug uniqueness only if we aren't updating by title
        $i = 2;
        while (true) {
            $check = $pdo->prepare("SELECT 1 FROM kb_articles WHERE slug = ? LIMIT 1");
            $check->execute([$slug]);
            if (!$check->fetchColumn()) break;
            $slug = $baseSlug . '-' . $i;
            $i++;
        }
    }
}

// Upsert by slug: update if exists, else insert
if ($existingId === 0) {
    $check = $pdo->prepare("SELECT id FROM kb_articles WHERE slug = ? LIMIT 1");
    $check->execute([$slug]);
    $existingId = (int)($check->fetchColumn() ?: 0);
}

if ($existingId > 0) {
    $upd = $pdo->prepare("UPDATE kb_articles SET title=?, content=?, category=?, tags=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
    $upd->execute([$title, $finalHtml, $category !== '' ? $category : null, json_encode($doc['tags'] ?? []), $status, $existingId]);
    echo "Updated KB Article ID: {$existingId}\n";
    echo "Title: {$title}\n";
    echo "Slug: {$slug}\n";
    echo "Status: {$status}\n";
    echo "Category: {$category}\n";
    echo "View (if published): kb-article.php?slug={$slug}\n";
} else {
    $stmt = $pdo->prepare("INSERT INTO kb_articles (title, slug, content, category, tags, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    $stmt->execute([
        $title,
        $slug,
        $finalHtml,
        $category !== '' ? $category : null,
        json_encode($doc['tags'] ?? []),
        $status
    ]);
    $newId = (int)$pdo->lastInsertId();
    echo "Imported KB Article ID: {$newId}\n";
    echo "Title: {$title}\n";
    echo "Slug: {$slug}\n";
    echo "Status: {$status}\n";
    echo "Category: {$category}\n";
    echo "View (if published): kb-article.php?slug={$slug}\n";
}


