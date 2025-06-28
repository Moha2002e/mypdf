/**
 * MyPDF - Script principal
 * Gère les interactions utilisateur, les animations et les fonctionnalités PDF
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips Bootstrap
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // Initialiser les popovers Bootstrap
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    
    // Animer les éléments au défilement
    initScrollAnimations();
    
    // Initialiser l'effet de parallaxe
    initParallaxEffect();
    
    // Initialiser les compteurs animés
    initCounters();
    
    // Initialiser les animations de badges
    initBadgeAnimations();
    
    // Initialiser les prévisualisations PDF
    initPdfPreviews();
    
    // Initialiser les transitions de page
    initPageTransitions();
    
    // Marquer la page comme chargée pour les animations
    setTimeout(() => {
        document.body.classList.add('page-loaded');
    }, 100);
});

/**
 * Initialise les animations au défilement
 */
function initScrollAnimations() {
    // Animation des éléments au défilement (en plus de AOS)
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                
                // Ajouter des classes d'animation en fonction des attributs data
                if (entry.target.dataset.animation) {
                    entry.target.classList.add(entry.target.dataset.animation);
                }
                
                // Désinscrire l'élément après l'animation si once est défini
                if (entry.target.dataset.once === 'true') {
                    observer.unobserve(entry.target);
                }
            } else if (!entry.target.dataset.once) {
                entry.target.classList.remove('animated');
                
                if (entry.target.dataset.animation) {
                    entry.target.classList.remove(entry.target.dataset.animation);
                }
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -10% 0px'
    });
    
    animatedElements.forEach(element => {
        observer.observe(element);
    });
}

/**
 * Initialise l'effet de parallaxe pour les arrière-plans
 */
function initParallaxEffect() {
    const parallaxBg = document.querySelector('.parallax-bg');
    
    if (parallaxBg) {
        window.addEventListener('scroll', () => {
            const scrollPosition = window.pageYOffset;
            parallaxBg.style.transform = `translateY(${scrollPosition * 0.5}px)`;
        });
    }
    
    // Parallaxe pour les éléments avec la classe .parallax
    const parallaxElements = document.querySelectorAll('.parallax');
    
    window.addEventListener('mousemove', (e) => {
        const mouseX = e.clientX / window.innerWidth;
        const mouseY = e.clientY / window.innerHeight;
        
        parallaxElements.forEach(element => {
            const speed = element.dataset.speed || 0.1;
            const x = (window.innerWidth - e.pageX * speed) / 100;
            const y = (window.innerHeight - e.pageY * speed) / 100;
            
            element.style.transform = `translateX(${x}px) translateY(${y}px)`;
        });
    });
}

/**
 * Initialise les compteurs animés
 */
function initCounters() {
    const counters = document.querySelectorAll('.counter');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.dataset.target);
                const duration = parseInt(counter.dataset.duration) || 2000;
                let current = 0;
                const increment = target / (duration / 16);
                
                const updateCounter = () => {
                    current += increment;
                    
                    if (current < target) {
                        counter.textContent = Math.ceil(current);
                        counter.classList.add('counter-animation', 'animate');
                        setTimeout(() => {
                            counter.classList.remove('animate');
                        }, 500);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                updateCounter();
                observer.unobserve(counter);
            }
        });
    }, {
        threshold: 0.5
    });
    
    counters.forEach(counter => {
        observer.observe(counter);
    });
}

/**
 * Initialise les animations pour les badges
 */
function initBadgeAnimations() {
    // Animation pour l'ajout de badges
    const addTagInput = document.getElementById('tagInput');
    
    if (addTagInput) {
        addTagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && this.value.trim() !== '') {
                e.preventDefault();
                
                const tagName = this.value.trim();
                addTagBadge(tagName);
                
                this.value = '';
            }
        });
    }
}

/**
 * Ajoute un badge de tag avec animation
 * @param {string} tagName - Nom du tag à ajouter
 */
