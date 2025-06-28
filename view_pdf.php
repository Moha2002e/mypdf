<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/spotify_functions.php'; // Inclusion des fonctions Spotify

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Stocker l'ID du PDF actuel pour le callback Spotify
if (isset($_GET['id'])) {
    $_SESSION['current_pdf_id'] = $_GET['id'];
}

/**
 * Page de visualisation d'un PDF
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

// Vérifier si un message de succès doit être affiché
$showSuccess = isset($_GET['success']) && $_GET['success'] == 1;

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="row mb-4" id="navigation-area">
    <div class="col-md-12 animate-on-load">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="list_pdfs.php">Tous les PDFs</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($pdf['title']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($showSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show animate-on-load animate-delay-1">
        <i class="bi bi-check-circle me-2"></i>
        Le PDF a été ajouté avec succès.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8 mb-4" id="pdf-viewer-area">
        <div class="card animate-on-load animate-delay-2">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-2 mb-sm-0">
                    <i class="bi bi-file-earmark-pdf-fill me-2 text-danger animate-float"></i>
                    <?php echo htmlspecialchars($pdf['title']); ?>
                </h4>
                <div class="d-flex flex-wrap">
                    <div class="btn-group me-2 mb-2 mb-sm-0">
                        <a href="edit_pdf.php?id=<?php echo $id; ?>" class="btn btn-sm btn-warning hover-scale">
                            <i class="bi bi-pencil"></i> <span class="d-none d-sm-inline">Modifier</span>
                        </a>
                        <a href="download_pdf.php?id=<?php echo $id; ?>" class="btn btn-sm btn-success hover-scale">
                            <i class="bi bi-download"></i> <span class="d-none d-sm-inline">Télécharger</span>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger hover-scale" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Supprimer</span>
                        </button>
                    </div>
                    <div class="btn-group">
                        <button id="toggleImmersiveMode" class="btn btn-sm btn-primary me-2 hover-scale">
                            <i class="bi bi-arrows-fullscreen"></i> <span class="d-none d-sm-inline">Mode immersif</span>
                        </button>
                        <button id="toggleMusicPlayerButton" class="btn btn-sm btn-secondary hover-scale">
                            <i class="bi bi-music-note-beamed"></i> <span class="d-none d-sm-inline">Musique</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body d-flex justify-content-center">
                <div id="pdfViewer" class="w-100" data-pdf-url="uploads/<?php echo urlencode($pdf['file_name']); ?>">
                    <div class="pdf-container">
                        <canvas id="pdfCanvas"></canvas>
                    </div>
                    <div id="pdf-controls" class="mt-3">
                        <div class="row g-2">
                            <div class="col-12 col-md-6 mb-2">
                                <div class="btn-group w-100" role="group">
                                    <button id="prevPage" class="btn btn-sm btn-secondary">
                                        <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Page précédente</span>
                                    </button>
                                    <div class="d-flex align-items-center px-2 bg-light">
                                        <span>Page <input id="pageNumber" type="number" value="1" min="1" class="form-control form-control-sm d-inline-block" style="width: 60px;"> / <span id="pageCount">0</span></span>
                                    </div>
                                    <button id="nextPage" class="btn btn-sm btn-secondary">
                                        <span class="d-none d-sm-inline">Page suivante</span> <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="d-flex flex-wrap justify-content-center justify-content-md-end">
                                    <div class="btn-group me-2 mb-2 mb-sm-0" role="group">
                                        <button id="zoomOut" class="btn btn-sm btn-secondary">
                                            <i class="bi bi-zoom-out"></i>
                                        </button>
                                        <select id="zoomLevel" class="form-select form-select-sm" style="width: auto;">
                                            <option value="0.5">50%</option>
                                            <option value="0.75">75%</option>
                                            <option value="1" >100%</option>
                                            <option value="1.25" selected>125%</option>
                                            <option value="1.5">150%</option>
                                            <option value="2">200%</option>
                                            <option value="3">300%</option>
                                        </select>
                                        <button id="zoomIn" class="btn btn-sm btn-secondary">
                                            <i class="bi bi-zoom-in"></i>
                                        </button>
                                        <button id="adjustContainer" class="btn btn-sm btn-secondary">
                                            <i class="bi bi-arrows-angle-expand"></i>
                                        </button>
                                    </div>
                                    <div class="input-group" style="max-width: 300px;">
                                        <input type="text" id="searchPdf" class="form-control form-control-sm" placeholder="Rechercher...">
                                        <button id="searchButton" class="btn btn-sm btn-primary">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <button id="prevMatch" class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="bi bi-arrow-up"></i>
                                        </button>
                                        <button id="nextMatch" class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="bi bi-arrow-down"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-center text-md-end mt-1">
                                    <small id="matchInfo" class="text-muted"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4" id="sidebar-area">
        <!-- Onglets pour mobile pour basculer entre signets et notes -->
        <ul class="nav nav-tabs d-md-none mb-3" id="sidebarTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="bookmarks-tab" data-bs-toggle="tab" data-bs-target="#bookmarks-content" type="button" role="tab">
                    <i class="bi bi-bookmark"></i> Signets
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes-content" type="button" role="tab">
                    <i class="bi bi-journal-text"></i> Notes
                </button>
            </li>
        </ul>
        
        <!-- Contenu des onglets pour mobile -->
        <div class="tab-content d-md-none">
            <div class="tab-pane fade show active" id="bookmarks-content" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Signets</h5>
                    </div>
                    <div class="card-body">
                        <div class="input-group mb-3">
                            <input type="text" id="bookmarkName-mobile" class="form-control" placeholder="Nom du signet">
                            <button id="addBookmark-mobile" class="btn btn-primary">
                                <i class="bi bi-bookmark-plus"></i> <span class="d-none d-sm-inline">Ajouter</span>
                            </button>
                        </div>
                        <div id="bookmarksList-mobile" class="list-group">
                            <!-- Les signets seront ajoutés ici dynamiquement -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="notes-content" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        <textarea id="pdfNotes-mobile" class="form-control mb-3" rows="5" placeholder="Prenez des notes sur ce document..."></textarea>
                        <div class="d-flex justify-content-between align-items-center">
                            <div id="notesStatus-mobile" class="text-muted"></div>
                            <button id="saveNotes-mobile" class="btn btn-success">
                                <i class="bi bi-save"></i> <span class="d-none d-sm-inline">Enregistrer</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Version desktop des signets et notes -->
        <div class="d-none d-md-block">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Signets</h5>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" id="bookmarkName" class="form-control" placeholder="Nom du signet">
                        <button id="addBookmark" class="btn btn-primary">
                            <i class="bi bi-bookmark-plus"></i> Ajouter
                        </button>
                    </div>
                    <div id="bookmarksList" class="list-group">
                        <!-- Les signets seront ajoutés ici dynamiquement -->
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <textarea id="pdfNotes" class="form-control mb-3" rows="5" placeholder="Prenez des notes sur ce document..."></textarea>
                    <div class="d-flex justify-content-between align-items-center">
                        <div id="notesStatus" class="text-muted"></div>
                        <button id="saveNotes" class="btn btn-success">
                            <i class="bi bi-save"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Informations du PDF -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations du PDF</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($pdf['author'])): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-person me-2"></i>Auteur</span>
                                <span><?php echo htmlspecialchars($pdf['author']); ?></span>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($pdf['category'])): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-folder me-2"></i>Catégorie</span>
                                <span class="badge bg-info"><?php echo htmlspecialchars($pdf['category']); ?></span>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (isset($pdf['course_id']) && !empty($pdf['course_id'])): ?>
                            <?php $course = getCourseById($pdf['course_id']); ?>
                            <?php if ($course): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-book me-2"></i>Cours</span>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($course['name']); ?></span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if (isset($pdf['block_year']) && !empty($pdf['block_year'])): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-calendar-event me-2"></i>Année de bloc</span>
                                <span class="badge bg-success">Bloc <?php echo htmlspecialchars($pdf['block_year']); ?></span>
                            </li>
                        <?php endif; ?>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-calendar me-2"></i>Date d'ajout</span>
                            <span><?php echo formatDate($pdf['uploaded_at']); ?></span>
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
                                <span><i class="bi bi-hdd me-2"></i>Taille</span>
                                <span><?php echo $formattedSize; ?></span>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($pdf['tags'])): ?>
                            <li class="list-group-item">
                                <span><i class="bi bi-tags me-2"></i>Tags</span>
                                <div class="mt-2">
                                    <?php foreach (explode(',', $pdf['tags']) as $tag): ?>
                                        <?php if (trim($tag)): ?>
                                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Éléments pour le mode immersif amélioré -->
<div id="immersive-toolbar" style="display: none; z-index: 10001;">
    <button id="toggleNightMode" title="Mode nuit" class="btn btn-sm">
        <i class="bi bi-moon-stars"></i>
    </button>
    <button id="toggleFocusMode" title="Mode focus" class="btn btn-sm">
        <i class="bi bi-bullseye"></i>
    </button>
    <button id="toggleAutoScroll" title="Défilement automatique" class="btn btn-sm">
        <i class="bi bi-arrow-down-circle"></i>
    </button>
    <button id="toggleFullscreen" title="Plein écran" class="btn btn-sm">
        <i class="bi bi-arrows-fullscreen"></i>
    </button>
    <button id="toggleImmersiveMode" title="Quitter le mode immersif" class="btn btn-sm">
        <i class="bi bi-fullscreen-exit"></i>
    </button>
</div>

<!-- Overlay pour le mode focus -->
<div class="focus-overlay" style="pointer-events: none;"></div>

<!-- Boîte de dialogue personnalisée pour la musique -->
<div id="music-dialog" class="dialog-container">
    <div class="dialog-box large">
        <div class="dialog-header">
            <h4 class="dialog-title">Musique d'ambiance</h4>
            <button class="dialog-close" id="close-music-dialog">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="dialog-body">
            <div id="musicPlayer" class="mb-4">
                <audio id="audioPlayer" class="w-100 mb-3" controls></audio>
                
                <div class="d-flex justify-content-between mb-3">
                    <button id="prevTrack" class="btn btn-sm btn-secondary" disabled>
                        <i class="bi bi-skip-backward-fill"></i> Précédent
                    </button>
                    <button id="playPause" class="btn btn-sm btn-primary">
                        <i class="bi bi-play-fill"></i> Lecture
                    </button>
                    <button id="nextTrack" class="btn btn-sm btn-secondary" disabled>
                        <i class="bi bi-skip-forward-fill"></i> Suivant
                    </button>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="autoplaySwitch">
                    <label class="form-check-label" for="autoplaySwitch">Lecture automatique</label>
                </div>
            </div>
            
            <div class="music-library">
                <h6 class="border-bottom pb-2 mb-3">Bibliothèque musicale</h6>
                
                <div class="list-group music-list">
                    <?php
                    // Inclure le scanner de musique
                    require_once 'includes/music_scanner.php';
                    
                    // Chemin du dossier de musique
                    $musicDirectory = 'C:\\Users\\pasch\\Documents\\music';
                    
                    // Récupérer les fichiers musicaux
                    $musicFiles = scanMusicDirectory($musicDirectory);
                    
                    // Afficher les fichiers musicaux
                    if (empty($musicFiles)) {
                        echo '<div class="alert alert-info">Aucun fichier musical trouvé dans le dossier spécifié.</div>';
                    } else {
                        foreach ($musicFiles as $index => $music) {
                            $musicUrl = generateMusicUrl($music['full_path']);
                            echo '<button type="button" class="list-group-item list-group-item-action music-item" 
                                data-music-url="' . htmlspecialchars($musicUrl) . '" 
                                data-music-index="' . $index . '">
                                <i class="bi bi-music-note me-2"></i> ' . htmlspecialchars($music['name']) . '
                            </button>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="dialog-footer">
            <button id="openMusicLibrary" class="btn btn-sm btn-outline-light ms-3" data-bs-toggle="modal" data-bs-target="#musicModal" title="Bibliothèque musicale">
                <i class="bi bi-music-note-list"></i>
            </button>
            <button id="closeAudioPlayer" class="btn btn-sm btn-outline-light ms-2" title="Fermer">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</div>

<!-- Lecteur audio flottant (toujours visible) -->
<div id="floating-player" class="fixed-bottom bg-dark text-white p-2" style="display: none; z-index: 1030;">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-4">
                <span id="current-track-name">Aucune piste en cours</span>
            </div>
            <div class="col-md-6">
                <audio id="floating-audio-player" controls class="w-100"></audio>
            </div>
            <div class="col-md-2 text-end">
                <button id="show-music-dialog" class="btn btn-sm btn-outline-light me-2" title="Bibliothèque musicale">
                    <i class="bi bi-music-note-list"></i>
                </button>
                <button id="close-floating-player" class="btn btn-sm btn-outline-light" title="Fermer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Boîte de dialogue personnalisée pour la suppression -->
<div id="delete-dialog" class="dialog-container">
    <div class="dialog-box">
        <div class="dialog-header">
            <h4 class="dialog-title">Confirmer la suppression</h4>
            <button class="dialog-close" id="close-delete-dialog">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="dialog-body">
            <p>Êtes-vous sûr de vouloir supprimer le PDF <strong><?php echo htmlspecialchars($pdf['title']); ?></strong> ?</p>
            <p class="text-danger">Cette action est irréversible.</p>
        </div>
        <div class="dialog-footer">
            <button id="delete-pdf" class="btn btn-sm btn-danger">
                Supprimer
            </button>
            <button id="cancel-delete" class="btn btn-sm btn-secondary">
                Annuler
            </button>
        </div>
    </div>
</div>

<!-- Styles CSS -->
<style>
    .pdf-container {
        overflow: auto;
        max-height: 80vh;
        margin: 0 auto;
        border: 1px solid #ddd;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        transition: width 0.3s ease, height 0.3s ease;
        background: #1e1e1e;
        display: flex;
        justify-content: center;
    }
    
    #pdfCanvas {
        display: block;
        margin: 0 auto;
        transition: all 0.3s ease;
    }

    .highlight {
        position: absolute;
        background-color: rgba(255, 255, 0, 0.3);
        border-radius: 2px;
        z-index: 10;
    }

    .current-highlight {
        background-color: rgba(255, 165, 0, 0.5);
    }

    /* Styles pour le mode immersif */
    body.immersive-mode {
        overflow: hidden;
    }
    
    body.immersive-mode #pdf-viewer-area {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1050;
        background-color: #000;
        padding: 0;
    }
    
    body.immersive-mode .card {
        height: 100%;
        border: none;
        border-radius: 0;
    }
    
    body.immersive-mode .card-header {
        border-radius: 0;
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 0.5rem 1rem;
    }
    
    body.immersive-mode .card-body {
        height: calc(100% - 56px);
        padding: 0;
    }
    
    body.immersive-mode .pdf-container {
        height: 100%;
        max-height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    body.immersive-mode #pdf-controls {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 0.5rem;
        z-index: 1051;
    }
    
    body.immersive-mode .btn-light {
        background-color: rgba(255, 255, 255, 0.2);
        color: white;
        border: none;
    }
    
    body.immersive-mode .btn-light:hover {
        background-color: rgba(255, 255, 255, 0.3);
    }
    
    .focus-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1049;
        display: none;
    }
    
    body.immersive-mode .focus-overlay {
        display: block;
    }
    
    /* Styles pour les modales personnalisées */
    .dialog-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
        z-index: 2000;
    }
    
    .dialog-box {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
        width: 90%;
        max-width: 500px;
    }
    
    .dialog-box.large {
        max-width: 800px;
    }
    
    .dialog-header {
        padding: 10px 15px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .dialog-title {
        margin: 0;
        font-size: 1.25rem;
    }
    
    .dialog-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
    }
    
    .dialog-body {
        padding: 15px;
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .dialog-footer {
        padding: 10px 15px;
        border-top: 1px solid #ddd;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    /* Style pour le lecteur de musique */
    .music-list {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .music-item {
        display: block;
        width: 100%;
        padding: 8px 12px;
        margin-bottom: 5px;
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: left;
        cursor: pointer;
    }
    
    .music-item:hover {
        background-color: #e9ecef;
    }
    
    .music-item.active {
        background-color: #0d6efd;
        color: white;
    }
    
    @keyframes pageTurn {
        0% { opacity: 0; transform: translateX(20px); }
        100% { opacity: 1; transform: translateX(0); }
    }
</style>

<!-- Inclure les scripts JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script src="js/audio-player.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM chargé, initialisation du PDF');
        
        // Initialiser PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

        // Initialisation de PDF.js
        const pdfViewer = document.getElementById('pdfViewer');
        const pdfUrl = pdfViewer ? pdfViewer.dataset.pdfUrl : null;
        console.log('URL du PDF:', pdfUrl);
        
        const canvas = document.getElementById('pdfCanvas');
        const ctx = canvas && canvas.getContext('2d');
        const pdfContainer = document.querySelector('.pdf-container');
        
        if (!pdfUrl || !canvas || !ctx) {
            console.error('Éléments nécessaires non trouvés:', {
                pdfUrl: !!pdfUrl,
                canvas: !!canvas,
                ctx: !!ctx
            });
            if (pdfContainer) {
                pdfContainer.innerHTML = '<div class="alert alert-danger m-3">Erreur d\'initialisation du visualiseur PDF.</div>';
            }
            return;
        }

        // Éléments de contrôle du PDF
        const prevButton = document.getElementById('prevPage');
        const nextButton = document.getElementById('nextPage');
        const pageNumberInput = document.getElementById('pageNumber');
        const pageCountSpan = document.getElementById('pageCount');

        // Éléments de zoom
        const zoomInButton = document.getElementById('zoomIn');
        const zoomOutButton = document.getElementById('zoomOut');
        const zoomLevelSelect = document.getElementById('zoomLevel');

        // Éléments de recherche
        const searchInput = document.getElementById('searchPdf');
        const searchButton = document.getElementById('searchButton');
        const prevMatchButton = document.getElementById('prevMatch');
        const nextMatchButton = document.getElementById('nextMatch');
        const matchInfoSpan = document.getElementById('matchInfo');

        // Éléments de signets
        const bookmarkNameInput = document.getElementById('bookmarkName');
        const addBookmarkButton = document.getElementById('addBookmark');
        const bookmarksListDiv = document.getElementById('bookmarksList');

        // Éléments de notes
        const pdfNotesTextarea = document.getElementById('pdfNotes');
        const saveNotesButton = document.getElementById('saveNotes');
        const notesStatusDiv = document.getElementById('notesStatus');

        // Variables pour le PDF
        let pdfDoc = null;
        let pageNum = 1;
        let pageRendering = false;
        let pageNumPending = null;
        let scale = 1.25; // Échelle de zoom par défaut

        // Variables pour la recherche
        let searchMatches = [];
        let currentMatchIndex = -1;
        let highlights = [];

        // Éléments pour le mode immersif et l'audio
        const toggleImmersiveButton = document.getElementById('toggleImmersiveMode');
        const toggleMusicPlayerButton = document.getElementById('toggleMusicPlayerButton');
        const openMusicLibraryButton = document.getElementById('openMusicLibrary');
        const pdfControls = document.getElementById('pdf-controls');
        const immersiveToolbar = document.getElementById('immersive-toolbar');
        const toggleNightModeButton = document.getElementById('toggleNightMode');
        const toggleFocusModeButton = document.getElementById('toggleFocusMode');
        const toggleAutoScrollButton = document.getElementById('toggleAutoScroll');
        const toggleFullscreenButton = document.getElementById('toggleFullscreen');
        const focusOverlay = document.querySelector('.focus-overlay');
        
        // Variables pour le mode immersif
        let isNightMode = false;
        let isFocusMode = false;
        let isAutoScrolling = false;
        let autoScrollSpeed = 1; // pixels par seconde
        let autoScrollInterval;

        // Fonction pour activer/désactiver le mode immersif
        if (toggleImmersiveButton) {
            toggleImmersiveButton.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Mode immersif cliqué');

                // Toggle la classe sur le body
                if (document.body.classList.contains('immersive-mode')) {
                    exitImmersiveMode();
                } else {
                    enterImmersiveMode();
                }

                // Redessiner la page actuelle pour l'adapter à la nouvelle taille
                if (pdfDoc) queueRenderPage(pageNum);
            });
        } else {
            console.error('Bouton mode immersif non trouvé');
        }

        // Fonction pour entrer en mode immersif
        function enterImmersiveMode() {
            document.body.classList.add('immersive-mode');
            console.log('Mode immersif activé');
            
            // Afficher la barre d'outils immersive
            if (immersiveToolbar) immersiveToolbar.style.display = 'flex';
            
            // En mode immersif, masquer les contrôles après un délai
            if (pdfControls) {
                setTimeout(function() {
                    pdfControls.classList.add('hidden');
                }, 3000);
            }
            
            // Forcer le rendu du PDF après un court délai pour s'assurer qu'il s'affiche correctement
            setTimeout(function() {
                if (pdfDoc && pageNum) {
                    console.log('Re-rendu du PDF en mode immersif');
                    renderPage(pageNum);
                }
            }, 100);
            
            // Afficher les contrôles au survol
            if (pdfContainer) pdfContainer.addEventListener('mousemove', showControls);
            
            // Activer les raccourcis clavier spécifiques au mode immersif
            document.addEventListener('keydown', handleImmersiveKeydown);
        }
        
        // Fonction pour quitter le mode immersif
        function exitImmersiveMode() {
            document.body.classList.remove('immersive-mode');
            document.body.classList.remove('night-mode');
            document.body.classList.remove('focus-mode');
            console.log('Mode immersif désactivé');
            
            // Masquer la barre d'outils immersive
            if (immersiveToolbar) immersiveToolbar.style.display = 'none';
            
            // Réafficher les contrôles
            if (pdfControls) pdfControls.classList.remove('hidden');
            
            // Arrêter le défilement automatique
            stopAutoScroll();
            
            // Hors du mode immersif
            if (pdfContainer) pdfContainer.removeEventListener('mousemove', showControls);
            
            // Désactiver les raccourcis clavier spécifiques au mode immersif
            document.removeEventListener('keydown', handleImmersiveKeydown);
            
            // Quitter le mode plein écran si actif
            if (document.fullscreenElement) {
                document.exitFullscreen().catch(err => {
                    console.error('Erreur lors de la sortie du mode plein écran:', err);
                });
            }
            
            // Forcer le rendu du PDF après un court délai
            setTimeout(function() {
                if (pdfDoc && pageNum) {
                    console.log('Re-rendu du PDF après sortie du mode immersif');
                    renderPage(pageNum);
                }
            }, 100);
        }

        // Fonction pour afficher les contrôles en mode immersif
        let controlsTimeout;
        function showControls() {
            if (!pdfControls) return;

            pdfControls.classList.remove('hidden');

            clearTimeout(controlsTimeout);
            controlsTimeout = setTimeout(function() {
                if (document.body.classList.contains('immersive-mode')) {
                    pdfControls.classList.add('hidden');
                }
            }, 3000);
        }

        // Gestion du mode nuit
        if (toggleNightModeButton) {
            toggleNightModeButton.addEventListener('click', function() {
                isNightMode = !isNightMode;
                if (isNightMode) {
                    document.body.classList.add('night-mode');
                    this.innerHTML = '<i class="bi bi-sun"></i>';
                    this.title = 'Mode jour';
                } else {
                    document.body.classList.remove('night-mode');
                    this.innerHTML = '<i class="bi bi-moon-stars"></i>';
                    this.title = 'Mode nuit';
                }
            });
        }
        
        // Gestion du mode focus
        if (toggleFocusModeButton) {
            toggleFocusModeButton.addEventListener('click', function() {
                isFocusMode = !isFocusMode;
                if (isFocusMode) {
                    document.body.classList.add('focus-mode');
                    this.innerHTML = '<i class="bi bi-eye"></i>';
                    this.title = 'Désactiver le mode focus';
                } else {
                    document.body.classList.remove('focus-mode');
                    this.innerHTML = '<i class="bi bi-bullseye"></i>';
                    this.title = 'Mode focus';
                }
            });
        }
        
        // Gestion du défilement automatique
        if (toggleAutoScrollButton) {
            toggleAutoScrollButton.addEventListener('click', function() {
                if (isAutoScrolling) {
                    stopAutoScroll();
                    this.innerHTML = '<i class="bi bi-arrow-down-circle"></i>';
                    this.title = 'Défilement automatique';
                } else {
                    startAutoScroll();
                    this.innerHTML = '<i class="bi bi-pause-circle"></i>';
                    this.title = 'Arrêter le défilement';
                }
            });
        }
        
        function startAutoScroll() {
            if (isAutoScrolling) return;
            
            isAutoScrolling = true;
            autoScrollInterval = setInterval(function() {
                if (pdfContainer) {
                    pdfContainer.scrollTop += autoScrollSpeed;
                    
                    // Si on atteint le bas de la page actuelle, passer à la suivante
                    if (pdfContainer.scrollTop + pdfContainer.clientHeight >= pdfContainer.scrollHeight) {
                        if (pageNum < pdfDoc.numPages) {
                            clearInterval(autoScrollInterval);
                            setTimeout(function() {
                                goNextPage();
                                // Remettre le scroll en haut pour la nouvelle page
                                pdfContainer.scrollTop = 0;
                                // Redémarrer le défilement après un court délai
                                setTimeout(startAutoScroll, 1000);
                            }, 500);
                        } else {
                            // On est à la dernière page, arrêter le défilement
                            stopAutoScroll();
                        }
                    }
                }
            }, 50); // Mise à jour toutes les 50ms pour un défilement fluide
        }
        
        function stopAutoScroll() {
            if (!isAutoScrolling) return;
            
            isAutoScrolling = false;
            clearInterval(autoScrollInterval);
            
            if (toggleAutoScrollButton) {
                toggleAutoScrollButton.innerHTML = '<i class="bi bi-arrow-down-circle"></i>';
                toggleAutoScrollButton.title = 'Défilement automatique';
            }
        }
        
        // Gestion du mode plein écran
        if (toggleFullscreenButton) {
            toggleFullscreenButton.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.error('Erreur lors du passage en mode plein écran:', err);
                    });
                    this.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
                    this.title = 'Quitter le plein écran';
                } else {
                    document.exitFullscreen().catch(err => {
                        console.error('Erreur lors de la sortie du mode plein écran:', err);
                    });
                    this.innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
                    this.title = 'Plein écran';
                }
            });
        }
        
        // Gestion des raccourcis clavier en mode immersif
        function handleImmersiveKeydown(e) {
            // Ne pas intercepter les événements si un champ de texte est actif
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }
            
            switch (e.key) {
                case 'Escape':
                    if (document.body.classList.contains('immersive-mode')) {
                        exitImmersiveMode();
                        e.preventDefault();
                    }
                    break;
                case 'n':
                case 'N':
                    // Toggle mode nuit
                    if (toggleNightModeButton) toggleNightModeButton.click();
                    e.preventDefault();
                    break;
                case 'f':
                case 'F':
                    // Toggle mode focus
                    if (toggleFocusModeButton) toggleFocusModeButton.click();
                    e.preventDefault();
                    break;
                case 's':
                case 'S':
                    // Toggle défilement automatique
                    if (toggleAutoScrollButton) toggleAutoScrollButton.click();
                    e.preventDefault();
                    break;
                case '+':
                    // Augmenter la vitesse de défilement
                    if (isAutoScrolling) {
                        autoScrollSpeed = Math.min(autoScrollSpeed + 0.5, 10);
                        e.preventDefault();
                    }
                    break;
                case '-':
                    // Diminuer la vitesse de défilement
                    if (isAutoScrolling) {
                        autoScrollSpeed = Math.max(autoScrollSpeed - 0.5, 0.5);
                        e.preventDefault();
                    }
                    break;
                case 'ArrowUp':
                    // Défiler vers le haut
                    if (pdfContainer) {
                        pdfContainer.scrollTop -= 50;
                        showControls();
                        e.preventDefault();
                    }
                    break;
                case 'ArrowDown':
                    // Défiler vers le bas
                    if (pdfContainer) {
                        pdfContainer.scrollTop += 50;
                        showControls();
                        e.preventDefault();
                    }
                    break;
                case 'ArrowLeft':
                    // Page précédente
                    goPrevPage();
                    showControls();
                    e.preventDefault();
                    break;
                case 'ArrowRight':
                    // Page suivante
                    goNextPage();
                    showControls();
                    e.preventDefault();
                    break;
                case 'Home':
                    // Première page
                    if (pageNum !== 1) {
                        pageNum = 1;
                        queueRenderPage(pageNum);
                    }
                    e.preventDefault();
                    break;
                case 'End':
                    // Dernière page
                    if (pdfDoc && pageNum !== pdfDoc.numPages) {
                        pageNum = pdfDoc.numPages;
                        queueRenderPage(pageNum);
                    }
                    e.preventDefault();
                    break;
            }
        }

        // Contrôle de la musique
        if (toggleMusicPlayerButton) {
            toggleMusicPlayerButton.addEventListener('click', function() {
                const musicDialog = document.getElementById('music-dialog');
                if (musicDialog) {
                    musicDialog.style.display = 'block';
                    if (audioPlayer && !audioPlayer.isPlaying) {
                        audioPlayer.play();
                    }
                }
            });
        }

        if (openMusicLibraryButton) {
            openMusicLibraryButton.addEventListener('click', function() {
                // Code pour ouvrir la recherche de musique
            });
        }

        // Gestion du bouton de fermeture du lecteur audio
        const closeAudioPlayerButton = document.getElementById('closeAudioPlayer');
        if (closeAudioPlayerButton) {
            closeAudioPlayerButton.addEventListener('click', function() {
                const audioPlayerContainer = document.querySelector('.audio-player-container');
                if (audioPlayerContainer) {
                    audioPlayerContainer.style.display = 'none';
                }
            });
        }

        // Fonction pour mettre en file d'attente le rendu d'une page
        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
                console.log('Page en attente:', num);
            } else {
                renderPage(num);
            }
        }

        // Fonction pour rendre une page
        function renderPage(num) {
            if (!pdfDoc) {
                console.error('Document PDF non chargé');
                return;
            }
            
            pageRendering = true;
            console.log('Rendu de la page', num);

            // Mettre à jour l'entrée de numéro de page
            if (pageNumberInput) pageNumberInput.value = num;

            // Récupérer la page
            pdfDoc.getPage(num).then(function(page) {
                console.log('Page récupérée avec succès');

                // Calculer l'échelle pour s'adapter à la largeur du conteneur
                const containerWidth = pdfContainer.clientWidth;
                const initialViewport = page.getViewport({ scale: 1 });
                const baseScale = containerWidth / initialViewport.width;
                
                // Appliquer l'échelle de zoom de l'utilisateur à l'échelle de base
                const finalScale = baseScale * scale;
                const viewport = page.getViewport({ scale: finalScale });

                // Ajuster la taille du canvas
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                console.log('Dimensions du canvas ajustées:', viewport.width, 'x', viewport.height);

                // Rendre la page
                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };

                const renderTask = page.render(renderContext);

                // Attendre la fin du rendu
                renderTask.promise.then(function() {
                    console.log('Rendu de la page terminé');
                    pageRendering = false;

                    if (pageNumPending !== null) {
                        // Un nouveau rendu est en attente
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }

                    // Redessiner les surlignages si nécessaire
                    if (searchMatches.length > 0) {
                        drawHighlights();
                    }

                    // Ajouter une classe pour l'animation de transition de page
                    if (canvas) {
                        canvas.classList.add('page-transition');
                        setTimeout(() => {
                            canvas.classList.remove('page-transition');
                        }, 400);
                    }
                }).catch(function(error) {
                    console.error('Erreur lors du rendu de la page:', error);
                    pageRendering = false;
                });

            }).catch(function(error) {
                console.error('Erreur lors de la récupération de la page:', error);
                pageRendering = false;
            });
        }

        // Fonction pour aller à la page précédente
        function goPrevPage() {
            if (pageNum <= 1) {
                return;
            }
            pageNum--;
            queueRenderPage(pageNum);
        }

        // Fonction pour aller à la page suivante
        function goNextPage() {
            if (!pdfDoc || pageNum >= pdfDoc.numPages) {
                return;
            }
            pageNum++;
            queueRenderPage(pageNum);
        }

        // Événements pour les boutons de navigation
        if (prevButton) {
            prevButton.addEventListener('click', goPrevPage);
        }

        if (nextButton) {
            nextButton.addEventListener('click', goNextPage);
        }

        // Événement pour l'entrée de numéro de page
        if (pageNumberInput) {
            pageNumberInput.addEventListener('change', function() {
                const num = parseInt(this.value);
                if (pdfDoc && num > 0 && num <= pdfDoc.numPages) {
                    pageNum = num;
                    queueRenderPage(pageNum);
                } else {
                    this.value = pageNum;
                }
            });
        }

        // Événements pour les boutons de zoom
        if (zoomInButton) {
            zoomInButton.addEventListener('click', function() {
                if (zoomLevelSelect) {
                    const currentZoomIndex = zoomLevelSelect.selectedIndex;
                    if (currentZoomIndex < zoomLevelSelect.options.length - 1) {
                        zoomLevelSelect.selectedIndex = currentZoomIndex + 1;
                        const newScale = parseFloat(zoomLevelSelect.value);
                        updateScale(newScale);
                    }
                } else {
                    const newScale = scale * 1.2;
                    updateScale(newScale);
                }
            });
        }

        if (zoomOutButton) {
            zoomOutButton.addEventListener('click', function() {
                if (zoomLevelSelect) {
                    const currentZoomIndex = zoomLevelSelect.selectedIndex;
                    if (currentZoomIndex > 0) {
                        zoomLevelSelect.selectedIndex = currentZoomIndex - 1;
                        const newScale = parseFloat(zoomLevelSelect.value);
                        updateScale(newScale);
                    }
                } else {
                    const newScale = scale / 1.2;
                    updateScale(newScale);
                }
            });
        }

        if (zoomLevelSelect) {
            zoomLevelSelect.addEventListener('change', function() {
                const newScale = parseFloat(this.value);
                updateScale(newScale);
            });
        }

        // Fonction pour mettre à jour l'échelle et recharger la page
        function updateScale(newScale) {
            console.log('Mise à jour de l\'échelle:', scale, '->', newScale);
            
            // Mettre à jour l'échelle
            scale = newScale;
            
            // Mettre à jour la sélection dans le menu déroulant si elle existe
            if (zoomLevelSelect) {
                // Chercher l'option la plus proche de la nouvelle échelle
                let closestOption = null;
                let closestDiff = Infinity;
                
                for (let i = 0; i < zoomLevelSelect.options.length; i++) {
                    const optionValue = parseFloat(zoomLevelSelect.options[i].value);
                    const diff = Math.abs(optionValue - newScale);
                    
                    if (diff < closestDiff) {
                        closestDiff = diff;
                        closestOption = i;
                    }
                }
                
                // Si on a trouvé une option proche, la sélectionner
                if (closestOption !== null && closestDiff < 0.1) {
                    zoomLevelSelect.selectedIndex = closestOption;
                }
            }
            
            // Recharger la page avec la nouvelle échelle
            // L'ajustement du conteneur sera fait après le rendu
            queueRenderPage(pageNum);
        }
        
        // Fonction pour ajuster la taille du conteneur PDF en fonction du zoom
        function adjustContainerSize(currentScale) {
            const pdfContainer = document.querySelector('.pdf-container');
            const pdfCanvas = document.getElementById('pdfCanvas');
            
            if (pdfContainer && pdfCanvas) {
                // Calculer la nouvelle largeur du conteneur en fonction de l'échelle
                const scaleRatio = currentScale / 1.25; // 1.25 est l'échelle de base (125%)
                
                // Calculer la nouvelle largeur
                let newWidth;
                if (window.innerWidth < 768) {
                    // Sur mobile, toujours utiliser 100% de largeur
                    newWidth = '100%';
                } else {
                    // Sur desktop, ajuster en fonction du zoom
                    // Plus le zoom est grand, plus la largeur est grande
                    newWidth = Math.min(Math.max(scaleRatio * 80, 60), 100) + '%';
                }
                
                // Calculer la nouvelle hauteur en fonction de l'échelle et de la hauteur du canvas
                let newHeight;
                
                // Si le canvas a une hauteur définie, l'utiliser pour calculer la hauteur du conteneur
                if (pdfCanvas.height) {
                    // Calculer la hauteur en fonction du zoom
                    // Plus le zoom est grand, plus la hauteur doit être grande
                    const heightRatio = Math.max(scaleRatio, 0.8); // Ne pas descendre en dessous de 80% de la hauteur
                    
                    // Hauteur de base pour le canvas (hauteur actuelle + marge)
                    const baseCanvasHeight = pdfCanvas.height + 40; // 40px de marge
                    
                    // Hauteur maximale disponible dans la fenêtre (85% de la hauteur de la fenêtre)
                    const maxViewportHeight = window.innerHeight * 0.85;
                    
                    // Calculer la hauteur optimale
                    // Si le zoom est élevé, on augmente la hauteur proportionnellement
                    // mais on ne dépasse pas la hauteur maximale de la fenêtre
                    newHeight = Math.min(baseCanvasHeight * heightRatio, maxViewportHeight) + 'px';
                } else {
                    // Fallback si la hauteur du canvas n'est pas disponible
                    const baseHeight = 80; // 80vh pour l'échelle de base (125%)
                    newHeight = Math.min(Math.max(baseHeight * scaleRatio, 60), 95) + 'vh';
                }
                
                // Appliquer les nouvelles dimensions avec une transition fluide
                pdfContainer.style.width = newWidth;
                pdfContainer.style.height = newHeight;
                pdfContainer.style.maxHeight = '95vh'; // Limiter la hauteur maximale
                
                console.log('Ajustement du conteneur PDF:', {
                    scale: currentScale,
                    newWidth: newWidth,
                    newHeight: newHeight,
                    canvasHeight: pdfCanvas.height
                });
            }
        }

        // Fonction pour maintenir les dimensions du conteneur en mode immersif
        function maintainImmersiveSize() {
            const body = document.body;
            const pdfContainer = document.querySelector('.pdf-container');
            const pdfCanvas = document.querySelector('#pdfCanvas');
            
            if (body.classList.contains('immersive-mode') && pdfContainer && pdfCanvas) {
                console.log('Maintien des dimensions en mode immersif');
                
                // Sauvegarder les dimensions actuelles
                const currentWidth = pdfContainer.style.width;
                const currentHeight = pdfContainer.style.height;
                
                // Appliquer les dimensions après le rendu
                if (currentWidth && currentHeight) {
                    console.log('Dimensions à maintenir:', currentWidth, currentHeight);
                    
                    // Utiliser un délai pour s'assurer que le rendu est terminé
                    setTimeout(() => {
                        pdfContainer.style.width = currentWidth;
                        pdfContainer.style.height = currentHeight;
                        console.log('Dimensions maintenues avec succès');
                    }, 200);
                }
            }
        }

        // Initialisation de PDF.js
        if (pdfUrl) {
            console.log('Chargement du PDF:', pdfUrl);
            pdfjsLib.getDocument(pdfUrl).promise
                .then(function(pdf) {
                    console.log('PDF chargé avec succès');
                    pdfDoc = pdf;
                    
                    // Mettre à jour le nombre total de pages
                    if (pageCountSpan) pageCountSpan.textContent = pdf.numPages;
                    
                    // Mettre à jour les limites de l'entrée de numéro de page
                    if (pageNumberInput) {
                        pageNumberInput.max = pdf.numPages;
                    }

                    // Charger la première page
                    renderPage(pageNum);
                    
                    // Ajouter un bouton pour ajuster manuellement le conteneur
                    const zoomControls = document.querySelector('#zoomIn').parentNode;
                    if (zoomControls) {
                        const adjustContainerButton = document.createElement('button');
                        adjustContainerButton.id = 'adjustContainer';
                        adjustContainerButton.className = 'btn btn-sm btn-secondary';
                        adjustContainerButton.innerHTML = '<i class="bi bi-arrows-angle-expand"></i>';
                        adjustContainerButton.title = 'Ajuster le conteneur';
                        adjustContainerButton.addEventListener('click', function() {
                            adjustContainerSize(scale);
                        });
                        
                        zoomControls.appendChild(adjustContainerButton);
                    }
                    
                    // Ajuster le conteneur initialement
                    setTimeout(() => {
                        adjustContainerSize(scale);
                    }, 500);
                })
                .catch(function(error) {
                    console.error('Erreur lors du chargement du PDF:', error);
                    if (pdfContainer) {
                        pdfContainer.innerHTML = '<div class="alert alert-danger m-3">Erreur lors du chargement du PDF. Veuillez vérifier que le fichier existe et qu\'il s\'agit bien d\'un PDF valide.</div>';
                    }
                });
        } else {
            console.error('URL du PDF non trouvée');
            if (pdfContainer) {
                pdfContainer.innerHTML = '<div class="alert alert-danger m-3">URL du PDF non trouvée.</div>';
            }
        }

        // Fonction pour dessiner les surlignages
        function drawHighlights() {
            // Supprimer les anciens surlignages
            highlights.forEach(highlight => highlight.remove());
            highlights = [];

            // Créer de nouveaux surlignages
            searchMatches.forEach((match, index) => {
                if (match.pageIndex === pageNum - 1) {
                    const highlight = document.createElement('div');
                    highlight.className = 'highlight';
                    if (index === currentMatchIndex) {
                        highlight.classList.add('current-highlight');
                    }

                    // Positionner le surlignage
                    const viewport = pdfDoc.getPage(pageNum).getViewport({ scale: scale });
                    
                    // Ajuster la taille du canvas selon le mode immersif ou non
                    if (document.body.classList.contains('immersive-mode')) {
                        // En mode immersif, on utilise une taille optimisée pour l'écran
                        const containerWidth = pdfContainer.clientWidth;
                        const containerHeight = pdfContainer.clientHeight;
                        
                        // Calculer le ratio pour s'adapter à la hauteur ou à la largeur selon ce qui est le plus contraignant
                        const widthRatio = containerWidth / viewport.width;
                        const heightRatio = containerHeight / viewport.height;
                        const fitRatio = Math.min(widthRatio, heightRatio) * 0.95; // 95% pour laisser une marge
                        
                        // Appliquer la nouvelle échelle
                        const immersiveScale = scale * fitRatio;
                        const immersiveViewport = page.getViewport({ scale: immersiveScale });
                        
                        highlight.style.left = match.left * immersiveScale + 'px';
                        highlight.style.top = match.top * immersiveScale + 'px';
                        highlight.style.width = match.width * immersiveScale + 'px';
                        highlight.style.height = match.height * immersiveScale + 'px';
                    } else {
                        highlight.style.left = match.left * scale + 'px';
                        highlight.style.top = match.top * scale + 'px';
                        highlight.style.width = match.width * scale + 'px';
                        highlight.style.height = match.height * scale + 'px';
                    }
                    
                    pdfContainer.appendChild(highlight);
                    highlights.push(highlight);
                }
            });
        }

        // Recherche dans le PDF
        if (searchButton && searchInput) {
            searchButton.addEventListener('click', function() {
                const query = searchInput.value.trim();
                if (query === '') return;

                searchMatches = [];
                currentMatchIndex = -1;

                // Rechercher dans toutes les pages
                let numPagesSearched = 0;
                for (let i = 1; i <= pdfDoc.numPages; i++) {
                    pdfDoc.getPage(i).then(page => {
                        page.getTextContent().then(textContent => {
                            const text = textContent.items.map(item => item.str).join(' ');
                            const regex = new RegExp(query, 'gi');
                            let match;

                            while ((match = regex.exec(text)) !== null) {
                                // Trouver l'item qui contient cette correspondance
                                let charIndex = match.index;
                                let itemIndex = 0;

                                while (charIndex >= 0 && itemIndex < textContent.items.length) {
                                    const item = textContent.items[itemIndex];
                                    if (charIndex < item.str.length) {
                                        // Correspondance trouvée dans cet item
                                        searchMatches.push({
                                            pageIndex: i - 1,
                                            text: match[0],
                                            left: item.transform[4],
                                            top: item.transform[5],
                                            width: item.width,
                                            height: item.height
                                        });
                                        break;
                                    }
                                    charIndex -= item.str.length + 1; // +1 pour l'espace
                                    itemIndex++;
                                }
                            }

                            numPagesSearched++;
                            if (numPagesSearched === pdfDoc.numPages) {
                                // Toutes les pages ont été recherchées
                                updateSearchResults();
                            }
                        });
                    });
                }
            });

            // Fonction pour mettre à jour les résultats de recherche
            function updateSearchResults() {
                if (searchMatches.length > 0) {
                    prevMatchButton.disabled = false;
                    nextMatchButton.disabled = false;
                    currentMatchIndex = 0;
                    matchInfoSpan.textContent = `1 sur ${searchMatches.length}`;

                    // Aller à la première correspondance
                    const match = searchMatches[0];
                    if (pageNum !== match.pageIndex + 1) {
                        pageNum = match.pageIndex + 1;
                        queueRenderPage(pageNum);
                    } else {
                        drawHighlights();
                    }
                } else {
                    prevMatchButton.disabled = true;
                    nextMatchButton.disabled = true;
                    matchInfoSpan.textContent = 'Aucune correspondance';
                }
            }

            // Navigation entre les correspondances
            if (prevMatchButton) {
                prevMatchButton.addEventListener('click', function() {
                    if (searchMatches.length === 0) return;

                    currentMatchIndex = (currentMatchIndex - 1 + searchMatches.length) % searchMatches.length;
                    const match = searchMatches[currentMatchIndex];
                    matchInfoSpan.textContent = `${currentMatchIndex + 1} sur ${searchMatches.length}`;

                    if (pageNum !== match.pageIndex + 1) {
                        pageNum = match.pageIndex + 1;
                        queueRenderPage(pageNum);
                    } else {
                        drawHighlights();
                    }
                });
            }

            if (nextMatchButton) {
                nextMatchButton.addEventListener('click', function() {
                    if (searchMatches.length === 0) return;

                    currentMatchIndex = (currentMatchIndex + 1) % searchMatches.length;
                    const match = searchMatches[currentMatchIndex];
                    matchInfoSpan.textContent = `${currentMatchIndex + 1} sur ${searchMatches.length}`;

                    if (pageNum !== match.pageIndex + 1) {
                        pageNum = match.pageIndex + 1;
                        queueRenderPage(pageNum);
                    } else {
                        drawHighlights();
                    }
                });
            }
        }

        // Gestion des signets
        if (addBookmarkButton && bookmarkNameInput) {
            addBookmarkButton.addEventListener('click', function() {
                const name = bookmarkNameInput.value.trim();
                if (name === '') return;

                // Ajouter un signet pour la page actuelle
                const bookmark = {
                    name: name,
                    page: pageNum,
                    pdfId: <?php echo $pdf['id']; ?>
                };

                // Stocker le signet dans localStorage
                let bookmarks = JSON.parse(localStorage.getItem('pdfBookmarks') || '{}');
                if (!bookmarks[<?php echo $pdf['id']; ?>]) {
                    bookmarks[<?php echo $pdf['id']; ?>] = [];
                }
                bookmarks[<?php echo $pdf['id']; ?>].push(bookmark);
                localStorage.setItem('pdfBookmarks', JSON.stringify(bookmarks));

                // Mettre à jour l'affichage des signets
                displayBookmarks();

                // Effacer le champ de saisie
                bookmarkNameInput.value = '';
            });

            // Fonction pour afficher les signets
            function displayBookmarks() {
                const bookmarks = JSON.parse(localStorage.getItem('pdfBookmarks') || '{}');
                const pdfBookmarks = bookmarks[<?php echo $pdf['id']; ?>] || [];

                if (pdfBookmarks.length === 0) {
                    bookmarksListDiv.innerHTML = '<div class="text-center text-muted"><i>Aucun signet pour ce PDF</i></div>';
                    return;
                }

                bookmarksListDiv.innerHTML = '';
                pdfBookmarks.forEach((bookmark, index) => {
                    const bookmarkItem = document.createElement('button');
                    bookmarkItem.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                    bookmarkItem.innerHTML = `
                    <span>${bookmark.name} (Page ${bookmark.page})</span>
                    <button class="btn btn-sm btn-outline-danger delete-bookmark" data-index="${index}">
                        <i class="bi bi-x"></i>
                    </button>
                `;

                    bookmarkItem.addEventListener('click', function(e) {
                        if (e.target.closest('.delete-bookmark')) return;

                        // Aller à la page du signet
                        const pageNum = bookmark.page;
                        queueRenderPage(pageNum);
                    });

                    bookmarksListDiv.appendChild(bookmarkItem);
                });

                // Ajouter des gestionnaires d'événements pour supprimer les signets
                document.querySelectorAll('.delete-bookmark').forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const index = parseInt(this.dataset.index);

                        // Supprimer le signet
                        let bookmarks = JSON.parse(localStorage.getItem('pdfBookmarks') || '{}');
                        bookmarks[<?php echo $pdf['id']; ?>].splice(index, 1);
                        localStorage.setItem('pdfBookmarks', JSON.stringify(bookmarks));

                        // Mettre à jour l'affichage
                        displayBookmarks();
                    });
                });
            }

            // Afficher les signets au chargement
            displayBookmarks();
        }

        // Gestion des notes
        if (pdfNotesTextarea && saveNotesButton) {
            // Charger les notes existantes
            const notes = localStorage.getItem(`pdfNotes_${<?php echo $pdf['id']; ?>}`) || '';
            pdfNotesTextarea.value = notes;

            // Enregistrer les notes
            saveNotesButton.addEventListener('click', function() {
                const notes = pdfNotesTextarea.value;
                localStorage.setItem(`pdfNotes_${<?php echo $pdf['id']; ?>}`, notes);

                // Afficher un message de confirmation
                notesStatusDiv.innerHTML = '<div class="alert alert-success">Notes enregistrées avec succès</div>';
                setTimeout(() => {
                    notesStatusDiv.innerHTML = '';
                }, 3000);
            });
        }

        // Ajouter la navigation avec les touches du clavier
        document.addEventListener('keydown', function(e) {
            // Ne pas intercepter les événements si un champ de texte est actif
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }

            switch (e.key) {
                case 'ArrowLeft':
                    goPrevPage();
                    break;
                case 'ArrowRight':
                    goNextPage();
                    break;
                case 'f':
                    if (toggleImmersiveButton) {
                        toggleImmersiveButton.click();
                    }
                    break;
                case 'm':
                    if (toggleMusicPlayerButton) {
                        toggleMusicPlayerButton.click();
                    }
                    break;
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Ajouter un gestionnaire d'événements pour le bouton de suppression
        const deleteButton = document.querySelector('.btn-danger[data-action="delete"]');
        if (deleteButton) {
            deleteButton.addEventListener('click', function(e) {
                e.preventDefault();
                const deleteDialog = document.getElementById('delete-dialog');
                if (deleteDialog) {
                    deleteDialog.style.display = 'block';
                }
            });
        }
        
        // Gestion du bouton de suppression dans la boîte de dialogue personnalisée
        document.getElementById('delete-pdf').addEventListener('click', function() {
            window.location.href = 'delete_pdf.php?id=<?php echo $id; ?>';
        });
        
        // Synchronisation des signets et des notes entre les versions mobile et desktop
        const syncMobileDesktop = () => {
            // Synchroniser les champs de saisie des signets
            if (document.getElementById('bookmarkName') && document.getElementById('bookmarkName-mobile')) {
                const desktopBookmarkInput = document.getElementById('bookmarkName');
                const mobileBookmarkInput = document.getElementById('bookmarkName-mobile');
                
                desktopBookmarkInput.addEventListener('input', function() {
                    mobileBookmarkInput.value = this.value;
                });
                
                mobileBookmarkInput.addEventListener('input', function() {
                    desktopBookmarkInput.value = this.value;
                });
                
                // Synchroniser les boutons d'ajout de signets
                document.getElementById('addBookmark-mobile').addEventListener('click', function() {
                    // Utiliser la même fonction que le bouton desktop
                    document.getElementById('addBookmark').click();
                });
            }
            
            // Synchroniser les zones de texte des notes
            if (document.getElementById('pdfNotes') && document.getElementById('pdfNotes-mobile')) {
                const desktopNotesTextarea = document.getElementById('pdfNotes');
                const mobileNotesTextarea = document.getElementById('pdfNotes-mobile');
                
                desktopNotesTextarea.addEventListener('input', function() {
                    mobileNotesTextarea.value = this.value;
                });
                
                mobileNotesTextarea.addEventListener('input', function() {
                    desktopNotesTextarea.value = this.value;
                });
                
                // Synchroniser les boutons de sauvegarde des notes
                document.getElementById('saveNotes-mobile').addEventListener('click', function() {
                    // Utiliser la même fonction que le bouton desktop
                    document.getElementById('saveNotes').click();
                });
            }
            
            // Synchroniser l'affichage des signets
            const updateBookmarksList = (bookmarks) => {
                const desktopList = document.getElementById('bookmarksList');
                const mobileList = document.getElementById('bookmarksList-mobile');
                
                if (desktopList && mobileList) {
                    mobileList.innerHTML = desktopList.innerHTML;
                    
                    // Ajouter les écouteurs d'événements aux éléments du mobile
                    const mobileBookmarkItems = mobileList.querySelectorAll('.bookmark-item');
                    mobileBookmarkItems.forEach(item => {
                        item.addEventListener('click', function() {
                            const pageNum = parseInt(this.dataset.page);
                            if (!isNaN(pageNum)) {
                                queueRenderPage(pageNum);
                            }
                        });
                    });
                }
            };
            
            // Observer les changements dans la liste des signets desktop
            const observer = new MutationObserver(() => {
                updateBookmarksList();
            });
            
            const desktopBookmarksList = document.getElementById('bookmarksList');
            if (desktopBookmarksList) {
                observer.observe(desktopBookmarksList, { childList: true, subtree: true });
            }
            
            // Synchroniser les statuts des notes
            const updateNotesStatus = (message) => {
                const desktopStatus = document.getElementById('notesStatus');
                const mobileStatus = document.getElementById('notesStatus-mobile');
                
                if (desktopStatus && mobileStatus) {
                    mobileStatus.textContent = desktopStatus.textContent;
                }
            };
            
            // Observer les changements dans le statut des notes desktop
            const statusObserver = new MutationObserver(() => {
                updateNotesStatus();
            });
            
            const desktopNotesStatus = document.getElementById('notesStatus');
            if (desktopNotesStatus) {
                statusObserver.observe(desktopNotesStatus, { childList: true, characterData: true, subtree: true });
            }
        };
        
        // Initialiser la synchronisation
        syncMobileDesktop();
        
        // Ajuster la taille du canvas PDF pour les appareils mobiles
        const adjustPdfCanvasForMobile = () => {
            const pdfCanvas = document.getElementById('pdfCanvas');
            const pdfContainer = document.querySelector('.pdf-container');
            
            if (pdfCanvas && pdfContainer) {
                // Calculer la nouvelle largeur du conteneur en fonction de la largeur de l'écran
                const updateCanvasSize = () => {
                    // Ajuster la taille du canvas en fonction de l'échelle
                    if (window.innerWidth < 768) {
                        // Sur mobile, toujours utiliser 100% de largeur
                        const containerWidth = pdfContainer.clientWidth;
                        if (pdfCanvas.width > containerWidth) {
                            const scaleFactor = containerWidth / pdfCanvas.width;
                            // Utiliser la fonction updateScale pour ajuster l'échelle
                            if (typeof updateScale === 'function' && !pageRendering) {
                                // Calculer la nouvelle échelle en fonction de la largeur du conteneur
                                const newScale = scale * 0.95; // Réduire légèrement pour avoir une marge
                                console.log("Ajustement de l'échelle pour mobile:", newScale);
                                updateScale(newScale);
                            }
                        }
                    }
                };
                
                // Mettre à jour la taille lors du redimensionnement de la fenêtre
                window.addEventListener('resize', () => {
                    // Attendre que le rendu soit terminé avant de redimensionner
                    if (!pageRendering) {
                        setTimeout(updateCanvasSize, 200);
                    }
                });
                
                // Appeler une première fois pour initialiser après le chargement complet
                setTimeout(updateCanvasSize, 1000);
            }
        };
        
        // Initialiser l'ajustement du canvas
        adjustPdfCanvasForMobile();
        
        // Gestion du lecteur de musique
        const initMusicPlayer = () => {
            const audioPlayer = document.getElementById('audioPlayer');
            const floatingAudioPlayer = document.getElementById('floating-audio-player');
            const playPauseBtn = document.getElementById('playPause');
            const prevTrackBtn = document.getElementById('prevTrack');
            const nextTrackBtn = document.getElementById('nextTrack');
            const autoplaySwitch = document.getElementById('autoplaySwitch');
            const musicItems = document.querySelectorAll('.music-item');
            const currentTrackName = document.getElementById('current-track-name');
            const floatingPlayer = document.getElementById('floating-player');
            
            let currentTrackIndex = -1;
            let playlist = [];
            
            // Initialiser la playlist
            musicItems.forEach(item => {
                playlist.push({
                    url: item.dataset.musicUrl,
                    index: parseInt(item.dataset.musicIndex),
                    name: item.innerText.trim()
                });
                
                // Ajouter un événement de clic sur chaque élément de la liste
                item.addEventListener('click', function() {
                    const index = parseInt(this.dataset.musicIndex);
                    playTrack(index);
                });
            });
            
            // Activer/désactiver les boutons de navigation
            const updateNavigationButtons = () => {
                prevTrackBtn.disabled = currentTrackIndex <= 0;
                nextTrackBtn.disabled = currentTrackIndex >= playlist.length - 1;
            };
            
            // Jouer une piste spécifique
            const playTrack = (index) => {
                if (index >= 0 && index < playlist.length) {
                    currentTrackIndex = index;
                    
                    // Mettre à jour l'URL de la source audio
                    audioPlayer.src = playlist[index].url;
                    floatingAudioPlayer.src = playlist[index].url;
                    
                    // Mettre à jour le nom de la piste
                    if (currentTrackName) {
                        currentTrackName.textContent = playlist[index].name;
                    }
                    
                    // Jouer la piste
                    audioPlayer.play().catch(error => {
                        console.error('Erreur lors de la lecture:', error);
                    });
                    
                    floatingAudioPlayer.play().catch(error => {
                        console.error('Erreur lors de la lecture (flottant):', error);
                    });
                    
                    // Afficher le lecteur flottant
                    if (floatingPlayer) {
                        floatingPlayer.style.display = 'block';
                    }
                    
                    // Mettre à jour l'interface
                    updateNavigationButtons();
                    updateActiveTrack();
                    updatePlayPauseButton();
                }
            };
            
            // Mettre à jour la piste active dans la liste
            const updateActiveTrack = () => {
                musicItems.forEach(item => {
                    const itemIndex = parseInt(item.dataset.musicIndex);
                    if (itemIndex === currentTrackIndex) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
            };
            
            // Mettre à jour le bouton lecture/pause
            const updatePlayPauseButton = () => {
                if (audioPlayer.paused) {
                    playPauseBtn.innerHTML = '<i class="bi bi-play-fill"></i> Lecture';
                } else {
                    playPauseBtn.innerHTML = '<i class="bi bi-pause-fill"></i> Pause';
                }
            };
            
            // Synchroniser les lecteurs audio
            const syncAudioPlayers = () => {
                // Synchroniser la lecture/pause
                audioPlayer.addEventListener('play', () => {
                    if (floatingAudioPlayer.paused) {
                        floatingAudioPlayer.currentTime = audioPlayer.currentTime;
                        floatingAudioPlayer.play();
                    }
                });
                
                audioPlayer.addEventListener('pause', () => {
                    if (!floatingAudioPlayer.paused) {
                        floatingAudioPlayer.pause();
                    }
                });
                
                floatingAudioPlayer.addEventListener('play', () => {
                    if (audioPlayer.paused) {
                        audioPlayer.currentTime = floatingAudioPlayer.currentTime;
                        audioPlayer.play();
                    }
                });
                
                floatingAudioPlayer.addEventListener('pause', () => {
                    if (!audioPlayer.paused) {
                        audioPlayer.pause();
                    }
                });
                
                // Synchroniser la position de lecture
                audioPlayer.addEventListener('timeupdate', () => {
                    if (Math.abs(audioPlayer.currentTime - floatingAudioPlayer.currentTime) > 0.5) {
                        floatingAudioPlayer.currentTime = audioPlayer.currentTime;
                    }
                });
                
                floatingAudioPlayer.addEventListener('timeupdate', () => {
                    if (Math.abs(floatingAudioPlayer.currentTime - audioPlayer.currentTime) > 0.5) {
                        audioPlayer.currentTime = floatingAudioPlayer.currentTime;
                    }
                });
            };
            
            // Événement pour le bouton lecture/pause
            playPauseBtn.addEventListener('click', function() {
                if (currentTrackIndex === -1 && playlist.length > 0) {
                    // Si aucune piste n'est sélectionnée, jouer la première
                    playTrack(0);
                } else if (audioPlayer.paused) {
                    audioPlayer.play();
                    updatePlayPauseButton();
                } else {
                    audioPlayer.pause();
                    updatePlayPauseButton();
                }
            });
            
            // Événement pour le bouton précédent
            prevTrackBtn.addEventListener('click', function() {
                if (currentTrackIndex > 0) {
                    playTrack(currentTrackIndex - 1);
                }
            });
            
            // Événement pour le bouton suivant
            nextTrackBtn.addEventListener('click', function() {
                if (currentTrackIndex < playlist.length - 1) {
                    playTrack(currentTrackIndex + 1);
                }
            });
            
            // Événement de fin de piste
            audioPlayer.addEventListener('ended', function() {
                if (autoplaySwitch.checked && currentTrackIndex < playlist.length - 1) {
                    // Passer à la piste suivante si l'autoplay est activé
                    playTrack(currentTrackIndex + 1);
                } else {
                    // Réinitialiser l'interface
                    updatePlayPauseButton();
                }
            });
            
            floatingAudioPlayer.addEventListener('ended', function() {
                if (autoplaySwitch.checked && currentTrackIndex < playlist.length - 1) {
                    // Passer à la piste suivante si l'autoplay est activé
                    playTrack(currentTrackIndex + 1);
                } else {
                    // Réinitialiser l'interface
                    updatePlayPauseButton();
                }
            });
            
            // Événements pour mettre à jour l'interface
            audioPlayer.addEventListener('play', updatePlayPauseButton);
            audioPlayer.addEventListener('pause', updatePlayPauseButton);
            
            // Initialiser les boutons de navigation
            updateNavigationButtons();
            
            // Synchroniser les lecteurs audio
            syncAudioPlayers();
            
            // Gestion du lecteur flottant
            const showMusicDialogBtn = document.getElementById('show-music-dialog');
            const closeFloatingPlayerBtn = document.getElementById('close-floating-player');
            const musicDialog = document.getElementById('music-dialog');
            
            if (showMusicDialogBtn && musicDialog) {
                showMusicDialogBtn.addEventListener('click', function() {
                    musicDialog.style.display = 'block';
                });
            }
            
            if (closeFloatingPlayerBtn && floatingPlayer) {
                closeFloatingPlayerBtn.addEventListener('click', function() {
                    floatingPlayer.style.display = 'none';
                });
            }
            
            // Fermer la boîte de dialogue de musique mais garder le lecteur flottant
            const closeMusicDialogBtn = document.getElementById('close-music-dialog');
            if (closeMusicDialogBtn && musicDialog) {
                closeMusicDialogBtn.addEventListener('click', function() {
                    musicDialog.style.display = 'none';
                    // Garder le lecteur flottant visible si une piste est en cours de lecture
                    if (currentTrackIndex >= 0 && !audioPlayer.paused) {
                        floatingPlayer.style.display = 'block';
                    }
                });
            }
            
            // Ajouter un bouton "Continuer en arrière-plan" dans la boîte de dialogue de musique
            const dialogFooter = document.querySelector('.dialog-footer');
            if (dialogFooter) {
                const continueBackgroundBtn = document.createElement('button');
                continueBackgroundBtn.className = 'btn btn-sm btn-primary';
                continueBackgroundBtn.innerHTML = 'Continuer en arrière-plan';
                continueBackgroundBtn.addEventListener('click', function() {
                    musicDialog.style.display = 'none';
                    // Afficher le lecteur flottant
                    if (currentTrackIndex >= 0) {
                        floatingPlayer.style.display = 'block';
                    }
                });
                dialogFooter.prepend(continueBackgroundBtn);
            }
        };
        
        // Initialiser le lecteur de musique lorsque la boîte de dialogue est ouverte
        const musicDialog = document.getElementById('music-dialog');
        if (musicDialog) {
            // Initialiser le lecteur dès le chargement de la page
            initMusicPlayer();
        }
        
        // Gestion du bouton pour afficher le lecteur de musique
        const toggleMusicPlayerButton = document.getElementById('toggleMusicPlayerButton');
        
        if (toggleMusicPlayerButton) {
            toggleMusicPlayerButton.addEventListener('click', function() {
                const musicDialog = document.getElementById('music-dialog');
                if (musicDialog) {
                    musicDialog.style.display = 'block';
                }
            });
        }
        
        // Supprimer complètement le backdrop des modales
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.setAttribute('data-bs-backdrop', 'false');
            modal.setAttribute('data-bs-keyboard', 'false');
        });
        
        // Fermer les boîtes de dialogue personnalisées
        const closeMusicDialogButton = document.getElementById('close-music-dialog');
        if (closeMusicDialogButton) {
            closeMusicDialogButton.addEventListener('click', function() {
                const musicDialog = document.getElementById('music-dialog');
                if (musicDialog) {
                    musicDialog.style.display = 'none';
                }
            });
        }
        
        const closeDeleteDialogButton = document.getElementById('close-delete-dialog');
        if (closeDeleteDialogButton) {
            closeDeleteDialogButton.addEventListener('click', function() {
                const deleteDialog = document.getElementById('delete-dialog');
                if (deleteDialog) {
                    deleteDialog.style.display = 'none';
                }
            });
        }
        
        const cancelDeleteButton = document.getElementById('cancel-delete');
        if (cancelDeleteButton) {
            cancelDeleteButton.addEventListener('click', function() {
                const deleteDialog = document.getElementById('delete-dialog');
                if (deleteDialog) {
                    deleteDialog.style.display = 'none';
                }
            });
        }
    });
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>
