<?php
/**
 * Configuration de la base de données et connexion
 */

// Paramètres de connexion à la base de données
$config = [
    'db' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname' => 'mypdf'
    ],
    'app' => [
        'name' => 'Gestion de PDFs',
        'version' => '1.0.0'
    ]
];

// Fonction pour vérifier et créer la base de données si elle n'existe pas
function checkAndCreateDatabase() {
    global $config;
    
    try {
        // Connexion sans sélectionner de base de données
        $conn = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password']);
        
        if ($conn->connect_error) {
            throw new Exception("Erreur de connexion: " . $conn->connect_error);
        }
        
        // Vérifier si la base de données existe
        $result = $conn->query("SHOW DATABASES LIKE '{$config['db']['dbname']}'");
        
        if ($result->num_rows == 0) {
            // La base de données n'existe pas, on la crée
            $sql = "CREATE DATABASE {$config['db']['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='alert alert-success'>Base de données créée avec succès</div>";
                
                // Sélectionner la base de données nouvellement créée
                $conn->select_db($config['db']['dbname']);
                
                // Créer les tables nécessaires
                createTables($conn);
            } else {
                throw new Exception("Erreur lors de la création de la base de données: " . $conn->error);
            }
        }
        
        $conn->close();
        return true;
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Erreur: " . $e->getMessage() . "</div>";
        return false;
    }
}

/**
 * Crée les tables nécessaires dans la base de données
 */
function createTables($conn) {
    // Table pour les cours (doit être créée avant pdfs à cause de la clé étrangère)
    $sql = "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    
    if (!$conn->query($sql)) {
        throw new Exception("Erreur lors de la création de la table courses: " . $conn->error);
    }
    
    // Insérer les cours par défaut s'ils n'existent pas
    $defaultCourses = [
        ['Analyse 1', 'Cours d\'analyse mathématique niveau 1'],
        ['Analyse 2', 'Cours d\'analyse mathématique niveau 2'],
        ['SGBD 3', 'Systèmes de gestion de bases de données niveau 3'],
        ['SGBD 4', 'Systèmes de gestion de bases de données niveau 4'],
        ['BD Avancées', 'Bases de données avancées'],
        ['JAVA', 'Programmation en Java'],
        ['C Thread', 'Programmation C avec threads'],
        ['C Linux', 'Programmation C sous Linux'],
        ['CPP', 'Programmation C++'],
        ['C#', 'Programmation C#'],
        ['Design Pattern', 'Patrons de conception logicielle'],
        ['Complete BD', 'Bases de données complètes'],
        ['Statistique', 'Analyse statistique']
    ];
    
    foreach ($defaultCourses as $course) {
        try {
            $stmt = $conn->prepare("INSERT INTO courses (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $course[0], $course[1]);
            $stmt->execute();
        } catch (Exception $e) {
            // Ignorer les erreurs de duplications (clé unique)
            if ($conn->errno != 1062) { // 1062 est le code d'erreur pour duplicate entry
                throw $e;
            }
        }
    }

    // Table principale pour les PDFs
    $sql = "CREATE TABLE IF NOT EXISTS pdfs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        author VARCHAR(100),
        tags TEXT,
        category VARCHAR(50),
        description TEXT,
        course_id INT NOT NULL,
        block_year INT DEFAULT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB";
    
    if (!$conn->query($sql)) {
        throw new Exception("Erreur lors de la création de la table pdfs: " . $conn->error);
    }
    
    // Table pour les paramètres utilisateur
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_name VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL
    ) ENGINE=InnoDB";
    
    if (!$conn->query($sql)) {
        throw new Exception("Erreur lors de la création de la table settings: " . $conn->error);
    }
    
    // Insérer les paramètres par défaut s'ils n'existent pas
    $defaultSettings = [
        ['view_mode', 'grid'],
        ['theme', 'light'],
        ['items_per_page', '12']
    ];
    
    foreach ($defaultSettings as $setting) {
        try {
            $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES (?, ?)");
            $stmt->bind_param("ss", $setting[0], $setting[1]);
            $stmt->execute();
        } catch (Exception $e) {
            // Ignorer les erreurs de duplications (clé unique)
            if ($conn->errno != 1062) {
                throw $e;
            }
        }
    }
}

/**
 * Établit une connexion à la base de données
 * @return PDO Instance de connexion PDO
 */
function getDbConnection() {
    global $config;
    
    try {
        // Vérifier et créer la base de données si nécessaire
        checkAndCreateDatabase();
        
        // Connexion à la base de données avec PDO
        $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password'], $options);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}

/**
 * Vérifie si une colonne existe dans une table
 * @param PDO $pdo Instance de connexion PDO
 * @param string $table Nom de la table
 * @param string $column Nom de la colonne
 * @return bool True si la colonne existe, false sinon
 */
function columnExists($pdo, $table, $column) {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
        $stmt->execute([$column]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Vérifie et met à jour la structure de la base de données si nécessaire
 * @param PDO $pdo Instance de connexion PDO
 */
function checkAndUpdateDatabaseStructure($pdo = null) {
    if ($pdo === null) {
        $pdo = getDbConnection();
    }
    
    try {
        // Vérifier si la colonne course_id existe dans la table pdfs
        if (!columnExists($pdo, 'pdfs', 'course_id')) {
            // Ajouter la colonne course_id
            $pdo->exec("ALTER TABLE pdfs ADD COLUMN course_id INT");
        }
        
        // Vérifier si la colonne block_year existe dans la table pdfs
        if (!columnExists($pdo, 'pdfs', 'block_year')) {
            // Ajouter la colonne block_year
            $pdo->exec("ALTER TABLE pdfs ADD COLUMN block_year INT DEFAULT NULL");
        }
    } catch (PDOException $e) {
        // Si une erreur se produit, on la log mais on ne stoppe pas l'exécution
        error_log("Erreur lors de la mise à jour de la structure de la base de données: " . $e->getMessage());
    }
}

// Initialiser la connexion à la base de données
$pdo = getDbConnection();

// Appeler la fonction pour vérifier et mettre à jour la structure
checkAndUpdateDatabaseStructure($pdo);
