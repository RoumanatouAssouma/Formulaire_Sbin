<?php
// config/dotenv_loader.php
$env_path = __DIR__ . '/../.env'; // Ajustez ce chemin si votre .env est ailleurs

if (!file_exists($env_path)) {
    // Optionnel: Gérer l'erreur si le .env n'est pas trouvé
    error_log("Fichier .env non trouvé à : " . $env_path);
    // Ou même die("Configuration manquante.");
} else {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || empty($line)) continue;

        // Gérer le cas où il n'y a pas de '=' (ligne mal formée)
        if (strpos($line, '=') === false) {
            error_log("Ligne mal formée dans .env : " . $line);
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value; // Ajouter aussi à $_SERVER est souvent une bonne pratique
    }
}
?>
