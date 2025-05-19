<?php
require 'db.php';

if (empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    exit('ID manquant ou invalide');
}

$id = (int)$_GET['id'];

/* récupérer le chemin image pour le supprimer du disque */
$stmt = $pdo->prepare('SELECT media_path FROM formulaire_standard WHERE id = ?');
$stmt->execute([$id]);
$img = $stmt->fetchColumn();

/* suppression BDD */
$pdo->prepare('DELETE FROM formulaire_standard WHERE id = ?')->execute([$id]);

/* suppression fichier */
if ($img && file_exists(__DIR__ . '/' . ltrim($img, '/'))) {
    unlink(__DIR__ . '/' . ltrim($img, '/'));
}

/* retour à la liste avec message */
header('Location: list.php?deleted=1');
exit;
