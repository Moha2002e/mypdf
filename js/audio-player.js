/**
 * Lecteur audio personnalisé pour la visionneuse PDF
 */

class AudioPlayer {
    constructor() {
        this.audio = new Audio();
        this.playlist = [];
        this.currentTrackIndex = 0;
        this.isPlaying = false;
        this.volume = 0.5;
        this.isRepeat = false;
        this.isShuffle = false;
        
        // Initialiser les événements audio
        this.initAudioEvents();
    }
    
    /**
     * Initialiser les événements de l'élément audio
     */
    initAudioEvents() {
        // Lorsqu'une piste se termine
        this.audio.addEventListener('ended', () => {
            if (this.isRepeat) {
                // Répéter la piste actuelle
                this.play();
            } else {
                // Passer à la piste suivante
                this.next();
            }
        });
        
        // Mettre à jour la progression
        this.audio.addEventListener('timeupdate', () => {
            this.updateProgress();
        });
        
        // Mettre à jour l'interface lorsque les métadonnées sont chargées
        this.audio.addEventListener('loadedmetadata', () => {
            this.updateTrackInfo();
        });
    }
    
    /**
     * Charger une playlist
     * @param {Array} tracks - Liste des pistes
     */
    loadPlaylist(tracks) {
        this.playlist = tracks;
        if (tracks.length > 0) {
            this.currentTrackIndex = 0;
            this.loadTrack(0);
        }
    }
    
    /**
     * Charger une piste spécifique
     * @param {number} index - Index de la piste dans la playlist
     */
    loadTrack(index) {
        if (index >= 0 && index < this.playlist.length) {
            this.currentTrackIndex = index;
            this.audio.src = this.playlist[index].path;
            this.audio.load();
            this.updateTrackInfo();
        }
    }
    
    /**
     * Jouer ou mettre en pause la piste actuelle
     */
    togglePlay() {
        if (this.audio.paused) {
            this.play();
        } else {
            this.pause();
        }
    }
    
    /**
     * Jouer la piste actuelle
     */
    play() {
        this.audio.play();
        this.isPlaying = true;
        this.updatePlayButton();
    }
    
    /**
     * Mettre en pause la piste actuelle
     */
    pause() {
        this.audio.pause();
        this.isPlaying = false;
        this.updatePlayButton();
    }
    
    /**
     * Passer à la piste suivante
     */
    next() {
        let nextIndex;
        
        if (this.isShuffle) {
            // Mode aléatoire
            nextIndex = Math.floor(Math.random() * this.playlist.length);
        } else {
            // Mode normal
            nextIndex = (this.currentTrackIndex + 1) % this.playlist.length;
        }
        
        this.loadTrack(nextIndex);
        if (this.isPlaying) {
            this.play();
        }
    }
    
    /**
     * Passer à la piste précédente
     */
    previous() {
        let prevIndex;
        
        if (this.audio.currentTime > 3) {
            // Si la lecture est > 3 secondes, revenir au début de la piste
            this.audio.currentTime = 0;
            return;
        }
        
        if (this.isShuffle) {
            // Mode aléatoire
            prevIndex = Math.floor(Math.random() * this.playlist.length);
        } else {
            // Mode normal
            prevIndex = (this.currentTrackIndex - 1 + this.playlist.length) % this.playlist.length;
        }
        
        this.loadTrack(prevIndex);
        if (this.isPlaying) {
            this.play();
        }
    }
    
    /**
     * Définir le volume
     * @param {number} value - Volume (0-1)
     */
    setVolume(value) {
        this.volume = Math.max(0, Math.min(1, value));
        this.audio.volume = this.volume;
        this.updateVolumeUI();
    }
    
    /**
     * Activer/désactiver le mode répétition
     */
    toggleRepeat() {
        this.isRepeat = !this.isRepeat;
        this.updateRepeatButton();
    }
    
    /**
     * Activer/désactiver le mode aléatoire
     */
    toggleShuffle() {
        this.isShuffle = !this.isShuffle;
        this.updateShuffleButton();
    }
    
    /**
     * Chercher dans la piste
     * @param {number} value - Position en pourcentage (0-100)
     */
    seek(value) {
        const seekTime = (value / 100) * this.audio.duration;
        if (!isNaN(seekTime)) {
            this.audio.currentTime = seekTime;
        }
    }
    
