<?php
require_once 'config/spotify_config.php';
require_once 'includes/header.php';

// Traitement du code d'autorisation reçu de Spotify
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Échange du code contre un token d'accès
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => SPOTIFY_REDIRECT_URI,
        'client_id' => SPOTIFY_CLIENT_ID,
        'client_secret' => SPOTIFY_CLIENT_SECRET
    ]));
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    $token_data = json_decode($result, true);
    
    if (isset($token_data['access_token'])) {
        // Stockage des tokens dans la session
        session_start();
        $_SESSION['spotify_access_token'] = $token_data['access_token'];
        $_SESSION['spotify_refresh_token'] = $token_data['refresh_token'];
        $_SESSION['spotify_token_expires'] = time() + $token_data['expires_in'];
        
        echo '<div class="container mt-5">';
        echo '<div class="alert alert-success">Connexion à Spotify réussie ! Vous pouvez maintenant retourner au visualiseur PDF.</div>';
        echo '<a href="view_pdf.php?id=' . (isset($_SESSION['current_pdf_id']) ? $_SESSION['current_pdf_id'] : '') . '" class="btn btn-primary">Retour au PDF</a>';
        echo '</div>';
    } else {
        echo '<div class="container mt-5">';
        echo '<div class="alert alert-danger">Erreur lors de la connexion à Spotify: ' . (isset($token_data['error_description']) ? $token_data['error_description'] : 'Erreur inconnue') . '</div>';
        echo '<a href="index.php" class="btn btn-primary">Retour à l\'accueil</a>';
        echo '</div>';
    }
} else {
    echo '<div class="container mt-5">';
    echo '<div class="alert alert-danger">Aucun code d\'autorisation reçu de Spotify.</div>';
    echo '<a href="index.php" class="btn btn-primary">Retour à l\'accueil</a>';
    echo '</div>';
}

require_once 'includes/footer.php';
?>
