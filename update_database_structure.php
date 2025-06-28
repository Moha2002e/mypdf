<?php
/**
 * Script de mise à jour de la structure de la base de données
 * Ajoute la colonne block_year à la table pdfs
 */
require_once 'includes/db.php';

// Fonction pour ajouter un message au log
function addToLog($message) {
    echo "<div class='alert alert-info'>$message</div>";
    flush();
    ob_flush();
}

// Fonction pour définir le statut final
function setStatus($status, $success = true) {
    $class = $success ? 'success' : 'danger';
    echo "<div class='alert alert-{$class}'>{$status}</div>";
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1><i class="bi bi-database-gear me-2"></i>Mise à jour de la structure de la base de données</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Progression de la mise à jour</h5>
            <div id="update-log">
                Démarrage de la mise à jour...
            </div>
        </div>
    </div>
    
    <div class="text-center mb-4">
        <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
    </div>
</div>

<?php
// Activer la sortie de buffer pour afficher les mises à jour en temps réel
ob_start();

try {
    // Connexion à la base de données
    addToLog("Connexion à la base de données...");
    $pdo = getDbConnection();
    addToLog("Connexion établie avec succès.");
    
    // Vérifier si la colonne block_year existe déjà
    addToLog("Vérification de la structure de la table pdfs...");
    $columns = $pdo->query("SHOW COLUMNS FROM pdfs")->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('block_year', $columns)) {
        addToLog("La colonne block_year existe déjà dans la table pdfs.");
        setStatus("La structure de la base de données est déjà à jour.");
    } else {
        // Ajouter la colonne block_year
        addToLog("Ajout de la colonne block_year...");
        $pdo->exec("ALTER TABLE pdfs ADD COLUMN block_year INT DEFAULT NULL");
        addToLog("Colonne block_year ajoutée avec succès.");
        
        setStatus("Structure de la base de données mise à jour avec succès. La colonne block_year a été ajoutée.");
    }
    
} catch (Exception $e) {
    setStatus("Erreur lors de la mise à jour : " . $e->getMessage(), false);
}

// Inclure le pied de page
include 'includes/footer.php';
?> 