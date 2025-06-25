<?php
session_start();

// 1. Inclure le fichier de vérification d'authentification et de rôle
// Supposons que check_auth.php contient votre logique de protection
// et redirection si non connecté, et définit $_SESSION['role'].
require_once 'check_auth.php'; 
include '../composant/header.php';

// Redirection si l'utilisateur n'est PAS un administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    // Redirige vers le tableau de bord ou une page d'erreur d'accès non autorisé
    header("Location: dashboard.php?access_denied=true"); // Remplacez dashboard.php par votre page principale
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username_db = "root";
    $password_db = "";
    $dbname = "formulaire_sbin";

    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    if ($conn->connect_error) {
        $message = "Erreur de connexion à la base de données.";
        $message_type = 'error';
    } else {
        $nom_utilisateur = $_POST['nom_utilisateur'] ?? '';
        $email = $_POST['email'] ?? '';
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';
        $confirm_mot_de_passe = $_POST['confirm_mot_de_passe'] ?? '';
        $new_user_role = $_POST['new_user_role'] ?? 'utilisateur'; // Par défaut, 'utilisateur' si non spécifié

        // Validation simple des entrées
        if (empty($nom_utilisateur) || empty($email) || empty($mot_de_passe) || empty($confirm_mot_de_passe)) {
            $message = "Veuillez remplir tous les champs.";
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Adresse e-mail invalide.";
            $message_type = 'error';
        } elseif ($mot_de_passe !== $confirm_mot_de_passe) {
            $message = "Les mots de passe ne correspondent pas.";
            $message_type = 'error';
        } elseif (strlen($mot_de_passe) < 8) {
            $message = "Le mot de passe doit contenir au moins 8 caractères.";
            $message_type = 'error';
        } else {
            // Vérifier si l'email existe déjà
            $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message = "Cette adresse e-mail est déjà utilisée.";
                $message_type = 'error';
            } else {
                // Hasher le mot de passe
                $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);

                // Insérer le nouvel utilisateur
                $stmt_insert = $conn->prepare("INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("ssss", $nom_utilisateur, $email, $hashed_password, $new_user_role);

                if ($stmt_insert->execute()) {
                    $message = "Utilisateur '" . htmlspecialchars($nom_utilisateur) . "' inscrit avec le rôle : " . htmlspecialchars($new_user_role) . ".";
                    $message_type = 'success';
                    // Effacer les champs du formulaire après succès pour une nouvelle inscription
                    $nom_utilisateur = '';
                    $email = '';
                    $mot_de_passe = '';
                    $confirm_mot_de_passe = '';
                    $new_user_role = 'utilisateur'; // Réinitialiser à la valeur par défaut
                } else {
                    $message = "Erreur lors de l'inscription de l'utilisateur : " . $stmt_insert->error;
                    $message_type = 'error';
                }
                $stmt_insert->close();
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscrire un nouvel utilisateur - Celtiis Bénin</title>
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
                    }
                }
            }
        }
    </script>
    <style>
        .bg-pattern {
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
        }
    </style>
    
