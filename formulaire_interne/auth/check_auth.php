<?php
// check_auth.php
// Ce fichier est inclus au début de chaque page nécessitant une authentification

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, le rediriger vers la page de connexion
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// L'utilisateur est connecté, récupérer son rôle si ce n'est pas déjà fait
// C'est une bonne pratique de stocker le rôle directement dans la session lors de la connexion
// pour éviter une requête DB à chaque chargement de page.

// Si le rôle n'est pas défini en session, le récupérer depuis la DB (moins performant, mais assure la cohérence)
if (!isset($_SESSION['role'])) {
    $servername = "localhost";
    $username_db = "root";
    $password_db = "";
    $dbname = "formulaire_sbin";

    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    if ($conn->connect_error) {
        // Gérer l'erreur de connexion à la base de données (peut-être loguer et afficher un message générique)
        error_log("Erreur de connexion à la base de données dans check_auth.php: " . $conn->connect_error);
        session_destroy(); // Détruire la session si la DB n'est pas accessible
        header("Location: login.php?error=db_error");
        exit();
    }

    $stmt = $conn->prepare("SELECT role FROM utilisateurs WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user_data = $result->fetch_assoc();
        $_SESSION['role'] = $user_data['role']; // Stocker le rôle dans la session
    } else {
        // Utilisateur non trouvé malgré l'ID de session, probablement un problème
        session_destroy(); // Invalider la session
        header("Location: login.php?error=user_not_found");
        exit();
    }
    $stmt->close();
    $conn->close();
}

// Maintenant, $_SESSION['role'] contient le rôle de l'utilisateur connecté ('utilisateur' ou 'administrateur')
// Vous pouvez l'utiliser dans toutes vos pages protégées pour des vérifications supplémentaires.

// Exemple d'utilisation dans d'autres pages :
// if ($_SESSION['role'] === 'administrateur') {
//     // Afficher des éléments spécifiques à l'admin
// }
?>