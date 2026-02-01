/**
 * /public/scorm_core/js/API.js
 * API SCORM 1.2 minimal + envoi vers Laravel + affichage bouton "suivant" (leçon ou quiz)
 *
 * Objectifs :
 * - Support iSpring + Storyline (Storyline envoie souvent "passed" au lieu de "completed")
 * - Afficher le bouton dès que la leçon est terminée (completed OU passed), ou si le backend confirme la complétion
 * - Conserver l’affichage dynamique "Passer au Questionnaire" si quiz_start_url existe
 * - Éviter les doublons d’affichage / appels réseau inutiles
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
    return window.parent?.SCORM_CONTEXT?.lecture_id || 0;
  }

  function isDoneStatus(scormValue) {
    // SCORM 1.2 : Storyline peut envoyer "passed"
    // iSpring : souvent "completed"
    return scormValue === "completed" || scormValue === "passed";
  }

  // Empêche de réafficher le bouton plusieurs fois
  let nextButtonShown = false;

  function afficherBoutonSuivantDepuisIframe() {
    if (nextButtonShown) return;

    const wrapper = window.parent?.document.getElementById("next-lesson-wrapper");
    const bouton = window.parent?.document.getElementById("next-lesson-button");
    const texteBouton = window.parent?.document.getElementById("next-button-text");
    const context = getContext();

    if (!wrapper || !bouton || !context) {
      console.warn("❗ Bouton suivant: éléments introuvables dans le parent (wrapper/bouton/context).");
      return;
    }

    // Affiche le conteneur (flex)
    wrapper.classList.remove("hidden");

    // Anime l'apparition du bouton
    bouton.classList.remove("opacity-0");
    bouton.style.opacity = "1";

    // Texte + action selon présence d'un quiz
    if (context.quiz_start_url) {
      if (texteBouton) texteBouton.innerText = "Passer au Questionnaire";
      bouton.onclick = function () {
        if (typeof context.goToQuiz === "function") context.goToQuiz();
      };
    } else {
      if (texteBouton) texteBouton.innerText = "Leçon suivante";
      bouton.onclick = function () {
        if (typeof context.goToNextLesson === "function") context.goToNextLesson();
      };
    }

    nextButtonShown = true;
  }

  // ----------------------------
  // 2) Envoi SCORM -> Laravel
  // ----------------------------
  // Anti-spam: on évite de renvoyer la même paire clé/valeur en boucle
  let lastSent = { key: null, value: null, at: 0 };

  function shouldSend(key, value) {
    const now = Date.now();
    // ignore répétitions immédiates (souvent le player renvoie la même valeur en rafale)
    if (lastSent.key === key && lastSent.value === value && (now - lastSent.at) < 400) {
      return false;
    }
    lastSent = { key, value, at: now };
    return true;
  }

  async function envoyerProgression(key, value) {
    const lectureId = getLectureId();
    if (!lectureId) {
      console.warn("❗ lecture_id introuvable dans SCORM_CONTEXT");
      return null;
    }

    if (!shouldSend(key, value)) return null;

    try {
      const res = await fetch("/scorm/save-progress", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        keepalive: true,
        body: JSON.stringify({
          lecture_id: lectureId,
          scorm_key: key,
          scorm_value: value
        })
      });

      // Réponse JSON attendue (idéalement) :
      // { success:true, lesson_status:"completed", scorm_lesson_status:"passed" }
      const data = await res.json().catch(() => null);

      console.log("📨 Donnée envoyée à Laravel:", data || { success: res.ok });

      // Si le backend confirme une fin, on affiche le bouton
      // - lesson_status: completed (logique Onéduc)
      // - scorm_lesson_status: passed/completed (logique SCORM)
      const backendDone =
        data?.lesson_status === "completed" ||
        data?.scorm_lesson_status === "completed" ||
        data?.scorm_lesson_status === "passed";

      if (backendDone) {
        afficherBoutonSuivantDepuisIframe();
      }

      return data;
    } catch (err) {
      console.error("❌ Erreur lors de l'envoi SCORM → Laravel:", err);
      return null;
    }
  }

  // ----------------------------
  // 3) API SCORM 1.2 minimale
  // ----------------------------
  // Mémoire locale des valeurs (utile pour les GetValue)
  const cmiStore = Object.create(null);

  const API = {
    LMSInitialize: function () {
      console.log("✅ LMSInitialize called");
      return "true";
    },

    LMSGetValue: function (name) {
      console.log("📥 LMSGetValue", name);
      // On renvoie ce qu’on a en mémoire locale (sinon vide)
      return cmiStore[name] ?? "";
    },

    LMSSetValue: function (name, value) {
      console.log("📤 LMSSetValue", name, value);

      // Stockage local pour cohérence GetValue
      cmiStore[name] = value;

      // Envoi asynchrone vers Laravel
      envoyerProgression(name, value);

      // Déclenchement immédiat du bouton si statut final SCORM
      // - iSpring: completed
      // - Storyline: passed
      if (name === "cmi.core.lesson_status" && isDoneStatus(value)) {
        afficherBoutonSuivantDepuisIframe();
      }

      return "true";
    },

    LMSCommit: function () {
      console.log("💾 LMSCommit");
      // On peut aussi ping Laravel si besoin, mais pas obligatoire
      return "true";
    },

    LMSFinish: function () {
      console.log("✅ LMSFinish");
      return "true";
    },

    LMSGetLastError: function () {
      return "0";
    },

    LMSGetErrorString: function () {
      return "";
    },

    LMSGetDiagnostic: function () {
      return "";
    }
  };

  // Exposition globale attendue par SCORM 1.2
  window.API = API;

  console.log("✅ API.js chargé — SCORM 1.2 prêt (iSpring + Storyline) + bouton navigation géré");
})();
