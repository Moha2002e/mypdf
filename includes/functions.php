<?php
/**
 * Fonctions utilitaires pour l'application MyPDF
 */

require_once 'db.php';

/**
 * Récupère tous les PDFs de la base de données
 * @param string $orderBy Champ pour le tri (title, uploaded_at, author, category)
 * @param string $order Direction du tri (ASC ou DESC)
 * @return array Liste des PDFs
 */
function getAllPdfs($orderBy = 'uploaded_at', $order = 'DESC') {
    $allowedOrderBy = ['title', 'uploaded_at', 'author', 'category'];
    $allowedOrder = ['ASC', 'DESC'];
    
    // Validation des paramètres
    if (!in_array($orderBy, $allowedOrderBy)) {
        $orderBy = 'uploaded_at';
    }
    
    if (!in_array($order, $allowedOrder)) {
        $order = 'DESC';
    }
    
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM pdfs ORDER BY $orderBy $order");
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Récupère un PDF par son ID
 * @param int $id ID du PDF
 * @return array|false Données du PDF ou false si non trouvé
 */
function getPdfById($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM pdfs WHERE id = ?");
    $stmt->execute([$id]);
    
    return $stmt->fetch();
}

/**
 * Ajoute un nouveau PDF dans la base de données
 * @param array $data Données du PDF à ajouter
 * @return int|false ID du PDF ajouté ou false en cas d'erreur
 */
function addPdf($data) {
    $pdo = getDbConnection();
    
    // S'assurer que course_id est défini et n'est pas null
    if (!isset($data['course_id']) || $data['course_id'] === null || $data['course_id'] === '') {
        // Récupérer le premier cours disponible comme valeur par défaut
        $courses = getAllCourses();
        if (!empty($courses)) {
            $data['course_id'] = $courses[0]['id'];
        } else {
            // Si aucun cours n'existe, on en crée un par défaut
            $defaultCourseId = addCourse('Cours par défaut', 'Cours créé automatiquement');
            if ($defaultCourseId) {
                $data['course_id'] = $defaultCourseId;
            } else {
                // Impossible de créer un cours par défaut
                return false;
            }
        }
    }
    
    // Vérifier si la colonne block_year existe
    if (columnExists($pdo, 'pdfs', 'block_year')) {
        // Toujours inclure course_id et block_year dans la requête SQL
        $stmt = $pdo->prepare("INSERT INTO pdfs (title, file_name, author, tags, category, description, course_id, block_year) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([
            $data['title'],
            $data['file_name'],
            $data['author'] ?? '',
            $data['tags'] ?? '',
            $data['category'] ?? '',
            $data['description'] ?? '',
            $data['course_id'],
            $data['block_year'] ?? null
        ]);
    } else {
        // Ancienne version sans block_year
        $stmt = $pdo->prepare("INSERT INTO pdfs (title, file_name, author, tags, category, description, course_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([
            $data['title'],
            $data['file_name'],
            $data['author'] ?? '',
            $data['tags'] ?? '',
            $data['category'] ?? '',
            $data['description'] ?? '',
            $data['course_id']
        ]);
    }
    
    return $result ? $pdo->lastInsertId() : false;
}

/**
 * Met à jour les informations d'un PDF
 * @param int $id ID du PDF à mettre à jour
 * @param array $data Nouvelles données
 * @return bool Succès de la mise à jour
 */
