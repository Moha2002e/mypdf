<?php
/**
 * Script de streaming de fichiers musicaux
 * Permet de diffuser des fichiers audio en toute sécurité
 */

// Vérifier si un fichier est spécifié
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header('HTTP/1.0 400 Bad Request');
    exit('Aucun fichier spécifié');
}

// Décoder le chemin du fichier
$filePath = base64_decode($_GET['file']);

// Vérifier si le fichier existe et est lisible
if (!file_exists($filePath) || !is_readable($filePath)) {
    header('HTTP/1.0 404 Not Found');
    exit('Fichier non trouvé ou inaccessible');
}

// Vérifier si c'est un fichier audio
$allowedExtensions = ['mp3', 'ogg', 'wav', 'm4a', 'flac'];
$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions)) {
    header('HTTP/1.0 403 Forbidden');
    exit('Type de fichier non autorisé');
}

// Définir les en-têtes MIME appropriés selon l'extension
$contentTypes = [
    'mp3' => 'audio/mpeg',
    'ogg' => 'audio/ogg',
    'wav' => 'audio/wav',
    'm4a' => 'audio/mp4',
    'flac' => 'audio/flac'
];

$contentType = $contentTypes[$extension] ?? 'application/octet-stream';

// Définir les en-têtes pour le streaming
header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
header('Accept-Ranges: bytes');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Lire et envoyer le fichier
readfile($filePath);
exit;
?>
