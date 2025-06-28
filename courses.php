<?php
/**
 * Page de gestion des cours
 */
require_once 'includes/functions.php';

// Récupérer tous les cours
$courses = getAllCourses();

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-book me-2"></i>Cours disponibles</h1>
        <p class="lead">Consultez les PDFs par cours.</p>
        <hr>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
    <?php foreach ($courses as $course): ?>
        <?php 
        // Récupérer le nombre de PDFs pour ce cours
        $pdfs = getPdfsByCourse($course['id']);
        $pdfCount = count($pdfs);
        ?>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-book-half me-2 text-primary"></i>
                        <?php echo htmlspecialchars($course['name']); ?>
                    </h5>
                    <?php if (!empty($course['description'])): ?>
                        <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                    <?php endif; ?>
                    <p class="card-text">
                        <span class="badge bg-info">
                            <i class="bi bi-file-earmark-pdf me-1"></i>
                            <?php echo $pdfCount; ?> PDF<?php echo $pdfCount > 1 ? 's' : ''; ?>
                        </span>
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="course_pdfs.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">
                        <i class="bi bi-eye me-1"></i>Voir les PDFs
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>
