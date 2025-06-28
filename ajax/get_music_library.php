<?php
session_start();
header('Content-Type: application/json');

// Du00e9finir le ru00e9pertoire de la bibliothu00e8que de musique
$musicDir = '../assets/audio/library';

// Vu00e9rifier si le ru00e9pertoire existe
if (!is_dir($musicDir)) {
    echo json_encode([
        'error' => 'Le ru00e9pertoire de musique nu2019existe pas',
        'tracks' => []
    ]);
    exit;
}

// Extensions audio autorisu00e9es
$allowedExtensions = ['mp3', 'ogg', 'wav'];

// Lire le contenu du ru00e9pertoire
$tracks = [];

// Ajouter le fichier de piano existant
$tracks[] = [
    'title' => 'Piano Background',
    'artist' => 'Background Music',
    'path' => 'assets/audio/piano-background.mp3',
    'cover' => 'assets/images/default-cover.jpg'
];

// Parcourir les fichiers du ru00e9pertoire
$files = scandir($musicDir);
foreach ($files as $file) {
    // Ignorer les ru00e9pertoires et les fichiers cachu00e9s
    if ($file === '.' || $file === '..' || substr($file, 0, 1) === '.') {
        continue;
    }
    
    // Vu00e9rifier l'extension du fichier
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        continue;
    }
    
    // Extraire le nom du fichier sans extension
    $filename = pathinfo($file, PATHINFO_FILENAME);
    
    // Essayer d'extraire le titre et l'artiste (format: Artiste - Titre)
    $parts = explode(' - ', $filename, 2);
    
    if (count($parts) === 2) {
        $artist = $parts[0];
        $title = $parts[1];
    } else {
        $artist = 'Unknown Artist';
        $title = $filename;
    }
    
    // Chercher une image de couverture correspondante
    $coverPath = null;
    $possibleCovers = [
        $musicDir . '/' . $filename . '.jpg',
        $musicDir . '/' . $filename . '.png',
        $musicDir . '/' . $filename . '.jpeg'
    ];
    
    foreach ($possibleCovers as $cover) {
        if (file_exists($cover)) {
            $coverPath = str_replace('../', '', $cover);
            break;
        }
    }
    
    // Ajouter la piste u00e0 la liste
    $tracks[] = [
        'title' => $title,
        'artist' => $artist,
        'path' => 'assets/audio/library/' . $file,
        'cover' => $coverPath ?: 'assets/images/default-cover.jpg'
    ];
}

// Retourner la liste des pistes
echo json_encode([
    'tracks' => $tracks
]);
