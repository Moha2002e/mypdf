<?php
/**
 * Script de téléchargement de PDF
 * Permet aux utilisateurs de télécharger un PDF spécifique
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];
$pdf = getPdfById($id);

// Vérifier si le PDF existe
if (!$pdf) {
    header('Location: index.php');
    exit;
}

// Chemin vers le fichier PDF
$file_path = 'uploads/' . $pdf['file_name'];

// Vérifier si le fichier existe
if (!file_exists($file_path)) {
    die('Erreur: Le fichier PDF n\'existe pas sur le serveur.');
}

// Définir les en-têtes pour le téléchargement
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $pdf['title'] . '.pdf"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Lire et envoyer le fichier
readfile($file_path);
exit;
?>
