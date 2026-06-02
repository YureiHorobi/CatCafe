<?php
$pageTitle = $pageTitle ?? 'Cat Cafe';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> - Cat Cafe</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="app-shell">
