<?php
/**
 * Page de liste de tous les PDFs
 */
require_once 'includes/functions.php';

// Récupérer les paramètres de filtrage et de tri
$orderBy = isset($_GET['order_by']) ? $_GET['order_by'] : 'uploaded_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Filtrage par catégorie, tag ou cours
$filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filterValue = isset($_GET['filter_value']) ? $_GET['filter_value'] : '';

// Recherche par texte
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Récupérer le mode d'affichage (grille ou liste)
$viewMode = getSetting('view_mode', 'grid');

// Récupérer les PDFs selon les filtres
if (!empty($filterType) && !empty($filterValue)) {
    $pdo = getDbConnection();
    
    if ($filterType === 'category') {
        $stmt = $pdo->prepare("SELECT * FROM pdfs WHERE category = ? ORDER BY $orderBy $order");
        $stmt->execute([$filterValue]);
        $pdfs = $stmt->fetchAll();
        $filterTitle = "Catégorie : " . htmlspecialchars($filterValue);
    } elseif ($filterType === 'tag') {
        $stmt = $pdo->prepare("SELECT * FROM pdfs WHERE tags LIKE ? ORDER BY $orderBy $order");
        $stmt->execute(['%' . $filterValue . '%']);
        $pdfs = $stmt->fetchAll();
        $filterTitle = "Tag : " . htmlspecialchars($filterValue);
    } elseif ($filterType === 'course') {
        // Filtrage par cours
        $courseId = (int)$filterValue;
        $course = getCourseById($courseId);
        $pdfs = getPdfsByCourseWithOrder($courseId, $orderBy, $order);
        $filterTitle = "Cours : " . htmlspecialchars($course['name']);
    } elseif ($filterType === 'block_year') {
        // Filtrage par année de bloc
        $blockYear = (int)$filterValue;
        $stmt = $pdo->prepare("SELECT * FROM pdfs WHERE block_year = ? ORDER BY $orderBy $order");
        $stmt->execute([$blockYear]);
        $pdfs = $stmt->fetchAll();
        $filterTitle = "Bloc : " . $blockYear;
    } else {
        // Filtre non reconnu, afficher tous les PDFs
        $pdfs = getAllPdfs($orderBy, $order);
        $filterTitle = "";
    }
} elseif (!empty($search)) {
    // Recherche par texte
    $pdfs = searchPdfs($search, $orderBy, $order);
    $filterTitle = "Résultats de la recherche pour : " . htmlspecialchars($search);
} else {
    // Pas de filtre, afficher tous les PDFs
    $pdfs = getAllPdfs($orderBy, $order);
    $filterTitle = "";
}

