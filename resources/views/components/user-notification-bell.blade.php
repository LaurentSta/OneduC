@php
  $authUser = auth()->user();
  $hasNotificationsTable = \Illuminate\Support\Facades\Schema::hasTable('notifications');
  $latestNotifications = $hasNotificationsTable && $authUser
      ? $authUser->notifications()->latest()->limit(8)->get()
      : collect();
  $unreadCount = $hasNotificationsTable && $authUser
      ? $authUser->unreadNotifications()->count()
      : 0;
  $latestUnread = $latestNotifications->firstWhere('read_at', null);
@endphp

<div x-data="{ open: false }" class="relative" @click.outside="open = false" @keydown.escape.window="open = false">
  <button type="button"
          @click="open = !open"
          class="text-gray-600 hover:text-orangeone relative"
          aria-label="Notifications">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-[34px] h-[34px]">
      <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.099A3.001 3.001 0 0112 18a3.001 3.001 0 01-2.857-0.901M6 8c0-3.314 2.239-6 5-6s5 2.686 5 6c0 5.25 2 6 2 6H4s2-0.75 2-6z" />
    </svg>
    @if($unreadCount > 0)
      <span class="absolute top-[10px] right-0 translate-x-1/2 bg-red-600 text-white text-[10px] min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
    @endif
  </button>

  <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-[340px] max-h-[420px] overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg z-50" style="display:none;">
    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
      <p class="text-sm font-semibold text-gray-800">Notifications</p>
      <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button class="text-xs text-blue-600 hover:underline">Tout marquer lu</button>
      </form>
    </div>

    @forelse($latestNotifications as $notification)
      @php
        $title = data_get($notification->data, 'title', 'Notification');
        $message = data_get($notification->data, 'message', '');
        $url = data_get($notification->data, 'url');
      @endphp
      <div class="px-4 py-3 border-b border-gray-100 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/40' }}">
        @if($url)
          <a href="{{ $url }}" class="block">
            <p class="text-xs font-semibold text-gray-800">{{ $title }}</p>
            <p class="text-xs text-gray-600 mt-1">{{ $message }}</p>
            <p class="mt-1 text-[11px] text-gray-400">{{ $notification->created_at?->diffForHumans() }}</p>
          </a>
        @else
          <p class="text-xs font-semibold text-gray-800">{{ $title }}</p>
          <p class="text-xs text-gray-600 mt-1">{{ $message }}</p>
          <p class="mt-1 text-[11px] text-gray-400">{{ $notification->created_at?->diffForHumans() }}</p>
        @endif

        @if(is_null($notification->read_at))
          <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="mt-2">
            @csrf
            <button class="text-[11px] text-blue-700 hover:underline">Marquer comme lu</button>
          </form>
        @endif
      </div>
    @empty
      <p class="px-4 py-6 text-sm text-gray-500">Aucune notification.</p>
    @endforelse
  </div>
</div>

@if($latestUnread)
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const latestId = @json($latestUnread->id);
      const title = @json(data_get($latestUnread->data, 'title', 'Notification'));
      const message = @json(data_get($latestUnread->data, 'message', ''));
      const storageKey = 'last_seen_notification_id';

      if (!latestId || localStorage.getItem(storageKey) === latestId) {
        return;
      }

      localStorage.setItem(storageKey, latestId);

      const toast = document.createElement('div');
      toast.className = 'fixed top-5 right-5 z-[120] max-w-sm bg-white border border-green-200 shadow-lg rounded-xl px-4 py-3';
      toast.innerHTML = '<p class="text-xs font-semibold text-green-700">' + title + '</p>'
        + '<p class="text-xs text-gray-700 mt-1">' + message + '</p>';

      document.body.appendChild(toast);
      setTimeout(() => { toast.remove(); }, 4500);
    });
  </script>
@endif
