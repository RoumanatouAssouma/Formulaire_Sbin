<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log incoming requests for debugging
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Request received\n", FILE_APPEND);
file_put_contents('debug_log.txt', "Raw input: " . file_get_contents('php://input') . "\n", FILE_APPEND);

// Vérifier si la requête est de type POST et que le corps de la requête est bien en JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données JSON envoyées
    $input = file_get_contents('php://input');
    file_put_contents('debug_log.txt', "Input received: " . $input . "\n", FILE_APPEND);
    
    $data = json_decode($input, true);

    // Si la décodage a échoué, renvoyer une erreur
    if (json_last_error() !== JSON_ERROR_NONE) {
        file_put_contents('debug_log.txt', "JSON decode error: " . json_last_error_msg() . "\n", FILE_APPEND);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Données invalides: ' . json_last_error_msg()]);
        exit;
    }

    file_put_contents('debug_log.txt', "Decoded data: " . print_r($data, true) . "\n", FILE_APPEND);

    // Valider les données
    if (empty($data['information']) || empty($data['mainSubject']) || empty($data['content'])) {
        file_put_contents('debug_log.txt', "Validation error: Missing required fields\n", FILE_APPEND);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Des champs obligatoires sont manquants.']);
        exit;
    }

    // Si tout est valide, traiter les données (ex : insertion dans la base de données)
    try {
        // Connexion à la base de données
        $pdo = new PDO('mysql:host=localhost;dbname=formulaire_sbin', 'root', ''); // Modifier avec vos identifiants réels
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Préparer la requête d'insertion
        $stmt = $pdo->prepare('INSERT INTO formulaire_alerte (information, main_subject, content, source, source_date, lien, author, name_or_alias, contact_info) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        
        $stmt->execute([
            $data['information'],
            $data['mainSubject'],
            $data['content'],
            $data['source'] ?? null,
            $data['source-date'] ?? null, // Notez le tiret ici
            $data['lien'] ?? null,
            $data['author'] ?? null,
            $data['nameOrAlias'] ?? null,
            $data['contactInfo'] ?? null,
        ]);

        file_put_contents('debug_log.txt', "Database insertion successful\n", FILE_APPEND);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        file_put_contents('debug_log.txt', "Database error: " . $e->getMessage() . "\n", FILE_APPEND);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
    }
} else {
    file_put_contents('debug_log.txt', "Invalid request method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Requête invalide. Méthode: ' . $_SERVER['REQUEST_METHOD']]);
}
?>
