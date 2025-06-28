<?php
/**
 * Page d'accueil - Tableau de bord avec les derniers PDF ajoutés
 */
require_once 'includes/functions.php';

// Récupérer les PDFs récents
$recentPdfs = getRecentPdfs(6);

// Récupérer les statistiques
try {
    $pdo = getDbConnection();
    $totalPdfs = (int)$pdo->query("SELECT COUNT(*) FROM pdfs")->fetchColumn();
    
    // Récupérer les catégories, tags et cours avec vérifications
    $categories = getAllCategories();
    $tags = getAllTags();
    $courses = getAllCourses();
    
    $totalCategories = is_array($categories) ? count($categories) : 0;
    $totalTags = is_array($tags) ? count($tags) : 0;
    $totalCourses = is_array($courses) ? count($courses) : 0;
    $recentPdfsCount = is_array($recentPdfs) ? count($recentPdfs) : 0;
} catch (Exception $e) {
    // En cas d'erreur, utiliser des valeurs par défaut
    error_log("Erreur lors de la récupération des statistiques: " . $e->getMessage());
    $totalPdfs = 0;
    $totalCategories = 0;
    $totalTags = 0;
    $totalCourses = 0;
    $recentPdfsCount = 0;
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center mb-4 animate-on-load">
                <i class="bi bi-file-earmark-pdf-fill me-3 text-danger animate-float"></i>
                Bienvenue dans votre bibliothèque PDF
            </h1>
            <p class="lead text-center mb-5 animate-on-load animate-delay-1">
                Gérez, organisez et accédez facilement à tous vos documents PDF
            </p>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center animate-on-load animate-delay-2">
                <div class="card-body">
                    <div class="stat-icon mb-2">
                        <i class="bi bi-file-earmark-pdf-fill fs-1 text-primary animate-pulse"></i>
                    </div>
                    <div class="stat-value counter"><?php echo $totalPdfs; ?></div>
                    <div class="stat-label">PDFs au total</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center animate-on-load animate-delay-3">
                <div class="card-body">
                    <div class="stat-icon mb-2">
                        <i class="bi bi-folder-fill fs-1 text-success animate-pulse"></i>
                    </div>
                    <div class="stat-value counter"><?php echo $totalCategories; ?></div>
                    <div class="stat-label">Catégories</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center animate-on-load animate-delay-4">
                <div class="card-body">
                    <div class="stat-icon mb-2">
                        <i class="bi bi-book-fill fs-1 text-info animate-pulse"></i>
                    </div>
                    <div class="stat-value counter"><?php echo $totalCourses; ?></div>
                    <div class="stat-label">Cours</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center animate-on-load animate-delay-5">
                <div class="card-body">
                    <div class="stat-icon mb-2">
                        <i class="bi bi-calendar-event-fill fs-1 text-warning animate-pulse"></i>
                    </div>
                    <div class="stat-value counter"><?php echo $recentPdfsCount; ?></div>
                    <div class="stat-label">Ajoutés récemment</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4 animate-on-load animate-delay-2">
                <i class="bi bi-lightning-fill me-2 text-warning"></i>
                Actions rapides
            </h2>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card theme-card text-center hover-lift animate-on-load animate-delay-3">
                <div class="card-body">
                    <i class="bi bi-upload fs-1 text-primary mb-3 animate-float"></i>
                    <h5 class="card-title">Ajouter un PDF</h5>
                    <p class="card-text">Téléchargez et organisez vos nouveaux documents</p>
                    <a href="upload_pdf.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Ajouter
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card theme-card text-center hover-lift animate-on-load animate-delay-4">
                <div class="card-body">
                    <i class="bi bi-search fs-1 text-success mb-3 animate-float"></i>
                    <h5 class="card-title">Rechercher</h5>
                    <p class="card-text">Trouvez rapidement vos documents</p>
                    <a href="search.php" class="btn btn-success">
                        <i class="bi bi-search me-1"></i>Rechercher
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card theme-card text-center hover-lift animate-on-load animate-delay-5">
                <div class="card-body">
                    <i class="bi bi-list-ul fs-1 text-info mb-3 animate-float"></i>
                    <h5 class="card-title">Voir tous les PDFs</h5>
                    <p class="card-text">Parcourez votre collection complète</p>
                    <a href="list_pdfs.php" class="btn btn-info">
                        <i class="bi bi-collection me-1"></i>Voir tout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- PDFs récents -->
    <div class="row">
        <div class="col-12">
            <h2 class="text-center mb-4 animate-on-load animate-delay-3">
                <i class="bi bi-clock-history me-2 text-primary"></i>
                PDFs récents
            </h2>
        </div>
    </div>
    
    <div class="row">
        <?php foreach ($recentPdfs as $index => $pdf): ?>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4 animate-on-scroll" style="animation-delay: <?php echo $index * 0.1; ?>s;">
            <div class="card pdf-card hover-lift">
                <div class="card-img-top">
                    <i class="bi bi-file-earmark-pdf-fill pdf-icon"></i>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($pdf['title']); ?></h5>
                    <p class="card-text text-muted">
                        <small>
                            <i class="bi bi-calendar me-1"></i>
                            <?php echo date('d/m/Y', strtotime($pdf['uploaded_at'])); ?>
                        </small>
                    </p>
                    <?php if (!empty($pdf['category'])): ?>
                        <p class="card-text">
                            <span class="badge bg-info category-badge">
                                <i class="bi bi-folder me-1"></i>
                                <?php echo htmlspecialchars($pdf['category']); ?>
                            </span>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (isset($pdf['block_year']) && !empty($pdf['block_year'])): ?>
                        <p class="card-text">
                            <span class="badge bg-success">
                                <i class="bi bi-calendar-event me-1"></i>
                                Bloc <?php echo htmlspecialchars($pdf['block_year']); ?>
                            </span>
                        </p>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="view_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-eye me-1"></i>Voir
                        </a>
                        <div class="btn-group">
                            <a href="edit_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="delete_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-outline-danger btn-sm" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce PDF ?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($recentPdfs)): ?>
    <div class="row">
        <div class="col-12 text-center animate-on-load animate-delay-4">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Aucun PDF n'a encore été ajouté. 
                <a href="upload_pdf.php" class="alert-link">Commencez par ajouter votre premier document</a>.
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row mt-5">
    <div class="col-12">
        <div class="card theme-card">
            <div class="card-body">
                <h3><i class="bi bi-search me-2"></i>Rechercher un PDF</h3>
                <form action="search.php" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" placeholder="Rechercher par titre, auteur, tag, catégorie...">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search me-1"></i>Rechercher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>
