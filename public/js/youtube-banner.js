 // Función para cargar la API de YouTube
 // youtube-banner.js
let player;

function onYouTubeIframeAPIReady() {
    player = new YT.Player('youtube-player', {
        height: '390',
        width: '100%',
        videoId: 'KsX9cwdnq8Y', // ¡Reemplazar!
        playerVars: {
            autoplay: 1,
            mute: 0,
            loop: 1,
            playlist: 'KsX9cwdnq8Y', // Mismo ID que videoId
            modestbranding: 1,
            fs: 0 // Deshabilita pantalla completa
        },
        events: {
            onReady: onPlayerReady,
            onStateChange: onPlayerStateChange
        }
    });
}

function onPlayerReady(event) {
    event.target.playVideo();
}

// Loop del video
function onPlayerStateChange(event) {
    if (event.data === YT.PlayerState.ENDED) {
        player.playVideo();
    }
}

// Cerrar banner
document.getElementById('close-banner').addEventListener('click', function() {
    document.getElementById('video-banner').style.display = 'none';
    if (player) {
        player.stopVideo();
    }
});

// Carga segura de la API
if (typeof YT === 'undefined') {
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    document.body.appendChild(tag);
}
 
 