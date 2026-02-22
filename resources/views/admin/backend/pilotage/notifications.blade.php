@extends('admin.admin_dashboard')

@section('admin')
<div class="mx-auto w-full max-w-6xl space-y-6">
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <a href="{{ route('admin.pilotage.index') }}" class="text-sm text-blue-700 hover:underline">Retour au pilotage</a>
      <h1 class="mt-1 text-3xl font-semibold text-bleuone">Centre notifications</h1>
      <p class="mt-1 text-sm text-gray-600">Suivi des alertes pilotage, en interface et par mail.</p>
    </div>
  </div>

  @if (session('success'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-lg font-semibold text-gray-900">Preferences</h2>
      <form method="POST" action="{{ route('admin.pilotage.notifications.read-all') }}">
        @csrf
        <button type="submit" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Marquer tout comme lu</button>
      </form>
    </div>

    <form method="POST" action="{{ route('admin.pilotage.preferences.update') }}" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <label class="flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" name="in_app_enabled" value="1" @checked(old('in_app_enabled', $preference->in_app_enabled))>
          Notifications dans l'interface
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" name="email_enabled" value="1" @checked(old('email_enabled', $preference->email_enabled))>
          Notifications par mail
        </label>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Frequence</label>
          <select name="frequency" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            @foreach($frequencies as $key => $label)
              <option value="{{ $key }}" @selected(old('frequency', $preference->frequency) === $key)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div>
        <p class="mb-2 text-sm font-medium text-gray-700">Types d'evenements</p>
        <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
          @php $selectedEvents = old('event_types', $preference->event_types ?: []); @endphp
          @foreach($eventTypes as $key => $label)
            <label class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700">
              <input type="checkbox" name="event_types[]" value="{{ $key }}" @checked(in_array($key, $selectedEvents, true))>
              {{ $label }}
            </label>
          @endforeach
        </div>
      </div>

      <button type="submit" class="rounded-md bg-[#004461] px-4 py-2 text-sm font-medium text-white hover:bg-[#00364d]">Enregistrer preferences</button>
    </form>
  </section>

  <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <h2 class="mb-4 text-lg font-semibold text-gray-900">Historique des notifications</h2>
    <div class="space-y-3">
      @forelse($notifications as $notification)
        @php
          $title = data_get($notification->data, 'title', 'Notification');
          $message = data_get($notification->data, 'message', '');
          $url = data_get($notification->data, 'url', route('admin.pilotage.index'));
        @endphp
        <article class="rounded-lg border {{ $notification->read_at ? 'border-gray-200 bg-white' : 'border-blue-200 bg-blue-50/40' }} p-4">
          <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ $title }}</p>
              <p class="mt-1 text-sm text-gray-700">{{ $message }}</p>
              <p class="mt-1 text-xs text-gray-500">{{ $notification->created_at?->format('d/m/Y H:i') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <a href="{{ $url }}" class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50">Ouvrir</a>
              @if(is_null($notification->read_at))
                <form method="POST" action="{{ route('admin.pilotage.notifications.read', $notification->id) }}">
                  @csrf
                  <button type="submit" class="rounded-md bg-[#004461] px-3 py-1.5 text-xs text-white hover:bg-[#00364d]">Marquer lu</button>
                </form>
              @endif
            </div>
          </div>
        </article>
      @empty
        <p class="text-sm text-gray-500">Aucune notification.</p>
      @endforelse
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
  </section>
</div>
@endsection

