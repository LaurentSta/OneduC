@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-5xl px-6 lg:px-8">
  <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 border border-gray-100 space-y-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h1 class="text-[22px] font-varela text-bleuone">{{ $wordCloud->title }}</h1>
        <p class="text-sm text-gray-600 mt-1">{{ $wordCloud->question }}</p>
      </div>
      <span class="font-mono text-lg bg-gray-100 px-3 py-1 rounded">{{ $wordCloud->access_code }}</span>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
      <div class="rounded-xl border border-gray-200 p-4">
        <p class="text-sm text-gray-500 mb-1">Lien stagiaire</p>
        <a href="{{ $joinUrl }}" class="text-bleuone break-all hover:underline">{{ $joinUrl }}</a>
        <p class="text-sm text-gray-500 mt-3">Réponses reçues: <strong>{{ $wordCloud->entries_count }}</strong></p>
        <div class="mt-4 flex gap-2">
          <a href="{{ route('admin.nuage.live', $wordCloud) }}" class="px-3 py-2 rounded-lg bg-[#004461] text-white text-sm">Afficher le live</a>
          <a href="{{ route('admin.nuage.edit', $wordCloud) }}" class="px-3 py-2 rounded-lg border border-gray-300 text-sm">Configurer</a>
        </div>
      </div>

      <div class="rounded-xl border border-gray-200 p-4 flex items-center justify-center">
        <div id="qrcode" class="min-h-[240px] min-w-[240px]"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  new QRCode(document.getElementById('qrcode'), {
    text: @json($joinUrl),
    width: 240,
    height: 240,
    colorDark: '#004461',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
  });
</script>

@endsection
