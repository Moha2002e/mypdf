<?php
require_once 'functions.php';

// Récupérer le thème actuel
$theme = getSetting('theme', 'light');
$viewMode = getSetting('view_mode', 'grid');

// Déterminer la page active
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPDF - Votre bibliothèque PDF personnelle</title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Animation CSS - AOS -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    
    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <!-- Script d'animations -->
    <script src="assets/js/animations.js" defer></script>
    
    <style>
        /* Animations et transitions de page */
        body {
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .page-transition {
            opacity: 0;
            transition: opacity 0.3s ease-out;
        }
        
        .page-loaded {
            opacity: 1;
        }
        
        /* Animation pour les badges */
        .scale-in-animation {
            animation: scaleIn 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        .scale-out-animation {
            animation: scaleOut 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        @keyframes scaleIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        @keyframes scaleOut {
            from { transform: scale(1); opacity: 1; }
            to { transform: scale(0); opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- Fond avec effet de parallaxe -->
    <?php if ($theme === 'light'): ?>
    <div class="parallax-bg" style="background-image: linear-gradient(135deg, rgba(248, 249, 250, 0.8) 0%, rgba(248, 249, 250, 0.4) 100%);"></div>
    <?php else: ?>
    <div class="parallax-bg" style="background-image: linear-gradient(135deg, rgba(33, 37, 41, 0.8) 0%, rgba(33, 37, 41, 0.4) 100%);"></div>
    <?php endif; ?>
    
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg <?php echo $theme === 'dark' ? 'navbar-dark bg-dark' : 'navbar-light bg-light'; ?> shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    <div class="brand-icon me-2 float-animation">
                        <i class="bi bi-file-earmark-pdf-fill fs-3 text-danger"></i>
                    </div>
                    <div class="brand-text">
                        <span class="fw-bold gradient-heading">MyPDF</span>
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" 
                               href="index.php">
                                <i class="bi bi-house-door"></i> <span class="ms-1">Accueil</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'list_pdfs.php' ? 'active' : ''; ?>" 
                               href="list_pdfs.php">
                                <i class="bi bi-list-ul"></i> <span class="ms-1">Tous les PDFs</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'courses.php' || $currentPage === 'course_pdfs.php' ? 'active' : ''; ?>" 
                               href="courses.php">
                                <i class="bi bi-book"></i> <span class="ms-1">Cours</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'upload_pdf.php' ? 'active' : ''; ?>" 
                               href="upload_pdf.php">
                                <i class="bi bi-upload"></i> <span class="ms-1">Ajouter</span>
                            </a>
                        </li>
                    </ul>
                    
                    <form class="d-flex position-relative mt-3 mt-lg-0" action="search.php" method="GET">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input class="form-control border-start-0" type="search" name="q" placeholder="Rechercher..."
                                   value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                            <button class="btn btn-primary d-none d-sm-block" type="submit">
                                Rechercher
                            </button>
                        </div>
                    </form>
                    
                    <ul class="navbar-nav ms-2">
                        <li class="nav-item dropdown">
                            <button class="btn nav-link dropdown-toggle" id="navbarDropdown" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>" 
                                       href="settings.php">
                                        <i class="bi bi-sliders"></i> Paramètres
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo $currentPage === 'export.php' ? 'active' : ''; ?>" 
                                       href="export.php">
                                        <i class="bi bi-download"></i> Exporter
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="#" id="toggleTheme">
                                        <?php if ($theme === 'dark'): ?>
                                            <i class="bi bi-sun"></i> Mode clair
                                        <?php else: ?>
                                            <i class="bi bi-moon"></i> Mode sombre
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" id="toggleView">
                                        <?php if ($viewMode === 'list'): ?>
                                            <i class="bi bi-grid"></i> Vue grille
                                        <?php else: ?>
                                            <i class="bi bi-list"></i> Vue liste
                                        <?php endif; ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Barre de progression de défilement -->
        <div class="scroll-progress-container d-none d-md-block">
            <div class="scroll-progress-bar" id="scrollProgress"></div>
        </div>
    </header>
    
    <main class="container py-4">
        <!-- Le contenu de la page sera inséré ici -->
