<?php
/**
 * Script d'importation des données dans MySQL
 * Ce script importe les données fournies dans la base de données MySQL
 */
require_once 'includes/functions.php';

// Afficher l'en-tête
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Importation des données dans MySQL</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Progression de l'importation</h5>
            <div id="import-log" class="alert alert-info" style="max-height: 300px; overflow-y: auto;">
                Démarrage de l'importation...
            </div>
            
            <div class="progress mb-3">
                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
            
            <div id="import-status"></div>
        </div>
    </div>
    
    <div class="text-center mb-4">
        <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
    </div>
</div>

<?php
// Fonction pour ajouter un message au log
function addToLog($message) {
    echo "<script>
        document.getElementById('import-log').innerHTML += '<br>' + '" . addslashes($message) . "';
        document.getElementById('import-log').scrollTop = document.getElementById('import-log').scrollHeight;
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
        document.getElementById('import-status').innerHTML = '<div class=\"alert alert-{$class}\">{$status}</div>';
    </script>";
    flush();
    ob_flush();
}

// Activer la sortie de buffer pour afficher les mises à jour en temps réel
ob_start();

try {
    // Connexion à la base de données MySQL
    addToLog("Connexion à la base de données...");
    $pdo = getDbConnection();
    addToLog("Connexion établie avec succès.");
    updateProgress(10);
    
    // Forcer la création des tables
    addToLog("Création des tables si elles n'existent pas...");
    $mysqli = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['dbname']);
    if ($mysqli->connect_error) {
        throw new Exception("Erreur de connexion: " . $mysqli->connect_error);
    }
    createTables($mysqli);
    $mysqli->close();
    addToLog("Structure de la base de données vérifiée et mise à jour.");
    updateProgress(15);
    
    // Vérifier si la table settings existe, sinon la créer
    addToLog("Vérification de la structure de la base de données...");
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('settings', $tables)) {
        addToLog("Création de la table 'settings'...");
        $pdo->exec("CREATE TABLE settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_name VARCHAR(50) NOT NULL UNIQUE,
            setting_value TEXT NOT NULL
        )");
        addToLog("Table 'settings' créée avec succès.");
    }
    
    // Vider les tables existantes
    addToLog("Nettoyage des tables existantes...");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE pdfs");
    $pdo->exec("TRUNCATE TABLE settings");
    $pdo->exec("TRUNCATE TABLE courses");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    addToLog("Tables nettoyées avec succès.");
    updateProgress(20);
    
    // Importer les données des cours
    addToLog("Importation des données des cours...");
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
    
    foreach ($courseInserts as $sql) {
        $pdo->exec($sql);
    }
    addToLog("Données des cours importées avec succès.");
    updateProgress(40);
    
    // Importer les données des paramètres
    addToLog("Importation des paramètres...");
    $settingsInserts = [
        "INSERT INTO settings (id, setting_name, setting_value) VALUES (3, 'items_per_page', '12')",
        "INSERT INTO settings (id, setting_name, setting_value) VALUES (4187, 'view_mode', 'grid')",
        "INSERT INTO settings (id, setting_name, setting_value) VALUES (4693, 'theme', 'light')"
    ];
    
    foreach ($settingsInserts as $sql) {
        $pdo->exec($sql);
    }
    addToLog("Paramètres importés avec succès.");
    updateProgress(60);
    
    // Importer les données des PDFs
    addToLog("Importation des PDFs...");
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
    
    foreach ($pdfInserts as $sql) {
        $pdo->exec($sql);
    }
    addToLog("PDFs importés avec succès.");
    updateProgress(80);
    
    // Mettre à jour les séquences d'auto-incrémentation
    addToLog("Mise à jour des séquences d'auto-incrémentation...");
    $pdo->exec("ALTER TABLE pdfs AUTO_INCREMENT = 23");
    $pdo->exec("ALTER TABLE settings AUTO_INCREMENT = 4694");
    $pdo->exec("ALTER TABLE courses AUTO_INCREMENT = 14");
    addToLog("Séquences mises à jour avec succès.");
    updateProgress(90);
    
    // Associer les PDFs aux cours en fonction de leur titre
    addToLog("Association des PDFs aux cours...");
    $pdo->exec("UPDATE pdfs SET course_id = 13 WHERE title LIKE '%stat%' OR category = 'laboratoire'");
    $pdo->exec("UPDATE pdfs SET course_id = 7 WHERE title LIKE '%thread%' OR title = 'threads' OR title LIKE '%fiche threads%'");
    $pdo->exec("UPDATE pdfs SET course_id = 11 WHERE title IN ('builder', 'adapter', 'composite', 'factory') OR title LIKE '%Design Pattern%'");
    $pdo->exec("UPDATE pdfs SET course_id = 6 WHERE title LIKE '%exception%'");
    addToLog("Association terminée avec succès.");
    updateProgress(100);
    
    // Importation terminée
    setStatus("Importation terminée avec succès. Toutes les données ont été importées dans la base de données MySQL.");
    
} catch (Exception $e) {
    setStatus("Erreur lors de l'importation : " . $e->getMessage(), false);
}

// Inclure le pied de page
include 'includes/footer.php';
?>
