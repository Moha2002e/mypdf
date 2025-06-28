<?php
/**
 * Page d'affichage des PDFs d'un cours spécifique
 */
require_once 'includes/functions.php';

// Vérifier si l'ID du cours est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$courseId = (int)$_GET['id'];
$course = getCourseById($courseId);

// Vérifier si le cours existe
if (!$course) {
    header('Location: courses.php');
    exit;
}

// Récupérer les PDFs associés à ce cours
$pdfs = getPdfsByCourse($courseId);

// Récupérer les paramètres d'affichage
$viewMode = getSetting('view_mode', 'grid');

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="courses.php">Cours</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($course['name']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>
            <i class="bi bi-book-half me-2 text-primary"></i>
            <?php echo htmlspecialchars($course['name']); ?>
        </h1>
        <?php if (!empty($course['description'])): ?>
            <p class="lead"><?php echo htmlspecialchars($course['description']); ?></p>
        <?php endif; ?>
    </div>
    <div class="col-md-4 text-md-end">
        <div class="btn-group" role="group" aria-label="Mode d'affichage">
            <a href="?id=<?php echo $courseId; ?>&view=grid" class="btn btn-outline-primary <?php echo $viewMode === 'grid' ? 'active' : ''; ?>">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
            <a href="?id=<?php echo $courseId; ?>&view=list" class="btn btn-outline-primary <?php echo $viewMode === 'list' ? 'active' : ''; ?>">
                <i class="bi bi-list-ul"></i>
            </a>
        </div>
    </div>
</div>

<?php if (empty($pdfs)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Aucun PDF n'est associé à ce cours pour le moment. 
        <a href="upload_pdf.php" class="alert-link">Ajoutez un PDF</a> et sélectionnez ce cours.
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
                            
                            <?php if (!empty($pdf['tags'])): ?>
                                <p class="card-text">
                                    <?php foreach (explode(',', $pdf['tags']) as $tag): ?>
                                        <?php if (trim($tag)): ?>
                                            <span class="badge bg-secondary tag-badge">
                                                <?php echo htmlspecialchars(trim($tag)); ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
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
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Catégorie</th>
                        <th>Date d'ajout</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pdfs as $pdf): ?>
                        <tr>
                            <td>
                                <a href="view_pdf.php?id=<?php echo $pdf['id']; ?>" class="text-decoration-none">
                                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                    <?php echo htmlspecialchars($pdf['title']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($pdf['author']); ?></td>
                            <td>
                                <?php if (!empty($pdf['category'])): ?>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($pdf['category']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($pdf['uploaded_at']); ?></td>
                            <td>
                                <a href="view_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete_pdf.php?id=<?php echo $pdf['id']; ?>" class="btn btn-sm btn-outline-danger delete-confirm">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>
