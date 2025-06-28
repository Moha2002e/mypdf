<?php
/**
 * Script d'exportation de la base de données SQLite
 * Ce script génère un fichier SQL contenant la structure et les données de la base de données
 */
require_once 'includes/functions.php';

// Chemin vers la base de données SQLite
$dbPath = __DIR__ . '/data/mypdf.db';

// Vérifier si le fichier de base de données existe
if (!file_exists($dbPath)) {
    die("Erreur : Le fichier de base de données n'existe pas à l'emplacement spécifié.");
}

// Nom du fichier d'exportation
$exportFileName = 'mypdf_export_' . date('Y-m-d_H-i-s') . '.sql';
$exportFilePath = __DIR__ . '/' . $exportFileName;

// Commande pour exporter la base de données
$command = "sqlite3 {$dbPath} .dump > {$exportFilePath}";

// Exécuter la commande
$output = [];
$returnVar = 0;

// Sous Windows, nous devons utiliser une approche différente
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Vérifier si sqlite3 est disponible
    exec('where sqlite3', $output, $returnVar);
    
    if ($returnVar !== 0) {
        // Si sqlite3 n'est pas disponible, utiliser PDO pour exporter manuellement
        try {
            $pdo = getDbConnection();
            
            // Ouvrir le fichier d'exportation
            $exportFile = fopen($exportFilePath, 'w');
            
            if (!$exportFile) {
                die("Erreur : Impossible de créer le fichier d'exportation.");
            }
            
            // Exporter la structure des tables
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                // Obtenir la requête CREATE TABLE
                $createTableQuery = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='{$table}'")->fetchColumn();
                fwrite($exportFile, $createTableQuery . ";\n\n");
                
                // Obtenir les données de la table
                $rows = $pdo->query("SELECT * FROM {$table}")->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($rows) > 0) {
                    foreach ($rows as $row) {
                        $columns = array_keys($row);
                        $values = array_map(function($value) use ($pdo) {
                            if ($value === null) {
                                return 'NULL';
                            } elseif (is_numeric($value)) {
                                return $value;
                            } else {
                                return $pdo->quote($value);
                            }
                        }, array_values($row));
                        
                        fwrite($exportFile, "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n");
                    }
                    fwrite($exportFile, "\n");
                }
            }
            
            // Exporter les index
            $indexes = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type='index' AND sql IS NOT NULL AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($indexes as $index) {
                fwrite($exportFile, $index['sql'] . ";\n");
            }
            
            fclose($exportFile);
            
            echo "La base de données a été exportée avec succès vers le fichier : {$exportFileName}";
            echo "<br><a href='{$exportFileName}' download>Télécharger le fichier d'exportation</a>";
            
        } catch (PDOException $e) {
            die("Erreur lors de l'exportation de la base de données : " . $e->getMessage());
        }
    } else {
        // Si sqlite3 est disponible, utiliser la commande
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            die("Erreur lors de l'exportation de la base de données. Code d'erreur : {$returnVar}");
        }
        
        echo "La base de données a été exportée avec succès vers le fichier : {$exportFileName}";
        echo "<br><a href='{$exportFileName}' download>Télécharger le fichier d'exportation</a>";
    }
} else {
    // Sous Linux/Mac, utiliser directement la commande
    exec($command, $output, $returnVar);
    
    if ($returnVar !== 0) {
        die("Erreur lors de l'exportation de la base de données. Code d'erreur : {$returnVar}");
    }
    
    echo "La base de données a été exportée avec succès vers le fichier : {$exportFileName}";
    echo "<br><a href='{$exportFileName}' download>Télécharger le fichier d'exportation</a>";
}
