<?php
session_start();
require_once 'check_auth.php'; 

// Redirection si l'utilisateur n'est PAS un administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: dashboard.php?access_denied=true");
    exit();
}

$message = '';
$message_type = '';

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "formulaire_sbin";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    $message = "Erreur de connexion à la base de données.";
    $message_type = 'error';
} else {
    // Gérer la suppression d'un utilisateur
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user_id'])) {
        $user_id_to_delete = intval($_POST['delete_user_id']);

        // Empêcher un administrateur de supprimer son propre compte (optionnel, mais recommandé)
        if ($user_id_to_delete === $_SESSION['user_id']) {
            $message = "Vous ne pouvez pas supprimer votre propre compte.";
            $message_type = 'error';
        } else {
            // Optionnel: Vérifier que l'utilisateur à supprimer n'est pas le seul admin
            // (nécessiterait une requête supplémentaire COUNT(*) WHERE role = 'administrateur')

            $stmt_delete = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $stmt_delete->bind_param("i", $user_id_to_delete);
            if ($stmt_delete->execute()) {
                if ($stmt_delete->affected_rows > 0) {
                    $message = "Utilisateur supprimé avec succès.";
                    $message_type = 'success';
                } else {
                    $message = "Aucun utilisateur trouvé avec cet ID ou suppression impossible.";
                    $message_type = 'error';
                }
            } else {
                $message = "Erreur lors de la suppression de l'utilisateur : " . $stmt_delete->error;
                $message_type = 'error';
            }
            $stmt_delete->close();
        }
    }

    // Récupérer tous les utilisateurs pour l'affichage
    $users = [];
    $stmt_select_users = $conn->prepare("SELECT id, nom_utilisateur, email, role FROM utilisateurs ORDER BY nom_utilisateur ASC");
    $stmt_select_users->execute();
    $result_users = $stmt_select_users->get_result();
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt_select_users->close();
    $conn->close();
    
}
include '../composant/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les utilisateurs - Celtiis Bénin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'celtiis-blue': '#1e40af',
                        'celtiis-green': '#059669',
                        'celtiis-light-blue': '#3b82f6',
                        'celtiis-light-green': '#10b981'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'pulse-slow': 'pulse 3s infinite'
                    }
                }
            }
        }
    </script>
    
</head>
<body class="min-h-screen mt-20 bg-gradient-to-br from-blue-300 via-white to-green-400">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute rounded-full -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/10 to-emerald-400/10 blur-3xl"></div>
        <div class="absolute rounded-full -bottom-40 -left-40 w-80 h-80 bg-gradient-to-tr from-emerald-400/10 to-blue-400/10 blur-3xl"></div>
        <div class="absolute transform -translate-x-1/2 -translate-y-1/2 rounded-full top-1/2 left-1/2 w-96 h-96 bg-gradient-to-r from-blue-400/5 to-emerald-400/5 blur-3xl"></div>
    </div>
    <div class="flex items-center justify-center min-h-screen px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-2xl space-y-8 lg:max-w-6xl">
            <div class="text-center">
                <h2 class="mb-2 text-3xl font-bold text-gray-800">
                    Gestion des utilisateurs
                </h2>
                <p class="px-6 text-sm text-gray-600 border lg:p-1">
                    Ajouter, modifier ou supprimer des comptes utilisateurs.
                </p>
            </div>

            <div class="p-8 bg-white border-2 border-gray-400 shadow-2xl rounded-2xl">
                <?php if (!empty($message)): ?>
                    <div class="p-4 mb-6 border-l-4 rounded-r-lg <?php echo $message_type === 'success' ? 'border-green-400 bg-green-50' : 'border-red-400 bg-red-50'; ?>">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 <?php echo $message_type === 'success' ? 'text-green-400' : 'text-red-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?php if ($message_type === 'success'): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                <?php else: ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                <?php endif; ?>
                            </svg>
                            <p class="text-sm <?php echo $message_type === 'success' ? 'text-green-700' : 'text-red-700'; ?>"><?php echo htmlspecialchars($message); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-12 border-b border-gray-100 lg:mx-9">
                    <div class="flex justify-between pb-2">
                        <h3 class="text-lg font-semibold text-blue-900 lg:text-3xl">Liste des utilisateurs</h3>
                        <a href="register.php" class="inline-flex items-center justify-center lg:px-6 lg:py-3 text-sm font-semibold text-white bg-gradient-to-r from-celtiis-blue to-celtiis-light-blue rounded-xl hover:from-celtiis-light-blue hover:to-celtiis-blue transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Nouvel utilisateur
                        </a>
                    </div>
                    <p class="text-sm text-gray-600 lg:text-lg">Gérez tous vos utilisateurs depuis cette interface</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                        <thead>
                            <tr class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
                                <th class="px-6 py-3 border-b">ID</th>
                                <th class="px-6 py-3 border-b">Nom d'utilisateur</th>
                                <th class="px-6 py-3 border-b">Email</th>
                                <th class="px-6 py-3 border-b">Rôle</th>
                                <th class="px-6 py-3 text-center border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 text-sm text-gray-700 border-b"><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td class="px-6 py-3 text-sm text-gray-700 border-b"><?php echo htmlspecialchars($user['nom_utilisateur']); ?></td>
                                        <td class="px-6 py-3 text-sm text-gray-700 border-b"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="px-6 py-3 text-sm text-gray-700 capitalize border-b"><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td class="px-6 py-3 text-center border-b">
                                            <form action="manage_users.php" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="px-3 py-1 text-xs font-semibold text-white transition-colors duration-200 bg-red-500 rounded-md hover:bg-red-600">
                                                    Supprimer
                                                </button>
                                            </form>
                                            </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucun utilisateur trouvé.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Retour au 
                        <a href="login.php" class="font-medium transition-colors duration-200 text-celtiis-green hover:text-celtiis-light-green">
                            formulaire
                        </a>
                    </p>
                </div>
            </div>

            <div class="text-center">
                <p class="text-xs text-gray-500">
                    © 2025 SBIN/Celtiis Bénin. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>
</body>
</html>