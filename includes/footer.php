    </main>
    
    <footer class="bg-light py-4 mt-5 shadow-sm animate-on-load animate-delay-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                        <div class="brand-icon me-2 animate-float">
                            <i class="bi bi-file-earmark-pdf-fill fs-3 text-danger"></i>
                        </div>
                        <div class="brand-text">
                            <span class="fw-bold gradient-heading">MyPDF</span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 small">Votre bibliothèque PDF personnelle</p>
                </div>
                <div class="col-md-4 text-center mb-3 mb-md-0">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> - Tous droits réservés</p>
                    <div class="mt-2">
                        <a href="#" class="text-decoration-none me-2 animated-underline hover-scale">Confidentialité</a>
                        <a href="#" class="text-decoration-none me-2 animated-underline hover-scale">Conditions</a>
                        <a href="#" class="text-decoration-none animated-underline hover-scale">Contact</a>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end">
                        <button class="btn btn-sm btn-outline-primary me-2 hover-scale" id="scrollToTop">
                            <i class="bi bi-arrow-up"></i> Haut de page
                        </button>
                        <button class="btn btn-sm btn-primary hover-scale" id="toggleThemeBtn">
                            <?php if ($theme === 'dark'): ?>
                                <i class="bi bi-sun"></i> Mode clair
                            <?php else: ?>
                                <i class="bi bi-moon"></i> Mode sombre
                            <?php endif; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- PDF.js pour la visualisation des PDFs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    
    <!-- AOS - Animate On Scroll Library -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    
    <!-- Scripts personnalisés -->
    <script src="assets/js/main.js"></script>
    
    <script>
        // Initialiser AOS (Animate On Scroll)
        AOS.init({
            duration: 800,
            easing: 'ease-out',
            once: true
        });
        
        // Barre de progression de défilement
        window.addEventListener('scroll', function() {
            const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrollProgress = (scrollTop / scrollHeight) * 100;
            
            const progressBar = document.getElementById('scrollProgress');
            if (progressBar) {
                progressBar.style.width = scrollProgress + '%';
            }
        });
        
        // Bouton de retour en haut de page
        const scrollToTopBtn = document.getElementById('scrollToTop');
        if (scrollToTopBtn) {
            scrollToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
        
        // Gestion du changement de thème
        const toggleThemeBtn = document.getElementById('toggleTheme');
        if (toggleThemeBtn) {
            toggleThemeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleTheme();
            });
        }
        
        // Bouton alternatif pour changer de thème
        const toggleThemeBtnFooter = document.getElementById('toggleThemeBtn');
        if (toggleThemeBtnFooter) {
            toggleThemeBtnFooter.addEventListener('click', function() {
                toggleTheme();
            });
        }
        
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            // Ajouter une animation de transition
            document.body.classList.add('theme-transition');
            
            // Mettre à jour le thème
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            
            // Enregistrer la préférence via AJAX
            fetch('settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update_theme&theme=' + newTheme
            })
            .then(response => {
                if (response.ok) {
                    // Mettre à jour tous les éléments qui affichent le thème
                    const themeElements = document.querySelectorAll('[id="toggleTheme"], [id="toggleThemeBtn"]');
                    themeElements.forEach(el => {
                        el.innerHTML = newTheme === 'dark' 
                            ? '<i class="bi bi-sun"></i> Mode clair' 
                            : '<i class="bi bi-moon"></i> Mode sombre';
                    });
                    
                    // Mettre à jour le fond avec effet de parallaxe
                    const parallaxBg = document.querySelector('.parallax-bg');
                    if (parallaxBg) {
                        parallaxBg.style.backgroundImage = newTheme === 'dark'
                            ? 'linear-gradient(135deg, rgba(33, 37, 41, 0.8) 0%, rgba(33, 37, 41, 0.4) 100%)'
                            : 'linear-gradient(135deg, rgba(248, 249, 250, 0.8) 0%, rgba(248, 249, 250, 0.4) 100%)';
                    }
                    
                    // Mettre à jour la navbar
                    const navbar = document.querySelector('.navbar');
                    if (navbar) {
                        if (newTheme === 'dark') {
                            navbar.classList.add('navbar-dark', 'bg-dark');
                            navbar.classList.remove('navbar-light', 'bg-light');
                        } else {
                            navbar.classList.add('navbar-light', 'bg-light');
                            navbar.classList.remove('navbar-dark', 'bg-dark');
                        }
                    }
                    
                    // Retirer la classe de transition après l'animation
                    setTimeout(() => {
                        document.body.classList.remove('theme-transition');
                    }, 500);
                }
            });
        }
        
        // Gestion du changement de vue (grille/liste)
        const toggleViewBtn = document.getElementById('toggleView');
        if (toggleViewBtn) {
            toggleViewBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const viewModeText = this.textContent.trim();
                const isListView = viewModeText.includes('Vue grille'); // Si le texte contient "Vue grille", c'est qu'on est actuellement en mode liste
                const newViewMode = isListView ? 'grid' : 'list';
                
                // Ajouter une animation de transition
                document.body.classList.add('page-transition');
                
                // Enregistrer la préférence via AJAX
                fetch('settings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=update_view_mode&view_mode=' + newViewMode
                })
                .then(response => {
                    if (response.ok) {
                        // Mettre à jour l'icône et le texte
                        this.innerHTML = newViewMode === 'list' 
                            ? '<i class="bi bi-grid"></i> Vue grille' 
                            : '<i class="bi bi-list"></i> Vue liste';
                        
                        // Recharger la page pour appliquer le changement
                        setTimeout(() => {
                            location.reload();
                        }, 300);
                    }
                });
            });
        }
    </script>
</body>
</html>
