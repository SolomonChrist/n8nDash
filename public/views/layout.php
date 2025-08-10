<?php
// Layout wrapper
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>n8nDash</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script>window.Chart || document.write('<script src="/assets/vendor/chart.fallback.js"><\/script>')</script>
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg bg-white border-bottom mb-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">n8nDash</a>
    <div class="d-flex gap-2">
      <a class="btn btn-sm btn-outline-primary" href="/dashboards">Dashboards</a>
      <a class="btn btn-sm btn-outline-secondary" href="/login">Login</a>
    </div>
  </div>
</nav>
<main class="container">
  <?php include __DIR__.'/' . $page . '.php'; ?>
</main>
<script src="/assets/js/app.js"></script>
<script src="/assets/js/jobs.js"></script>
<script src="/assets/js/widgets.js"></script>
</body>
</html>
