<?php
/**
 * Script de migration des données de SQLite vers MySQL
 * Ce script transfère toutes les données de la base SQLite vers la nouvelle base MySQL
 */
require_once 'includes/functions.php';

// Afficher l'en-tête
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Migration des données SQLite vers MySQL</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Progression de la migration</h5>
            <div id="migration-log" class="alert alert-info">
                Démarrage de la migration...
            </div>
            
            <div class="progress mb-3">
                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
            
            <div id="migration-status"></div>
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
        document.getElementById('migration-log').innerHTML += '<br>' + '" . addslashes($message) . "';
        document.getElementById('migration-log').scrollTop = document.getElementById('migration-log').scrollHeight;
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
        document.getElementById('migration-status').innerHTML = '<div class=\"alert alert-{$class}\">{$status}</div>';
    </script>";
    flush();
    ob_flush();
}

// Activer la sortie de buffer pour afficher les mises à jour en temps réel
ob_start();

try {
    // Chemin vers la base de données SQLite
    $sqlitePath = __DIR__ . '/data/mypdf.db';
    
    // Vérifier si le fichier SQLite existe
    if (!file_exists($sqlitePath)) {
        throw new Exception("Le fichier de base de données SQLite n'existe pas à l'emplacement spécifié.");
    }
    
    // Connexion à la base de données SQLite
    addToLog("Connexion à la base de données SQLite...");
    $sqlitePdo = new PDO('sqlite:' . $sqlitePath);
    $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    addToLog("Connexion à SQLite établie avec succès.");
    updateProgress(10);
    
    // Connexion à la base de données MySQL
    addToLog("Connexion à la base de données MySQL...");
    $mysqlPdo = getDbConnection();
    addToLog("Connexion à MySQL établie avec succès.");
    updateProgress(20);
    
    // Obtenir la liste des tables dans SQLite
    addToLog("Récupération de la liste des tables...");
    $tables = $sqlitePdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
    addToLog("Tables trouvées : " . implode(", ", $tables));
    updateProgress(30);
    
    // Migrer chaque table
    $totalTables = count($tables);
    $currentTable = 0;
    
    foreach ($tables as $table) {
        $currentTable++;
        $progressPercentage = 30 + ($currentTable / $totalTables) * 60;
        
        addToLog("Migration de la table '{$table}'...");
        
        // Obtenir les données de la table SQLite
        $rows = $sqlitePdo->query("SELECT * FROM {$table}")->fetchAll(PDO::FETCH_ASSOC);
        $rowCount = count($rows);
        
        addToLog("- {$rowCount} enregistrements trouvés dans '{$table}'");
        
        if ($rowCount > 0) {
            // Obtenir les colonnes de la première ligne
            $columns = array_keys($rows[0]);
            
            // Vider la table MySQL avant d'insérer les données
            $mysqlPdo->exec("TRUNCATE TABLE {$table}");
            
            // Préparer la requête d'insertion
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $columnList = implode(', ', $columns);
            $stmt = $mysqlPdo->prepare("INSERT INTO {$table} ({$columnList}) VALUES ({$placeholders})");
            
            // Insérer les données
            $insertedCount = 0;
            foreach ($rows as $row) {
                try {
                    $stmt->execute(array_values($row));
                    $insertedCount++;
                } catch (PDOException $e) {
                    addToLog("- Erreur lors de l'insertion d'un enregistrement dans '{$table}': " . $e->getMessage());
                }
            }
            
            addToLog("- {$insertedCount} enregistrements migrés vers '{$table}'");
        }
        
        updateProgress($progressPercentage);
    }
    
    // Migration terminée
    updateProgress(100);
    setStatus("Migration terminée avec succès. Toutes les données ont été transférées de SQLite vers MySQL.");
    
} catch (Exception $e) {
    setStatus("Erreur lors de la migration : " . $e->getMessage(), false);
}

// Inclure le pied de page
include 'includes/footer.php';
?>
