/**
 * Animations et transitions pour MyPDF
 */

class AnimationManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupPageTransitions();
        this.setupScrollAnimations();
        this.setupHoverEffects();
        this.setupLoadingAnimations();
        this.setupNotificationAnimations();
        this.setupFormAnimations();
        this.setupCardAnimations();
    }

    // Transitions de page
    setupPageTransitions() {
        // Animation d'entrée de page
        document.addEventListener('DOMContentLoaded', () => {
            const mainContent = document.querySelector('main');
            if (mainContent) {
                mainContent.classList.add('page-transition');
                
                setTimeout(() => {
                    mainContent.classList.add('loaded');
                }, 100);
            }

            // Animation des éléments avec délai
            this.animateElementsWithDelay();
        });

        // Animation de sortie de page
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href && !link.href.includes('#') && !link.href.includes('javascript:')) {
                if (!link.target || link.target === '_self') {
                    e.preventDefault();
                    this.animatePageExit(() => {
                        window.location.href = link.href;
                    });
                }
            }
        });
    }

    // Animation des éléments avec délai
    animateElementsWithDelay() {
        const elements = document.querySelectorAll('.animate-on-load');
        elements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.6s ease-out';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, 100 + (index * 100));
        });
    }

    // Animation de sortie de page
    animatePageExit(callback) {
        const mainContent = document.querySelector('main');
        if (mainContent) {
            mainContent.style.transition = 'all 0.3s ease-in';
            mainContent.style.opacity = '0';
            mainContent.style.transform = 'translateY(-20px)';
            
            setTimeout(callback, 300);
        } else {
            callback();
        }
    }

    // Animations au scroll
    setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observer les éléments avec la classe animate-on-scroll
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });

        // Animation des cartes PDF au scroll
        document.querySelectorAll('.pdf-card').forEach((card, index) => {
            card.classList.add('animate-on-scroll');
            card.style.animationDelay = `${index * 0.1}s`;
        });
    }

    // Effets de hover
    setupHoverEffects() {
        // Effet de ripple sur les boutons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.createRippleEffect(e, btn);
            });
        });

        // Animation des cartes au hover
        document.querySelectorAll('.pdf-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                this.animateCardHover(card, true);
            });

            card.addEventListener('mouseleave', () => {
                this.animateCardHover(card, false);
            });
        });
    }

    // Effet de ripple
    createRippleEffect(event, element) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');

        element.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    // Animation des cartes au hover
    animateCardHover(card, isHovering) {
        const icon = card.querySelector('.pdf-icon');
        const title = card.querySelector('.card-title');
        const badges = card.querySelectorAll('.badge');

        if (isHovering) {
            if (icon) {
                icon.style.transform = 'scale(1.1) rotate(-5deg)';
                icon.style.transition = 'all 0.3s ease-out';
            }
            if (title) {
                title.style.color = 'var(--primary-color)';
                title.style.transition = 'color 0.3s ease-out';
            }
            badges.forEach((badge, index) => {
                badge.style.transform = `scale(1.1) translateY(-${index * 2}px)`;
                badge.style.transition = 'all 0.3s ease-out';
            });
        } else {
            if (icon) {
                icon.style.transform = 'scale(1) rotate(0deg)';
            }
            if (title) {
                title.style.color = '';
            }
            badges.forEach(badge => {
                badge.style.transform = 'scale(1) translateY(0)';
            });
        }
    }

    // Animations de chargement
    setupLoadingAnimations() {
        // Animation de chargement pour les images
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('load', () => {
                img.classList.add('animate-scale-in');
            });
        });

        // Animation de chargement pour les PDFs
        document.querySelectorAll('.pdf-container').forEach(container => {
            container.classList.add('loading-shimmer');
            
            // Simuler le chargement
            setTimeout(() => {
                container.classList.remove('loading-shimmer');
                container.classList.add('animate-fade-in-up');
            }, 1000);
        });
    }

    // Animations de notification
    setupNotificationAnimations() {
        // Observer les nouvelles alertes
        const alertObserver = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && node.classList.contains('alert')) {
                        this.animateNotification(node);
                    }
                });
            });
        });

        alertObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Animation des notifications
    animateNotification(notification) {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            notification.style.transition = 'all 0.4s ease-out';
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        }, 100);

        // Auto-suppression avec animation
        if (notification.classList.contains('alert-dismissible')) {
            setTimeout(() => {
                notification.style.transition = 'all 0.4s ease-in';
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-20px)';
                
                setTimeout(() => {
                    notification.remove();
                }, 400);
            }, 5000);
        }
    }

    // Animations des formulaires
    setupFormAnimations() {
        // Animation des champs de formulaire
        document.querySelectorAll('.form-control, .form-select').forEach(field => {
            field.addEventListener('focus', () => {
                this.animateFieldFocus(field, true);
            });

            field.addEventListener('blur', () => {
                this.animateFieldFocus(field, false);
            });
        });

        // Animation de soumission de formulaire
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                this.animateFormSubmit(form);
            });
        });
    }

    // Animation des champs de formulaire
    animateFieldFocus(field, isFocused) {
        if (isFocused) {
            field.style.transform = 'scale(1.02)';
            field.style.boxShadow = '0 0 0 3px rgba(67, 97, 238, 0.1)';
            field.style.transition = 'all 0.3s ease-out';
        } else {
            field.style.transform = 'scale(1)';
            field.style.boxShadow = '';
        }
    }

    // Animation de soumission de formulaire
    animateFormSubmit(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise animate-spin"></i> Envoi...';
            submitBtn.disabled = true;
        }
    }

    // Animations des cartes
    setupCardAnimations() {
        // Animation des cartes de statistiques
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease-out';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 200 + (index * 150));
        });

        // Animation des badges
        document.querySelectorAll('.badge').forEach((badge, index) => {
            badge.style.opacity = '0';
            badge.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                badge.style.transition = 'all 0.4s ease-out';
                badge.style.opacity = '1';
                badge.style.transform = 'scale(1)';
            }, 300 + (index * 50));
        });
    }

    // Méthodes utilitaires
    static fadeIn(element, duration = 300) {
        element.style.opacity = '0';
        element.style.transition = `opacity ${duration}ms ease-out`;
        
        setTimeout(() => {
            element.style.opacity = '1';
        }, 10);
    }

    static fadeOut(element, duration = 300, callback = null) {
        element.style.transition = `opacity ${duration}ms ease-in`;
        element.style.opacity = '0';
        
        setTimeout(() => {
            if (callback) callback();
        }, duration);
    }

    static slideIn(element, direction = 'up', duration = 300) {
        const transforms = {
            up: 'translateY(30px)',
            down: 'translateY(-30px)',
            left: 'translateX(30px)',
            right: 'translateX(-30px)'
        };

        element.style.opacity = '0';
        element.style.transform = transforms[direction];
        element.style.transition = `all ${duration}ms ease-out`;
        
        setTimeout(() => {
            element.style.opacity = '1';
            element.style.transform = 'translate(0, 0)';
        }, 10);
    }

    static bounce(element, duration = 600) {
        element.style.animation = `bounceIn ${duration}ms ease-out`;
        
        setTimeout(() => {
            element.style.animation = '';
        }, duration);
    }
}

