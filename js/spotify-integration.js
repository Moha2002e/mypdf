/**
 * Spotify Integration for PDF Viewer
 */

// Initialisation du SDK Spotify Web Playback
let player;
let deviceId;
let currentTrackId = null;
let isPlaying = false;

// Initialiser l'intu00e9gration Spotify
function initSpotify() {
    // Vu00e9rifier si le SDK est du00e9ju00e0 chargu00e9
    if (window.Spotify) {
        setupSpotifyPlayer();
    } else {
        // Charger le SDK Spotify
        const script = document.createElement('script');
        script.src = 'https://sdk.scdn.co/spotify-player.js';
        script.async = true;
        document.body.appendChild(script);

        window.onSpotifyWebPlaybackSDKReady = () => {
            setupSpotifyPlayer();
        };
    }

    // Configurer les u00e9vu00e9nements pour les boutons Spotify
    setupSpotifyEvents();
}

// Configurer le lecteur Spotify
function setupSpotifyPlayer() {
    // Ru00e9cupu00e9rer le token d'accu00e8s depuis la session PHP
    fetch('ajax/get_spotify_token.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Erreur de ru00e9cupu00e9ration du token:', data.error);
                return;
            }

            // Cru00e9er le lecteur Spotify
            player = new Spotify.Player({
                name: 'PDF Viewer Player',
                getOAuthToken: cb => cb(data.access_token),
                volume: 0.5
            });

            // Gestion des erreurs
            player.addListener('initialization_error', ({ message }) => {
                console.error('Erreur d\'initialisation:', message);
            });

            player.addListener('authentication_error', ({ message }) => {
                console.error('Erreur d\'authentification:', message);
            });

            player.addListener('account_error', ({ message }) => {
                console.error('Erreur de compte:', message);
            });

            player.addListener('playback_error', ({ message }) => {
                console.error('Erreur de lecture:', message);
            });

            // Appareil pru00eat
            player.addListener('ready', ({ device_id }) => {
                console.log('Appareil Spotify pru00eat avec ID:', device_id);
                deviceId = device_id;
                document.getElementById('toggleSpotify').disabled = false;
            });

            // Appareil du00e9connectu00e9
            player.addListener('not_ready', ({ device_id }) => {
                console.log('L\'appareil est devenu indisponible:', device_id);
                deviceId = null;
                document.getElementById('toggleSpotify').disabled = true;
            });

            // u00c9tat de lecture changu00e9
            player.addListener('player_state_changed', state => {
                if (state) {
                    currentTrackId = state.track_window.current_track.id;
                    isPlaying = !state.paused;
                    updatePlayButton();
                    updateTrackInfo(state.track_window.current_track);
                }
            });

            // Connecter le lecteur
            player.connect();
        })
        .catch(error => {
            console.error('Erreur lors de la ru00e9cupu00e9ration du token:', error);
        });
}

// Configurer les u00e9vu00e9nements pour les boutons Spotify
function setupSpotifyEvents() {
    // Bouton de lecture/pause
    const toggleButton = document.getElementById('toggleSpotify');
    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            if (player) {
                player.togglePlay();
            }
        });
    }

    // Recherche Spotify
    const searchForm = document.getElementById('spotifySearchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = document.getElementById('spotifySearchQuery').value;
            if (query.trim() !== '') {
                searchSpotifyTracks(query);
            }
        });
    }

    // Onglets de la modal Spotify
    const searchTab = document.getElementById('search-tab');
    const playlistsTab = document.getElementById('playlists-tab');
    
    if (searchTab) {
        searchTab.addEventListener('click', function() {
            document.getElementById('spotifySearchQuery').focus();
        });
    }
    
    if (playlistsTab) {
        playlistsTab.addEventListener('click', function() {
            loadUserPlaylists();
        });
    }
}