// Récupérer toutes les catégories et tags pour les filtres
$categories = getAllCategories();
$tags = getAllTags();
$courses = getAllCourses();

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
                <div class="animate-on-load">
                    <h1 class="mb-2">
                        <i class="bi bi-list-ul me-2 text-primary animate-float"></i>
                        Tous les PDFs
                    </h1>
                    <p class="text-muted mb-0">
                        <?php echo count($pdfs); ?> document<?php echo count($pdfs) > 1 ? 's' : ''; ?> trouvé<?php echo count($pdfs) > 1 ? 's' : ''; ?>
                    </p>
                </div>
                <div class="d-flex gap-2 animate-on-load animate-delay-1">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary <?php echo $viewMode === 'grid' ? 'active' : ''; ?>" 
                                onclick="setViewMode('grid')">
                            <i class="bi bi-grid"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary <?php echo $viewMode === 'list' ? 'active' : ''; ?>" 
                                onclick="setViewMode('list')">
                            <i class="bi bi-list"></i>
                        </button>
                    </div>
                    <a href="upload_pdf.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Ajouter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($filterTitle)): ?>
    <div class="row mb-4">
        <div class="col-12 animate-on-load animate-delay-2">
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-funnel me-2"></i>
                    <strong>Filtre actif :</strong> <?php echo $filterTitle; ?>
                </div>
                <a href="list_pdfs.php" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-x-circle me-1"></i>Effacer
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Sidebar avec filtres -->
        <div class="col-lg-3 mb-4 animate-on-load animate-delay-2">
            <div class="card theme-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel me-2"></i>Filtres
                    </h5>
                    <a href="add_course.php" class="btn btn-sm btn-outline-primary" title="Ajouter un cours">
                        <i class="bi bi-plus"></i>
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filtre par catégorie -->
                    <div class="mb-3">
                        <label class="form-label">Catégories</label>
                        <div class="d-flex flex-wrap gap-1">
                            <?php if (empty($categories)): ?>
                                <span class="text-muted">Aucune catégorie disponible</span>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                    <a href="list_pdfs.php?filter_type=category&filter_value=<?php echo urlencode($category); ?>" 
                                       class="badge bg-secondary category-badge hover-scale">
                                        <i class="bi bi-folder me-1"></i>
                                        <?php echo htmlspecialchars($category); ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Filtre par cours -->
                    <div class="mb-2">
                        <label class="form-label">Cours</label>
                        <div class="d-flex flex-wrap gap-1">
                            <?php if (empty($courses)): ?>
                                <span class="text-muted">Aucun cours disponible</span>
                            <?php else: ?>
                                <?php foreach ($courses as $course): ?>
                                    <a href="list_pdfs.php?filter_type=course&filter_value=<?php echo $course['id']; ?>" 
                                       class="badge course-badge hover-scale">
                                        <i class="bi bi-book me-1"></i>
                                        <?php echo htmlspecialchars($course['name']); ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Filtre par année de bloc -->
                    <div class="mb-3">
                        <label class="form-label">Année de bloc</label>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="list_pdfs.php?filter_type=block_year&filter_value=1" 
                               class="badge bg-success hover-scale">
                                <i class="bi bi-calendar-event me-1"></i>Bloc 1
                            </a>
                            <a href="list_pdfs.php?filter_type=block_year&filter_value=2" 
                               class="badge bg-success hover-scale">
                                <i class="bi bi-calendar-event me-1"></i>Bloc 2
                            </a>
                            <a href="list_pdfs.php?filter_type=block_year&filter_value=3" 
                               class="badge bg-success hover-scale">
                                <i class="bi bi-calendar-event me-1"></i>Bloc 3
                            </a>
                        </div>
                    </div>

                    <!-- Tri -->
                    <div class="mb-3">
                        <label for="sortSelect" class="form-label">Trier par</label>
                        <select class="form-select" id="sortSelect" onchange="sortPdfs(this.value)">
                            <option value="title_asc" <?php echo $orderBy === 'title' && $order === 'ASC' ? 'selected' : ''; ?>>Titre A-Z</option>
                            <option value="title_desc" <?php echo $orderBy === 'title' && $order === 'DESC' ? 'selected' : ''; ?>>Titre Z-A</option>
                            <option value="uploaded_at_desc" <?php echo $orderBy === 'uploaded_at' && $order === 'DESC' ? 'selected' : ''; ?>>Plus récents</option>
                            <option value="uploaded_at_asc" <?php echo $orderBy === 'uploaded_at' && $order === 'ASC' ? 'selected' : ''; ?>>Plus anciens</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="col-lg-9">
            <?php if (empty($pdfs)): ?>
            <div class="text-center py-5 animate-on-load animate-delay-3">
                <i class="bi bi-file-earmark-pdf display-1 text-muted mb-3"></i>
                <h3>Aucun PDF trouvé</h3>
                <p class="text-muted">Aucun document ne correspond à vos critères de recherche.</p>
                <a href="upload_pdf.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Ajouter un PDF
                </a>
            </div>
            <?php else: ?>
                <?php if ($viewMode === 'grid'): ?>
                <div class="row">
                    <?php foreach ($pdfs as $index => $pdf): ?>
                    <div class="col-lg-4 col-md-6 mb-4 animate-on-scroll" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                        <div class="card pdf-card hover-lift">
                            <div class="card-img-top">
                                <i class="bi bi-file-earmark-pdf-fill pdf-icon"></i>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($pdf['title']); ?></h5>
                                
                                <?php if (isset($pdf['course_id']) && !empty($pdf['course_id'])): ?>
                                    <?php $course = getCourseById($pdf['course_id']); ?>
                                    <?php if ($course): ?>
                                        <p class="card-text">
                                            <a href="list_pdfs.php?filter_type=course&filter_value=<?php echo $course['id']; ?>" 
                                               class="badge course-badge hover-scale">
                                                <i class="bi bi-book me-1"></i>
                                                <?php echo htmlspecialchars($course['name']); ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php
                                    // Récupérer le cours par défaut ou le premier cours disponible
                                    $courses = getAllCourses();
                                    if (!empty($courses)) {
                                        $defaultCourse = $courses[0];
                                        echo '<p class="card-text"><span class="badge bg-secondary">Cours non assigné</span></p>';
                                    }
                                    ?>
                                <?php endif; ?>
                                
                                <?php if (isset($pdf['block_year']) && !empty($pdf['block_year'])): ?>
                                    <p class="card-text">
                                        <span class="badge bg-success hover-scale">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            Bloc <?php echo htmlspecialchars($pdf['block_year']); ?>
                                        </span>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($pdf['category'])): ?>
                                    <p class="card-text">
                                        <span class="badge bg-info category-badge hover-scale">
                                            <i class="bi bi-folder me-1"></i>
                                            <?php echo htmlspecialchars($pdf['category']); ?>
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
                <?php else: ?>
                <div class="animate-on-scroll">
                    <?php foreach ($pdfs as $index => $pdf): ?>
                    <div class="card pdf-list-item mb-3 animate-on-scroll" style="animation-delay: <?php echo $index * 0.05; ?>s;">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="bi bi-file-earmark-pdf-fill pdf-icon"></i>
                                </div>
                                <div class="col">
                                    <h5 class="card-title flex-grow-1"><?php echo htmlspecialchars($pdf['title']); ?></h5>
                                    <div class="mb-2">
                                        <?php if (isset($pdf['course_id']) && !empty($pdf['course_id'])): ?>
                                            <?php $course = getCourseById($pdf['course_id']); ?>
                                            <?php if ($course): ?>
                                                <a href="list_pdfs.php?filter_type=course&filter_value=<?php echo $course['id']; ?>" 
                                                   class="badge me-1 course-badge hover-scale">
                                                   <i class="bi bi-book me-1"></i><?php echo htmlspecialchars($course['name']); ?>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($pdf['category'])): ?>
                                            <span class="badge bg-info category-badge hover-scale">
                                                <i class="bi bi-folder me-1"></i><?php echo htmlspecialchars($pdf['category']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <a href="view_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>Voir
                                        </a>
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
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

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