    /**
     * Mettre à jour la barre de progression
     */
    updateProgress() {
        const progressBar = document.getElementById('audioProgressBar');
        const currentTimeEl = document.getElementById('audioCurrentTime');
        const durationEl = document.getElementById('audioDuration');
        
        if (progressBar && !isNaN(this.audio.duration)) {
            const percentage = (this.audio.currentTime / this.audio.duration) * 100;
            progressBar.value = percentage;
        }
        
        if (currentTimeEl) {
            currentTimeEl.textContent = this.formatTime(this.audio.currentTime);
        }
        
        if (durationEl && !isNaN(this.audio.duration)) {
            durationEl.textContent = this.formatTime(this.audio.duration);
        }
    }
    
    /**
     * Mettre à jour les informations de la piste
     */
    updateTrackInfo() {
        const trackTitleEl = document.getElementById('audioTrackTitle');
        const trackArtistEl = document.getElementById('audioTrackArtist');
        const trackCoverEl = document.getElementById('audioTrackCover');
        
        if (this.playlist.length > 0 && this.currentTrackIndex < this.playlist.length) {
            const currentTrack = this.playlist[this.currentTrackIndex];
            
            if (trackTitleEl) {
                trackTitleEl.textContent = currentTrack.title || 'Titre inconnu';
            }
            
            if (trackArtistEl) {
                trackArtistEl.textContent = currentTrack.artist || 'Artiste inconnu';
            }
            
            if (trackCoverEl) {
                trackCoverEl.src = currentTrack.cover || 'assets/images/default-cover.jpg';
                trackCoverEl.alt = `${currentTrack.title} - ${currentTrack.artist}`;
            }
        }
    }
    
    /**
     * Mettre à jour le bouton de lecture/pause
     */
    updatePlayButton() {
        const playButton = document.getElementById('audioPlayPause');
        
        if (playButton) {
            if (this.isPlaying) {
                playButton.innerHTML = '<i class="bi bi-pause-fill"></i>';
                playButton.setAttribute('title', 'Pause');
            } else {
                playButton.innerHTML = '<i class="bi bi-play-fill"></i>';
                playButton.setAttribute('title', 'Lecture');
            }
        }
    }
    
    /**
     * Mettre à jour l'interface du volume
     */
    updateVolumeUI() {
        const volumeSlider = document.getElementById('audioVolumeSlider');
        const volumeIcon = document.getElementById('audioVolumeIcon');
        
        if (volumeSlider) {
            volumeSlider.value = this.volume * 100;
        }
        
        if (volumeIcon) {
            if (this.volume === 0) {
                volumeIcon.className = 'bi bi-volume-mute-fill';
            } else if (this.volume < 0.5) {
                volumeIcon.className = 'bi bi-volume-down-fill';
            } else {
                volumeIcon.className = 'bi bi-volume-up-fill';
            }
        }
    }
    
    /**
     * Mettre à jour le bouton de répétition
     */
    updateRepeatButton() {
        const repeatButton = document.getElementById('audioRepeat');
        
        if (repeatButton) {
            if (this.isRepeat) {
                repeatButton.classList.add('active');
            } else {
                repeatButton.classList.remove('active');
            }
        }
    }
    
    /**
     * Mettre à jour le bouton de lecture aléatoire
     */
    updateShuffleButton() {
        const shuffleButton = document.getElementById('audioShuffle');
        
        if (shuffleButton) {
            if (this.isShuffle) {
                shuffleButton.classList.add('active');
            } else {
                shuffleButton.classList.remove('active');
            }
        }
    }
    
    /**
     * Formater le temps en minutes:secondes
     * @param {number} time - Temps en secondes
     * @returns {string} - Temps formaté
     */
    formatTime(time) {
        if (isNaN(time)) return '0:00';
        
        const minutes = Math.floor(time / 60);
        const seconds = Math.floor(time % 60).toString().padStart(2, '0');
        return `${minutes}:${seconds}`;
    }
}

// Initialiser le lecteur audio au chargement de la page
let audioPlayer;

document.addEventListener('DOMContentLoaded', () => {
    // Créer l'instance du lecteur audio
    audioPlayer = new AudioPlayer();
    
    // Charger la bibliothèque de musique
    loadMusicLibrary();
    
    // Configurer les événements de l'interface utilisateur
    setupAudioPlayerEvents();
});

/**
 * Charger la bibliothèque de musique
 */
function loadMusicLibrary() {
    fetch('ajax/get_music_library.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Erreur lors du chargement de la bibliothèque:', data.error);
                return;
            }
            
            // Afficher la bibliothèque de musique
            displayMusicLibrary(data.tracks);
            
            // Charger la playlist dans le lecteur
            audioPlayer.loadPlaylist(data.tracks);
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
}

