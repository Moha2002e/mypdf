<?php
/**
 * Page de suppression d'un PDF
 */
require_once 'includes/functions.php';

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];
$pdf = getPdfById($id);

// Vérifier si le PDF existe
if (!$pdf) {
    header('Location: index.php');
    exit;
}

// Récupérer les informations du cours associé
$course = null;
if (isset($pdf['course_id']) && $pdf['course_id']) {
    $course = getCourseById($pdf['course_id']);
}

// Vérifier si la confirmation est fournie
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] === '1';

// Si la suppression est confirmée, supprimer le PDF
if ($confirmed) {
    $success = deletePdf($id);
    
    if ($success) {
        // Rediriger vers la liste avec un message de succès
        header('Location: list_pdfs.php?deleted=1');
        exit;
    } else {
        // Rediriger vers la liste avec un message d'erreur
        header('Location: list_pdfs.php?error=1');
        exit;
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="list_pdfs.php">Tous les PDFs</a></li>
                <li class="breadcrumb-item"><a href="view_pdf.php?id=<?php echo $pdf['id']; ?>"><?php echo htmlspecialchars($pdf['title']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Supprimer</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Confirmation de suppression</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Attention :</strong> Cette action est irréversible. Le fichier PDF sera définitivement supprimé.
                </div>
                
                <h5>Êtes-vous sûr de vouloir supprimer ce PDF ?</h5>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 3rem;"></i>
                            </div>
                            <div>
                                <h4 class="mb-1"><?php echo htmlspecialchars($pdf['title']); ?></h4>
                                <?php if (!empty($pdf['author'])): ?>
                                    <p class="mb-1">
                                        <i class="bi bi-person me-1"></i>
                                        <strong>Auteur :</strong> <?php echo htmlspecialchars($pdf['author']); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="mb-1">
                                    <i class="bi bi-calendar me-1"></i>
                                    <strong>Ajouté le :</strong> <?php echo formatDate($pdf['uploaded_at']); ?>
                                </p>
                                <?php if (!empty($pdf['category'])): ?>
                                    <p class="mb-1">
                                        <i class="bi bi-folder me-1"></i>
                                        <strong>Catégorie :</strong> <?php echo htmlspecialchars($pdf['category']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($course): ?>
                                    <p class="mb-1">
                                        <i class="bi bi-book me-1"></i>
                                        <strong>Cours :</strong> <?php echo htmlspecialchars($course['name']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (isset($pdf['block_year']) && !empty($pdf['block_year'])): ?>
                                    <p class="mb-1">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <strong>Année de bloc :</strong> Bloc <?php echo htmlspecialchars($pdf['block_year']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="view_pdf.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-x-circle me-1"></i>Annuler
                    </a>
                    <a href="delete_pdf.php?id=<?php echo $id; ?>&confirm=1" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Oui, supprimer définitivement
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>
