document.addEventListener('DOMContentLoaded', () => {
    const iframe = document.querySelector('iframe#embeddedSmartPlayerInstance');
    if (!iframe) {
        console.error('❌ Iframe Camtasia non trouvée.');
        return;
    }

    const trackingUrl = '/scorm/camtasia-track';
    const userId = window.ONEDUC_USER_ID ?? null;
    const moduleId = window.ONEDUC_MODULE_ID ?? null;
    const videoId = window.ONEDUC_VIDEO_ID ?? 'camtasia_video_001';
    let isCompleted = false;

    if (!userId || !moduleId) {
        console.warn('⚠️ Tracking désactivé : informations manquantes.');
        return;
    }

    // 🔁 Accéder au contenu de l’iframe (si même domaine)
    iframe.addEventListener('load', () => {
        const iframeWindow = iframe.contentWindow;
        const iframeDoc = iframe.contentDocument || iframeWindow.document;

        const tryGetVideo = () => {
            const video = iframeDoc.querySelector('video');
            if (!video) {
                console.warn('⏳ Vidéo pas encore chargée... attente...');
                return setTimeout(tryGetVideo, 1000);
            }

            console.log('✅ Vidéo trouvée. Tracking activé.');

            setInterval(() => {
                const progress = (video.currentTime / video.duration) * 100;
                if (progress >= 90 && !isCompleted) isCompleted = true;

                fetch(trackingUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        module_id: moduleId,
                        video_id: videoId,
                        progress_percent: Math.floor(progress),
                        is_completed: isCompleted
                    })
                }).then(res => res.json())
                  .then(data => console.log('[ONEDUC] Tracking envoyé :', data))
                  .catch(err => console.error('[ONEDUC] Erreur tracking:', err));
            }, 30000);
        };

        tryGetVideo();
    });
});
