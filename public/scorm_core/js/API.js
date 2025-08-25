// ---- API.js (Oneduc_Prod) ----

// Affiche le bouton "leçon suivante" depuis l'iframe SCORM
function afficherBoutonSuivantDepuisIframe() {
    const wrapper = window.parent?.document.getElementById('next-lesson-wrapper');
    const bouton  = window.parent?.document.getElementById('next-lesson-button');
    const nextUrl = window.parent?.SCORM_CONTEXT?.next_url;

    if (wrapper) wrapper.classList.remove('hidden');
    if (bouton) {
        bouton.classList.remove('opacity-0', 'pointer-events-none');
        bouton.classList.add('opacity-100');
        bouton.style.pointerEvents = 'auto';
        bouton.style.opacity = '1';
        if (nextUrl) bouton.onclick = () => { window.parent.location.href = nextUrl; };
    }
    console.log("Bouton 'leçon suivante' affiché depuis l'iframe SCORM");
}

// ---------- Envoi résilient vers Laravel ----------
const SCORM_ENDPOINT = '/scorm/save-progress';
let   SCORM_PENDING = [];       // tampon à vider sur pagehide/visibilitychange
let   SCORM_SENDING = false;

// Envoi robuste (fetch keepalive) + fallback sendBeacon
function scormSave(payload) {
    const body = JSON.stringify(payload);

    // Tentative fetch persistante
    return fetch(SCORM_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',   // mettre 'include' + mode:'cors' si cross-site
        cache: 'no-store',
        keepalive: true,
        body
    }).catch(() => {
        // Fallback sendBeacon si Chrome rejette le body à la fermeture
        try {
            const blob = new Blob([body], { type: 'application/json' });
            navigator.sendBeacon(SCORM_ENDPOINT, blob);
        } catch (e) {
            console.error('Beacon fallback failed', e);
        }
    });
}

// Ajoute au tampon et tente l'envoi immédiat
function queueScorm(payload) {
    SCORM_PENDING.push(payload);
    flushScorm(false);
}

// Vide le tampon; si onHide=true, on force l’envoi sans attendre
function flushScorm(onHide) {
    if (SCORM_SENDING && !onHide) return;
    if (SCORM_PENDING.length === 0) return;

    SCORM_SENDING = true;
    const toSend = SCORM_PENDING.slice();
    SCORM_PENDING = [];

    // Envoi séquentiel pour limiter les collisions réseau
    (async () => {
        for (const p of toSend) {
            try { await scormSave(p); } catch (_) {}
        }
        SCORM_SENDING = false;
    })();
}

// Vider avant navigation/fermeture (Chrome)
function flushOnHide() { flushScorm(true); }
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') flushOnHide();
}, { capture: true });
window.addEventListener('pagehide', flushOnHide, { capture: true });

// -------------- Implémentation API SCORM 1.2 ---------------
var API = {
    LMSInitialize: function(param) {
        console.log("LMSInitialize");
        return "true";
    },
    LMSGetValue: function(name) {
        console.log("LMSGetValue", name);
        return "";
    },
    LMSSetValue: function(name, value) {
        // lecture_id fourni par le parent Laravel
        const lectureId = window.parent?.SCORM_CONTEXT?.lecture_id || 0;
        if (!lectureId) {
            console.warn("lecture_id introuvable dans SCORM_CONTEXT");
            return "true";
        }

        const payload = {
            lecture_id: lectureId,
            scorm_key: name,
            scorm_value: value
        };

        // 1) Envoi immédiat, avant tout changement de slide
        queueScorm(payload);

        // 2) Afficher le bouton si la leçon est marquée completed
        if (name === "cmi.core.lesson_status" && value === "completed") {
            afficherBoutonSuivantDepuisIframe();
        }

        console.log("LMSSetValue", name, value);
        return "true";
    },
    LMSCommit: function(param) {
        console.log("LMSCommit -> flush");
        flushScorm(false);
        return "true";
    },
    LMSFinish: function(param) {
        console.log("LMSFinish -> flushOnHide");
        flushOnHide();
        return "true";
    },
    LMSGetLastError: () => "0",
    LMSGetErrorString: () => "",
    LMSGetDiagnostic: () => ""
};

window.API = API;
console.log("API.js chargé");
