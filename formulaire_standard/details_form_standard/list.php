<?php
require 'db.php';

// 1. Récupération
$stmt = $pdo->query(
    'SELECT id, main_subject, information, created_at
     FROM formulaire_standard
     ORDER BY created_at DESC'
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$notifCount = count($rows);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <title>Liste des alertes</title>

    <!-- Tailwind CSS CDN (remplacer par build local en prod) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen px-4 py-10 bg-gray-50 md:px-10">

    <!-- Message succès -->
    <?php if (!empty($_GET['deleted'])): ?>
        <div class="px-6 py-4 mb-6 rounded-lg shadow bg-emerald-100 text-emerald-800">
            Enregistrement supprimé.
        </div>
    <?php endif ?>

    <!-- Titre + compteur -->
    <div class="flex justify-between px-6 pt-10 border-4 border-double rounded-t-full bg-amber-50 border-lime-500">
        <img src="/src/images/logo-celtiis_Bleu.png" alt="" class="w-32 h-24 ml-6">
        <h1 class="flex items-center gap-3 p-4 pt-8 mt-6 text-4xl font-bold text-black ">
            Alertes
            <span class="inline-flex h-7 min-w-[2rem] items-center justify-center rounded-full bg-red-600
                 px-2 text-sm font-medium text-white">
                <?= $notifCount ?>
            </span>
        </h1>
        <img src="/src/images/SBIN-Logo.png" alt="" class="h-20 pr-3 mr-8 w-36">
    </div>

    <!-- Tableau -->
    <div class="mt-6 overflow-x-auto bg-white shadow-xl rounded-2xl">
        <table class="min-w-full text-sm divide-y divide-gray-200">
            <thead class="tracking-wider text-white uppercase bg-blue-800 ">
                <tr>
                    <th class="px-4 py-6 text-left">#</th>
                    <th class="px-4 py-6 text-left">Sujet principal</th>
                    <th class="px-4 py-6 text-left">Information</th>
                    <th class="px-4 py-6 text-left">Date</th>
                    <th class="px-4 py-6 text-center">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                <?php foreach ($rows as $r): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-700"><?= $r['id'] ?></td>

                        <td class="px-4 py-6">
                            <a href="detail.php?id=<?= $r['id'] ?>" class="text-indigo-600 hover:underline">
                                <?= htmlspecialchars($r['main_subject']) ?>
                            </a>
                        </td>

                        <td class="px-4 py-6 text-gray-600">
                            <?= htmlspecialchars(substr($r['information'], 0, 60)) ?>…
                        </td>

                        <td class="px-4 py-6 text-gray-500">
                            <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                        </td>

                        <td class="px-4 py-6 text-center">
                            <a href="delete.php?id=<?= $r['id'] ?>" class="inline-flex items-center justify-center gap-1 rounded-md border border-red-500
                        bg-white px-3 py-1.5 text-sm font-medium text-red-600
                        hover:bg-red-50 transition" onclick="return confirm('Supprimer cet enregistrement ?');">

                                <!-- Icône poubelle -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
                                </svg>
                                Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

</body>

</html>