/**
 * Afficher la bibliothèque de musique
 * @param {Array} tracks - Liste des pistes
 */
function displayMusicLibrary(tracks) {
    const libraryContainer = document.getElementById('musicLibrary');
    
    if (!libraryContainer) return;
    
    // Vider le conteneur
    libraryContainer.innerHTML = '';
    
    if (tracks.length === 0) {
        libraryContainer.innerHTML = '<p class="text-center text-muted">Aucune musique disponible</p>';
        return;
    }
    
    // Créer la liste des pistes
    const tracksList = document.createElement('div');
    tracksList.className = 'list-group';
    
    tracks.forEach((track, index) => {
        const item = document.createElement('a');
        item.className = 'list-group-item list-group-item-action d-flex align-items-center';
        item.href = '#';
        
        // Image de couverture
        const coverImg = track.cover ? 
            `<img src="${track.cover}" class="me-3" width="40" height="40">` : 
            '<div class="me-3 bg-secondary" style="width:40px;height:40px;"></div>';
        
        item.innerHTML = `
            ${coverImg}
            <div class="flex-grow-1">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${track.title || 'Titre inconnu'}</h6>
                </div>
                <p class="mb-1 small">${track.artist || 'Artiste inconnu'}</p>
            </div>
        `;
        
        // Événement de clic pour jouer la piste
        item.addEventListener('click', (e) => {
            e.preventDefault();
            audioPlayer.loadTrack(index);
            audioPlayer.play();
            
            // Fermer la modal si elle est ouverte
            const musicModal = bootstrap.Modal.getInstance(document.getElementById('musicModal'));
            if (musicModal) {
                musicModal.hide();
            }
        });
        
        tracksList.appendChild(item);
    });
    
    libraryContainer.appendChild(tracksList);
}

/**
 * Configurer les événements du lecteur audio
 */
function setupAudioPlayerEvents() {
    // Bouton de lecture/pause
    const playButton = document.getElementById('audioPlayPause');
    if (playButton) {
        playButton.addEventListener('click', () => {
            audioPlayer.togglePlay();
        });
    }
    
    // Bouton précédent
    const prevButton = document.getElementById('audioPrev');
    if (prevButton) {
        prevButton.addEventListener('click', () => {
            audioPlayer.previous();
        });
    }
    
    // Bouton suivant
    const nextButton = document.getElementById('audioNext');
    if (nextButton) {
        nextButton.addEventListener('click', () => {
            audioPlayer.next();
        });
    }
    
    // Barre de progression
    const progressBar = document.getElementById('audioProgressBar');
    if (progressBar) {
        progressBar.addEventListener('input', () => {
            audioPlayer.seek(progressBar.value);
        });
    }
    
    // Contrôle du volume
    const volumeSlider = document.getElementById('audioVolumeSlider');
    if (volumeSlider) {
        volumeSlider.addEventListener('input', () => {
            audioPlayer.setVolume(volumeSlider.value / 100);
        });
    }
    
    // Bouton muet/son
    const volumeButton = document.getElementById('audioVolumeBtn');
    if (volumeButton) {
        volumeButton.addEventListener('click', () => {
            if (audioPlayer.volume > 0) {
                // Sauvegarder le volume actuel et mettre en muet
                volumeButton.dataset.prevVolume = audioPlayer.volume;
                audioPlayer.setVolume(0);
            } else {
                // Restaurer le volume précédent
                const prevVolume = parseFloat(volumeButton.dataset.prevVolume || 0.5);
                audioPlayer.setVolume(prevVolume);
            }
        });
    }
    
    // Bouton de répétition
    const repeatButton = document.getElementById('audioRepeat');
    if (repeatButton) {
        repeatButton.addEventListener('click', () => {
            audioPlayer.toggleRepeat();
        });
    }
    
    // Bouton de lecture aléatoire
    const shuffleButton = document.getElementById('audioShuffle');
    if (shuffleButton) {
        shuffleButton.addEventListener('click', () => {
            audioPlayer.toggleShuffle();
        });
    }
    
    // Bouton d'ouverture de la bibliothèque
    const openLibraryButton = document.getElementById('openMusicLibrary');
    if (openLibraryButton) {
        openLibraryButton.addEventListener('click', () => {
            // Assurer que la bibliothèque est chargée
            loadMusicLibrary();
        });
    }
}
