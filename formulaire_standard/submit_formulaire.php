<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $response = ['success' => false];
    $data     = $_POST;

    /* ---------------------- 1) Validation ---------------------- */
    if (empty($data['information']) || empty($data['mainSubject']) || empty($data['content'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Des champs obligatoires sont manquants.'
        ]);
        exit;
    }

    /* ---------------------- 2) Connexion BDD ------------------- */
    try {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=formulaire_sbin;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        /* ----- 3) Gestion du fichier uploadé (stockage disque) --- */
        $mediaPath = null;                      // chemin relatif enregistré en BDD

        if (!empty($_FILES['mediaUpload']['name']) && $_FILES['mediaUpload']['error'] === UPLOAD_ERR_OK) {

            /* 3-a) Création (ou vérification) du dossier */
            $uploadFolder = __DIR__ . '/upload_image/';
            if (!is_dir($uploadFolder)) {
                mkdir($uploadFolder, 0777, true);
            }

            /* 3-b) Vérifications sécurité */
            $allowedTypes = ['image/jpeg','image/png','image/webp','image/gif','application/pdf'];
            if (!in_array($_FILES['mediaUpload']['type'], $allowedTypes)) {
                throw new RuntimeException('Type de fichier non autorisé. JPEG, PNG, GIF, WebP, PDF uniquement.');
            }
            $maxSize = 10 * 1024 * 1024; // 10 Mo
            if ($_FILES['mediaUpload']['size'] > $maxSize) {
                throw new RuntimeException('Fichier trop volumineux (max 10 Mo).');
            }

            /* 3-c) Déplacement + nom unique */
            $extension  = pathinfo($_FILES['mediaUpload']['name'], PATHINFO_EXTENSION);
            $newName    = uniqid('', true) . '.' . strtolower($extension);
            $destination = $uploadFolder . $newName;

            if (!move_uploaded_file($_FILES['mediaUpload']['tmp_name'], $destination)) {
                throw new RuntimeException('Erreur lors du déplacement du fichier.');
            }

            $mediaPath = "/formulaire_standard/upload_image/" . $newName; // ex: upload_image/64e3f3b24a3fc.jpg
        }

        /* --------------------- 4) Insertion ---------------------- */
        $stmt = $pdo->prepare(
            'INSERT INTO formulaire_standard
             (information, main_subject, content, source, source_date,
              lien, author, name_or_alias, contact_info, media_path)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['information'],
            $data['mainSubject'],
            $data['content'],
            $data['source']        ?? null,
            $data['source-date']   ?? null,
            $data['lien']          ?? null,
            $data['author']        ?? null,
            $data['nameOrAlias']   ?? null,
            $data['contactInfo']   ?? null,
            $mediaPath
        ]);

        /* --------------------- 5) Notification mail -------------- */
        $to      = 'roumanatou.assouma@celtiis.bj';
        $subject = 'Nouvelle alerte soumise';
        $body    = "Une nouvelle alerte a été soumise :\n\n" .
                   "Information : {$data['information']}\n" .
                   "Sujet principal : {$data['mainSubject']}\n" .
                   "Contenu : {$data['content']}\n" .
                   "Source : " . ($data['source'] ?? 'N/A') . "\n" .
                   "Date de la source : " . ($data['source-date'] ?? 'N/A') . "\n" .
                   "Lien : " . ($data['lien'] ?? 'N/A') . "\n" .
                   "Auteur : " . ($data['author'] ?? 'N/A') . "\n" .
                   "Nom ou Alias : " . ($data['nameOrAlias'] ?? 'N/A') . "\n" .
                   "Contact : " . ($data['contactInfo'] ?? 'N/A') . "\n" .
                   ($mediaPath ? "Fichier média : $mediaPath\n" : '');

        @mail($to, $subject, $body, "From: formulaire@localhost\r\n");

        $response = [
            'success'   => true,
            'message'   => 'Données insérées et mail envoyé avec succès.',
            'mediaPath' => $mediaPath
        ];

    } catch (Throwable $e) {               // PDOException ou RuntimeException
        file_put_contents(__DIR__ . '/debug_log.txt',
            '['.date('c')."] ".$e->getMessage()."\n", FILE_APPEND);
        $response['message'] = 'Erreur : ' . $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/* ----------- Si la requête n'est PAS un POST ------------- */
?>