// Rechercher des pistes Spotify
function searchSpotifyTracks(query) {
    const resultsContainer = document.getElementById('spotifySearchResults');
    resultsContainer.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Chargement...</span></div></div>';
    
    fetch(`ajax/spotify_search.php?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                resultsContainer.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }
            
            displaySearchResults(data);
        })
        .catch(error => {
            resultsContainer.innerHTML = `<div class="alert alert-danger">Erreur: ${error.message}</div>`;
        });
}

// Afficher les ru00e9sultats de recherche
function displaySearchResults(data) {
    const resultsContainer = document.getElementById('spotifySearchResults');
    resultsContainer.innerHTML = '';
    
    if (!data.tracks || data.tracks.items.length === 0) {
        resultsContainer.innerHTML = '<div class="alert alert-info">Aucun ru00e9sultat trouvu00e9</div>';
        return;
    }
    
    const tracksList = document.createElement('div');
    tracksList.className = 'list-group';
    
    data.tracks.items.forEach(track => {
        const artists = track.artists.map(artist => artist.name).join(', ');
        const item = document.createElement('a');
        item.className = 'list-group-item list-group-item-action d-flex align-items-center';
        item.href = '#';
        
        // Miniature de l'album
        const albumImg = track.album.images.length > 0 ? 
            `<img src="${track.album.images[track.album.images.length-1].url}" class="me-3" width="40" height="40">` : 
            '<div class="me-3 bg-secondary" style="width:40px;height:40px;"></div>';
        
        item.innerHTML = `
            ${albumImg}
            <div class="flex-grow-1">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${track.name}</h6>
                    <small>${Math.floor(track.duration_ms/60000)}:${(Math.floor(track.duration_ms/1000)%60).toString().padStart(2, '0')}</small>
                </div>
                <p class="mb-1 small">${artists}</p>
                <small>${track.album.name}</small>
            </div>
        `;
        
        item.addEventListener('click', (e) => {
            e.preventDefault();
            playTrack(track.uri);
        });
        
        tracksList.appendChild(item);
    });
    
    resultsContainer.appendChild(tracksList);
}

// Charger les playlists de l'utilisateur
function loadUserPlaylists() {
    const playlistsContainer = document.getElementById('spotifyPlaylists');
    playlistsContainer.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Chargement...</span></div></div>';
    
    fetch('ajax/spotify_playlists.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                playlistsContainer.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }
            
            displayUserPlaylists(data);
        })
        .catch(error => {
            playlistsContainer.innerHTML = `<div class="alert alert-danger">Erreur: ${error.message}</div>`;
        });
}

// Afficher les playlists de l'utilisateur
function displayUserPlaylists(data) {
    const playlistsContainer = document.getElementById('spotifyPlaylists');
    playlistsContainer.innerHTML = '';
    
    if (!data.items || data.items.length === 0) {
        playlistsContainer.innerHTML = '<div class="alert alert-info">Aucune playlist trouvu00e9e</div>';
        return;
    }
    
    const playlistsList = document.createElement('div');
    playlistsList.className = 'list-group';
    
    data.items.forEach(playlist => {
        const item = document.createElement('a');
        item.className = 'list-group-item list-group-item-action d-flex align-items-center';
        item.href = '#';
        
        // Image de la playlist
        const playlistImg = playlist.images.length > 0 ? 
            `<img src="${playlist.images[0].url}" class="me-3" width="40" height="40">` : 
            '<div class="me-3 bg-secondary" style="width:40px;height:40px;"></div>';
        
        item.innerHTML = `
            ${playlistImg}
            <div>
                <h6 class="mb-1">${playlist.name}</h6>
                <p class="mb-1 small">${playlist.tracks.total} pistes</p>
            </div>
        `;
        
        item.addEventListener('click', (e) => {
            e.preventDefault();
            loadPlaylistTracks(playlist.id, playlist.name);
        });
        
        playlistsList.appendChild(item);
    });
    
    playlistsContainer.appendChild(playlistsList);
}

// Charger les pistes d'une playlist
function loadPlaylistTracks(playlistId, playlistName) {
    const playlistsContainer = document.getElementById('spotifyPlaylists');
    playlistsContainer.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Chargement...</span></div></div>';
    
    fetch(`ajax/spotify_playlist_tracks.php?playlist_id=${encodeURIComponent(playlistId)}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                playlistsContainer.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }
            
            displayPlaylistTracks(data, playlistName, playlistId);
        })
        .catch(error => {
            playlistsContainer.innerHTML = `<div class="alert alert-danger">Erreur: ${error.message}</div>`;
        });
}

