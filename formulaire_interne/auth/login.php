<?php
session_start(); // Démarre la session PHP

$message = ''; // Pour afficher des messages d'erreur ou de succès

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username_db = "root"; // Nom d'utilisateur de la base de données
    $password_db = "";     // Mot de passe de la base de données
    $dbname = "formulaire_sbin";

    // Créer la connexion
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    // Vérifier la connexion
    if ($conn->connect_error) {
        $message = "Erreur de connexion à la base de données.";
    } else {
        $nom_utilisateur = $_POST['nom_utilisateur'] ?? '';
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';

        // Préparer la requête pour éviter les injections SQL
        $sql = "SELECT id, nom_utilisateur, mot_de_passe FROM utilisateurs WHERE nom_utilisateur = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nom_utilisateur);
        $stmt->execute();
        $result = $stmt->get_result();

        // ... votre code de connexion existant ...

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                // Authentification réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['nom_utilisateur'];
                $_SESSION['role'] = $user['role']; // <<< AJOUTEZ CETTE LIGNE pour stocker le rôle
                
                // Rediriger l'utilisateur
                if (isset($_SESSION['redirect_to'])) {
                    $redirect_page = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']);
                    header("Location: " . $redirect_page);
                } else {
                    header("Location: index.php"); // Rediriger vers votre tableau de bord par défaut
                }
                exit();
            } else {
                $message = "Mot de passe incorrect.";
                $message_type = 'error';
            }
        } else {
            $message = "Nom d'utilisateur ou e-mail introuvable.";
            $message_type = 'error';
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Celtiis Bénin</title>
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
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 bg-pattern">
    <div class="flex items-center justify-center min-h-screen px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <!-- En-tête avec logo -->
            <div class="text-center">
                <!-- Logo placeholder - Remplacez par votre vraie image -->
                <div class="flex items-center justify-center w-40 h-16 p-2 mx-auto mb-8 border-4 rounded-full shadow-lg bg-gradient-to-br from-blue-200 to-green-200">
                    <img src="../../src/images/SBIN-Logo.png" alt="" class="rounded-l-xl">
                </div>
                
                <h2 class="mb-2 text-3xl font-bold text-gray-800">
                    Connexion
                </h2>
                <p class="text-sm text-gray-600">
                     SBIN/Celtiis Bénin - Accédez à votre fiche d'alerte
                </p>
            </div>

            <!-- Formulaire de connexion -->
            <div class="p-8 bg-white border border-gray-100 shadow-2xl rounded-2xl">
                <?php if (!empty($message)): ?>
                    <div class="p-4 mb-6 border-l-4 border-red-400 rounded-r-lg bg-red-50">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <p class="text-sm text-red-700"><?php echo htmlspecialchars($message); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="space-y-6">
                    <!-- Nom d'utilisateur -->
                    <div>
                        <label for="nom_utilisateur" class="block mb-2 text-sm font-semibold text-gray-700">
                            Nom d'utilisateur
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   id="nom_utilisateur" 
                                   name="nom_utilisateur" 
                                   required
                                   class="w-full py-3 pl-10 pr-4 placeholder-gray-400 transition-all duration-300 border border-gray-300 rounded-lg focus:ring-2 focus:ring-celtiis-blue focus:border-transparent"
                                   placeholder="Entrez votre nom d'utilisateur">
                        </div>
                    </div>

                    <!-- Mot de passe -->
                    <div>
                        <label for="mot_de_passe" class="block mb-2 text-sm font-semibold text-gray-700">
                            Mot de passe
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input type="password"
                                id="mot_de_passe"
                                name="mot_de_passe"
                                required
                                class="w-full py-3 pl-10 pr-12 placeholder-gray-400 transition-all duration-300 border border-gray-300 rounded-lg focus:ring-2 focus:ring-celtiis-blue focus:border-transparent"
                                placeholder="Entrez votre mot de passe">
                            
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 transition-colors duration-200 hover:text-celtiis-blue focus:outline-none">
                                <!-- Icône œil fermé (mot de passe masqué) -->
                                <svg id="eyeSlashIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.243 5.485m-1.757-3.364a3 3 0 001.364-2.476M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                                </svg>
                                <!-- Icône œil ouvert (mot de passe visible) -->
                                <svg id="eyeIcon" class="hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Options supplémentaires -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" 
                                   name="remember-me" 
                                   type="checkbox" 
                                   class="w-4 h-4 border-gray-300 rounded text-celtiis-blue focus:ring-celtiis-blue">
                            <label for="remember-me" class="block ml-2 text-sm text-gray-700">
                                Se souvenir de moi
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="./auth/forgot_password.php" class="font-medium transition-colors duration-200 text-celtiis-green hover:text-celtiis-light-green">
                                Mot de passe oublié ?
                            </a>
                        </div>
                    </div>

                    <!-- Bouton de connexion -->
                    <div>
                        <button type="submit" 
                                class="relative flex justify-center w-full px-4 py-3 text-sm font-semibold text-white transition-all duration-300 transform border border-transparent rounded-lg shadow-lg group bg-gradient-to-r from-celtiis-blue to-celtiis-green hover:from-celtiis-light-blue hover:to-celtiis-light-green focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-celtiis-blue hover:-translate-y-1 hover:shadow-xl">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="w-5 h-5 text-white transition-colors duration-200 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </span>
                            Se connecter
                        </button>
                    </div>
                </form>

                <!-- Liens supplémentaires -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Nouveau sur la plateforme ? 
                        <u class="font-medium transition-colors duration-200 text-celtiis-green hover:text-celtiis-light-green">
                            Contactez l'administrateur
                        </u>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center">
                <p class="text-xs text-gray-500">
                    © 2025 SBIN/Celtiis Bénin. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.max-w-md > *');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    el.style.transition = 'all 0.6s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });

        // Fonctionnalité de basculement du mot de passe
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('mot_de_passe');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeSlashIcon = document.getElementById('eyeSlashIcon');

            if (togglePassword && passwordInput && eyeIcon && eyeSlashIcon) {
                togglePassword.addEventListener('click', function(e) {
                    e.preventDefault(); // Empêche la soumission du formulaire
                    
                    // Basculer le type de l'input entre 'password' et 'text'
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    // Basculer les icônes
                    eyeIcon.classList.toggle('hidden');
                    eyeSlashIcon.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>