function addTagBadge(tagName) {
    const tagsContainer = document.getElementById('tagsContainer');
    
    if (!tagsContainer) return;
    
    // Vérifier si le tag existe déjà
    const existingTags = Array.from(tagsContainer.querySelectorAll('.badge')).map(badge => 
        badge.textContent.trim().replace(' ×', '')
    );
    
    if (existingTags.includes(tagName)) {
        // Animer le badge existant pour indiquer qu'il existe déjà
        const existingBadge = Array.from(tagsContainer.querySelectorAll('.badge')).find(badge => 
            badge.textContent.trim().replace(' ×', '') === tagName
        );
        
        existingBadge.classList.add('animate__animated', 'animate__headShake');
        setTimeout(() => {
            existingBadge.classList.remove('animate__animated', 'animate__headShake');
        }, 1000);
        
        return;
    }
    
    // Créer le nouveau badge
    const badge = document.createElement('span');
    badge.className = 'badge bg-primary me-1 mb-1 scale-in-animation';
    badge.innerHTML = `${tagName} <i class="bi bi-x-circle" role="button"></i>`;
    
    // Ajouter le badge au conteneur
    tagsContainer.appendChild(badge);
    
    // Ajouter l'événement de suppression
    const removeIcon = badge.querySelector('.bi-x-circle');
    removeIcon.addEventListener('click', function() {
        badge.classList.remove('scale-in-animation');
        badge.classList.add('scale-out-animation');
        
        setTimeout(() => {
            badge.remove();
            
            // Mettre à jour le champ caché des tags
            updateHiddenTagsField();
        }, 300);
    });
    
    // Mettre à jour le champ caché des tags
    updateHiddenTagsField();
    
    // Ajouter une classe pour l'animation d'entrée
    setTimeout(() => {
        badge.classList.remove('scale-in-animation');
    }, 300);
}

/**
 * Met à jour le champ caché contenant les tags
 */
function updateHiddenTagsField() {
    const tagsContainer = document.getElementById('tagsContainer');
    const tagsInput = document.getElementById('tags');
    
    if (!tagsContainer || !tagsInput) return;
    
    const tags = Array.from(tagsContainer.querySelectorAll('.badge')).map(badge => 
        badge.textContent.trim().replace(' ×', '')
    );
    
    tagsInput.value = tags.join(',');
}

/**
 * Initialise les prévisualisations PDF
 */
function initPdfPreviews() {
    const pdfContainers = document.querySelectorAll('.pdf-container');
    
    pdfContainers.forEach(container => {
        const pdfUrl = container.dataset.pdfUrl;
        
        if (pdfUrl) {
            // Ajouter un indicateur de chargement
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'pdf-loading';
            loadingDiv.innerHTML = '<div class="pdf-loading-spinner"></div>';
            container.appendChild(loadingDiv);
            
            // Charger le PDF avec PDF.js
            pdfjsLib.getDocument(pdfUrl).promise.then(pdfDoc => {
                // Obtenir la première page
                return pdfDoc.getPage(1);
            }).then(page => {
                const canvas = document.createElement('canvas');
                container.appendChild(canvas);
                
                const viewport = page.getViewport({ scale: 1.5 });
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                const renderContext = {
                    canvasContext: canvas.getContext('2d'),
                    viewport: viewport
                };
                
                return page.render(renderContext).promise;
            }).then(() => {
                // Supprimer l'indicateur de chargement avec animation
                loadingDiv.style.opacity = '0';
                setTimeout(() => {
                    loadingDiv.remove();
                }, 500);
            }).catch(error => {
                console.error('Erreur lors du chargement du PDF:', error);
                loadingDiv.innerHTML = '<p class="text-danger">Erreur de chargement</p>';
            });
        }
    });
}

/**
 * Initialise les transitions de page
 */
function initPageTransitions() {
    // Ajouter des transitions lors des clics sur les liens
    document.querySelectorAll('a:not([target="_blank"])').forEach(link => {
        link.addEventListener('click', function(e) {
            // Ignorer les liens avec des événements personnalisés ou les ancres
            if (this.getAttribute('href').startsWith('#') || 
                this.getAttribute('href') === 'javascript:void(0)' ||
                this.getAttribute('data-bs-toggle')) {
                return;
            }
            
            e.preventDefault();
            
            const href = this.getAttribute('href');
            
            // Ajouter une animation de sortie
            document.body.classList.add('page-transition');
            
            // Rediriger après l'animation
            setTimeout(() => {
                window.location.href = href;
            }, 300);
        });
    });
}

/**
 * Affiche un message de notification
 * @param {string} message - Message à afficher
 * @param {string} type - Type de notification (success, danger, warning, info)
 * @param {number} duration - Durée d'affichage en ms
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Créer l'élément de notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type} slide-in-right`;
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="bi ${getIconForType(type)}"></i>
        </div>
        <div class="notification-content">
            <p>${message}</p>
        </div>
        <button class="notification-close">
            <i class="bi bi-x"></i>
        </button>
    `;
    
    // Ajouter au conteneur de notifications ou créer un nouveau
    let notificationsContainer = document.querySelector('.notifications-container');
    
    if (!notificationsContainer) {
        notificationsContainer = document.createElement('div');
        notificationsContainer.className = 'notifications-container';
        document.body.appendChild(notificationsContainer);
    }
    
    notificationsContainer.appendChild(notification);
    
    // Gérer la fermeture
    const closeButton = notification.querySelector('.notification-close');
    closeButton.addEventListener('click', () => {
        notification.classList.remove('slide-in-right');
        notification.classList.add('slide-out-right');
        
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
    
    // Fermeture automatique après la durée spécifiée
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.remove('slide-in-right');
                notification.classList.add('slide-out-right');
                
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }, duration);
    }
    
    return notification;
}

/**
 * Obtient l'icône Bootstrap correspondant au type de notification
 * @param {string} type - Type de notification
 * @returns {string} - Classe d'icône Bootstrap
 */
