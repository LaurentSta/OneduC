function afficherBoutonSuivantDepuisIframe() {
    const wrapper = window.parent?.document.getElementById('next-lesson-wrapper');
    const bouton = window.parent?.document.getElementById('next-lesson-button');
    const nextUrl = window.parent?.SCORM_CONTEXT?.next_url;

    if (wrapper) {
        wrapper.classList.remove('hidden');
    }

    if (bouton) {
        bouton.classList.remove('opacity-0', 'pointer-events-none');
        bouton.classList.add('opacity-100');
        bouton.style.pointerEvents = 'auto'; // sécurité
        bouton.style.opacity = '1';

        if (nextUrl) {
            bouton.onclick = function () {
                window.parent.location.href = nextUrl;
            };
        }
    }

    console.log("✅ Bouton 'leçon suivante' affiché depuis l'iframe SCORM");
}

var API = {
    LMSInitialize: function(param) {
        console.log("✅ LMSInitialize called");
        return "true";
    },
    LMSGetValue: function(name) {
        console.log("📥 LMSGetValue", name);
        return "";
    },
    LMSSetValue: function(name, value) {
        console.log("📤 LMSSetValue", name, value);

        // 🔁 Lecture ID dynamique depuis le parent Laravel
        const lectureId = window.parent?.SCORM_CONTEXT?.lecture_id || 0;

        if (lectureId) {
            fetch('/scorm/save-progress', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',          // ← important
                keepalive: true,                     // ← en cas de déchargement de page
                body: JSON.stringify({
                    lecture_id: window.parent?.SCORM_CONTEXT?.lecture_id,
                    scorm_key: name,
                    scorm_value: value
                })
                })
            .then(res => res.json())
            .then(data => console.log('📨 Donnée envoyée à Laravel:', data))
            .catch(err => console.error('❌ Erreur lors de l\'envoi SCORM → Laravel:', err));
        } else {
            console.warn("❗ lecture_id introuvable dans SCORM_CONTEXT");
        }

        // ✅ Affiche le bouton si la leçon est terminée
        if (name === "cmi.core.lesson_status" && value === "completed") {
            afficherBoutonSuivantDepuisIframe();
        }

        return "true";
    },
    LMSCommit: function(param) {
        console.log("💾 LMSCommit");
        return "true";
    },
    LMSFinish: function(param) {
        console.log("✅ LMSFinish");
        return "true";
    },
    LMSGetLastError: () => "0",
    LMSGetErrorString: () => "",
    LMSGetDiagnostic: () => ""
};

window.API = API;
console.log("✅ API.js chargé — prêt à interagir avec Laravel et afficher le bouton de navigation");
