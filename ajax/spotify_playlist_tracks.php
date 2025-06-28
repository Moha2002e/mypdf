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

// Récupérer l'ID de la playlist
$playlistId = isset($_GET['playlist_id']) ? $_GET['playlist_id'] : '';

if (empty($playlistId)) {
    echo json_encode(['error' => 'ID de playlist requis']);
    exit;
}

// Récupérer les pistes de la playlist
try {
    $playlistTracks = getPlaylistTracks($playlistId);
    echo json_encode($playlistTracks);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