// Afficher les pistes d'une playlist
function displayPlaylistTracks(data, playlistName, playlistId) {
    const playlistsContainer = document.getElementById('spotifyPlaylists');
    playlistsContainer.innerHTML = '';
    
    // Bouton de retour aux playlists
    const backButton = document.createElement('button');
    backButton.className = 'btn btn-sm btn-outline-secondary mb-3';
    backButton.innerHTML = '<i class="bi bi-arrow-left"></i> Retour aux playlists';
    backButton.addEventListener('click', loadUserPlaylists);
    playlistsContainer.appendChild(backButton);
    
    // Titre de la playlist
    const title = document.createElement('h5');
    title.className = 'mb-3';
    title.textContent = playlistName;
    playlistsContainer.appendChild(title);
    
    // Bouton pour jouer toute la playlist
    const playAllButton = document.createElement('button');
    playAllButton.className = 'btn btn-success mb-3';
    playAllButton.innerHTML = '<i class="bi bi-play-fill"></i> Jouer la playlist';
    playAllButton.addEventListener('click', () => {
        playPlaylist(playlistId);
    });
    playlistsContainer.appendChild(playAllButton);
    
    if (!data.items || data.items.length === 0) {
        const noTracks = document.createElement('div');
        noTracks.className = 'alert alert-info';
        noTracks.textContent = 'Cette playlist ne contient aucune piste';
        playlistsContainer.appendChild(noTracks);
        return;
    }
    
    const tracksList = document.createElement('div');
    tracksList.className = 'list-group mt-3';
    
    data.items.forEach((item, index) => {
        const track = item.track;
        if (!track) return; // Ignorer les pistes null
        
        const artists = track.artists.map(artist => artist.name).join(', ');
        const trackItem = document.createElement('a');
        trackItem.className = 'list-group-item list-group-item-action';
        trackItem.href = '#';
        
        trackItem.innerHTML = `
            <div class="d-flex w-100 justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">${track.name}</h6>
                    <p class="mb-1 small">${artists}</p>
                </div>
                <small>${Math.floor(track.duration_ms/60000)}:${(Math.floor(track.duration_ms/1000)%60).toString().padStart(2, '0')}</small>
            </div>
        `;
        
        trackItem.addEventListener('click', (e) => {
            e.preventDefault();
            playTrack(track.uri);
        });
        
        tracksList.appendChild(trackItem);
    });
    
    playlistsContainer.appendChild(tracksList);
}

// Jouer une piste
function playTrack(uri) {
    if (!deviceId) {
        alert('Lecteur Spotify non initialisu00e9');
        return;
    }
    
    fetch('ajax/spotify_play.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            device_id: deviceId,
            uri: uri
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Erreur de lecture:', data.error);
            alert('Erreur lors de la lecture: ' + data.error);
            return;
        }
        
        // Fermer la modal apru00e8s avoir su00e9lectionnu00e9 une piste
        const spotifyModal = bootstrap.Modal.getInstance(document.getElementById('spotifyModal'));
        if (spotifyModal) {
            spotifyModal.hide();
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la lecture: ' + error.message);
    });
}

// Jouer une playlist entiu00e8re
function playPlaylist(playlistId) {
    if (!deviceId) {
        alert('Lecteur Spotify non initialisu00e9');
        return;
    }
    
    fetch('ajax/spotify_play.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            device_id: deviceId,
            context_uri: `spotify:playlist:${playlistId}`
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Erreur de lecture:', data.error);
            alert('Erreur lors de la lecture: ' + data.error);
            return;
        }
        
        // Fermer la modal apru00e8s avoir su00e9lectionnu00e9 une playlist
        const spotifyModal = bootstrap.Modal.getInstance(document.getElementById('spotifyModal'));
        if (spotifyModal) {
            spotifyModal.hide();
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la lecture: ' + error.message);
    });
}

// Mettre u00e0 jour le bouton de lecture/pause
function updatePlayButton() {
    const toggleButton = document.getElementById('toggleSpotify');
    if (toggleButton) {
        toggleButton.innerHTML = isPlaying ? 
            '<i class="bi bi-pause-fill"></i> Pause' : 
            '<i class="bi bi-play-fill"></i> Jouer';
    }
}

// Mettre u00e0 jour les informations de la piste en cours
function updateTrackInfo(track) {
    const trackInfoElement = document.getElementById('currentTrackInfo');
    if (trackInfoElement) {
        const artists = track.artists.map(artist => artist.name).join(', ');
        trackInfoElement.innerHTML = `
            <div class="d-flex align-items-center">
                <img src="${track.album.images[track.album.images.length-1].url}" width="40" height="40" class="me-2">
                <div>
                    <div class="fw-bold">${track.name}</div>
                    <div class="small">${artists}</div>
                </div>
            </div>
        `;
        trackInfoElement.style.display = 'block';
    }
}

// Initialiser l'intu00e9gration Spotify au chargement de la page
document.addEventListener('DOMContentLoaded', initSpotify);
