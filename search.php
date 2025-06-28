<?php
/**
 * Page de recherche de PDFs
 */
require_once 'includes/functions.php';

// Récupérer le terme de recherche
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Récupérer le mode d'affichage (grille ou liste)
$viewMode = getSetting('view_mode', 'grid');

// Effectuer la recherche si un terme est fourni
$pdfs = [];
if (!empty($query)) {
    $pdfs = searchPdfs($query);
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-search me-2"></i>Recherche</h1>
        <hr>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="search.php" method="GET">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" name="q" placeholder="Rechercher par titre, auteur, tag, catégorie..." 
                               value="<?php echo htmlspecialchars($query); ?>" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search me-1"></i>Rechercher
                        </button>
                    </div>
                    <div class="form-text">
                        Entrez un terme de recherche pour trouver des PDFs par titre, auteur, tags, catégorie ou description.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($query)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>
                    Résultats pour "<?php echo htmlspecialchars($query); ?>"
                    <span class="badge bg-primary"><?php echo count($pdfs); ?> résultat(s)</span>
                </h2>
                <div class="btn-group" role="group">
                    <a href="#" id="gridViewBtn" class="btn btn-outline-secondary <?php echo $viewMode === 'grid' ? 'active' : ''; ?>">
                        <i class="bi bi-grid"></i> Grille
                    </a>
                    <a href="#" id="listViewBtn" class="btn btn-outline-secondary <?php echo $viewMode === 'list' ? 'active' : ''; ?>">
                        <i class="bi bi-list"></i> Liste
                    </a>
                </div>
            </div>
            <hr>
        </div>
    </div>

    <?php if (empty($pdfs)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Aucun PDF ne correspond à votre recherche. Essayez avec d'autres termes ou <a href="upload_pdf.php" class="alert-link">ajoutez un nouveau PDF</a>.
        </div>
    <?php else: ?>
        <?php if ($viewMode === 'grid'): ?>
            <!-- Affichage en grille -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($pdfs as $pdf): ?>
                    <div class="col">
                        <div class="card pdf-card h-100">
                            <div class="card-img-top">
                                <i class="bi bi-file-earmark-pdf pdf-icon"></i>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title" title="<?php echo htmlspecialchars($pdf['title']); ?>">
                                    <?php echo htmlspecialchars($pdf['title']); ?>
                                </h5>
                                
                                <?php if (!empty($pdf['author'])): ?>
                                    <p class="card-text text-muted">
                                        <i class="bi bi-person me-1"></i>
                                        <?php echo htmlspecialchars($pdf['author']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($pdf['category'])): ?>
                                    <p class="card-text">
                                        <a href="list_pdfs.php?filter_type=category&filter_value=<?php echo urlencode($pdf['category']); ?>" 
                                           class="badge bg-info category-badge">
                                            <i class="bi bi-folder me-1"></i>
                                            <?php echo htmlspecialchars($pdf['category']); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($pdf['tags'])): ?>
                                    <p class="card-text">
                                        <?php foreach (explode(',', $pdf['tags']) as $tag): ?>
                                            <?php if (trim($tag)): ?>
                                                <a href="list_pdfs.php?filter_type=tag&filter_value=<?php echo urlencode(trim($tag)); ?>" 
                                                   class="badge bg-secondary tag-badge">
                                                    <?php echo htmlspecialchars(trim($tag)); ?>
                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($pdf['description'])): ?>
                                    <p class="card-text">
                                        <?php 
                                        $desc = htmlspecialchars($pdf['description']);
                                        echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;
                                        ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        Ajouté le <?php echo formatDate($pdf['uploaded_at']); ?>
                                    </small>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between">
                                    <a href="view_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye me-1"></i>Voir
                                    </a>
                                    <div>
                                        <a href="edit_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-sm btn-outline-danger delete-confirm">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Affichage en liste -->
            <div class="card">
                <div class="list-group list-group-flush">
                    <?php foreach ($pdfs as $pdf): ?>
                        <div class="list-group-item pdf-list-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="bi bi-file-earmark-pdf pdf-icon"></i>
                                </div>
                                <div class="col-md-7">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($pdf['title']); ?></h5>
                                    <div class="d-flex flex-wrap mb-1">
                                        <?php if (!empty($pdf['author'])): ?>
                                            <div class="me-3">
                                                <i class="bi bi-person me-1"></i>
                                                <?php echo htmlspecialchars($pdf['author']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <i class="bi bi-calendar me-1"></i>
                                            <?php echo formatDate($pdf['uploaded_at']); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <?php if (!empty($pdf['category'])): ?>
                                            <a href="list_pdfs.php?filter_type=category&filter_value=<?php echo urlencode($pdf['category']); ?>" 
                                               class="badge bg-info category-badge me-1">
                                                <i class="bi bi-folder me-1"></i>
                                                <?php echo htmlspecialchars($pdf['category']); ?>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($pdf['tags'])): ?>
                                            <?php foreach (explode(',', $pdf['tags']) as $tag): ?>
                                                <?php if (trim($tag)): ?>
                                                    <a href="list_pdfs.php?filter_type=tag&filter_value=<?php echo urlencode(trim($tag)); ?>" 
                                                       class="badge bg-secondary tag-badge me-1">
                                                        <?php echo htmlspecialchars(trim($tag)); ?>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($pdf['description'])): ?>
                                        <div class="mt-1 text-muted small">
                                            <?php 
                                            $desc = htmlspecialchars($pdf['description']);
                                            echo strlen($desc) > 150 ? substr($desc, 0, 150) . '...' : $desc;
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="view_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye me-1"></i>Voir
                                    </a>
                                    <a href="edit_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-sm btn-outline-danger delete-confirm">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<script>
    // Gestion du changement de vue (grille/liste)
    document.getElementById('gridViewBtn').addEventListener('click', function(e) {
        e.preventDefault();
        
        // Enregistrer la préférence via AJAX
        fetch('settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=update_view_mode&view_mode=grid'
        })
        .then(response => {
            if (response.ok) {
                // Recharger la page pour appliquer le changement
                location.reload();
            }
        });
    });
    
    document.getElementById('listViewBtn').addEventListener('click', function(e) {
        e.preventDefault();
        
        // Enregistrer la préférence via AJAX
        fetch('settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=update_view_mode&view_mode=list'
        })
        .then(response => {
            if (response.ok) {
                // Recharger la page pour appliquer le changement
                location.reload();
            }
        });
    });
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>
