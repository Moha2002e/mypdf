<?php
/**
 * Script pour associer chaque PDF à son cours correspondant
 * Ce script analyse le titre, la description et les tags de chaque PDF
 * pour déterminer le cours le plus pertinent
 */
require_once 'includes/functions.php';

// Afficher l'en-tête
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Association des PDFs aux cours</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Progression de l'association</h5>
            <div id="association-log" class="alert alert-info" style="max-height: 300px; overflow-y: auto;">
                Démarrage de l'association des PDFs aux cours...
            </div>
            
            <div class="progress mb-3">
                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
            
            <div id="association-status"></div>
        </div>
    </div>
    
    <div class="text-center mb-4">
        <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
    </div>
</div>

<?php
// Fonction pour ajouter un message au log
function addToLog($message) {
    echo "<script>
        document.getElementById('association-log').innerHTML += '<br>' + '" . addslashes($message) . "';
        document.getElementById('association-log').scrollTop = document.getElementById('association-log').scrollHeight;
    </script>";
    flush();
    ob_flush();
}

// Fonction pour mettre à jour la barre de progression
function updateProgress($percentage) {
    echo "<script>
        document.getElementById('progress-bar').style.width = '{$percentage}%';
        document.getElementById('progress-bar').setAttribute('aria-valuenow', '{$percentage}');
    </script>";
    flush();
    ob_flush();
}

// Fonction pour définir le statut final
function setStatus($status, $success = true) {
    $class = $success ? 'success' : 'danger';
    echo "<script>
        document.getElementById('association-status').innerHTML = '<div class=\"alert alert-{$class}\">{$status}</div>';
    </script>";
    flush();
    ob_flush();
}

// Fonction pour calculer la pertinence d'un cours pour un PDF
function calculateRelevance($pdf, $course) {
    $relevance = 0;
    $title = strtolower($pdf['title']);
    $description = isset($pdf['description']) ? strtolower($pdf['description']) : '';
    $tags = isset($pdf['tags']) ? strtolower($pdf['tags']) : '';
    $category = isset($pdf['category']) ? strtolower($pdf['category']) : '';
    $courseName = strtolower($course['name']);
    $courseDesc = strtolower($course['description']);
    
    // Mots-clés spécifiques pour chaque cours
    $courseKeywords = [
        'Analyse 1' => ['analyse', 'mathématique', 'calcul', 'fonction', 'dérivée', 'intégrale', 'limite'],
        'Analyse 2' => ['analyse', 'mathématique', 'calcul', 'fonction', 'dérivée', 'intégrale', 'limite', 'série'],
        'SGBD 3' => ['sgbd', 'base de données', 'sql', 'requête', 'table', 'jointure'],
        'SGBD 4' => ['sgbd', 'base de données', 'sql', 'requête', 'table', 'jointure', 'transaction'],
        'BD Avancées' => ['base de données', 'sql', 'nosql', 'mongodb', 'oracle', 'optimisation'],
        'JAVA' => ['java', 'oop', 'objet', 'classe', 'interface', 'exception', 'thread'],
        'C Thread' => ['thread', 'processus', 'concurrence', 'mutex', 'sémaphore'],
        'C Linux' => ['linux', 'unix', 'posix', 'système', 'fichier', 'processus'],
        'CPP' => ['c++', 'cpp', 'classe', 'template', 'stl', 'objet'],
        'C#' => ['c#', 'csharp', '.net', 'wpf', 'linq', 'entity'],
        'Design Pattern' => ['design pattern', 'patron', 'conception', 'factory', 'singleton', 'observer', 'adapter', 'composite', 'builder'],
        'Complete BD' => ['base de données', 'sql', 'modèle', 'entité', 'relation', 'normalisation'],
        'Statistique' => ['statistique', 'probabilité', 'moyenne', 'écart-type', 'distribution', 'test', 'labo', 'laboratoire']
    ];
    
    // Vérifier si le nom du cours apparaît dans le titre, la description ou les tags
    if (stripos($title, $courseName) !== false) {
        $relevance += 10;
    }
    if (stripos($description, $courseName) !== false) {
        $relevance += 5;
    }
    if (stripos($tags, $courseName) !== false) {
        $relevance += 5;
    }
    if (stripos($category, $courseName) !== false) {
        $relevance += 3;
    }
    
    // Vérifier les mots-clés spécifiques au cours
    if (isset($courseKeywords[$course['name']])) {
        foreach ($courseKeywords[$course['name']] as $keyword) {
            if (stripos($title, $keyword) !== false) {
                $relevance += 3;
            }
            if (stripos($description, $keyword) !== false) {
                $relevance += 2;
            }
            if (stripos($tags, $keyword) !== false) {
                $relevance += 2;
            }
            if (stripos($category, $keyword) !== false) {
                $relevance += 1;
            }
        }
    }
    
    // Associations spécifiques basées sur le titre
    if ($course['name'] == 'Statistique' && (
        stripos($title, 'stat') !== false || 
        stripos($category, 'laboratoire') !== false ||
        stripos($title, 'labo') !== false
    )) {
        $relevance += 15;
    }
    
    if ($course['name'] == 'C Thread' && (
        stripos($title, 'thread') !== false || 
        $title == 'threads' || 
        stripos($title, 'fiche threads') !== false
    )) {
        $relevance += 15;
    }
    
    if ($course['name'] == 'Design Pattern' && (
        $title == 'builder' || 
        $title == 'adapter' || 
        $title == 'composite' || 
        $title == 'factory' || 
        stripos($title, 'design pattern') !== false
    )) {
        $relevance += 15;
    }
    
    if ($course['name'] == 'JAVA' && stripos($title, 'exception') !== false) {
        $relevance += 15;
    }
    
    return $relevance;
}

