<?php
session_start();
require_once '../includes/spotify_functions.php';
require_once '../config/spotify_config.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté à Spotify
if (!isSpotifyConnected()) {
    echo json_encode(['error' => 'Non connecté à Spotify']);
    exit;
}

// Vérifier si le token d'accès est expiré et le rafraîchir si nécessaire
checkAndRefreshToken();

// Récupérer le terme de recherche
$query = isset($_GET['query']) ? $_GET['query'] : '';

if (empty($query)) {
    echo json_encode(['error' => 'Terme de recherche requis']);
    exit;
}

// Effectuer la recherche de pistes via l'API Spotify
try {
    $searchResults = searchSpotifyTracks($query);
    echo json_encode($searchResults);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
