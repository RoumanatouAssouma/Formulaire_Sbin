<?php
$pdo = new PDO(
    'mysql:host=localhost;dbname=formulaire_sbin;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
