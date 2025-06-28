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

// Renvoyer le token d'accès
echo json_encode([
    'access_token' => $_SESSION['spotify_access_token'],
    'expires_in' => $_SESSION['spotify_token_expires'] - time()
]);
