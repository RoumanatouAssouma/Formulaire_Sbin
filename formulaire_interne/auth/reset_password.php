<?php
session_start();

$message = '';
$message_type = '';
$valid_token = false; // Indique si le jeton est valide ET non expiré
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "formulaire_sbin";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    $message = "Erreur de connexion à la base de données.";
    $message_type = 'error';
} else {
    // 1. Vérifier la validité du jeton depuis l'URL
    // Cette vérification est faite que la page soit chargée pour la première fois
    // ou qu'un formulaire de mot de passe soit soumis.
    $stmt = $conn->prepare("SELECT id, expires_at FROM password_resets WHERE email = ? AND token = ?");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $reset_entry = $result->fetch_assoc();
        if (strtotime($reset_entry['expires_at']) > time()) {
            $valid_token = true; // Le jeton est valide et non expiré
        } else {
            $message = "Le lien de réinitialisation a expiré. Veuillez refaire une demande.";
            $message_type = 'error';
            // Important: Si le jeton est expiré, on peut le considérer comme "utilisé" ou invalide
            // et ne plus afficher le formulaire.
            $valid_token = false; 
        }
    } else {
        $message = "Lien de réinitialisation invalide ou déjà utilisé.";
        $message_type = 'error';
        $valid_token = false; // Si le jeton n'existe pas, il n'est pas valide
    }
    $stmt->close();

    // 2. Traiter la soumission du nouveau mot de passe UNIQUEMENT si le jeton est valide
    if ($valid_token && $_SERVER["REQUEST_METHOD"] == "POST") {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation côté serveur du nouveau mot de passe
        if (empty($new_password) || empty($confirm_password)) {
            $message = "Veuillez remplir tous les champs.";
            $message_type = 'error';
            // Le jeton reste valide car c'est une erreur de formulaire, pas d'authentification du jeton.
        } elseif ($new_password !== $confirm_password) {
            $message = "Les mots de passe ne correspondent pas.";
            $message_type = 'error';
        } elseif (strlen($new_password) < 8) { // Exemple de règle de mot de passe
            $message = "Le mot de passe doit contenir au moins 8 caractères.";
            $message_type = 'error';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Mettre à jour le mot de passe de l'utilisateur
            $stmt_update = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
            $stmt_update->bind_param("ss", $hashed_password, $email);

            if ($stmt_update->execute()) {
                // Supprimer le jeton de réinitialisation après utilisation (sécurité critique)
                $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE email = ? AND token = ?");
                $stmt_delete->bind_param("ss", $email, $token);
                $stmt_delete->execute();
                $stmt_delete->close();

                $message = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
                $message_type = 'success';
                $valid_token = false; // Pour ne plus afficher le formulaire après une réinitialisation réussie
            } else {
                $message = "Erreur lors de la mise à jour du mot de passe : " . $stmt_update->error;
                $message_type = 'error';
            }
            $stmt_update->close();
        }
    }
    
    // La connexion est fermée UNIQUEMENT après toutes les opérations de base de données
    $conn->close(); 
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - Celtiis Bénin</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full shadow-lg bg-gradient-to-br from-celtiis-blue to-celtiis-green">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                
                <h2 class="mb-2 text-3xl font-bold text-gray-800">
                    Réinitialiser le mot de passe
                </h2>
                <p class="text-sm text-gray-600">
                    SBIN/Celtiis Bénin - Définissez votre nouveau mot de passe
                </p>
            </div>

            <!-- Conteneur principal -->
            <div class="p-8 bg-white border border-gray-100 shadow-2xl rounded-2xl">
                <!-- Messages -->
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
                            <p class="text-sm <?php echo $message_type === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                                <?php echo htmlspecialchars($message); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Formulaire de réinitialisation -->
                <?php if ($valid_token): ?>
                    <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>&email=<?php echo urlencode($email); ?>" method="post" class="space-y-6">
                        <!-- Nouveau mot de passe -->
                        <div>
                            <label for="new_password" class="block mb-2 text-sm font-semibold text-gray-700">
                                Nouveau mot de passe
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input type="password"
                                    id="new_password"
                                    name="new_password"
                                    required
                                    minlength="8"
                                    class="w-full py-3 pl-10 pr-12 placeholder-gray-400 transition-all duration-300 border border-gray-300 rounded-lg focus:ring-2 focus:ring-celtiis-blue focus:border-transparent"
                                    placeholder="Entrez votre nouveau mot de passe">
                                
                                <button type="button" id="toggleNewPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 transition-colors duration-200 hover:text-celtiis-blue focus:outline-none">
                                    <svg id="eyeSlashNewIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.243 5.485m-1.757-3.364a3 3 0 001.364-2.476M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                                    </svg>
                                    <svg id="eyeNewIcon" class="hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Le mot de passe doit contenir au moins 8 caractères</p>
                        </div>

                        <!-- Confirmer le mot de passe -->
                        <div>
                            <label for="confirm_password" class="block mb-2 text-sm font-semibold text-gray-700">
                                Confirmer le mot de passe
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <input type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    required
                                    minlength="8"
                                    class="w-full py-3 pl-10 pr-12 placeholder-gray-400 transition-all duration-300 border border-gray-300 rounded-lg focus:ring-2 focus:ring-celtiis-blue focus:border-transparent"
                                    placeholder="Confirmez votre nouveau mot de passe">
                                
                                <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 transition-colors duration-200 hover:text-celtiis-blue focus:outline-none">
                                    <svg id="eyeSlashConfirmIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.243 5.485m-1.757-3.364a3 3 0 001.364-2.476M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                                    </svg>
                                    <svg id="eyeConfirmIcon" class="hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Bouton de soumission -->
                        <div>
                            <button type="submit" 
                                    class="relative flex justify-center w-full px-4 py-3 text-sm font-semibold text-white transition-all duration-300 transform border border-transparent rounded-lg shadow-lg group bg-gradient-to-r from-celtiis-blue to-celtiis-green hover:from-celtiis-light-blue hover:to-celtiis-light-green focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-celtiis-blue hover:-translate-y-1 hover:shadow-xl">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="w-5 h-5 text-white transition-colors duration-200 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                    </svg>
                                </span>
                                Réinitialiser le mot de passe
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

                <!-- Lien de retour -->
                <div class="mt-6 text-center">
                    <a href="../login.php" class="inline-flex items-center text-sm font-medium transition-colors duration-200 text-celtiis-green hover:text-celtiis-light-green">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour à la page de connexion
                    </a>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Animation d'entrée
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

            // Basculement pour le nouveau mot de passe
            const toggleNewPassword = document.getElementById('toggleNewPassword');
            const newPasswordInput = document.getElementById('new_password');
            const eyeNewIcon = document.getElementById('eyeNewIcon');
            const eyeSlashNewIcon = document.getElementById('eyeSlashNewIcon');

            if (toggleNewPassword && newPasswordInput && eyeNewIcon && eyeSlashNewIcon) {
                toggleNewPassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const type = newPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    newPasswordInput.setAttribute('type', type);

                    eyeNewIcon.classList.toggle('hidden');
                    eyeSlashNewIcon.classList.toggle('hidden');
                });
            }

            // Basculement pour la confirmation du mot de passe
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const eyeConfirmIcon = document.getElementById('eyeConfirmIcon');
            const eyeSlashConfirmIcon = document.getElementById('eyeSlashConfirmIcon');

            if (toggleConfirmPassword && confirmPasswordInput && eyeConfirmIcon && eyeSlashConfirmIcon) {
                toggleConfirmPassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPasswordInput.setAttribute('type', type);

                    eyeConfirmIcon.classList.toggle('hidden');
                    eyeSlashConfirmIcon.classList.toggle('hidden');
                });
            }

            // Validation en temps réel
            const form = document.querySelector('form');
            if (form) {
                confirmPasswordInput.addEventListener('input', function() {
                    if (newPasswordInput.value !== confirmPasswordInput.value) {
                        confirmPasswordInput.setCustomValidity('Les mots de passe ne correspondent pas');
                    } else {
                        confirmPasswordInput.setCustomValidity('');
                    }
                });

                newPasswordInput.addEventListener('input', function() {
                    if (confirmPasswordInput.value && newPasswordInput.value !== confirmPasswordInput.value) {
                        confirmPasswordInput.setCustomValidity('Les mots de passe ne correspondent pas');
                    } else {
                        confirmPasswordInput.setCustomValidity('');
                    }
                });
            }
        });
    </script>
</body>
</html>