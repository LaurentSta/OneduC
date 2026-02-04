@extends('admin.admin_dashboard')

@section('admin')
<script>
/**
 * API LMS minimale (SCORM 1.2 + SCORM 2004)
 * Objectif : permettre au module de se lancer sans erreur.
 * Aucune donnée n’est enregistrée côté serveur (V2 simplifiée).
 */
(function () {
  // Petit stockage en mémoire (optionnel)
  const store = new Map();
  let lastError = 0;

  // ---- SCORM 1.2 ----
  window.API = {
    LMSInitialize: function () { lastError = 0; return "true"; },
    LMSFinish:     function () { lastError = 0; return "true"; },
    LMSCommit:     function () { lastError = 0; return "true"; },

    LMSGetValue: function (key) {
      lastError = 0;
      return store.get(key) ?? "";
    },

    LMSSetValue: function (key, value) {
      lastError = 0;
      store.set(String(key), String(value));
      return "true";
    },

    LMSGetLastError:   function () { return String(lastError); },
    LMSGetErrorString: function () { return ""; },
    LMSGetDiagnostic:  function () { return ""; },
  };

  // ---- SCORM 2004 ----
  window.API_1484_11 = {
    Initialize: function () { lastError = 0; return "true"; },
    Terminate:  function () { lastError = 0; return "true"; },
    Commit:     function () { lastError = 0; return "true"; },

    GetValue: function (key) {
      lastError = 0;
      return store.get(key) ?? "";
    },

    SetValue: function (key, value) {
      lastError = 0;
      store.set(String(key), String(value));
      return "true";
    },

    GetLastError:   function () { return String(lastError); },
    GetErrorString: function () { return ""; },
    GetDiagnostic:  function () { return ""; },
  };
})();
</script>

<div class="w-full px-6 lg:px-8">
  <div class="max-w-[1100px] mx-auto my-6">
    <div class="bg-white rounded-[20px] shadow-soft p-6 border border-gray-100">
      <h2 class="admin-page-title">Prévisualisation SCORM (V2)</h2>
      <p class="text-sm text-gray-600 mb-4">{{ $lecture->lecture_title ?? 'Leçon' }}</p>

      <div class="rounded-xl overflow-hidden border border-gray-200">
        <iframe
          src="{{ $src }}"
          class="w-full"
          style="height: 75vh;"
          loading="lazy"
          referrerpolicy="no-referrer"
          title="SCORM Preview"
        ></iframe>
      </div>
    </div>
  </div>
</div>
@endsection
