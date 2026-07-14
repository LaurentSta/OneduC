<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oneduc - {{ $session->title }}</title>
  @vite(['resources/css/app.css'])
  <style>
    .card-sort-card { cursor: grab; }
    .card-sort-card.is-correct { border-color: #16a34a !important; background-color: #f0fdf4 !important; }
    .card-sort-card.is-incorrect { border-color: #dc2626 !important; background-color: #fef2f2 !important; }
    .card-sort-dropzone.drag-over { background-color: #fff7ed; }
  </style>
</head>
<body class="min-h-screen bg-slate-100 p-4 font-lisible">
  <main class="mx-auto w-full max-w-5xl rounded-[20px] bg-white p-6 shadow-md">
    <h1 class="font-raleway text-xl text-bleuone">{{ $session->title }}</h1>
    <p class="mt-1 text-sm text-gray-500">Glissez chaque carte dans la catégorie qui vous semble correcte, puis validez.</p>

    <div id="tri-cartes-score" class="mt-4 hidden rounded-[10px] border px-4 py-3 text-sm font-semibold"></div>

    @if($cartes->isEmpty() || $categories->isEmpty())
      <p class="mt-8 rounded-[8px] border-2 border-dashed border-gray-200 py-16 text-center text-sm text-gray-500">Cette activité n'est pas encore prête.</p>
    @else
      <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-4">
        <div class="card-sort-dropzone rounded-[16px] border-2 border-dashed border-gray-200 bg-slate-50 p-3 lg:col-span-4" data-category-id="" id="tri-cartes-pool">
          <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">Cartes à classer</p>
          <div class="flex flex-wrap gap-3">
            @foreach($cartes as $carte)
              @php($placement = $tentative?->placements?->get($carte->id))
              <div class="card-sort-card w-40 rounded-[10px] border-2 border-gray-200 bg-white p-3 text-center shadow-sm {{ $placement && $placement->is_correct ? 'is-correct' : ($placement ? 'is-incorrect' : '') }}"
                   draggable="true" data-card-id="{{ $carte->id }}">
                @if($carte->image_path)
                  <img src="{{ \Illuminate\Support\Facades\Storage::url($carte->image_path) }}" alt="" class="mx-auto max-h-16 w-auto rounded-[6px] object-contain">
                @endif
                @if($carte->text)
                  <p class="mt-1 text-xs text-gray-800">{{ $carte->text }}</p>
                @endif
              </div>
            @endforeach
          </div>
        </div>

        @foreach($categories as $categorie)
          <div class="card-sort-dropzone min-h-[140px] rounded-[16px] border-2 border-dashed border-orangeone/30 bg-orange-50/40 p-3" data-category-id="{{ $categorie->id }}">
            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-orangeone">{{ $categorie->label }}</p>
            <div class="flex flex-wrap gap-2"></div>
          </div>
        @endforeach
      </div>

      <button type="button" id="tri-cartes-valider" class="btn-oneduc mt-6 w-full justify-center">Valider mon classement</button>

      <script>
        (function () {
          const pool = document.getElementById('tri-cartes-pool');
          const zones = document.querySelectorAll('.card-sort-dropzone');
          const scoreBox = document.getElementById('tri-cartes-score');
          const submitUrl = @json(route('tri-cartes.submit', $session->access_code));
          const csrfToken = @json(csrf_token());

          // Pré-remplir les catégories si une tentative précédente existe.
          @if($tentative)
            @foreach($tentative->placements as $placement)
              (function () {
                const carte = document.querySelector('[data-card-id="{{ $placement->card_sort_card_id }}"]');
                const zone = document.querySelector('.card-sort-dropzone[data-category-id="{{ $placement->category_id }}"] > div');
                if (carte && zone) zone.appendChild(carte);
              })();
            @endforeach
          @endif

          let dragged = null;

          document.querySelectorAll('.card-sort-card').forEach((carte) => {
            carte.addEventListener('dragstart', () => { dragged = carte; });
          });

          zones.forEach((zone) => {
            zone.addEventListener('dragover', (event) => {
              event.preventDefault();
              zone.classList.add('drag-over');
            });
            zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
            zone.addEventListener('drop', (event) => {
              event.preventDefault();
              zone.classList.remove('drag-over');
              if (!dragged) return;
              zone.querySelector('div').appendChild(dragged);
              dragged.classList.remove('is-correct', 'is-incorrect');
            });
          });

          document.getElementById('tri-cartes-valider').addEventListener('click', async () => {
            const placements = {};
            document.querySelectorAll('.card-sort-dropzone[data-category-id]:not(#tri-cartes-pool)').forEach((zone) => {
              const categoryId = zone.dataset.categoryId;
              zone.querySelectorAll('.card-sort-card').forEach((carte) => {
                placements[carte.dataset.cardId] = parseInt(categoryId, 10);
              });
            });

            try {
              const reponse = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  Accept: 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ placements }),
              });

              if (!reponse.ok) {
                scoreBox.textContent = "Impossible d'enregistrer votre classement pour le moment.";
                scoreBox.className = 'mt-4 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700';
                return;
              }

              const resultat = await reponse.json();
              scoreBox.textContent = `Score : ${resultat.score} / ${resultat.total}`;
              scoreBox.className = 'mt-4 rounded-[10px] border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700';

              resultat.details.forEach((detail) => {
                const carte = document.querySelector(`[data-card-id="${detail.card_id}"]`);
                if (!carte) return;
                carte.classList.remove('is-correct', 'is-incorrect');
                carte.classList.add(detail.is_correct ? 'is-correct' : 'is-incorrect');
              });
            } catch (erreur) {
              scoreBox.textContent = "Impossible d'enregistrer votre classement pour le moment.";
              scoreBox.className = 'mt-4 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700';
            }
          });
        })();
      </script>
    @endif
  </main>
</body>
</html>
