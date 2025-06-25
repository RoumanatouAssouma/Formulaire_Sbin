<?php
session_start();

// Inclure les fichiers PHPMailer
// Si vous utilisez Composer:
require '../../vendor/autoload.php'; // Chemin relatif depuis 'auth/' vers le dossier 'vendor'

// Si vous avez téléchargé manuellement et placé dans 'PHPMailer' dans le même dossier 'auth/':
// require 'PHPMailer/PHPMailer.php';
// require 'PHPMailer/SMTP.php';
// require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = '';
$message_type = ''; // 'success' ou 'error'

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
        $email = $_POST['email'] ?? '';

        // 1. Vérifier si l'email existe dans la table utilisateurs
        $stmt = $conn->prepare("SELECT id, nom_utilisateur FROM utilisateurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Pour des raisons de sécurité, ne pas indiquer si l'email existe ou non.
            // Toujours afficher un message générique.
            $message = "Si votre adresse e-mail est valide, un lien de réinitialisation de mot de passe vous a été envoyé.";
            $message_type = 'success'; // Pour éviter d'indiquer qu'un email n'existe pas
        } else {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];

            // 2. Générer un jeton unique et sécurisé
            $token = bin2hex(random_bytes(32)); // 64 caractères hexadécimaux

            // 3. Stocker le jeton et son expiration dans la table password_resets
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Jeton valide 1 heure

            // Supprimer les anciens jetons pour cet email pour éviter l'accumulation.
            $stmt_delete_old = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt_delete_old->bind_param("s", $email);
            $stmt_delete_old->execute();
            $stmt_delete_old->close();

            $stmt_insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $email, $token, $expires_at);
            
            if ($stmt_insert->execute()) {
                // 4. Construire le lien de réinitialisation
                $reset_link = "http://localhost:3000/formulaire_interne/auth/reset_password.php?token=" . $token . "&email=" . urlencode($email);

                // *** DÉBUT DE L'INTÉGRATION PHPMailer ***
                $mail = new PHPMailer(true); // Passer 'true' active les exceptions

                try {
                    // Paramètres du serveur SMTP
                    // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Activer la sortie de débogage détaillée (désactivez en production)
                    $mail->isSMTP();                                            // Envoyer en utilisant SMTP
                    $mail->Host       = 'smtp.gmail.com';                       // Serveur SMTP de Gmail
                    $mail->SMTPAuth   = true;                                   // Activer l'authentification SMTP
                    $mail->Username   = 'assoumaroumanatou08@gmail.com';        // VOTRE ADRESSE GMAIL
                    $mail->Password   = 'cqfp gzwk kbez hpuf';         // VOTRE MOT DE PASSE D'APPLICATION GMAIL
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Activer le chiffrement TLS implicite (pour le port 587)
                    $mail->Port       = 587;                                    // Port TCP auquel se connecter (587 pour TLS, 465 pour SSL)

                    // Destinataires
                    $mail->setFrom('assoumaroumanatou08@gmail.com', 'Sentinelle Celtiis'); // Adresse d'expéditeur
                    $mail->addAddress($email, htmlspecialchars($user['nom_utilisateur'])); // Ajouter un destinataire

                    // Contenu de l'e-mail
                    $mail->isHTML(true);                                        // Définir le format de l'e-mail comme HTML
                    $mail->CharSet = 'UTF-8';                                   // Jeu de caractères
                    $mail->Subject = "Réinitialisation de votre mot de passe Sentinelle";
                    
                    $email_body = "Bonjour " . htmlspecialchars($user['nom_utilisateur']) . ",<br><br>";
                    $email_body .= "Vous avez demandé une réinitialisation de mot de passe pour votre compte Sentinelle.<br>";
                    $email_body .= "Cliquez sur le lien suivant pour réinitialiser votre mot de passe :<br><br>";
                    $email_body .= "<a href='" . htmlspecialchars($reset_link) . "'>" . htmlspecialchars($reset_link) . "</a><br><br>";
                    $email_body .= "Ce lien expirera dans 1 heure. Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet e-mail.<br><br>";
                    $email_body .= "L'équipe Sentinelle.";

                    $mail->Body    = $email_body;
                    $mail->AltBody = strip_tags($email_body); // Version texte brut pour les clients e-mail qui ne supportent pas le HTML

                    $mail->send();
                    $message = "Si votre adresse e-mail est valide, un lien de réinitialisation de mot de passe vous a été envoyé.";
                    $message_type = 'success';
                } catch (Exception $e) {
                    $message = "Une erreur est survenue lors de l'envoi de l'e-mail. Veuillez réessayer plus tard.";
                    $message_type = 'error';
                    // Log the detailed PHPMailer error for debugging
                    error_log("PHPMailer error: " . $e->getMessage());
                    // error_log("PHPMailer SMTP debug output: " . $mail->ErrorInfo); // Utile pour le débogage
                }
                // *** FIN DE L'INTÉGRATION PHPMailer ***

            } else {
                $message = "Erreur lors de la génération du lien de réinitialisation.";
                $message_type = 'error';
                error_log("Password reset token insert failed: " . $stmt_insert->error);
            }
            $stmt_insert->close();
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
    <title>Mot de passe oublié - Celtiis Bénin</title>
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
                <!-- Logo placeholder - Remplacez par votre vraie image -->
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full shadow-lg bg-gradient-to-br from-celtiis-blue to-celtiis-green">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <!-- Si vous avez une vraie image, remplacez la div ci-dessus par :
                <img src="chemin/vers/votre/logo.png" alt="Celtiis Bénin" class="w-20 h-20 mx-auto mb-6 rounded-full shadow-lg">
                -->
                
                <h2 class="mb-2 text-3xl font-bold text-gray-800">
                    Mot de passe oublié ?
                </h2>
                <p class="max-w-sm mx-auto text-sm leading-relaxed text-gray-600">
                    Pas de souci ! Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                </p>
            </div>

            <!-- Formulaire de réinitialisation -->
            <div class="p-8 bg-white border border-gray-100 shadow-2xl rounded-2xl">
                <?php if (!empty($message)): ?>
                    <div class="mb-6 p-4 <?php echo $message_type === 'success' ? 'bg-green-50 border-green-400' : 'bg-red-50 border-l-4 border-red-400'; ?> rounded-r-lg">
                        <div class="flex items-start">
                            <?php if ($message_type === 'success'): ?>
                                <svg class="w-5 h-5 text-green-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="mb-1 font-semibold text-green-800">E-mail envoyé avec succès !</h3>
                                    <p class="text-sm text-green-700"><?php echo htmlspecialchars($message); ?></p>
                                </div>
                            <?php else: ?>
                                <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div>
                                    <h3 class="mb-1 font-semibold text-red-800">Erreur</h3>
                                    <p class="text-sm text-red-700"><?php echo htmlspecialchars($message); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="forgot_password.php" method="post" class="space-y-6">
                    <!-- Adresse e-mail -->
                    <div>
                        <label for="email" class="block mb-2 text-sm font-semibold text-gray-700">
                            Adresse e-mail
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   class="w-full py-3 pl-10 pr-4 placeholder-gray-400 transition-all duration-300 border border-gray-300 rounded-lg focus:ring-2 focus:ring-celtiis-blue focus:border-transparent"
                                   placeholder="votre@email.com">
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Nous ne partagerons jamais votre adresse e-mail avec des tiers.
                        </p>
                    </div>

                    <!-- Bouton d'envoi -->
                    <div>
                        <button type="submit" 
                                class="relative flex justify-center w-full px-4 py-3 text-sm font-semibold text-white transition-all duration-300 transform border border-transparent rounded-lg shadow-lg group bg-gradient-to-r from-celtiis-blue to-celtiis-green hover:from-celtiis-light-blue hover:to-celtiis-light-green focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-celtiis-blue hover:-translate-y-1 hover:shadow-xl">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="w-5 h-5 text-white transition-colors duration-200 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </span>
                            Envoyer le lien de réinitialisation
                        </button>
                    </div>
                </form>

                <!-- Informations supplémentaires -->
                <div class="p-4 mt-6 rounded-lg bg-blue-50">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-blue-700">
                            <p class="mb-1 font-semibold">Que se passe-t-il ensuite ?</p>
                            <ul class="space-y-1 text-xs">
                                <li>• Vérifiez votre boîte de réception (et le dossier spam)</li>
                                <li>• Cliquez sur le lien dans l'e-mail reçu</li>
                                <li>• Le lien expire dans 1 heure pour votre sécurité</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="space-y-4 text-center">
                <a href="./login.php" 
                   class="inline-flex items-center px-4 py-2 bg-white text-celtiis-blue font-medium rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 border border-gray-200 hover:border-celtiis-blue">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Retour à la connexion
                </a>
                
                <div class="text-sm text-gray-600">
                    <p>
                        Besoin d'aide ? 
                        <a href="#" class="font-medium transition-colors duration-200 text-celtiis-green hover:text-celtiis-light-green">
                            Contactez le support
                        </a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center">
                <p class="text-xs text-gray-500">
                    © 2025 Celtiis Bénin - Plateforme Sentinelle. Tous droits réservés.
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

        // Animation du bouton au focus
        document.querySelector('input[type="email"]').addEventListener('focus', function() {
            this.parentElement.parentElement.classList.add('transform', 'scale-105');
            setTimeout(() => {
                this.parentElement.parentElement.classList.remove('scale-105');
            }, 200);
        });

        // Validation en temps réel
        document.querySelector('input[type="email"]').addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if (email && emailRegex.test(email)) {
                this.classList.remove('border-red-300');
                this.classList.add('border-green-300');
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else if (email) {
                this.classList.remove('border-green-300');
                this.classList.add('border-red-300');
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                this.classList.remove('border-red-300', 'border-green-300');
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });
    </script>
</body>
</html>