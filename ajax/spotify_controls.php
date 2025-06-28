<?php
session_start();
require_once '../includes/spotify_functions.php';
require_once '../config/spotify_config.php';

header('Content-Type: application/json');

// Vu00e9rifier si l'utilisateur est connectu00e9 u00e0 Spotify
if (!isSpotifyConnected()) {
    echo json_encode(['error' => 'Non connectu00e9 u00e0 Spotify']);
    exit;
}

// Vu00e9rifier si le token d'accu00e8s est expiru00e9 et le rafrau00eechir si nu00e9cessaire
checkAndRefreshToken();

// Ru00e9cupu00e9rer l'action u00e0 effectuer
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (empty($action)) {
    echo json_encode(['error' => 'Action requise']);
    exit;
}

// Exu00e9cuter l'action demandu00e9e
try {
    switch ($action) {
        case 'play':
            $response = spotifyApiRequest('me/player/play', 'PUT');
            break;
        case 'pause':
            $response = spotifyApiRequest('me/player/pause', 'PUT');
            break;
        case 'next':
            $response = spotifyApiRequest('me/player/next', 'POST');
            break;
        case 'previous':
            $response = spotifyApiRequest('me/player/previous', 'POST');
            break;
        case 'status':
            $response = spotifyApiRequest('me/player');
            break;
        default:
            echo json_encode(['error' => 'Action non reconnue']);
            exit;
    }
    
    // Si la ru00e9ponse est vide, c'est un succu00e8s (code 204)
    if (empty($response)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode($response);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
