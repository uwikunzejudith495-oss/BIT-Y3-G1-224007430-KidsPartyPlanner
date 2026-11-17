<?php
$pageTitle = $pageTitle ?? 'Kids Party Planner';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | Kids Party Planner</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php">🎈 Kids Party Planner</a>
  <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">☰</button>
  <nav id="mainNav">
    <a href="index.php">Home</a>
    <?php if (!empty($_SESSION['user_id'])): ?>
      <a href="index.php?page=dashboard">Dashboard</a>
      <a href="index.php?page=parties">My Parties</a>
      <a href="index.php?page=vendors">Vendors</a>
      <a href="index.php?page=bookings">Bookings</a>
      <a href="index.php?page=logout">Logout</a>
    <?php else: ?>
      <a href="index.php?page=login">Login</a>
      <a class="nav-cta" href="index.php?page=register">Register</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
<?php show_flash(); ?>