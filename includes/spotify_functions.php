<?php
require_once __DIR__ . '/../config/spotify_config.php';

/**
 * Génère l'URL d'autorisation Spotify
 */
function getSpotifyAuthUrl() {
    $scopes = [
        'user-read-private',
        'user-read-email',
        'user-read-playback-state',
        'user-modify-playback-state',
        'streaming',
        'user-library-read'
    ];
    
    $params = [
        'client_id' => SPOTIFY_CLIENT_ID,
        'response_type' => 'code',
        'redirect_uri' => SPOTIFY_REDIRECT_URI,
        'scope' => implode(' ', $scopes),
        'state' => bin2hex(random_bytes(16)) // Pour la sécurité
    ];
    
    return 'https://accounts.spotify.com/authorize?' . http_build_query($params);
}

/**
 * Vérifie si le token est expiré et le rafraîchit si nécessaire
 */
function checkAndRefreshToken() {
    // Si pas de token ou pas de refresh token, retourner false
    if (!isset($_SESSION['spotify_access_token']) || !isset($_SESSION['spotify_refresh_token'])) {
        return false;
    }
    
    // Si le token n'est pas expiré, retourner true
    if (isset($_SESSION['spotify_token_expires']) && $_SESSION['spotify_token_expires'] > time()) {
        return true;
    }
    
    // Sinon, rafraîchir le token
    return refreshSpotifyToken();
}

/**
 * Rafraîchit le token d'accès si nécessaire
 */
function refreshSpotifyToken() {
    if (!isset($_SESSION['spotify_refresh_token'])) {
        return false;
    }
    
    // Vérifier si le token a expiré
    if (isset($_SESSION['spotify_token_expires']) && $_SESSION['spotify_token_expires'] > time()) {
        return true; // Token toujours valide
    }
    
    // Rafraîchir le token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'refresh_token',
        'refresh_token' => $_SESSION['spotify_refresh_token'],
        'client_id' => SPOTIFY_CLIENT_ID,
        'client_secret' => SPOTIFY_CLIENT_SECRET
    ]));
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    $token_data = json_decode($result, true);
    
    if (isset($token_data['access_token'])) {
        $_SESSION['spotify_access_token'] = $token_data['access_token'];
        $_SESSION['spotify_token_expires'] = time() + $token_data['expires_in'];
        return true;
    }
    
    return false;
}

/**
 * Effectue une requête à l'API Spotify
 */
function spotifyApiRequest($endpoint, $method = 'GET', $data = null) {
    if (!isset($_SESSION['spotify_access_token'])) {
        return ['error' => 'Non connecté à Spotify'];
    }
    
    // Rafraîchir le token si nécessaire
    if (!checkAndRefreshToken()) {
        return ['error' => 'Impossible de rafraîchir le token Spotify'];
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.spotify.com/v1/' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $_SESSION['spotify_access_token'],
        'Content-Type: application/json'
    ]);
    
    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $response = json_decode($result, true);
    
    if ($http_code >= 400) {
        return ['error' => isset($response['error']['message']) ? $response['error']['message'] : 'Erreur API Spotify'];
    }
    
    return $response;
}

/**
 * Recherche des pistes sur Spotify
 */
function searchSpotifyTracks($query, $limit = 10) {
    $endpoint = 'search?q=' . urlencode($query) . '&type=track&limit=' . $limit;
    return spotifyApiRequest($endpoint);
}

/**
 * Obtient les playlists de l'utilisateur
 */
function getUserPlaylists($limit = 20) {
    $endpoint = 'me/playlists?limit=' . $limit;
    return spotifyApiRequest($endpoint);
}

/**
 * Obtient les pistes d'une playlist
 */
function getPlaylistTracks($playlist_id, $limit = 50) {
    $endpoint = 'playlists/' . $playlist_id . '/tracks?limit=' . $limit;
    return spotifyApiRequest($endpoint);
}

/**
 * Vérifie si l'utilisateur est connecté à Spotify
 */
function isSpotifyConnected() {
    return isset($_SESSION['spotify_access_token']);
}
?>
