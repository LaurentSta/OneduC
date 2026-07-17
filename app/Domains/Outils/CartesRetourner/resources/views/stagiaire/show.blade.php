<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oneduc - {{ $session->title }}</title>
  @vite(['resources/css/app.css'])
  <style>
    .flip-card { perspective: 1000px; height: 220px; }
    .flip-card-inner { position: relative; width: 100%; height: 100%; transition: transform 0.5s; transform-style: preserve-3d; }
    .flip-card.is-flipped .flip-card-inner { transform: rotateY(180deg); }
    .flip-card-face { position: absolute; inset: 0; backface-visibility: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem; padding: 1rem; text-align: center; border-radius: 12px; }
    .flip-card-back { transform: rotateY(180deg); }
  </style>
</head>
<body class="min-h-screen bg-slate-100 p-4 font-lisible">
  <main class="mx-auto w-full max-w-4xl rounded-[20px] bg-white p-6 shadow-md">
    <h1 class="font-raleway text-xl text-bleuone">{{ $session->title }}</h1>
    <p class="mt-1 text-sm text-gray-500">Cliquez sur une carte pour la retourner.</p>

    @if($cartes->isEmpty())
      <p class="mt-8 rounded-[8px] border-2 border-dashed border-gray-200 py-16 text-center text-sm text-gray-500">Cette activité ne contient pas encore de carte.</p>
    @else
      <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($cartes as $carte)
          <button type="button" class="flip-card cursor-pointer text-left" onclick="this.classList.toggle('is-flipped')">
            <div class="flip-card-inner">
              <div class="flip-card-face flip-card-front border border-purple-200 bg-purple-50">
                @if($carte->recto_image_path)
                  <img src="{{ \Illuminate\Support\Facades\Storage::url($carte->recto_image_path) }}" alt="" class="max-h-24 w-auto rounded-[8px] object-contain">
                @endif
                @if($carte->recto_text)
                  <p class="text-sm font-semibold text-purple-900">{{ $carte->recto_text }}</p>
                @endif
              </div>
              <div class="flip-card-face flip-card-back border border-orangeone/30 bg-orange-50">
                @if($carte->verso_image_path)
                  <img src="{{ \Illuminate\Support\Facades\Storage::url($carte->verso_image_path) }}" alt="" class="max-h-24 w-auto rounded-[8px] object-contain">
                @endif
                @if($carte->verso_text)
                  <p class="text-sm text-gray-800">{{ $carte->verso_text }}</p>
                @endif
              </div>
            </div>
          </button>
        @endforeach
      </div>
    @endif
  </main>
</body>
</html>
