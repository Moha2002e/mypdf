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

// Récupérer les playlists de l'utilisateur
try {
    $playlists = getUserPlaylists();
    echo json_encode($playlists);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
