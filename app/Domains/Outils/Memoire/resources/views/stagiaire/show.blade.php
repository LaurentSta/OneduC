<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $session->title }} - Jeu de mémoire</title>
  @vite(['resources/js/app.js'])
  <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-screen bg-slate-100 p-4 font-lisible"
      x-data='{
        deck: @json($cards), flipped: [], matched: [], locked: false, moves: 0, errors: 0,
        startedAt: null, started: false, finished: {{ $existingAttempt ? "true" : "false" }},
        previousMoves: {{ (int) ($existingAttempt->moves ?? 0) }}, previousErrors: {{ (int) ($existingAttempt->errors ?? 0) }},
        previousDuration: {{ (int) ($existingAttempt->duration_seconds ?? 0) }}, submitting: false,
        isActive: {{ $session->is_active ? "true" : "false" }}, canPlay: {{ $peutJouer ? "true" : "false" }}, submitError: "",
        start() { this.started=true; this.finished=false; this.flipped=[]; this.matched=[]; this.locked=false; this.moves=0; this.errors=0; this.submitError=""; this.startedAt=Date.now(); this.deck=[...this.deck].sort(() => Math.random() - 0.5); },
        isFlipped(i) { return this.flipped.includes(i) || this.matched.includes(i); },
        flip(i) {
          if (this.locked || !this.isActive || !this.canPlay || this.isFlipped(i) || this.flipped.length >= 2) return;
          this.flipped.push(i);
          if (this.flipped.length !== 2) return;
          this.moves++;
          const [a,b] = this.flipped;
          if (this.deck[a].pair_index === this.deck[b].pair_index) {
            this.matched.push(a,b); this.flipped=[];
            if (this.matched.length === this.deck.length) this.submit();
          } else {
            this.errors++; this.locked=true;
            setTimeout(() => { this.flipped=[]; this.locked=false; }, 900);
          }
        },
        async submit() {
          this.submitting=true;
          const duration=Math.max(1, Math.round((Date.now()-this.startedAt)/1000));
          try {
            const response=await fetch(@json(route("memoire.submit", $session->access_code)), {
              method:"POST", headers:{"Content-Type":"application/json","Accept":"application/json","X-CSRF-TOKEN":@json(csrf_token())},
              body:JSON.stringify({moves:this.moves,duration_seconds:duration})
            });
            if (!response.ok) throw new Error("submit");
            this.finished=true; this.started=false; this.previousMoves=this.moves; this.previousErrors=this.errors; this.previousDuration=duration;
          } catch (_) { this.submitError="Le résultat n’a pas pu être enregistré. Réessayez."; }
          this.submitting=false;
        }
      }'>
  <main class="mx-auto w-full max-w-3xl space-y-4">
    <header class="rounded-[20px] bg-white p-6 shadow-md">
      <p class="text-xs uppercase text-gray-400">Code {{ $session->access_code }}</p>
      <h1 class="mt-1 font-raleway text-2xl text-bleuone">{{ $session->title }}</h1>
      @unless($session->is_active)<p class="mt-3 rounded-[8px] bg-amber-50 px-3 py-2 text-sm text-amber-700">Cette partie est fermée.</p>@endunless
      @unless($peutJouer)<p class="mt-3 rounded-[8px] bg-blue-50 px-3 py-2 text-sm text-blue-700">Aperçu formateur : le jeu est désactivé.</p>@endunless
      <p x-show="submitError" x-text="submitError" class="mt-3 rounded-[8px] bg-red-50 px-3 py-2 text-sm text-red-700"></p>
    </header>

    <section x-show="!started && !finished" x-cloak class="rounded-[20px] bg-white p-6 text-center shadow-md">
      <p class="mb-4 text-gray-700">Retrouvez les {{ count($cards) / 2 }} paires en retournant deux cartes à la fois.</p>
      <button @click="start()" :disabled="!isActive || !canPlay" class="btn-oneduc-blue disabled:opacity-50">Commencer</button>
    </section>

    <section x-show="finished" x-cloak class="rounded-[20px] bg-white p-6 text-center shadow-md">
      <p class="text-sm uppercase text-gray-400">Résultat</p>
      <p class="mt-2 text-3xl font-bold text-bleuone"><span x-text="previousErrors"></span> erreur(s)</p>
      <p class="mt-2 text-sm text-gray-600"><span x-text="previousMoves"></span> coups · <span x-text="previousDuration"></span>s</p>
      <button @click="start()" :disabled="!isActive || !canPlay" class="btn-oneduc-blue mt-4 disabled:opacity-50">Rejouer</button>
    </section>

    <section x-show="started && !finished" x-cloak class="rounded-[20px] bg-white p-6 shadow-md">
      <div class="mb-4 flex justify-between gap-3 text-sm"><span>Coups : <strong x-text="moves"></strong></span><span>Erreurs : <strong class="text-orangeone" x-text="errors"></strong></span></div>
      <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 sm:gap-3">
        <template x-for="(card,index) in deck" :key="index">
          <button type="button" @click="flip(index)" :aria-label="isFlipped(index) ? card.text : 'Carte masquée ' + (index + 1)"
                  class="aspect-square rounded-[8px] border-2 p-2 text-center text-xs font-semibold transition sm:text-sm"
                  :class="matched.includes(index) ? 'border-vertone bg-vertone/10 text-green-800' : (isFlipped(index) ? 'border-bleuone bg-blue-50 text-bleuone' : 'border-bleuone bg-bleuone text-white hover:bg-bleuone-light')">
            <span x-show="isFlipped(index)" x-text="card.text"></span><span x-show="!isFlipped(index)" aria-hidden="true">?</span>
          </button>
        </template>
      </div>
    </section>
  </main>
</body>
</html>
