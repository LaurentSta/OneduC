// public/scorm_core/js/api_Scorm2004.js

console.log("🧠 SCORM 2004 API JS chargé");

(function () {
  const scormData = {}; // 🔸 Stocke les valeurs SCORM

  const SCORM_API = {
    Initialize: function (param) {
      console.log("🟢 Initialize:", param);
      return "true";
    },

    Terminate: function (param) {
      console.log("🔴 Terminate:", param);
      return "true";
    },

    GetValue: function (key) {
      const value = scormData[key] ?? "";
      console.log("🔍 GetValue:", key, "→", value);
      return value;
    },

    SetValue: function (key, value) {
      scormData[key] = value;
      console.log("✏️ SetValue:", key, "=", value);
      if (key.startsWith("cmi.interactions")) {
        console.log("📌 Interaction enregistrée :", key, value);
      }
      return "true";
    },

    Commit: function (param) {
      console.log("💾 Commit:", param);
      console.table(scormData);

      // Estimation des slides
      if (scormData["cmi.progress_measure"]) {
        const m = parseFloat(scormData["cmi.progress_measure"]);
        if (!isNaN(m) && m > 0) {
          const totalSlides = Math.round(1 / m);
          console.log(`📊 Slides estimés : ${totalSlides}`);
        }
      }

      // Récap interactions
      const interactions = Object.keys(scormData)
        .filter(k => k.startsWith("cmi.interactions."))
        .reduce((acc, k) => {
          const match = k.match(/^cmi\.interactions\.(\d+)\.(.+)$/);
          if (match) {
            const idx = match[1];
            const field = match[2];
            acc[idx] = acc[idx] || {};
            acc[idx][field] = scormData[k];
          }
          return acc;
        }, {});

      if (Object.keys(interactions).length > 0) {
        console.log("🧮 Interactions détectées :");
        console.table(interactions);
      }

      return "true";
    },

    GetLastError: () => "0",
    GetErrorString: (code) => "No error",
    GetDiagnostic: (code) => "No diagnostic available"
  };

  // 👉 Injection prioritaire dans tous les contextes
  window.API_1484_11 = SCORM_API;
  if (window.top) {
    window.top.API_1484_11 = SCORM_API;
  }
})();
