<?php
/**
 * Script pour créer la base de données MySQL et importer les données
 * Utilise exactement les mêmes structures et données que la base SQLite d'origine
 */
require_once 'includes/functions.php';

// Afficher l'en-tête HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de la base de données MySQL</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Création de la base de données MySQL</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Progression</h5>
                <div id="log" class="alert alert-info" style="max-height: 300px; overflow-y: auto;">
                    Démarrage de la création de la base de données...
                </div>
                
                <div class="progress mb-3">
                    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
                
                <div id="status"></div>
            </div>
        </div>
        
        <div class="text-center mb-4">
            <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Fonction pour ajouter un message au log
function addToLog($message) {
    echo "<script>
        document.getElementById('log').innerHTML += '<br>' + '" . addslashes($message) . "';
        document.getElementById('log').scrollTop = document.getElementById('log').scrollHeight;
    </script>";
    flush();
    ob_flush();
}

// Fonction pour mettre à jour la barre de progression
function updateProgress($percentage) {
    echo "<script>
        document.getElementById('progress-bar').style.width = '{$percentage}%';
        document.getElementById('progress-bar').setAttribute('aria-valuenow', '{$percentage}');
    </script>";
    flush();
    ob_flush();
}

// Fonction pour définir le statut final
function setStatus($status, $success = true) {
    $class = $success ? 'success' : 'danger';
    echo "<script>
        document.getElementById('status').innerHTML = '<div class=\"alert alert-{$class}\">{$status}</div>';
    </script>";
    flush();
    ob_flush();
}

// Activer la sortie de buffer pour afficher les mises à jour en temps réel
ob_start();

