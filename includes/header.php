<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>JJS Team Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<header class="bg-white border-b">
  <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
    <a href="/" class="font-semibold">J. Joseph Salon — Team</a>
    <nav class="text-sm flex items-center gap-4">
      <a href="/" class="hover:underline">Home</a>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="/dashboard.php" class="hover:underline">Dashboard</a>
        <a href="/logout.php" class="hover:underline">Logout</a>
      <?php else: ?>
        <a href="/login.php" class="hover:underline">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="max-w-7xl mx-auto px-4 py-8">