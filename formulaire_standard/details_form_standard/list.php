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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN (remplacer par build local en prod) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen px-4 py-10 bg-gray-50 md:px-10">

    <!-- Message succès -->
    <?php if (!empty($_GET['deleted'])): ?>
        <div class="px-6 py-4 mb-6 border-l-4 rounded-lg shadow-md bg-emerald-50 border-emerald-500 text-emerald-700">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Enregistrement supprimé avec succès.
            </div>
        </div>
    <?php endif ?>

    <!-- Titre + compteur -->
    <div class="flex justify-between px-6 pt-8 bg-white border-2 rounded-t-lg shadow-md border-lime-500">
        <img src="/src/images/logo-celtiis_Bleu.png" alt="Logo Celtiis" class="object-contain w-32 h-24 ml-6">
        <h1 class="flex items-center gap-3 p-4 pt-6 text-4xl font-semibold text-gray-800">
            Alertes
            <span class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-full bg-red-600
                 px-3 text-sm font-medium text-white shadow-sm">
                <?= $notifCount ?>
            </span>
        </h1>
        <img src="/src/images/SBIN-Logo.png" alt="Logo SBIN" class="object-contain h-20 pr-3 mr-8 w-36">
    </div>

    <!-- Tableau -->
    <div class="mt-1 overflow-x-auto bg-white border-b-2 rounded-b-lg shadow-lg border-lime-500 border-x-2">
        <table class="min-w-full text-sm divide-y divide-gray-200">
            <thead class="tracking-wider text-white uppercase bg-gradient-to-r from-blue-300 to-blue-500">
                <tr>
                    <th class="px-6 py-4 font-medium text-left">#</th>
                    <th class="px-6 py-4 font-medium text-left">Sujet principal</th>
                    <th class="px-6 py-4 font-medium text-left">Information</th>
                    <th class="px-6 py-4 font-medium text-left">Date</th>
                    <th class="px-6 py-4 font-medium text-center">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                <?php foreach ($rows as $r): ?>
                    <tr class="transition-colors duration-150 hover:bg-blue-50">
                        <td class="px-6 py-4 font-medium text-gray-700"><?= $r['id'] ?></td>

                        <td class="px-6 py-4 text-lg font-medium">
                            <a href="details.php?id=<?= $r['id'] ?>" class="text-blue-600 hover:text-blue-800 hover:underline">
                                <?= htmlspecialchars($r['main_subject']) ?>
                            </a>
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            <?= htmlspecialchars(substr($r['information'], 0, 60)) ?>…
                        </td>

                        <td class="px-6 py-4 text-gray-500">
                            <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <a href="delete.php?id=<?= $r['id'] ?>" class="inline-flex items-center justify-center gap-1 rounded-md border border-red-500
                                bg-white px-3 py-1.5 text-sm font-medium text-red-600
                                hover:bg-red-50 hover:shadow-sm transition" 
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement ?');">

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
                
                <?php if (count($rows) === 0): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            Aucune alerte à afficher pour le moment.
                        </td>
                    </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>

</body>

</html>