<?php
require_once 'includes/functions.php';

$pageTitle = "Ajouter un Cours";
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['course_name']) && !empty(trim($_POST['course_name']))) {
        $courseName = trim($_POST['course_name']);
        
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("INSERT INTO courses (name) VALUES (?)");
            
            if ($stmt->execute([$courseName])) {
                $successMessage = "Le cours \"" . htmlspecialchars($courseName) . "\" a été ajouté avec succès.";
            } else {
                $errorMessage = "Une erreur est survenue lors de l'ajout du cours.";
            }
        } catch (PDOException $e) {
            // Check for duplicate entry
            if ($e->getCode() == 23000) {
                $errorMessage = "Ce nom de cours existe déjà.";
            } else {
                $errorMessage = "Erreur de base de données : " . $e->getMessage();
            }
        }
    } else {
        $errorMessage = "Le nom du cours ne peut pas être vide.";
    }
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card theme-card">
                <div class="card-header">
                    <h1 class="h4 mb-0">
                        <i class="bi bi-plus-circle-fill me-2"></i>
                        Ajouter un nouveau cours
                    </h1>
                </div>
                <div class="card-body">
                    <?php if ($successMessage): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?php echo $successMessage; ?>
                        </div>
                        <a href="list_pdfs.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Retour à la liste des PDFs
                        </a>
                    <?php endif; ?>

                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo $errorMessage; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$successMessage): ?>
                        <form action="add_course.php" method="POST">
                            <div class="mb-3">
                                <label for="course_name" class="form-label">Nom du cours</label>
                                <input type="text" class="form-control" id="course_name" name="course_name" required>
                                <div class="form-text">
                                    Le nom du cours doit être unique.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-lg me-1"></i>
                                Ajouter le cours
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?> 