try {
    // Configuration de la base de données
    $config = [
        'db' => [
            'host' => 'localhost',
            'username' => 'root',
            'password' => '',
            'dbname' => 'mypdf'
        ]
    ];
    
    // Connexion à MySQL sans sélectionner de base de données
    addToLog("Connexion au serveur MySQL...");
    $mysqli = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password']);
    
    if ($mysqli->connect_error) {
        throw new Exception("Erreur de connexion: " . $mysqli->connect_error);
    }
    
    addToLog("Connexion établie avec succès.");
    updateProgress(10);
    
    // Vérifier si la base de données existe, sinon la créer
    addToLog("Vérification de l'existence de la base de données...");
    $result = $mysqli->query("SHOW DATABASES LIKE '{$config['db']['dbname']}'");
    
    if ($result->num_rows == 0) {
        addToLog("La base de données n'existe pas. Création en cours...");
        $mysqli->query("CREATE DATABASE {$config['db']['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        addToLog("Base de données créée avec succès.");
    } else {
        addToLog("La base de données existe déjà.");
    }
    
    // Sélectionner la base de données
    $mysqli->select_db($config['db']['dbname']);
    updateProgress(20);
    
    // Supprimer les tables existantes si elles existent
    addToLog("Suppression des tables existantes...");
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
    $mysqli->query("DROP TABLE IF EXISTS pdfs");
    $mysqli->query("DROP TABLE IF EXISTS settings");
    $mysqli->query("DROP TABLE IF EXISTS courses");
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
    addToLog("Tables supprimées avec succès.");
    updateProgress(30);
    
    // Créer la table pdfs
    addToLog("Création de la table pdfs...");
    $mysqli->query("CREATE TABLE pdfs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        author VARCHAR(100),
        tags TEXT,
        category VARCHAR(50),
        description TEXT,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        course_id INT,
        block_year INT DEFAULT NULL
    ) ENGINE=InnoDB");
    addToLog("Table pdfs créée avec succès.");
    updateProgress(40);
    
    // Créer la table settings
    addToLog("Création de la table settings...");
    $mysqli->query("CREATE TABLE settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_name VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL
    ) ENGINE=InnoDB");
    addToLog("Table settings créée avec succès.");
    updateProgress(50);
    
    // Créer la table courses
    addToLog("Création de la table courses...");
    $mysqli->query("CREATE TABLE courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    addToLog("Table courses créée avec succès.");
    updateProgress(60);
    
    // Insérer les données dans la table courses
    addToLog("Insertion des données dans la table courses...");
    $courseInserts = [
        "INSERT INTO courses (id, name, description, created_at) VALUES (1, 'Analyse 1', 'Cours d''analyse mathématique niveau 1', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (2, 'Analyse 2', 'Cours d''analyse mathématique niveau 2', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (3, 'SGBD 3', 'Systèmes de gestion de bases de données niveau 3', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (4, 'SGBD 4', 'Systèmes de gestion de bases de données niveau 4', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (5, 'BD Avancées', 'Bases de données avancées', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (6, 'JAVA', 'Programmation en Java', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (7, 'C Thread', 'Programmation C avec threads', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (8, 'C Linux', 'Programmation C sous Linux', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (9, 'CPP', 'Programmation C++', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (10, 'C#', 'Programmation C#', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (11, 'Design Pattern', 'Patrons de conception logicielle', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (12, 'Complete BD', 'Bases de données complètes', '2025-05-03 14:14:54')",
        "INSERT INTO courses (id, name, description, created_at) VALUES (13, 'Statistique', 'Analyse statistique', '2025-05-03 14:14:54')"
    ];
    
    // Désactiver l'auto-incrémentation pour permettre d'insérer des IDs spécifiques
    $mysqli->query("ALTER TABLE courses AUTO_INCREMENT = 1");
    
    foreach ($courseInserts as $sql) {
        if (!$mysqli->query($sql)) {
            addToLog("Erreur lors de l'insertion: " . $mysqli->error);
        }
    }
    
    addToLog("Données insérées dans la table courses avec succès.");
    updateProgress(70);
    
    // Insérer les données dans la table settings
    addToLog("Insertion des données dans la table settings...");
    $settingsInserts = [
        "INSERT INTO settings (id, setting_name, setting_value) VALUES (3, 'items_per_page', '12')",
        "INSERT INTO settings (id, setting_name, setting_value) VALUES (4187, 'view_mode', 'grid')",
        "INSERT INTO settings (id, setting_name, setting_value) VALUES (4693, 'theme', 'light')"
    ];
    
    // Désactiver l'auto-incrémentation pour permettre d'insérer des IDs spécifiques
    $mysqli->query("ALTER TABLE settings AUTO_INCREMENT = 1");
    
    foreach ($settingsInserts as $sql) {
        if (!$mysqli->query($sql)) {
            addToLog("Erreur lors de l'insertion: " . $mysqli->error);
        }
    }
    
    addToLog("Données insérées dans la table settings avec succès.");
    updateProgress(80);
    
    // Insérer les données dans la table pdfs
    addToLog("Insertion des données dans la table pdfs...");
    $pdfInserts = [
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (2, 'labo stat synthese', '68162567575d0_1746281831.pdf', 'moi', '', 'laboratoire', '', '2025-05-03 14:17:11', 13)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (3, 'cahier d''exercices', '68162806e6569_1746282502.pdf', 'moi', '', 'exercices', '', '2025-05-03 14:28:22', 13)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (4, 'chapitre 3', '68162836c2c45_1746282550.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:29:10',4 )",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (5, 'chapitre 3', '68162858098c1_1746282584.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:29:44', 2)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (6, 'synthese examen thread fiche', '6816288f71e86_1746282639.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:30:39', 7)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (7, 'chapitre 7', '681628ca5ce9e_1746282698.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:31:38', 4)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (8, 'chapitre6', '681628f2f3707_1746282738.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:32:19', 4)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (9, 'astuce stat ', '6816290f4b589_1746282767.pdf', 'moi', '', 'laboratoire', '', '2025-05-03 14:32:47', 13)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (10, 'intro', '6816292bb3271_1746282795.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:33:15', 11)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (11, 'builder', '6816296600058_1746282854.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:34:14', 11)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (12, 'exceptions', '681629801a053_1746282880.pdf', '', '', 'synthese', '', '2025-05-03 14:34:40', 4)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (13, 'threads', '6816299af01ba_1746282906.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:35:07', 7)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (14, 'chapitre2', '681629b4881cb_1746282932.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:35:32', 13)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (15, 'synthese complete ', '681629f250cfc_1746282994.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:36:34', 10)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (16, 'chapitre 4', '68162a1c15362_1746283036.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:37:16', 4)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (17, 'chapitre 5 ', '68162a50ca6f0_1746283088.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:38:08', 4)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (18, 'fiche threads ', '68162a7f67b9a_1746283135.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:38:55', 7)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (19, 'adapter', '68162a9bd8174_1746283163.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:39:23', 11)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (20, 'composite', '68162abaa94cf_1746283194.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:39:54', 11)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (21, 'factory', '68162b006e406_1746283264.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:41:04', 11)",
        "INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (22, 'synthese examen ', '68162b2422996_1746283300.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:41:40', 6)"
    ];
    
    // Désactiver l'auto-incrémentation pour permettre d'insérer des IDs spécifiques
    $mysqli->query("ALTER TABLE pdfs AUTO_INCREMENT = 1");
    
    foreach ($pdfInserts as $sql) {
        if (!$mysqli->query($sql)) {
            addToLog("Erreur lors de l'insertion: " . $mysqli->error);
        }
    }
    
    addToLog("Données insérées dans la table pdfs avec succès.");
    updateProgress(90);
    
    // Mettre à jour les séquences d'auto-incrémentation
    addToLog("Mise à jour des séquences d'auto-incrémentation...");
    $mysqli->query("ALTER TABLE pdfs AUTO_INCREMENT = 23");
    $mysqli->query("ALTER TABLE courses AUTO_INCREMENT = 14");
    $mysqli->query("ALTER TABLE settings AUTO_INCREMENT = 4694");
    addToLog("Séquences mises à jour avec succès.");
    updateProgress(100);
    
    // Fermer la connexion
    $mysqli->close();
    
    setStatus("Base de données MySQL créée et données importées avec succès !");
    
} catch (Exception $e) {
    setStatus("Erreur: " . $e->getMessage(), false);
}
?>
