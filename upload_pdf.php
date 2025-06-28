<?php
/**
 * Page d'ajout de PDF
 */
require_once 'includes/functions.php';

$message = '';
$messageType = '';

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si un fichier a été uploadé
    if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        $originalFileName = basename($_FILES['pdfFile']['name']);
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        
        // Vérifier que c'est bien un PDF
        if ($fileExtension !== 'pdf' || $_FILES['pdfFile']['type'] !== 'application/pdf') {
            $message = 'Le fichier doit être un PDF valide.';
            $messageType = 'danger';
        } else {
            // Générer un nom de fichier unique
            $newFileName = generateUniqueFilename($originalFileName);
            $uploadFile = $uploadDir . $newFileName;
            
            // Déplacer le fichier uploadé
            if (move_uploaded_file($_FILES['pdfFile']['tmp_name'], $uploadFile)) {
                // Préparer les données pour la base de données
                $pdfData = [
                    'title' => $_POST['title'],
                    'file_name' => $newFileName,
                    'author' => $_POST['author'] ?? '',
                    'category' => $_POST['category'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'course_id' => isset($_POST['course_id']) && !empty($_POST['course_id']) ? $_POST['course_id'] : null,
                    'block_year' => isset($_POST['block_year']) && !empty($_POST['block_year']) ? (int)$_POST['block_year'] : null
                ];
                
                // Traiter les tags
                if (isset($_POST['tags']) && is_array($_POST['tags'])) {
                    $pdfData['tags'] = implode(', ', $_POST['tags']);
                }
                
                // Ajouter le PDF dans la base de données
                $pdfId = addPdf($pdfData);
                
                if ($pdfId) {
                    $message = 'Le PDF a été ajouté avec succès.';
                    $messageType = 'success';
                    
                    // Rediriger vers la page de visualisation
                    header("Location: view_pdf.php?id=$pdfId&success=1");
                    exit;
                } else {
                    $message = 'Erreur lors de l\'ajout du PDF dans la base de données.';
                    $messageType = 'danger';
                }
            } else {
                $message = 'Erreur lors de l\'upload du fichier.';
                $messageType = 'danger';
            }
        }
    } else {
        $message = 'Veuillez sélectionner un fichier PDF à uploader.';
        $messageType = 'danger';
    }
}

// Récupérer toutes les catégories existantes pour l'autocomplétion
$categories = getAllCategories();
$tags = getAllTags();
$courses = getAllCourses();

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card theme-card animate-on-load">
                <div class="card-header">
                    <h2 class="mb-0">
                        <i class="bi bi-upload me-2 text-primary animate-float"></i>
                        Ajouter un nouveau PDF
                    </h2>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> animate-on-load animate-delay-1">
                            <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form action="upload_pdf.php" method="POST" enctype="multipart/form-data" class="animate-on-load animate-delay-2">
                        <div class="mb-3">
                            <label for="pdfFile" class="form-label form-required">Fichier PDF</label>
                            <input type="file" class="form-control" id="pdfFile" name="pdfFile" accept=".pdf" required>
                            <div class="form-text">Sélectionnez un fichier PDF à télécharger (max 10 MB)</div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label form-required">Titre</label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   placeholder="Entrez le titre du document">
                        </div>

                        <div class="mb-3">
                            <label for="author" class="form-label">Auteur</label>
                            <input type="text" class="form-control" id="author" name="author" 
                                   placeholder="Nom de l'auteur (optionnel)">
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Catégorie</label>
                            <input type="text" class="form-control" id="category" name="category" 
                                   placeholder="Ex: Cours, TD, Examen, etc.">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Description du document (optionnel)"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="course_id" class="form-label form-required">Cours associé</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">-- Sélectionner un cours --</option>
                                <?php 
                                foreach ($courses as $course): 
                                ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo htmlspecialchars($course['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Associez ce PDF à un cours spécifique. Créez un nouveau cours si nécessaire.</div>
                        </div>

                        <div class="mb-3">
                            <label for="block_year" class="form-label">Année de bloc</label>
                            <select class="form-select" id="block_year" name="block_year">
                                <option value="">-- Sélectionner une année de bloc --</option>
                                <option value="1">Bloc 1</option>
                                <option value="2">Bloc 2</option>
                                <option value="3">Bloc 3</option>
                            </select>
                            <div class="form-text">Spécifiez l'année de bloc si applicable</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center animate-on-load animate-delay-3">
                            <a href="list_pdfs.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-1"></i>Ajouter le PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>
