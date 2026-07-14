<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oneduc - {{ $session->title }}</title>
  @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 p-4 font-lisible">
  <main class="w-full max-w-2xl rounded-[20px] bg-white p-6 shadow-md">
    <h1 class="font-raleway text-xl text-bleuone">{{ $session->title }}</h1>

    @if($slides->isEmpty())
      <p class="mt-8 rounded-[8px] border-2 border-dashed border-gray-200 py-16 text-center text-sm text-gray-500">Ce carrousel ne contient pas encore de slide.</p>
    @else
      <div id="carrousel-viewer" class="mt-6" data-index="0">
        <div class="relative overflow-hidden rounded-[16px] border border-gray-100 bg-slate-50">
          @foreach($slides as $i => $slide)
            <div class="carrousel-slide {{ $i === 0 ? '' : 'hidden' }} flex flex-col items-center gap-4 p-6 text-center" data-slide-index="{{ $i }}">
              @if($slide->image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($slide->image_path) }}" alt="" class="max-h-80 w-auto rounded-[12px] object-contain">
              @endif
              @if($slide->text)
                <p class="whitespace-pre-line text-base text-gray-800">{{ $slide->text }}</p>
              @endif
            </div>
          @endforeach
        </div>

        <div class="mt-4 flex items-center justify-between">
          <button type="button" id="carrousel-prev" class="btn-oneduc-outline !px-4 !py-2">← Précédent</button>
          <div id="carrousel-dots" class="flex items-center gap-2">
            @foreach($slides as $i => $slide)
              <span class="h-2 w-2 rounded-full {{ $i === 0 ? 'bg-orangeone' : 'bg-gray-300' }}" data-dot-index="{{ $i }}"></span>
            @endforeach
          </div>
          <button type="button" id="carrousel-next" class="btn-oneduc !px-4 !py-2">Suivant →</button>
        </div>
      </div>

      <script>
        (function () {
          const viewer = document.getElementById('carrousel-viewer');
          const slides = viewer.querySelectorAll('.carrousel-slide');
          const dots = viewer.querySelectorAll('[data-dot-index]');
          const total = slides.length;

          function afficher(index) {
            index = Math.max(0, Math.min(total - 1, index));
            slides.forEach((slide, i) => slide.classList.toggle('hidden', i !== index));
            dots.forEach((dot, i) => dot.classList.toggle('bg-orangeone', i === index));
            dots.forEach((dot, i) => dot.classList.toggle('bg-gray-300', i !== index));
            viewer.dataset.index = String(index);
          }

          document.getElementById('carrousel-prev').addEventListener('click', () => afficher(parseInt(viewer.dataset.index, 10) - 1));
          document.getElementById('carrousel-next').addEventListener('click', () => afficher(parseInt(viewer.dataset.index, 10) + 1));
          dots.forEach((dot) => dot.addEventListener('click', () => afficher(parseInt(dot.dataset.dotIndex, 10))));

          document.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') afficher(parseInt(viewer.dataset.index, 10) - 1);
            if (event.key === 'ArrowRight') afficher(parseInt(viewer.dataset.index, 10) + 1);
          });
        })();
      </script>
    @endif
  </main>
</body>
</html>
