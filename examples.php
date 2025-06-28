<?php
$pageTitle = "Exemples d'Animations";
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12 mb-5">
            <h1 class="gradient-heading" data-aos="fade-up">Exemples d'Animations et d'Effets</h1>
            <p class="lead" data-aos="fade-up" data-aos-delay="100">Cette page démontre les différentes animations et effets disponibles dans notre application.</p>
        </div>
    </div>

    <!-- Section Notifications -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card" data-aos="fade-up">
                <div class="card-header">
                    <h2 class="h4 mb-0">Système de Notifications</h2>
                </div>
                <div class="card-body">
                    <p>Cliquez sur les boutons ci-dessous pour afficher différents types de notifications :</p>
                    <div class="d-flex flex-wrap gap-2">
                        <button id="notifyInfo" class="btn btn-info">Notification Info</button>
                        <button id="notifySuccess" class="btn btn-success">Notification Succès</button>
                        <button id="notifyWarning" class="btn btn-warning">Notification Avertissement</button>
                        <button id="notifyDanger" class="btn btn-danger">Notification Erreur</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Effet Ripple -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header">
                    <h2 class="h4 mb-0">Effet Ripple sur les Boutons</h2>
                </div>
                <div class="card-body">
                    <p>Tous les boutons de l'application ont maintenant un effet d'onde (ripple) au clic :</p>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary">Bouton Primaire</button>
                        <button class="btn btn-secondary">Bouton Secondaire</button>
                        <button class="btn btn-success">Bouton Succès</button>
                        <button class="btn btn-danger">Bouton Danger</button>
                        <button class="btn btn-warning">Bouton Avertissement</button>
                        <button class="btn btn-info">Bouton Info</button>
                        <button class="btn btn-light">Bouton Light</button>
                        <button class="btn btn-dark">Bouton Dark</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Animations -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header">
                    <h2 class="h4 mb-0">Animations au Défilement</h2>
                </div>
                <div class="card-body">
                    <p>Faites défiler la page pour voir les différentes animations :</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-3 mb-4" data-aos="fade-up">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="bi bi-file-pdf"></i></div>
                                <div class="stat-value counter" data-target="150">0</div>
                                <div class="stat-label">PDFs Téléchargés</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="100">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="bi bi-people"></i></div>
                                <div class="stat-value counter" data-target="1250">0</div>
                                <div class="stat-label">Utilisateurs</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="200">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="bi bi-tag"></i></div>
                                <div class="stat-value counter" data-target="45">0</div>
                                <div class="stat-label">Catégories</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="300">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="bi bi-star"></i></div>
                                <div class="stat-value counter" data-target="4.8">0</div>
                                <div class="stat-label">Note Moyenne</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Badges -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card" data-aos="fade-up" data-aos-delay="300">
                <div class="card-header">
                    <h2 class="h4 mb-0">Badges Animés</h2>
                </div>
                <div class="card-body">
                    <p>Ajoutez des tags et voyez l'animation :</p>
                    
                    <div class="mb-3">
                        <label for="tagInput" class="form-label">Ajouter un tag (appuyez sur Entrée) :</label>
                        <input type="text" class="form-control" id="tagInput" placeholder="Entrez un tag...">
                        <input type="hidden" id="tags" name="tags">
                    </div>
                    
                    <div id="tagContainer" class="tag-cloud mb-3"></div>
                    
                    <div class="mt-3">
                        <p>Tags prédéfinis :</p>
                        <div class="tag-cloud">
                            <span class="badge bg-primary me-1 mb-1 tag-badge">PDF <i class="bi bi-x-circle" role="button"></i></span>
                            <span class="badge bg-secondary me-1 mb-1 tag-badge">Document <i class="bi bi-x-circle" role="button"></i></span>
                            <span class="badge bg-success me-1 mb-1 tag-badge">Facture <i class="bi bi-x-circle" role="button"></i></span>
                            <span class="badge bg-danger me-1 mb-1 tag-badge">Important <i class="bi bi-x-circle" role="button"></i></span>
                            <span class="badge bg-warning me-1 mb-1 tag-badge">Urgent <i class="bi bi-x-circle" role="button"></i></span>
                            <span class="badge bg-info me-1 mb-1 tag-badge">Archive <i class="bi bi-x-circle" role="button"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Titres avec Dégradé -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card" data-aos="fade-up" data-aos-delay="400">
                <div class="card-header">
                    <h2 class="h4 mb-0">Titres avec Dégradé</h2>
                </div>
                <div class="card-body">
                    <h1 class="gradient-heading mb-3">Titre avec Dégradé H1</h1>
                    <h2 class="gradient-heading mb-3">Titre avec Dégradé H2</h2>
                    <h3 class="gradient-heading mb-3">Titre avec Dégradé H3</h3>
                    <h4 class="gradient-heading mb-3">Titre avec Dégradé H4</h4>
                    <h5 class="gradient-heading mb-3">Titre avec Dégradé H5</h5>
                    <h6 class="gradient-heading mb-3">Titre avec Dégradé H6</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Liens Animés -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card" data-aos="fade-up" data-aos-delay="500">
                <div class="card-header">
                    <h2 class="h4 mb-0">Liens avec Animation de Soulignement</h2>
                </div>
                <div class="card-body">
                    <p>Passez votre souris sur ces liens pour voir l'animation :</p>
                    <p>
                        <a href="#" class="animated-underline me-3">Lien avec animation 1</a>
                        <a href="#" class="animated-underline me-3">Lien avec animation 2</a>
                        <a href="#" class="animated-underline me-3">Lien avec animation 3</a>
                        <a href="#" class="animated-underline">Lien avec animation 4</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des notifications d'exemple
    document.getElementById('notifyInfo').addEventListener('click', function() {
        showNotification('Ceci est une notification d\'information', 'info');
    });
    
    document.getElementById('notifySuccess').addEventListener('click', function() {
        showNotification('Opération réussie !', 'success');
    });
    
    document.getElementById('notifyWarning').addEventListener('click', function() {
        showNotification('Attention, cette action pourrait être risquée', 'warning');
    });
    
    document.getElementById('notifyDanger').addEventListener('click', function() {
        showNotification('Une erreur s\'est produite !', 'danger');
    });
    
    // Afficher une notification de bienvenue
    setTimeout(() => {
        showNotification('Bienvenue sur la page d\'exemples !', 'info');
    }, 1000);
});
</script>

<?php include 'includes/footer.php'; ?>