// Initialisation des animations
document.addEventListener('DOMContentLoaded', () => {
    window.animationManager = new AnimationManager();
});

// Animation de la barre de progression de scroll
document.addEventListener('scroll', () => {
    const scrollTop = window.pageYOffset;
    const docHeight = document.body.offsetHeight - window.innerHeight;
    const scrollPercent = (scrollTop / docHeight) * 100;
    
    const progressBar = document.querySelector('.scroll-progress-bar');
    if (progressBar) {
        progressBar.style.width = scrollPercent + '%';
    }
});

// Animation des compteurs
function animateCounter(element, target, duration = 2000) {
    // Vérifier que target est un nombre valide
    if (isNaN(target) || target === null || target === undefined) {
        element.textContent = '0';
        return;
    }
    
    target = parseInt(target);
    if (target < 0) target = 0;
    
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start);
        }
    }, 16);
}

// Observer les compteurs
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const targetText = entry.target.textContent.trim();
            const target = parseInt(targetText);
            
            // Vérifier que c'est un nombre valide
            if (!isNaN(target) && target >= 0) {
                animateCounter(entry.target, target);
            } else {
                // Si ce n'est pas un nombre valide, afficher 0
                entry.target.textContent = '0';
            }
            
            counterObserver.unobserve(entry.target);
        }
    });
});

document.querySelectorAll('.counter').forEach(counter => {
    counterObserver.observe(counter);
}); 