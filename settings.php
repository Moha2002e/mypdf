<?php
/**
 * Page de paramètres de l'application
 */
require_once 'includes/functions.php';

// Traitement des requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Mise à jour du thème
    if ($action === 'update_theme' && isset($_POST['theme'])) {
        $theme = $_POST['theme'];
        if ($theme === 'light' || $theme === 'dark') {
            updateSetting('theme', $theme);
            echo json_encode(['success' => true]);
            exit;
        }
    }
    
    // Mise à jour du mode d'affichage
    if ($action === 'update_view_mode' && isset($_POST['view_mode'])) {
        $viewMode = $_POST['view_mode'];
        if ($viewMode === 'grid' || $viewMode === 'list') {
            updateSetting('view_mode', $viewMode);
            echo json_encode(['success' => true]);
            exit;
        }
    }
    
    // Mise à jour du nombre d'éléments par page
    if ($action === 'update_items_per_page' && isset($_POST['items_per_page'])) {
        $itemsPerPage = (int)$_POST['items_per_page'];
        if ($itemsPerPage > 0 && $itemsPerPage <= 100) {
            updateSetting('items_per_page', (string)$itemsPerPage);
            $message = 'Le nombre d\'éléments par page a été mis à jour.';
            $messageType = 'success';
        }
    }
}

// Récupérer les paramètres actuels
$theme = getSetting('theme', 'light');
$viewMode = getSetting('view_mode', 'grid');
$itemsPerPage = (int)getSetting('items_per_page', '12');

// Récupérer les données pour la page
try {
    $courses = getAllCoursesWithPdfCount();
    $stats = getDashboardStats();
    $totalPdfs = $stats['totalPdfs'];
    $totalCategories = $stats['totalCategories'];
    $totalCourses = $stats['totalCourses'];
    $totalTags = $stats['totalTags'];
} catch (Exception $e) {
    $courses = [];
    $totalPdfs = 0;
    $totalCategories = 0;
    $totalCourses = 0;
    $totalTags = 0;
    // Gérer ou logger l'erreur si nécessaire
}

// Message de succès ou d'erreur
$message = $message ?? '';
$messageType = $messageType ?? '';

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4 animate-on-load">
                <i class="bi bi-gear me-2 text-primary animate-float"></i>
                Paramètres
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Paramètres d'affichage -->
            <div class="card mb-4 animate-on-load animate-delay-1">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-display me-2"></i>Paramètres d'affichage
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Thème</label>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary <?php echo $theme === 'light' ? 'active' : ''; ?>" 
                                        onclick="setTheme('light')">
                                    <i class="bi bi-sun me-1"></i>Clair
                                </button>
                                <button class="btn btn-outline-primary <?php echo $theme === 'dark' ? 'active' : ''; ?>" 
                                        onclick="setTheme('dark')">
                                    <i class="bi bi-moon me-1"></i>Sombre
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mode d'affichage par défaut</label>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary <?php echo $viewMode === 'grid' ? 'active' : ''; ?>" 
                                        onclick="setViewMode('grid')">
                                    <i class="bi bi-grid me-1"></i>Grille
                                </button>
                                <button class="btn btn-outline-secondary <?php echo $viewMode === 'list' ? 'active' : ''; ?>" 
                                        onclick="setViewMode('list')">
                                    <i class="bi bi-list me-1"></i>Liste
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gestion des cours -->
            <div class="card mb-4 animate-on-load animate-delay-2">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-book me-2"></i>Gestion des cours
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <form action="settings.php" method="POST" class="row g-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="course_name" placeholder="Nom du cours" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="add_course" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle me-1"></i>Ajouter
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom du cours</th>
                                    <th>PDFs associés</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['name']); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $course['pdf_count']; ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="course_pdfs.php?id=<?php echo $course['id']; ?>" 
                                               class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="deleteCourse(<?php echo $course['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card mb-4 animate-on-load animate-delay-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Statistiques
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="display-6 text-primary counter"><?php echo $totalPdfs; ?></div>
                                <div class="text-muted">PDFs au total</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="display-6 text-success counter"><?php echo $totalCategories; ?></div>
                                <div class="text-muted">Catégories</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="display-6 text-info counter"><?php echo $totalCourses; ?></div>
                                <div class="text-muted">Cours</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="display-6 text-warning counter"><?php echo $totalTags; ?></div>
                                <div class="text-muted">Tags</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3 animate-on-load animate-delay-4">
                <h6>Mise à jour de la base de données</h6>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Si vous venez de mettre à jour MyPDF, vous devrez peut-être mettre à jour la structure de votre base de données.
                </div>
                <a href="update_database_structure.php" class="btn btn-warning">
                    <i class="bi bi-database-gear me-1"></i>Mettre à jour la structure
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Fonction pour changer le thème
    function setTheme(theme) {
        // Mettre à jour l'attribut data-bs-theme
        document.documentElement.setAttribute('data-bs-theme', theme);
        
        // Mettre à jour le bouton actif
        document.querySelectorAll('.btn-outline-primary').forEach(btn => btn.classList.remove('active'));
        document.querySelector(`button[onclick="setTheme('${theme}')"]`).classList.add('active');
        
        // Enregistrer la préférence via AJAX
        fetch('settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_theme&theme=${theme}`
        });
    }

    // Gestion du changement de mode d'affichage
    document.querySelectorAll('input[name="view_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const viewMode = this.value;
            
            // Enregistrer la préférence via AJAX
            fetch('settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update_view_mode&view_mode=' + viewMode
            });
        });
    });
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>
