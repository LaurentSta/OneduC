@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li><a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">Zone de clic</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Zone de clic</p>
        <p class="text-sm text-gray-500 mt-1">Uploadez une image, dessinez les zones à trouver, puis diffusez le jeu via un code d'accès.</p>
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8"
       x-data='{
         imageSrc: null,
         imageFile: null,
         zones: [],
         drawing: false,
         startX: 0,
         startY: 0,
         currentRect: null,
         currentShape: "rectangle",
         selectedShape: "rectangle",
         pendingZone: null,
         labelInput: "",
         movingIndex: null,
         moveOffsetX: 0,
         moveOffsetY: 0,
         onFileChange(e) {
           const file = e.target.files[0];
           if (!file) return;
           this.imageFile = file;
           this.imageSrc = URL.createObjectURL(file);
           this.zones = [];
           this.pendingZone = null;
         },
         pointFromEvent(e) {
           const rect = this.$refs.imageWrap.getBoundingClientRect();
           const x = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
           const y = Math.max(0, Math.min(100, ((e.clientY - rect.top) / rect.height) * 100));
           return { x, y };
         },
         startDraw(e) {
           if (this.pendingZone) return;
           const p = this.pointFromEvent(e);
           this.startX = p.x;
           this.startY = p.y;
           this.drawing = true;
           this.currentShape = this.selectedShape;
           this.currentRect = { x: p.x, y: p.y, w: 0, h: 0 };
         },
         shapeRatio(shape) {
           return (shape === "oval" || shape === "rectangle") ? (5 / 3) : 1;
         },
         moveDraw(e) {
           if (!this.drawing) return;
           const rect = this.$refs.imageWrap.getBoundingClientRect();
           const p = this.pointFromEvent(e);
           const rawWPx = (Math.abs(p.x - this.startX) / 100) * rect.width;
           const rawHPx = (Math.abs(p.y - this.startY) / 100) * rect.height;
           const ratio = this.shapeRatio(this.currentShape);
           let wPx = rawWPx;
           let hPx = rawHPx;
           if (rawHPx === 0 || (rawWPx / ratio) > rawHPx) {
             hPx = rawWPx / ratio;
           } else {
             wPx = rawHPx * ratio;
           }
           const w = (wPx / rect.width) * 100;
           const h = (hPx / rect.height) * 100;
           const x = p.x >= this.startX ? this.startX : this.startX - w;
           const y = p.y >= this.startY ? this.startY : this.startY - h;
           this.currentRect = { x, y, w, h };
         },
         endDraw() {
           if (!this.drawing) return;
           this.drawing = false;
           if (this.currentRect && this.currentRect.w > 1.5 && this.currentRect.h > 1.5) {
             this.pendingZone = { ...this.currentRect, shape: this.currentShape };
           }
           this.currentRect = null;
         },
         confirmZone() {
           const label = this.labelInput.trim();
           if (!label || !this.pendingZone) return;
           this.zones.push({ ...this.pendingZone, label });
           this.pendingZone = null;
           this.labelInput = "";
         },
         cancelZone() {
           this.pendingZone = null;
           this.labelInput = "";
         },
         removeZone(index) {
           this.zones.splice(index, 1);
         },
         startMove(e, i) {
           if (this.pendingZone || this.drawing) return;
           const p = this.pointFromEvent(e);
           this.movingIndex = i;
           this.moveOffsetX = p.x - this.zones[i].x;
           this.moveOffsetY = p.y - this.zones[i].y;
         },
         moveZone(e) {
           if (this.movingIndex === null) return;
           const p = this.pointFromEvent(e);
           const zone = this.zones[this.movingIndex];
           const maxX = 100 - zone.w;
           const maxY = 100 - zone.h;
           zone.x = Math.max(0, Math.min(maxX, p.x - this.moveOffsetX));
           zone.y = Math.max(0, Math.min(maxY, p.y - this.moveOffsetY));
         },
         endMove() {
           this.movingIndex = null;
         },
         shapeExtraClass(shape) {
           return (shape === "circle" || shape === "oval") ? "rounded-full" : "";
         },
       }'>

    @if($groups->isEmpty())
      <div class="lg:col-span-1">
        <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
          <p class="font-varela text-base font-bold text-bleuone mb-4">Nouveau jeu</p>
          <div class="rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            Aucun groupe disponible. Créez un groupe pour lancer ce jeu.
          </div>
        </div>
      </div>
    @else
      <div class="lg:col-span-1 space-y-6">
        <form method="POST" action="{{ route('formateur.composants.store') }}" enctype="multipart/form-data">
          @csrf
          <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
            <p class="font-varela text-base font-bold text-bleuone mb-4">Nouveau jeu</p>

            @if(session('success'))
              <div class="mb-4 rounded-[10px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
              </div>
            @endif

            @if($errors->any())
              <div class="mb-4 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
              </div>
            @endif

            <div class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Groupe</label>
                <select name="group_id" required
                        class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-200">
                  <option value="">Choisir un groupe...</option>
                  @foreach($groups as $group)
                    <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                      {{ $group->name }} ({{ $group->students_count }} stagiaire{{ $group->students_count > 1 ? 's' : '' }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Titre (optionnel)</label>
                <input type="text" name="title" maxlength="255" value="{{ old('title') }}"
                       placeholder="Ex : L'intérieur d'un ordinateur"
                       class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-200">
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Image</label>
                <div class="flex items-center gap-3">
                  <label for="component-image-input"
                         class="inline-flex shrink-0 cursor-pointer items-center rounded-[8px] bg-orange-50 px-3 py-2 text-xs font-semibold text-orangeone hover:bg-orange-100 transition">
                    Choisir un fichier
                  </label>
                  <span class="truncate text-xs text-gray-500" x-text="imageFile ? imageFile.name : 'Aucun fichier choisi'"></span>
                </div>
                <input id="component-image-input" type="file" name="image" accept="image/*" required
                       @change="onFileChange($event)"
                       class="hidden">
              </div>
            </div>

            <input type="hidden" name="zones" :value="JSON.stringify(zones)">

            <button type="submit"
                    :disabled="!imageFile || zones.length < 2"
                    :class="(!imageFile || zones.length < 2) ? 'opacity-50 cursor-not-allowed' : ''"
                    class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-orangeone px-4 py-2.5 text-sm font-bold text-white hover:bg-orangeone-hover transition">
              Créer le jeu
            </button>

            <p x-show="imageSrc && zones.length < 2" x-cloak class="mt-2 text-[11px] text-orangeone">
              Ajoutez au moins deux composants pour lancer le jeu.
            </p>
          </div>
        </form>

        <div class="space-y-4">
          @forelse($sessions as $session)
            <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3">
              <div class="flex items-start gap-3 min-w-0">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $session->is_active ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                </div>
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2 mb-0.5">
                    <p class="text-sm font-bold text-gray-900 truncate">{{ $session->title }}</p>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $session->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                      {{ $session->is_active ? 'Ouvert' : 'Fermé' }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-500 truncate">{{ count($session->zones ?? []) }} composant{{ count($session->zones ?? []) > 1 ? 's' : '' }} à trouver</p>
                  <p class="text-[10px] text-gray-400 mt-1">
                    Groupe : <span class="font-semibold">{{ $session->group?->name ?? '—' }}</span>
                    - Code : <span class="font-mono font-semibold text-orangeone">{{ $session->access_code }}</span>
                    - {{ $session->attempts_count }} participation{{ $session->attempts_count > 1 ? 's' : '' }}
                  </p>
                </div>
              </div>
              <div class="flex gap-2 shrink-0">
                <a href="{{ route('formateur.composants.show', $session) }}"
                   class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-[8px] bg-orangeone px-3 py-1.5 text-xs font-bold text-white hover:bg-orangeone-hover transition">
                  Ouvrir
                </a>
                <form method="POST" action="{{ route('formateur.composants.destroy', $session) }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit"
                          class="inline-flex items-center justify-center rounded-[8px] border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition">
                    Supprimer
                  </button>
                </form>
              </div>
            </div>
          @empty
            <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center">
              <p class="text-sm font-semibold text-gray-700">Aucun jeu créé</p>
              <p class="text-xs text-gray-400 mt-1">Utilisez le formulaire pour lancer votre premier jeu.</p>
            </div>
          @endforelse
        </div>
      </div>

      <div class="lg:col-span-2">
        <div class="bg-white rounded-[20px] shadow-md p-6" x-show="imageSrc" x-cloak>
          <label class="block text-xs font-semibold text-gray-600 mb-1.5">Forme de la zone</label>
          <div class="mb-3 grid grid-cols-2 sm:grid-cols-5 gap-2">
            <button type="button" @click="selectedShape = 'square'"
                    :class="selectedShape === 'square' ? 'border-orangeone bg-orange-50' : 'border-gray-200 bg-white hover:border-orange-300'"
                    class="flex flex-col items-center gap-1 rounded-[8px] border-2 py-2 transition">
              <span class="block h-4 w-4 bg-gray-500"></span>
              <span class="text-[10px] font-semibold text-gray-600">Carré</span>
            </button>
            <button type="button" @click="selectedShape = 'circle'"
                    :class="selectedShape === 'circle' ? 'border-orangeone bg-orange-50' : 'border-gray-200 bg-white hover:border-orange-300'"
                    class="flex flex-col items-center gap-1 rounded-[8px] border-2 py-2 transition">
              <span class="block h-4 w-4 rounded-full bg-gray-500"></span>
              <span class="text-[10px] font-semibold text-gray-600">Rond</span>
            </button>
            <button type="button" @click="selectedShape = 'oval'"
                    :class="selectedShape === 'oval' ? 'border-orangeone bg-orange-50' : 'border-gray-200 bg-white hover:border-orange-300'"
                    class="flex flex-col items-center gap-1 rounded-[8px] border-2 py-2 transition">
              <span class="block h-3 w-5 rounded-full bg-gray-500"></span>
              <span class="text-[10px] font-semibold text-gray-600">Ovale</span>
            </button>
            <button type="button" @click="selectedShape = 'triangle'"
                    :class="selectedShape === 'triangle' ? 'border-orangeone bg-orange-50' : 'border-gray-200 bg-white hover:border-orange-300'"
                    class="flex flex-col items-center gap-1 rounded-[8px] border-2 py-2 transition">
              <span class="block h-4 w-4 bg-gray-500" style="clip-path: polygon(50% 0%, 0% 100%, 100% 100%);"></span>
              <span class="text-[10px] font-semibold text-gray-600">Triangle</span>
            </button>
            <button type="button" @click="selectedShape = 'rectangle'"
                    :class="selectedShape === 'rectangle' ? 'border-orangeone bg-orange-50' : 'border-gray-200 bg-white hover:border-orange-300'"
                    class="flex flex-col items-center gap-1 rounded-[8px] border-2 py-2 transition">
              <span class="block h-3 w-5 bg-gray-500"></span>
              <span class="text-[10px] font-semibold text-gray-600">Rectangle</span>
            </button>
          </div>

          <label class="block text-xs font-semibold text-gray-600 mb-1">
            Dessinez une zone par composant à trouver, puis nommez-la.
          </label>
          <p class="mb-1 text-[11px] text-gray-400">Astuce : cliquez-glissez une zone déjà créée pour la déplacer.</p>
          <div class="relative select-none rounded-[10px] overflow-hidden border border-gray-200"
               x-ref="imageWrap"
               @mousedown="startDraw($event)"
               @mousemove="moveDraw($event); moveZone($event)"
               @mouseup="endDraw(); endMove()"
               @mouseleave="drawing && endDraw(); endMove()">
            <img :src="imageSrc" draggable="false" class="w-full h-auto block pointer-events-none">

            <template x-for="(zone, i) in zones" :key="i">
              <div class="absolute cursor-move pointer-events-auto"
                   :class="movingIndex === i ? 'z-10' : ''"
                   @mousedown.stop="startMove($event, i)"
                   :style="`left:${zone.x}%;top:${zone.y}%;width:${zone.w}%;height:${zone.h}%;`">
                <template x-if="zone.shape === 'triangle'">
                  <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 h-full w-full overflow-visible">
                    <polygon points="50,0 0,100 100,100" fill="rgba(1,198,156,0.2)" stroke="#01c69c" stroke-width="2" vector-effect="non-scaling-stroke" />
                  </svg>
                </template>
                <template x-if="zone.shape !== 'triangle'">
                  <div class="absolute inset-0 border-2 border-vertone bg-vertone/20" :class="shapeExtraClass(zone.shape)"></div>
                </template>
                <span class="absolute -top-5 left-0 whitespace-nowrap text-[10px] font-bold bg-vertone text-white px-1 rounded" x-text="zone.label"></span>
              </div>
            </template>

            <div x-show="currentRect" x-cloak class="absolute pointer-events-none"
                 :style="currentRect ? `left:${currentRect.x}%;top:${currentRect.y}%;width:${currentRect.w}%;height:${currentRect.h}%;` : ''">
              <template x-if="currentShape === 'triangle'">
                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 h-full w-full overflow-visible">
                  <polygon points="50,0 0,100 100,100" fill="rgba(233,77,42,0.1)" stroke="#E94D2A" stroke-width="2" stroke-dasharray="4 3" vector-effect="non-scaling-stroke" />
                </svg>
              </template>
              <template x-if="currentShape !== 'triangle'">
                <div class="absolute inset-0 border-2 border-dashed border-orangeone bg-orangeone/10" :class="shapeExtraClass(currentShape)"></div>
              </template>
            </div>

            <div x-show="pendingZone" x-cloak class="absolute pointer-events-none"
                 :style="pendingZone ? `left:${pendingZone.x}%;top:${pendingZone.y}%;width:${pendingZone.w}%;height:${pendingZone.h}%;` : ''">
              <template x-if="pendingZone && pendingZone.shape === 'triangle'">
                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 h-full w-full overflow-visible">
                  <polygon points="50,0 0,100 100,100" fill="rgba(233,77,42,0.2)" stroke="#E94D2A" stroke-width="2" vector-effect="non-scaling-stroke" />
                </svg>
              </template>
              <template x-if="pendingZone && pendingZone.shape !== 'triangle'">
                <div class="absolute inset-0 border-2 border-orangeone bg-orangeone/20" :class="pendingZone ? shapeExtraClass(pendingZone.shape) : ''"></div>
              </template>
            </div>
          </div>

          <div x-show="pendingZone" x-cloak class="mt-2 flex gap-2">
            <input type="text" x-model="labelInput" maxlength="100"
                   @keydown.enter.prevent="confirmZone()"
                   placeholder="Nom du composant (ex : clavier)"
                   class="flex-1 rounded-[8px] border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-200">
            <button type="button" @click="confirmZone()"
                    class="rounded-[8px] bg-orangeone px-3 py-2 text-xs font-bold text-white hover:bg-orangeone-hover">
              Ajouter
            </button>
            <button type="button" @click="cancelZone()"
                    class="rounded-[8px] border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50">
              Annuler
            </button>
          </div>

          <div class="mt-3 flex flex-wrap gap-1.5">
            <template x-for="(zone, i) in zones" :key="'list-' + i">
              <div class="flex items-center gap-2 rounded-[8px] bg-gray-50 px-3 py-1.5">
                <span class="text-xs font-semibold text-gray-700" x-text="zone.label"></span>
                <button type="button" @click="removeZone(i)"
                        class="text-[11px] font-semibold text-red-500 hover:text-red-600">
                  Supprimer
                </button>
              </div>
            </template>
          </div>
        </div>

        <div x-show="!imageSrc" x-cloak
             class="flex min-h-[420px] flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white p-10 text-center">
          <p class="text-sm font-semibold text-gray-700">Aucune image sélectionnée</p>
          <p class="text-xs text-gray-400 mt-1">Choisissez un fichier à gauche pour afficher l'image en grand et dessiner les zones à trouver.</p>
        </div>
      </div>
    @endif

  </div>
</div>
@endsection
