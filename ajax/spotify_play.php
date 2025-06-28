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

// Ru00e9cupu00e9rer les donnu00e9es POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'Donnu00e9es invalides']);
    exit;
}

// Vu00e9rifier si l'ID de l'appareil est fourni
if (!isset($data['device_id'])) {
    echo json_encode(['error' => 'ID d\'appareil requis']);
    exit;
}

// Pru00e9parer les donnu00e9es pour l'API Spotify
$playData = [
    'device_id' => $data['device_id']
];

// Ajouter l'URI de la piste ou le contexte URI (playlist, album)
if (isset($data['uri'])) {
    $playData['uris'] = [$data['uri']];
} elseif (isset($data['context_uri'])) {
    $playData['context_uri'] = $data['context_uri'];
} else {
    echo json_encode(['error' => 'URI de piste ou contexte URI requis']);
    exit;
}

// Du00e9marrer la lecture
try {
    $response = spotifyApiRequest('me/player/play', 'PUT', $playData);
    
    // Si la ru00e9ponse est vide, c'est un succu00e8s (code 204)
    if (empty($response)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode($response);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