// Activer la sortie de buffer pour afficher les mises à jour en temps réel
ob_start();

try {
    // Connexion à la base de données
    addToLog("Connexion à la base de données...");
    $pdo = getDbConnection();
    addToLog("Connexion établie avec succès.");
    updateProgress(10);
    
    // Récupérer tous les PDFs
    addToLog("Récupération de tous les PDFs...");
    $pdfs = $pdo->query("SELECT * FROM pdfs")->fetchAll(PDO::FETCH_ASSOC);
    $totalPdfs = count($pdfs);
    addToLog("Nombre de PDFs trouvés : {$totalPdfs}");
    updateProgress(20);
    
    // Récupérer tous les cours
    addToLog("Récupération de tous les cours...");
    $courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);
    $totalCourses = count($courses);
    addToLog("Nombre de cours trouvés : {$totalCourses}");
    updateProgress(30);
    
    // Associer chaque PDF au cours le plus pertinent
    addToLog("Début de l'association des PDFs aux cours...");
    $associatedCount = 0;
    
    foreach ($pdfs as $index => $pdf) {
        $progress = 30 + (($index + 1) / $totalPdfs) * 60;
        updateProgress($progress);
        
        $bestCourse = null;
        $bestRelevance = -1;
        
        foreach ($courses as $course) {
            $relevance = calculateRelevance($pdf, $course);
            
            if ($relevance > $bestRelevance) {
                $bestRelevance = $relevance;
                $bestCourse = $course;
            }
        }
        
        if ($bestCourse && $bestRelevance > 0) {
            // Mettre à jour le PDF avec le cours le plus pertinent
            $stmt = $pdo->prepare("UPDATE pdfs SET course_id = ? WHERE id = ?");
            $stmt->execute([$bestCourse['id'], $pdf['id']]);
            
            addToLog("PDF '{$pdf['title']}' associé au cours '{$bestCourse['name']}' (pertinence: {$bestRelevance})");
            $associatedCount++;
        } else {
            addToLog("Aucun cours pertinent trouvé pour le PDF '{$pdf['title']}'");
        }
    }
    
    updateProgress(90);
    
    // Afficher un résumé
    addToLog("Association terminée. {$associatedCount} PDFs sur {$totalPdfs} ont été associés à un cours.");
    
    // Afficher les associations par cours
    addToLog("Résumé des associations par cours :");
    foreach ($courses as $course) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pdfs WHERE course_id = ?");
        $stmt->execute([$course['id']]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            addToLog("- {$course['name']} : {$count} PDFs");
        }
    }
    
    updateProgress(100);
    setStatus("Association terminée avec succès. {$associatedCount} PDFs ont été associés à un cours.");
    
} catch (Exception $e) {
    setStatus("Erreur lors de l'association : " . $e->getMessage(), false);
}

// Inclure le pied de page
include 'includes/footer.php';
?>