function getIconForType(type) {
    switch (type) {
        case 'success':
            return 'bi-check-circle-fill';
        case 'danger':
            return 'bi-exclamation-circle-fill';
        case 'warning':
            return 'bi-exclamation-triangle-fill';
        case 'info':
        default:
            return 'bi-info-circle-fill';
    }
}

/**
 * Crée un effet de ripple sur les boutons
 */
function createRippleEffect(event) {
    const button = event.currentTarget;
    
    const circle = document.createElement('span');
    const diameter = Math.max(button.clientWidth, button.clientHeight);
    const radius = diameter / 2;
    
    circle.style.width = circle.style.height = `${diameter}px`;
    circle.style.left = `${event.clientX - button.getBoundingClientRect().left - radius}px`;
    circle.style.top = `${event.clientY - button.getBoundingClientRect().top - radius}px`;
    circle.classList.add('ripple');
    
    // Supprimer tout effet ripple existant
    const ripple = button.querySelector('.ripple');
    if (ripple) {
        ripple.remove();
    }
    
    button.appendChild(circle);
    
    // Supprimer l'élément ripple après l'animation
    setTimeout(() => {
        circle.remove();
    }, 600);
}

// Fonction pour l'effet ripple et le système de notifications
function initRippleAndNotifications() {
    // Effet Ripple pour les boutons
    document.addEventListener('click', function(e) {
        // Vérifier si l'élément cliqué est un bouton ou un parent d'un bouton
        const button = e.target.closest('.btn');
        if (button) {
            // Créer l'élément ripple
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            button.appendChild(ripple);
            
            // Positionner l'élément ripple
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            // Supprimer l'élément ripple après l'animation
            setTimeout(() => {
                ripple.remove();
            }, 600);
        }
    });

    // Système de notifications
    window.showNotification = function(message, type = 'info', duration = 3000) {
        const notificationsContainer = document.querySelector('.notifications-container');
        
        // Créer le conteneur de notifications s'il n'existe pas
        if (!notificationsContainer) {
            const container = document.createElement('div');
            container.className = 'notifications-container';
            document.body.appendChild(container);
        }
        
        const container = document.querySelector('.notifications-container');
        
        // Créer la notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} slide-in-right`;
        
        // Définir l'icône en fonction du type
        let icon = '';
        switch(type) {
            case 'success':
                icon = '<i class="bi bi-check-circle-fill notification-icon"></i>';
                break;
            case 'danger':
                icon = '<i class="bi bi-exclamation-circle-fill notification-icon"></i>';
                break;
            case 'warning':
                icon = '<i class="bi bi-exclamation-triangle-fill notification-icon"></i>';
                break;
            case 'info':
            default:
                icon = '<i class="bi bi-info-circle-fill notification-icon"></i>';
                break;
        }
        
        // Construire le contenu de la notification
        notification.innerHTML = `
            ${icon}
            <div class="notification-content">
                <p>${message}</p>
            </div>
            <button class="notification-close"><i class="bi bi-x"></i></button>
        `;
        
        // Ajouter la notification au conteneur
        container.appendChild(notification);
        
        // Gérer la fermeture de la notification
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            closeNotification(notification);
        });
        
        // Fermer automatiquement après la durée spécifiée
        if (duration > 0) {
            setTimeout(() => {
                closeNotification(notification);
            }, duration);
        }
        
        // Fonction pour fermer la notification avec animation
        function closeNotification(notif) {
            notif.classList.remove('slide-in-right');
            notif.classList.add('slide-out-right');
            
            setTimeout(() => {
                notif.remove();
            }, 300);
        }
        
        return notification;
    };

    // Exemple d'utilisation de la fonction de notification
    // Décommenter pour tester
    /*
    setTimeout(() => {
        showNotification('Bienvenue sur notre application de gestion de PDF !', 'info');
    }, 1000);
    
    setTimeout(() => {
        showNotification('PDF téléchargé avec succès !', 'success');
    }, 2000);
    
    setTimeout(() => {
        showNotification('Attention, certains champs sont obligatoires.', 'warning');
    }, 3000);
    
    setTimeout(() => {
        showNotification('Erreur lors du téléchargement du fichier.', 'danger');
    }, 4000);
    */
}

initRippleAndNotifications();