</head>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Utilisateur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .bg-pattern {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0);
            background-size: 20px 20px;
        }
        .from-celtiis-blue { --tw-gradient-from: #1e40af; }
        .to-celtiis-green { --tw-gradient-to: #059669; }
        .focus\:ring-celtiis-blue:focus { --tw-ring-color: #1e40af; }
        .text-celtiis-green { color: #059669; }
        .hover\:text-celtiis-light-green:hover { color: #10b981; }
        .hover\:from-celtiis-light-blue:hover { --tw-gradient-from: #3b82f6; }
        .hover\:to-celtiis-light-green:hover { --tw-gradient-to: #10b981; }
        .bg-gradient-to-r { background-image: linear-gradient(to right, var(--tw-gradient-from), var(--tw-gradient-to)); }
        .bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-from), var(--tw-gradient-to)); }
    </style>
</head>
<body class="min-h-screen mt-20 bg-gradient-to-br from-blue-50 via-white to-green-50 bg-pattern">
    <div class="flex items-center justify-center min-h-screen px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center">
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full shadow-lg bg-gradient-to-br from-celtiis-blue to-celtiis-green">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM12 14c-6.104 0-10 2.87-10 6v2h20v-2c0-3.13-3.896-6-10-6z"></path>
                    </svg>
                </div>
                <h2 class="mb-2 text-3xl font-bold text-gray-800">
                    Inscrire un nouvel utilisateur
                </h2>
                <p class="text-sm text-gray-600">
                    Seuls les administrateurs peuvent ajouter de nouveaux comptes.
                </p>
            </div>

            <div class="p-8 bg-white border border-gray-100 shadow-2xl rounded-2xl">

                <!-- Message d'alerte (remplacez par votre code PHP) -->
                <div id="message" class="hidden p-4 mb-6 border-l-4 border-green-400 rounded-r-lg bg-green-50">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-green-700">Message de succès</p>
                    </div>
                </div>

                <form action="register.php" method="POST" class="space-y-6">
                    <div>
                        <label for="nom_utilisateur" class="block mb-2 text-sm font-semibold text-gray-700">Nom d'utilisateur</label>
                        <input type="text" id="nom_utilisateur" name="nom_utilisateur" required class="w-full px-4 py-3 placeholder-gray-400 transition-all duration-300 border border-gray-300 rounded-lg focus:ring-2 focus:ring-celtiis-blue focus:border-transparent" placeholder="Nom complet ou nom d'utilisateur">
                    </div>
                    <div>
                        <label for="email" class="block mb-2 text-sm font-semibold text-gray-700">Adresse e-mail</label>
                        <input type="email" id="email" name="email" required class="w-full px-4 py-3 placeholder-gray-400 transition-all duration-300 border border-gray-300 rounded-lg focus:ring-2 focus:ring-celtiis-blue focus:border-transparent" placeholder="utilisateur@exemple.com">
                    </div>

                    <!-- Champ mot de passe avec icône toggle -->
                    <div>
                        <label for="mot_de_passe" class="block mb-2 text-sm font-semibold text-gray-700">Mot de passe</label>
                        <div class="relative">
                            <input type="password" id="mot_de_passe" name="mot_de_passe" required class="w-full px-4 py-3 pr-12 placeholder-gray-400 transition-all duration-300 border border-gray-300 rounded-lg focus:ring-2 focus:ring-celtiis-blue focus:border-transparent" placeholder="Minimum 8 caractères">
                            <button type="button" onclick="togglePassword('mot_de_passe')" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg id="eye-icon-mot_de_passe" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="eye-slash-icon-mot_de_passe" class="hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Champ confirmation mot de passe avec icône toggle -->
                    <div>
                        <label for="confirm_mot_de_passe" class="block mb-2 text-sm font-semibold text-gray-700">Confirmer le mot de passe</label>
                        <div class="relative">
                            <input type="password" id="confirm_mot_de_passe" name="confirm_mot_de_passe" required class="w-full px-4 py-3 pr-12 placeholder-gray-400 transition-all duration-300 border border-gray-300 rounded-lg focus:ring-2 focus:ring-celtiis-blue focus:border-transparent" placeholder="Confirmez le mot de passe">
                            <button type="button" onclick="togglePassword('confirm_mot_de_passe')" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg id="eye-icon-confirm_mot_de_passe" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="eye-slash-icon-confirm_mot_de_passe" class="hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                           </button>
                        </div>
                    </div>
                    <div>
                        <label for="new_user_role" class="block mb-2 text-sm font-semibold text-gray-700">Rôle de l'utilisateur</label>
                        <select id="new_user_role" name="new_user_role" class="w-full px-4 py-3 transition-all duration-300 border border-gray-300 rounded-lg focus:ring-2 focus:ring-celtiis-blue focus:border-transparent">
                            <option value="utilisateur">Utilisateur</option>
                            <option value="administrateur">Administrateur</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="relative flex justify-center w-full px-4 py-3 text-sm font-semibold text-white transition-all duration-300 transform border border-transparent rounded-lg shadow-lg group bg-gradient-to-r from-celtiis-blue to-celtiis-green hover:from-celtiis-light-blue hover:to-celtiis-light-green focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-celtiis-blue hover:-translate-y-1 hover:shadow-xl">
                            Inscrire l'utilisateur
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Retour au
                        <a href="index.php" class="font-medium transition-colors duration-200 text-celtiis-green hover:text-celtiis-light-green">
                            Formulaire
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

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const eyeIcon = document.getElementById('eye-icon-' + inputId);
            const eyeSlashIcon = document.getElementById('eye-slash-icon-' + inputId);
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }
    </script>

</body>
</html>
</html>