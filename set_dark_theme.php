<?php
/**
 * Script pour définir le thème sombre comme thème par défaut
 */
require_once 'includes/functions.php';

// Mettre à jour le paramètre de thème à "dark"
$result = updateSetting('theme', 'dark');

if ($result) {
    echo '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background-color: #343a40; color: #fff; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">';
    echo '<h1 style="color: #17a2b8;">Thème mis à jour avec succès</h1>';
    echo '<p>Le thème sombre est maintenant défini comme thème par défaut pour tout le site.</p>';
    echo '<p>Vous pouvez maintenant <a href="index.php" style="color: #17a2b8; text-decoration: none;">retourner à l\'accueil</a>.</p>';
    echo '</div>';
} else {
    echo '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background-color: #721c24; color: #f8d7da; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">';
    echo '<h1>Erreur</h1>';
    echo '<p>Une erreur est survenue lors de la mise à jour du thème.</p>';
    echo '<p>Vous pouvez <a href="index.php" style="color: #f8d7da; text-decoration: underline;">retourner à l\'accueil</a>.</p>';
    echo '</div>';
}
?>
