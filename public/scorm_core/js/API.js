/**
 * /public/scorm_core/js/API.js
 * API SCORM 1.2 Onéduc optimisée
 */

(function () {
  "use strict";

  // ----------------------------
  // 1) Helpers Onéduc (parent)
  // ----------------------------
  function getContext() {
    return window.parent?.SCORM_CONTEXT || null;
  }

  function getLectureId() {
    return getContext()?.lecture_id || 0;
  }

  function isDoneStatus(scormValue) {
    // SCORM 1.2 : Storyline ("passed") vs iSpring ("completed")
    return scormValue === "completed" || scormValue === "passed";
  }

  let nextButtonShown = false;

  function afficherBoutonSuivantDepuisIframe() {
    if (nextButtonShown) return;

    const context = getContext();
    const wrapper = window.parent?.document.getElementById("next-lesson-wrapper");
    const bouton = window.parent?.document.getElementById("next-lesson-button");
    const texteBouton = window.parent?.document.getElementById("next-button-text");
    if (wrapper && bouton) {
        wrapper.classList.remove("hidden");
        
        // CORRECTION : Forcer l'opacité et le curseur
        bouton.style.opacity = "1";
        bouton.style.pointerEvents = "auto"; // Réactive le clic et le curseur
        bouton.style.cursor = "pointer";    // Force l'affichage de la main
    }
    if (!wrapper || !bouton || !context) {
      return;
    }

    // Affiche le conteneur et gère l'opacité (Onéduc design)
    wrapper.classList.remove("hidden");
    bouton.style.opacity = "1";
    bouton.style.pointerEvents = "auto";

    // Logique de redirection Onéduc
    const shouldOfferQuiz = Boolean(context.quiz_start_url) && context.force_next_lesson !== true;
    if (shouldOfferQuiz) {
      if (texteBouton) texteBouton.innerText = "Passer au Questionnaire";
      bouton.onclick = function () {
        // Appel de la fonction globale définie dans lecon.blade.php
        if (typeof window.parent.goToQuiz === "function") {
            window.parent.goToQuiz();
        } else {
            window.parent.location.href = context.quiz_start_url;
        }
      };
    } else {
      if (texteBouton) texteBouton.innerText = "Leçon suivante";
      bouton.onclick = function () {
        if (typeof window.parent.goToNextLesson === "function") {
            window.parent.goToNextLesson();
        } else {
            window.parent.location.href = context.next_url;
        }
      };
    }

    nextButtonShown = true;
    console.log("🚀 Bouton de navigation Onéduc activé");
  }

  // ----------------------------
  // 2) Envoi SCORM -> Laravel
  // ----------------------------
  let lastSent = { key: null, value: null, at: 0 };

  function shouldSend(key, value) {
    const now = Date.now();
    if (lastSent.key === key && lastSent.value === value && (now - lastSent.at) < 500) {
      return false;
    }
    lastSent = { key, value, at: now };
    return true;
  }

  async function envoyerProgression(key, value) {
    const lectureId = getLectureId();
    if (!lectureId || !shouldSend(key, value)) return null;

    try {
      const res = await fetch("/scorm/save-progress", {
        method: "POST",
        headers: { 
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": window.parent?.document.querySelector('meta[name="csrf-token"]')?.content || ""
        },
        credentials: "same-origin",
        keepalive: true,
        body: JSON.stringify({
          lecture_id: lectureId,
          scorm_key: key,
          scorm_value: value
        })
      });

      const data = await res.json().catch(() => null);

      // Si le backend confirme la complétion, on affiche le bouton
      if (data?.lesson_status === "completed" || isDoneStatus(data?.scorm_lesson_status)) {
        afficherBoutonSuivantDepuisIframe();
      }

      return data;
    } catch (err) {
      console.error("❌ Erreur SCORM Onéduc:", err);
      return null;
    }
  }

  // ----------------------------
  // 3) API SCORM 1.2
  // ----------------------------
  const cmiStore = Object.create(null);

  const API = {
    LMSInitialize: function () {
      console.log("✅ LMSInitialize Onéduc");
      
      // CRITIQUE : Si le PHP nous dit que c'est déjà fini, on affiche le bouton direct
      const ctx = getContext();
      if (ctx && ctx.is_already_done === true) {
          afficherBoutonSuivantDepuisIframe();
      }
      
      return "true";
    },

    LMSGetValue: function (name) {
      return cmiStore[name] ?? "";
    },

    LMSSetValue: function (name, value) {
      cmiStore[name] = value;
      envoyerProgression(name, value);

      // Détection immédiate du succès (Storyline/iSpring)
      if (name === "cmi.core.lesson_status" && isDoneStatus(value)) {
        afficherBoutonSuivantDepuisIframe();
      }

      return "true";
    },

    LMSCommit: function () { return "true"; },
    LMSFinish: function () { return "true"; },
    LMSGetLastError: function () { return "0"; },
    LMSGetErrorString: function () { return ""; },
    LMSGetDiagnostic: function () { return ""; }
  };

  window.API = API;
  console.log("✅ API Onéduc initialisée");
})();