function updatePdf($id, $data) {
    $pdo = getDbConnection();
    
    // Vérifier si la colonne course_id existe
    if (columnExists($pdo, 'pdfs', 'course_id')) {
        // S'assurer que course_id est défini et n'est pas null
        if (!isset($data['course_id']) || $data['course_id'] === null || $data['course_id'] === '') {
            // Récupérer le premier cours disponible comme valeur par défaut
            $courses = getAllCourses();
            if (!empty($courses)) {
                $data['course_id'] = $courses[0]['id'];
            } else {
                // Si aucun cours n'existe, on en crée un par défaut
                $defaultCourseId = addCourse('Cours par défaut', 'Cours créé automatiquement');
                if ($defaultCourseId) {
                    $data['course_id'] = $defaultCourseId;
                } else {
                    // Impossible de créer un cours par défaut
                    return false;
                }
            }
        }
        
        // Vérifier si la colonne block_year existe
        if (columnExists($pdo, 'pdfs', 'block_year')) {
            $stmt = $pdo->prepare("UPDATE pdfs SET 
                                  title = ?, 
                                  author = ?, 
                                  tags = ?, 
                                  category = ?, 
                                  description = ?,
                                  course_id = ?,
                                  block_year = ?
                                  WHERE id = ?");
            
            return $stmt->execute([
                $data['title'],
                $data['author'] ?? '',
                $data['tags'] ?? '',
                $data['category'] ?? '',
                $data['description'] ?? '',
                $data['course_id'],
                $data['block_year'] ?? null,
                $id
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE pdfs SET 
                                  title = ?, 
                                  author = ?, 
                                  tags = ?, 
                                  category = ?, 
                                  description = ?,
                                  course_id = ?
                                  WHERE id = ?");
            
            return $stmt->execute([
                $data['title'],
                $data['author'] ?? '',
                $data['tags'] ?? '',
                $data['category'] ?? '',
                $data['description'] ?? '',
                $data['course_id'],
                $id
            ]);
        }
    } else {
        $stmt = $pdo->prepare("UPDATE pdfs SET 
                              title = ?, 
                              author = ?, 
                              tags = ?, 
                              category = ?, 
                              description = ? 
                              WHERE id = ?");
        
        return $stmt->execute([
            $data['title'],
            $data['author'] ?? '',
            $data['tags'] ?? '',
            $data['category'] ?? '',
            $data['description'] ?? '',
            $id
        ]);
    }
}

/**
 * Supprime un PDF de la base de données et le fichier associé
 * @param int $id ID du PDF à supprimer
 * @return bool Succès de la suppression
 */
function deletePdf($id) {
    // Récupérer le nom du fichier avant suppression
    $pdf = getPdfById($id);
    
    if (!$pdf) {
        return false;
    }
    
    // Supprimer le fichier physique
    $filePath = __DIR__ . '/../uploads/' . $pdf['file_name'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    // Supprimer l'entrée dans la base de données
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM pdfs WHERE id = ?");
    
    return $stmt->execute([$id]);
}

/**
 * Recherche des PDFs selon différents critères
 * @param string $query Terme de recherche
 * @return array Résultats de la recherche
 */
function searchPdfs($query) {
    $searchTerm = "%$query%";
    
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM pdfs WHERE 
                          title LIKE ? OR 
                          author LIKE ? OR 
                          tags LIKE ? OR 
                          category LIKE ? OR 
                          description LIKE ?
                          ORDER BY uploaded_at DESC");
    
    $stmt->execute([
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    ]);
    
    return $stmt->fetchAll();
}

/**
 * Récupère les PDFs les plus récents
 * @param int $limit Nombre de PDFs à récupérer
 * @return array Liste des PDFs récents
 */
function getRecentPdfs($limit = 6) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM pdfs ORDER BY uploaded_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Erreur lors de la récupération des PDFs récents: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère toutes les catégories distinctes
 * @return array Liste des catégories
 */
function getAllCategories() {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT DISTINCT category FROM pdfs WHERE category != '' ORDER BY category");
        
        $categories = [];
        while ($row = $stmt->fetch()) {
            $categories[] = $row['category'];
        }
        
        return $categories;
    } catch (Exception $e) {
        error_log("Erreur lors de la récupération des catégories: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère tous les tags distincts
 * @return array Liste des tags
 */
function getAllTags() {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT tags FROM pdfs WHERE tags != ''");
        
        $allTags = [];
        while ($row = $stmt->fetch()) {
            $tags = explode(',', $row['tags']);
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag) && !in_array($tag, $allTags)) {
                    $allTags[] = $tag;
                }
            }
        }
        
        sort($allTags);
        return $allTags;
    } catch (Exception $e) {
        error_log("Erreur lors de la récupération des tags: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère un paramètre de configuration
 * @param string $settingName Nom du paramètre
 * @param string $default Valeur par défaut si non trouvé
 * @return string Valeur du paramètre
 */
function getSetting($settingName, $default = '') {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_name = ?");
    $stmt->execute([$settingName]);
    
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

/**
 * Met à jour un paramètre de configuration
 * @param string $settingName Nom du paramètre
 * @param string $settingValue Nouvelle valeur
 * @return bool Succès de la mise à jour
 */
function updateSetting($settingName, $settingValue) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("REPLACE INTO settings (setting_name, setting_value) VALUES (?, ?)");
    
    return $stmt->execute([$settingName, $settingValue]);
}

/**
 * Génère un nom de fichier unique pour éviter les collisions
 * @param string $originalName Nom original du fichier
 * @return string Nom de fichier unique
 */
function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Formate la date pour l'affichage
 * @param string $dateString Date au format SQL
 * @return string Date formatée
 */
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('d/m/Y à H:i');
}

/**
 * Vérifie si un fichier est un PDF valide
 * @param string $filePath Chemin vers le fichier
 * @return bool True si c'est un PDF valide
 */
function isValidPdf($filePath) {
    // Vérifier l'extension
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        return false;
    }
    
    // Vérifier la signature du fichier (les 4 premiers octets d'un PDF sont %PDF)
    $handle = fopen($filePath, 'rb');
    if (!$handle) {
        return false;
    }
    
    $header = fread($handle, 4);
    fclose($handle);
    
    return $header === '%PDF';
}

/**
 * Génère un export CSV des PDFs
 * @return string Chemin vers le fichier CSV généré
 */
function generateCsvExport() {
    $pdfs = getAllPdfs();
    $csvPath = __DIR__ . '/../data/pdf_export_' . date('Y-m-d_H-i-s') . '.csv';
    
    $fp = fopen($csvPath, 'w');
    
    // En-têtes CSV
    fputcsv($fp, ['ID', 'Titre', 'Fichier', 'Auteur', 'Tags', 'Catégorie', 'Description', 'Date d\'ajout']);
    
    // Données
    foreach ($pdfs as $pdf) {
        fputcsv($fp, [
            $pdf['id'],
            $pdf['title'],
            $pdf['file_name'],
            $pdf['author'],
            $pdf['tags'],
            $pdf['category'],
            $pdf['description'],
            $pdf['uploaded_at']
        ]);
    }
    
    fclose($fp);
    return $csvPath;
}

/**
 * Génère un export JSON des PDFs
 * @return string Chemin vers le fichier JSON généré
 */
function generateJsonExport() {
    $pdfs = getAllPdfs();
    $jsonPath = __DIR__ . '/../data/pdf_export_' . date('Y-m-d_H-i-s') . '.json';
    
    file_put_contents($jsonPath, json_encode($pdfs, JSON_PRETTY_PRINT));
    
    return $jsonPath;
}

/**
 * Crée une archive ZIP de tous les PDFs
 * @return string|false Chemin vers l'archive ZIP ou false en cas d'erreur
 */
function createPdfBackup() {
    // Vérifier si l'extension ZIP est disponible
    if (!class_exists('ZipArchive')) {
        // Alternative sans ZipArchive - créer un dossier de sauvegarde
        $backupDir = __DIR__ . '/../data/pdf_backup_' . date('Y-m-d_H-i-s');
        if (!is_dir(__DIR__ . '/../data')) {
            mkdir(__DIR__ . '/../data', 0755, true);
        }
        
        if (!mkdir($backupDir, 0755, true)) {
            return false;
        }
        
        $pdfs = getAllPdfs();
        $copiedFiles = 0;
        
        foreach ($pdfs as $pdf) {
            $filePath = __DIR__ . '/../uploads/' . $pdf['file_name'];
            if (file_exists($filePath)) {
                $destFileName = $pdf['id'] . '_' . sanitizeFileName($pdf['title']) . '.pdf';
                $destPath = $backupDir . '/' . $destFileName;
                if (copy($filePath, $destPath)) {
                    $copiedFiles++;
                }
            }
        }
        
        // Ajouter un fichier JSON avec les métadonnées
        $metadataPath = $backupDir . '/metadata.json';
        file_put_contents($metadataPath, json_encode($pdfs, JSON_PRETTY_PRINT));
        
        return $backupDir;
    }
    
    // Utiliser ZipArchive si disponible
    $pdfs = getAllPdfs();
    
    // S'assurer que le répertoire data existe
    if (!is_dir(__DIR__ . '/../data')) {
        mkdir(__DIR__ . '/../data', 0755, true);
    }
    
    $zipPath = __DIR__ . '/../data/pdf_backup_' . date('Y-m-d_H-i-s') . '.zip';
    
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
        return false;
    }
    
    foreach ($pdfs as $pdf) {
        $filePath = __DIR__ . '/../uploads/' . $pdf['file_name'];
        if (file_exists($filePath)) {
            // Ajouter le fichier à l'archive avec un nom plus lisible
            $zipFileName = $pdf['id'] . '_' . sanitizeFileName($pdf['title']) . '.pdf';
            $zip->addFile($filePath, $zipFileName);
        }
    }
    
    // Ajouter un fichier JSON avec les métadonnées
    $metadataPath = __DIR__ . '/../data/metadata_temp.json';
    file_put_contents($metadataPath, json_encode($pdfs, JSON_PRETTY_PRINT));
    $zip->addFile($metadataPath, 'metadata.json');
    $zip->close();
    
    // Supprimer le fichier temporaire
    if (file_exists($metadataPath)) {
        unlink($metadataPath);
    }
    
    return $zipPath;
}

/**
 * Nettoie un nom de fichier pour le rendre sûr
 * @param string $fileName Nom de fichier à nettoyer
 * @return string Nom de fichier nettoyé
 */
function sanitizeFileName($fileName) {
    // Remplacer les caractères spéciaux et espaces
    $fileName = preg_replace('/[^\w\-\.]/', '_', $fileName);
    // Limiter la longueur
    return substr($fileName, 0, 50);
}

/**
 * Récupère tous les cours disponibles
 * @return array Liste des cours
 */
function getAllCourses() {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT * FROM courses ORDER BY name");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Erreur lors de la récupération des cours: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère un cours par son ID
 * @param int $id ID du cours
 * @return array|false Données du cours ou false si non trouvé
 */
function getCourseById($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Ajoute un nouveau cours
 * @param string $name Nom du cours
 * @param string $description Description du cours
 * @return int|false ID du cours ajouté ou false en cas d'erreur
 */
function addCourse($name, $description = '') {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("INSERT INTO courses (name, description) VALUES (?, ?)");
    $result = $stmt->execute([$name, $description]);
    return $result ? $pdo->lastInsertId() : false;
}

/**
 * Met à jour un cours existant
 * @param int $id ID du cours
 * @param string $name Nouveau nom
 * @param string $description Nouvelle description
 * @return bool Succès de la mise à jour
 */
function updateCourse($id, $name, $description = '') {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("UPDATE courses SET name = ?, description = ? WHERE id = ?");
    return $stmt->execute([$name, $description, $id]);
}

/**
 * Supprime un cours
 * @param int $id ID du cours à supprimer
 * @return bool Succès de la suppression
 */
function deleteCourse($id) {
    $pdo = getDbConnection();
    
    // Mettre à null le course_id des PDFs associés
    $stmt = $pdo->prepare("UPDATE pdfs SET course_id = NULL WHERE course_id = ?");
    $stmt->execute([$id]);
    
    // Supprimer le cours
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Récupère les PDFs associés à un cours spécifique
 * @param int $courseId ID du cours
 * @return array Liste des PDFs du cours
 */
function getPdfsByCourse($courseId) {
    $pdo = getDbConnection();
    
    // Récupérer les informations du cours
    $course = getCourseById($courseId);
    if (!$course) {
        return [];
    }
    
    // Nous savons que la colonne course_id existe dans la table pdfs
    // Rechercher les PDFs qui ont le course_id correspondant
    $stmt = $pdo->prepare("SELECT * FROM pdfs WHERE course_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$courseId]);
    
    return $stmt->fetchAll();
}

/**
 * Récupère tous les PDFs associés à un cours spécifique avec options de tri
 * @param int $courseId ID du cours
 * @param string $orderBy Champ pour le tri (title, uploaded_at, author, category)
 * @param string $order Direction du tri (ASC ou DESC)
 * @return array Liste des PDFs du cours
 */
function getPdfsByCourseWithOrder($courseId, $orderBy, $order)
{
    $pdo = getDbConnection();
    // Validate order_by and order direction to prevent SQL injection
    $allowed_columns = ['title', 'uploaded_at', 'block_year'];
    $allowed_orders = ['ASC', 'DESC'];
    if (!in_array($orderBy, $allowed_columns) || !in_array($order, $allowed_orders)) {
        // Default sorting
        $orderBy = 'uploaded_at';
        $order = 'DESC';
    }
    $stmt = $pdo->prepare("SELECT * FROM pdfs WHERE course_id = ? ORDER BY $orderBy $order");
    $stmt->execute([$courseId]);
    return $stmt->fetchAll();
}

/**
 * Récupère tous les cours avec le nombre de PDF associés.
 *
 * @return array
 */
function getAllCoursesWithPdfCount()
{
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT c.id, c.name, COUNT(p.id) as pdf_count FROM courses c LEFT JOIN pdfs p ON c.id = p.course_id GROUP BY c.id, c.name ORDER BY c.name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les statistiques pour le tableau de bord.
 *
 * @return array
 */
function getDashboardStats()
{
    $pdo = getDbConnection();
    $stats = [];
    $stats['totalPdfs'] = (int)$pdo->query("SELECT COUNT(*) FROM pdfs")->fetchColumn();
    $stats['totalCategories'] = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $stats['totalCourses'] = (int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    $stats['totalTags'] = (int)$pdo->query("SELECT COUNT(DISTINCT tags) FROM pdfs WHERE tags IS NOT NULL AND tags != ''")->fetchColumn(); // Approximation
    return $stats;
}
