var API = {
    LMSInitialize: function(param) {
        console.log("✅ [ÉVAL] LMSInitialize called");
        return "true";
    },

    LMSGetValue: function(name) {
        console.log("📥 [ÉVAL] LMSGetValue", name);
        return "";
    },

    LMSSetValue: function(name, value) {
        console.log("📤 [ÉVAL] LMSSetValue", name, value);

        const evaluationId = window.parent?.SCORM_CONTEXT?.evaluation_id;

        if (evaluationId) {
            fetch('/scorm/evaluation-progress', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    evaluation_id: evaluationId,
                    scorm_key: name,
                    scorm_value: value
                })
            })
            .then(res => res.json())
            .then(data => console.log('📨 Donnée ÉVAL envoyée à Laravel:', data))
            .catch(err => console.error('❌ Erreur ÉVAL → Laravel:', err));
        } else {
            console.warn("❗ [ÉVAL] evaluation_id introuvable dans SCORM_CONTEXT");
        }

        return "true";
    },

    LMSCommit: function(param) {
        console.log("💾 [ÉVAL] LMSCommit");
        return "true";
    },

    LMSFinish: function(param) {
        console.log("✅ [ÉVAL] LMSFinish");
        return "true";
    },

    LMSGetLastError: () => "0",
    LMSGetErrorString: () => "",
    LMSGetDiagnostic: () => ""
};

window.API = API;
console.log("✅ API_evaluation.js chargé — SCORM Évaluation prêt.");
