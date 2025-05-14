<?php
// Configuration de la connexion à la base de données XAMPP
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "formulaire_sbin";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => "Échec de la connexion à la base de données : " . $conn->connect_error
    ]));
}

$conn->set_charset("utf8mb4");

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    die(json_encode([
        'success' => false,
        'message' => "Aucune donnée reçue"
    ]));
}

// Préparer les données (sans accents dans les noms de variables)
$statut = $data['statut'] ?? 'nouvelle';
$dateObservation = $data['dateObservation'] ?? '';
$dateNotification = $data['dateNotification'] ?? '';
$canalPrimaire = $data['canalPrimaire'] ?? '';
$autreCanalPrimaire = $data['autreCanalPrimaire'] ?? '';
$liens = $data['liens'] ?? '';
$auteur = $data['auteur'] ?? '';
$objetPrincipal = $data['objetPrincipal'] ?? '';
$propos = $data['propos'] ?? '';
$niveauAlerte = $data['niveauAlerte'] ?? '';
$impactPotentiel = implode(',', $data['impactPotentiel'] ?? []);
$autreImpact = $data['autreImpact'] ?? '';
$actionsImmediates = $data['actionsImmediates'] ?? '';
$propositions = $data['propositions'] ?? '';
$responsable = $data['responsable'] ?? '';
$notes = $data['notes'] ?? '';
$dateCreation = date('Y-m-d H:i:s');

// Préparer la requête SQL
$sql = "INSERT INTO alertes (
    statut, date_observation, date_notification, canal_primaire,
    autre_canal_primaire, liens, auteur, objet_principal, propos,
    niveau_alerte, impact_potentiel, autre_impact, actions_immediates,
    propositions, responsable, notes, date_creation
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode([
        'success' => false,
        'message' => "Erreur de préparation de la requête : " . $conn->error
    ]));
}

// Lier les paramètres (tous sont des chaînes : "s")
$stmt->bind_param(
    "sssssssssssssssss",
    $statut,
    $dateObservation,
    $dateNotification,
    $canalPrimaire,
    $autreCanalPrimaire,
    $liens,
    $auteur,
    $objetPrincipal,
    $propos,
    $niveauAlerte,
    $impactPotentiel,
    $autreImpact,
    $actionsImmediates,
    $propositions,
    $responsable,
    $notes,
    $dateCreation
);

// Exécuter
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => "Alerte enregistrée avec succès",
        'id' => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "Erreur lors de l'enregistrement : " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>

