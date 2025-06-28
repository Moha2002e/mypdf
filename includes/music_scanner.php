<?php
/**
 * Scanner de fichiers musicaux
 * Permet de scanner un dossier pour trouver des fichiers audio
 */

/**
 * Récupère tous les fichiers audio d'un dossier
 * @param string $directory Chemin du dossier à scanner
 * @param array $extensions Extensions de fichiers audio à rechercher
 * @return array Liste des fichiers audio trouvés
 */
function scanMusicDirectory($directory, $extensions = ['mp3', 'ogg', 'wav', 'm4a', 'flac']) {
    $musicFiles = [];
    
    // Vérifier si le dossier existe
    if (!is_dir($directory)) {
        return $musicFiles;
    }
    
    try {
        // Récupérer tous les fichiers du dossier
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            // Ignorer les dossiers
            if ($file->isDir()) {
                continue;
            }
            
            // Vérifier l'extension du fichier
            $extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            if (in_array($extension, $extensions)) {
                // Récupérer le chemin relatif par rapport au dossier de base
                $relativePath = str_replace($directory, '', $file->getPathname());
                $relativePath = ltrim($relativePath, '/\\');
                
                // Ajouter le fichier à la liste
                $musicFiles[] = [
                    'name' => $file->getFilename(),
                    'path' => $relativePath,
                    'full_path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'extension' => $extension,
                    'modified' => $file->getMTime()
                ];
            }
        }
        
        // Trier les fichiers par nom
        usort($musicFiles, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
    } catch (Exception $e) {
        // En cas d'erreur, retourner un tableau vide
        return [];
    }
    
    return $musicFiles;
}

/**
 * Génère une URL sécurisée pour un fichier musical
 * @param string $filePath Chemin complet du fichier
 * @return string URL sécurisée pour accéder au fichier
 */
function generateMusicUrl($filePath) {
    // Encoder le chemin pour éviter les problèmes de caractères spéciaux
    $encodedPath = base64_encode($filePath);
    
    // Générer une URL qui pointe vers le script de streaming
    return 'stream_music.php?file=' . urlencode($encodedPath);
}
?>
