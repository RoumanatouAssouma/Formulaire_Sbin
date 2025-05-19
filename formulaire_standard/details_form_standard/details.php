<?php
require 'db.php';

if (empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    exit('Paramètre ID manquant ou invalide.');
}

$stmt = $pdo->prepare('SELECT * FROM formulaire_standard WHERE id = ?');
$stmt->execute([$_GET['id']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    exit('Enregistrement introuvable.');
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <title>Détail #<?= $row['id'] ?></title>

    <!-- Tailwind CSS CDN (vite à remplacer par build local en prod) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="max-w-screen-lg min-h-screen py-6 mx-10 bg-gray-50 md:px-8">

    <!-- Bouton retour -->
    <div class="items-center bg-blue-800 border rounded-tl-lg rounded-tr-lg">
        <a href="/formulaire_standard/details_form_standard/list.php"
        class="inline-flex gap-2 px-4 py-2 mx-4 my-6 text-sm font-medium text-white transition rounded-lg bg-lime-500 hover:bg-gray-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Retour à la liste
    </a>
    </div>


    <!-- Carte détail -->
    <div class="p-8 overflow-hidden bg-white shadow-xl rounded-2xl">
        <li class="flex justify-between">
            <img src="/src/images/logo-celtiis_Bleu.png" alt="" class="w-32">
            <img src="/src/images/SBIN-Logo.png" alt="" class="w-32 h-16 mr-6">
        </li>

        <!-- Titre -->
        <h2 class="mt-6 mb-8 text-4xl font-bold text-center text-gray-800">
            Enregistrement <?= $row['id'] ?>
        </h2>

        <ul class="divide-y divide-gray-200">
            <li class="flex flex-col p-5 sm:flex-row sm:gap-4">
                <span class="w-40 font-semibold shrink-0">Sujet principal :</span>
                <span><?= htmlspecialchars($row['main_subject']) ?></span>
            </li>

            <li class="flex flex-col p-5 sm:flex-row sm:gap-4">
                <span class="w-40 font-semibold shrink-0">Information :</span>
                <span><?= nl2br(htmlspecialchars($row['information'])) ?></span>
            </li>

            <li class="flex flex-col p-5 sm:flex-row sm:gap-4">
                <span class="w-40 font-semibold shrink-0">Contenu :</span>
                <span><?= nl2br(htmlspecialchars($row['content'])) ?></span>
            </li>

            <li class="flex flex-col p-5 sm:flex-row sm:gap-4">
                <span class="w-40 font-semibold shrink-0">Source :</span>
                <span><?= htmlspecialchars($row['source'] ?? 'N/A') ?></span>
            </li>

            <li class="flex flex-col p-5 sm:flex-row sm:gap-4">
                <span class="w-40 font-semibold shrink-0">Date source :</span>
                <span><?= htmlspecialchars($row['source_date'] ?? 'N/A') ?></span>
            </li>

            <li class="flex flex-col p-5 sm:flex-row sm:gap-4">
                <span class="w-40 font-semibold shrink-0">Lien :</span>
                <span>
                    <?php if (!empty($row['lien'])): ?>
                        <a href="<?= htmlspecialchars($row['lien']) ?>" target="_blank"
                            class="text-blue-600 hover:underline">
                            <?= htmlspecialchars($row['lien']) ?>
                        </a>
                    <?php else: ?>
                        N/A
                    <?php endif ?>
                </span>
            </li>

            <li class="flex flex-col p-5 sm:flex-row sm:gap-4">
                <span class="w-40 font-semibold shrink-0">Auteur :</span>
                <span><?= htmlspecialchars($row['author'] ?? 'N/A') ?></span>
            </li>

            <li class="flex flex-col p-5 sm:flex-row sm:gap-4">
                <span class="w-40 font-semibold shrink-0">Nom / Alias :</span>
                <span><?= htmlspecialchars($row['name_or_alias'] ?? 'N/A') ?></span>
            </li>

            <li class="flex flex-col p-5 sm:flex-row sm:gap-4">
                <span class="w-40 font-semibold shrink-0">Contact :</span>
                <span><?= htmlspecialchars($row['contact_info'] ?? 'N/A') ?></span>
            </li>

            <li class="flex flex-col gap-4 p-5">
                <span class="font-semibold">Fichier média :</span>

                <?php if ($row['media_path']): ?>
                    <img src="../<?= htmlspecialchars($row['media_path']) ?>" alt="media"
                        class="rounded-lg shadow w-80 max-h-80" />
                <?php else: ?>
                    <span class="text-gray-500">Aucun fichier.</span>
                <?php endif ?>
            </li>

            <li class="flex flex-col p-5 sm:flex-row sm:gap-4">
                <span class="w-40 font-semibold shrink-0">Créé le :</span>
                <span><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></span>
            </li>
        </ul>
    </div>

</body>

</html>