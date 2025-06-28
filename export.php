<?php
/**
 * Page d'exportation des données
 */
require_once 'includes/functions.php';

// Vérifier si un type d'export est spécifié
$exportType = isset($_GET['type']) ? $_GET['type'] : '';

// Traiter la demande d'exportation
if (!empty($exportType)) {
    switch ($exportType) {
        case 'csv':
            // Générer un export CSV
            $csvPath = generateCsvExport();
            if ($csvPath) {
                // Télécharger le fichier
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="pdf_export_' . date('Y-m-d') . '.csv"');
                header('Content-Length: ' . filesize($csvPath));
                readfile($csvPath);
                exit;
            }
            break;
            
        case 'json':
            // Générer un export JSON
            $jsonPath = generateJsonExport();
            if ($jsonPath) {
                // Télécharger le fichier
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="pdf_export_' . date('Y-m-d') . '.json"');
                header('Content-Length: ' . filesize($jsonPath));
                readfile($jsonPath);
                exit;
            }
            break;
            
        case 'zip':
            // Créer une archive ZIP
            $zipPath = createPdfBackup();
            if ($zipPath) {
                // Télécharger le fichier
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="pdf_backup_' . date('Y-m-d') . '.zip"');
                header('Content-Length: ' . filesize($zipPath));
                readfile($zipPath);
                exit;
            }
            break;
    }
}

// Si l'export a échoué ou aucun type n'est spécifié, afficher la page d'export
$message = '';
$messageType = '';

if (!empty($exportType)) {
    $message = 'L\'exportation a échoué. Veuillez réessayer.';
    $messageType = 'danger';
}

// Récupérer les statistiques
$pdo = getDbConnection();
$totalPdfs = $pdo->query("SELECT COUNT(*) FROM pdfs")->fetchColumn();

// Calculer la taille totale des fichiers PDF
$totalSize = 0;
$pdfs = getAllPdfs();
foreach ($pdfs as $pdf) {
    $filePath = __DIR__ . '/uploads/' . $pdf['file_name'];
    if (file_exists($filePath)) {
        $totalSize += filesize($filePath);
    }
}

// Formater la taille totale
$formattedTotalSize = $totalSize < 1048576 
    ? round($totalSize / 1024, 2) . ' Ko' 
    : round($totalSize / 1048576, 2) . ' Mo';

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-download me-2"></i>Exporter vos données</h1>
        <p class="lead">Exportez vos PDFs et leurs métadonnées pour les sauvegarder ou les transférer.</p>
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
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-download me-2"></i>Options d'exportation</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-file-earmark-spreadsheet display-4 text-success mb-3"></i>
                                <h5 class="card-title">Export CSV</h5>
                                <p class="card-text">Exporte les métadonnées de tous vos PDFs au format CSV.</p>
                                <a href="export.php?type=csv" class="btn btn-outline-success">
                                    <i class="bi bi-download me-1"></i>Télécharger CSV
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-file-earmark-code display-4 text-primary mb-3"></i>
                                <h5 class="card-title">Export JSON</h5>
                                <p class="card-text">Exporte les métadonnées de tous vos PDFs au format JSON.</p>
                                <a href="export.php?type=json" class="btn btn-outline-primary">
                                    <i class="bi bi-download me-1"></i>Télécharger JSON
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-file-earmark-zip display-4 text-danger mb-3"></i>
                                <h5 class="card-title">Archive ZIP</h5>
                                <p class="card-text">Crée une archive ZIP contenant tous vos PDFs et leurs métadonnées.</p>
                                <a href="export.php?type=zip" class="btn btn-outline-danger">
                                    <i class="bi bi-download me-1"></i>Télécharger ZIP
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note :</strong> L'exportation peut prendre un certain temps en fonction du nombre et de la taille de vos PDFs.
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-question-circle me-2"></i>À quoi servent ces exports ?</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="exportFaq">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                Pourquoi exporter mes données ?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#exportFaq">
                            <div class="accordion-body">
                                <p>L'exportation de vos données vous permet de :</p>
                                <ul>
                                    <li>Créer une sauvegarde de sécurité</li>
                                    <li>Transférer vos PDFs vers un autre système</li>
                                    <li>Analyser vos métadonnées dans un tableur</li>
                                    <li>Partager votre collection avec d'autres personnes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                Quelle est la différence entre les formats ?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#exportFaq">
                            <div class="accordion-body">
                                <ul>
                                    <li><strong>CSV</strong> : Format tabulaire simple, idéal pour l'importation dans Excel ou Google Sheets. Contient uniquement les métadonnées.</li>
                                    <li><strong>JSON</strong> : Format structuré utilisé pour l'échange de données entre applications. Contient uniquement les métadonnées.</li>
                                    <li><strong>ZIP</strong> : Archive complète contenant tous vos fichiers PDF ainsi qu'un fichier JSON avec les métadonnées.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                Comment réimporter mes données ?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#exportFaq">
                            <div class="accordion-body">
                                <p>Pour restaurer vos données à partir d'une archive ZIP :</p>
                                <ol>
                                    <li>Extrayez l'archive ZIP sur votre ordinateur</li>
                                    <li>Copiez les fichiers PDF dans le dossier <code>uploads</code> de votre application</li>
                                    <li>Importez le fichier <code>metadata.json</code> via la fonction d'importation (à venir dans une prochaine version)</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistiques</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Nombre total de PDFs</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $totalPdfs; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Taille totale des PDFs</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $formattedTotalSize; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Nombre de catégories</span>
                        <span class="badge bg-primary rounded-pill"><?php echo count(getAllCategories()); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Nombre de tags</span>
                        <span class="badge bg-primary rounded-pill"><?php echo count(getAllTags()); ?></span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Conseils de sauvegarde</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Important :</strong> Effectuez des sauvegardes régulières pour éviter la perte de données.
                </div>
                
                <h6><i class="bi bi-check2-circle me-2"></i>Bonnes pratiques</h6>
                <ul>
                    <li>Exportez régulièrement une archive ZIP complète</li>
                    <li>Stockez vos sauvegardes dans plusieurs endroits (disque externe, cloud)</li>
                    <li>Vérifiez périodiquement que vos sauvegardes sont exploitables</li>
                    <li>Utilisez des noms de fichiers descriptifs pour vos PDFs</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>
