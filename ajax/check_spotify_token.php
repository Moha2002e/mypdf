<?php
session_start();
require_once '../includes/spotify_functions.php';
require_once '../config/spotify_config.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté à Spotify
if (!isSpotifyConnected()) {
    echo json_encode(['connected' => false]);
    exit;
}

// Vérifier si le token d'accès est expiré et le rafraîchir si nécessaire
$tokenValid = checkAndRefreshToken();

echo json_encode([
    'connected' => true,
    'valid' => $tokenValid,
    'expires_in' => isset($_SESSION['spotify_token_expires']) ? $_SESSION['spotify_token_expires'] - time() : 0
]);
