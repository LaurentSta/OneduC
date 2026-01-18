function afficherBoutonSuivantDepuisIframe() {
    const wrapper = window.parent?.document.getElementById('next-lesson-wrapper');
    const bouton = window.parent?.document.getElementById('next-lesson-button');
    const texteBouton = window.parent?.document.getElementById('next-button-text');
    const context = window.parent?.SCORM_CONTEXT;

    if (wrapper && bouton && context) {
        // Affiche le conteneur (flex)
        wrapper.classList.remove('hidden');
        
        // Anime l'apparition du bouton
        bouton.classList.remove('opacity-0');
        bouton.style.opacity = '1';

        // Gestion dynamique du texte et de l'action
        if (context.quiz_start_url) {
            if (texteBouton) texteBouton.innerText = "Passer au Questionnaire";
            bouton.onclick = function () {
                context.goToQuiz();
            };
        } else {
            if (texteBouton) texteBouton.innerText = "Leçon suivante";
            bouton.onclick = function () {
                context.goToNextLesson();
            };
        }
    }
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
