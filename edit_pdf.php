<?php
/**
 * Page de modification des informations d'un PDF
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

$message = '';
$messageType = '';

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Préparer les données pour la mise à jour
    $pdfData = [
        'title' => $_POST['title'],
        'author' => $_POST['author'] ?? '',
        'category' => $_POST['category'] ?? '',
        'description' => $_POST['description'] ?? '',
        'course_id' => $_POST['course_id'] ?? null,
        'block_year' => isset($_POST['block_year']) && !empty($_POST['block_year']) ? (int)$_POST['block_year'] : null
    ];
    
    // Traiter les tags
    if (isset($_POST['tags']) && is_array($_POST['tags'])) {
        $pdfData['tags'] = implode(', ', $_POST['tags']);
    } else {
        $pdfData['tags'] = '';
    }
    
    // Mettre à jour le PDF dans la base de données
    $success = updatePdf($id, $pdfData);
    
    if ($success) {
        $message = 'Les informations du PDF ont été mises à jour avec succès.';
        $messageType = 'success';
        
        // Mettre à jour les données du PDF pour l'affichage
        $pdf = getPdfById($id);
    } else {
        $message = 'Erreur lors de la mise à jour des informations du PDF.';
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

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="list_pdfs.php">Tous les PDFs</a></li>
                <li class="breadcrumb-item"><a href="view_pdf.php?id=<?php echo $pdf['id']; ?>"><?php echo htmlspecialchars($pdf['title']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Modifier</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-pencil me-2"></i>Modifier les informations du PDF</h1>
        <hr>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="edit_pdf.php?id=<?php echo $id; ?>" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label form-required">Titre</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($pdf['title']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="author" class="form-label">Auteur(s)</label>
                        <input type="text" class="form-control" id="author" name="author" 
                               value="<?php echo htmlspecialchars($pdf['author']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Catégorie</label>
                        <input type="text" class="form-control" id="category" name="category" 
                               value="<?php echo htmlspecialchars($pdf['category']); ?>" list="categoryList">
                        <datalist id="categoryList">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="mb-3">
                        <label for="course_id" class="form-label form-required">Cours associé</label>
                        <select class="form-select" id="course_id" name="course_id" required>
                            <option value="">-- Sélectionner un cours --</option>
                            <?php 
                            foreach ($courses as $course): 
                            ?>
                                <option value="<?php echo $course['id']; ?>" <?php echo (isset($pdf['course_id']) && $pdf['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Associez ce PDF à un cours spécifique. Cette association est obligatoire.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="block_year" class="form-label">Année de bloc</label>
                        <select class="form-select" id="block_year" name="block_year">
                            <option value="">-- Sélectionner une année de bloc --</option>
                            <option value="1" <?php echo (isset($pdf['block_year']) && $pdf['block_year'] == 1) ? 'selected' : ''; ?>>Bloc 1</option>
                            <option value="2" <?php echo (isset($pdf['block_year']) && $pdf['block_year'] == 2) ? 'selected' : ''; ?>>Bloc 2</option>
                            <option value="3" <?php echo (isset($pdf['block_year']) && $pdf['block_year'] == 3) ? 'selected' : ''; ?>>Bloc 3</option>
                        </select>
                        <div class="form-text">Spécifiez l'année de bloc à laquelle ce PDF appartient (optionnel).</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="tags" placeholder="Ajouter un tag...">
                            <button class="btn btn-outline-secondary" type="button" id="addTag">
                                <i class="bi bi-plus"></i> Ajouter
                            </button>
                        </div>
                        <div class="form-text">Appuyez sur Entrée ou virgule pour ajouter plusieurs tags.</div>
                        
                        <div id="tagsContainer" class="mt-2">
                            <!-- Les tags seront ajoutés ici dynamiquement -->
                        </div>
                        
                        <input type="hidden" id="existingTags" value="<?php echo htmlspecialchars($pdf['tags']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($pdf['description']); ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="view_pdf.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary me-md-2">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations du fichier</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-file-earmark"></i> Nom du fichier</span>
                        <span class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($pdf['file_name']); ?>">
                            <?php echo htmlspecialchars($pdf['file_name']); ?>
                        </span>
                    </li>
                    <?php
                    $filePath = __DIR__ . '/uploads/' . $pdf['file_name'];
                    if (file_exists($filePath)):
                        $fileSize = filesize($filePath);
                        $formattedSize = $fileSize < 1048576 
                            ? round($fileSize / 1024, 2) . ' Ko' 
                            : round($fileSize / 1048576, 2) . ' Mo';
                    ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-hdd"></i> Taille du fichier</span>
                            <span><?php echo $formattedSize; ?></span>
                        </li>
                    <?php endif; ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-calendar"></i> Date d'ajout</span>
                        <span><?php echo formatDate($pdf['uploaded_at']); ?></span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card theme-card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-lightbulb me-2"></i>Suggestions</h5>
                <hr>
                <h6><i class="bi bi-tags me-2"></i>Tags populaires</h6>
                <div class="mb-3">
                    <?php if (empty($tags)): ?>
                        <p class="text-muted small">Aucun tag n'a encore été créé.</p>
                    <?php else: ?>
                        <?php foreach ($tags as $tag): ?>
                            <span class="badge bg-secondary me-1 mb-1 tag-suggestion" role="button" 
                                  onclick="document.getElementById('tags').value='<?php echo htmlspecialchars($tag); ?>';document.getElementById('addTag').click();">
                                <?php echo htmlspecialchars($tag); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <hr>
                <h6><i class="bi bi-folder me-2"></i>Catégories existantes</h6>
                <div>
                    <?php if (empty($categories)): ?>
                        <p class="text-muted small">Aucune catégorie n'a encore été créée.</p>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <span class="badge bg-info me-1 mb-1 category-suggestion" role="button" 
                                  onclick="document.getElementById('category').value='<?php echo htmlspecialchars($category); ?>';">
                                <?php echo htmlspecialchars($category); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>
