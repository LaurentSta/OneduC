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
  $activeLiveQuizSession = null;
  $availableWhiteboardGroup = null;

  if ($authUser && ($authUser->role ?? null) === 'stagiaire') {
      $lectureIds = $authUser->groupesStagiaire()
          ->with(['modules.sections.lectures:id,section_id,module_id'])
          ->get()
          ->flatMap->modules
          ->unique('id')
          ->flatMap(fn ($module) => $module->sections ?? collect())
          ->flatMap(fn ($section) => $section->lectures ?? collect())
          ->pluck('id')
          ->filter()
          ->unique()
          ->values();

      if ($lectureIds->isNotEmpty()) {
          $activeLiveQuizSession = \App\Models\LiveQuizSession::query()
              ->whereIn('lecture_id', $lectureIds->all())
              ->whereNull('ended_at')
              ->whereIn('status', [
                  \App\Models\LiveQuizSession::STATUS_QUESTION_OPEN,
                  \App\Models\LiveQuizSession::STATUS_ANSWER_REVEALED,
              ])
              ->latest('id')
              ->first();
      }

      $availableWhiteboardGroup = $authUser->groupesStagiaire()
          ->whereHas('whiteboard')
          ->with('whiteboard')
          ->get()
          ->sortByDesc(fn ($group) => optional($group->whiteboard?->updated_at)->timestamp ?? 0)
          ->first();
  }

  $activeWhiteboardUrl = $availableWhiteboardGroup
      ? route('stagiaire.whiteboard.show', ['group' => $availableWhiteboardGroup->id])
      : null;
  $whiteboardNotificationStatusUrl = $authUser
      && ($authUser->role ?? null) === 'stagiaire'
      && \Illuminate\Support\Facades\Route::has('stagiaire.whiteboard.notification-status')
      ? route('stagiaire.whiteboard.notification-status')
      : null;
  $bellIndicatorCount = $unreadCount + ($activeLiveQuizSession ? 1 : 0) + ($availableWhiteboardGroup ? 1 : 0);
  $liveQuizUrl = $activeLiveQuizSession
      ? route('stagiaire.live-quiz.join-code', ['code' => $activeLiveQuizSession->access_code])
      : null;
  $liveQuizNotificationStatusUrl = $authUser
      && ($authUser->role ?? null) === 'stagiaire'
      && \Illuminate\Support\Facades\Route::has('stagiaire.live-quiz.notification-status')
      ? route('stagiaire.live-quiz.notification-status')
      : null;
@endphp

<style>
  @keyframes oneduc-live-pulse {
    0%, 100% {
      transform: scale(1);
      opacity: 1;
    }
    50% {
      transform: scale(1.08);
      opacity: 0.78;
    }
  }

  .oneduc-bell-live {
    color: #f97316;
  }

  .oneduc-live-pulse {
    animation: oneduc-live-pulse 1.8s ease-in-out infinite;
    transform-origin: center;
  }

  @media (prefers-reduced-motion: reduce) {
    .oneduc-live-pulse {
      animation: none;
    }
  }
</style>

<div
  x-data="{ open: false }"
  class="relative"
  data-live-quiz-bell
  data-base-count="{{ $unreadCount }}"
  data-status-url="{{ $liveQuizNotificationStatusUrl }}"
  data-whiteboard-status-url="{{ $whiteboardNotificationStatusUrl }}"
  @click.outside="open = false"
  @keydown.escape.window="open = false"
>
  <button type="button"
          @click="open = !open"
          class="text-[#004461] hover:text-[#004461] relative pt-1"
          aria-label="Notifications">
    <svg
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke-width="1.5"
      stroke="currentColor"
      data-bell-icon
      class="w-[34px] h-[34px] {{ $activeLiveQuizSession ? 'oneduc-bell-live text-orangeone' : '' }}"
    >
      <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.099A3.001 3.001 0 0112 18a3.001 3.001 0 01-2.857-0.901M6 8c0-3.314 2.239-6 5-6s5 2.686 5 6c0 5.25 2 6 2 6H4s2-0.75 2-6z" />
    </svg>
    <span
      data-bell-badge
      class="absolute top-[10px] right-0 translate-x-1/2 {{ $bellIndicatorCount > 0 ? 'flex' : 'hidden' }} {{ $activeLiveQuizSession ? 'bg-orangeone oneduc-live-pulse' : ($availableWhiteboardGroup ? 'bg-teal-600' : 'bg-red-600') }} text-white text-[10px] min-w-[18px] h-[18px] px-1 rounded-full items-center justify-center"
    >
      {{ $bellIndicatorCount > 9 ? '9+' : $bellIndicatorCount }}
    </span>
  </button>

  <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-[340px] max-h-[420px] overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg z-50" style="display:none;">
    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
      <p class="text-sm font-semibold text-gray-800">Notifications</p>
      <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button class="text-xs text-blue-600 hover:underline">Tout marquer lu</button>
      </form>
    </div>

    <div
      data-live-quiz-item
      class="{{ $activeLiveQuizSession && $liveQuizUrl ? '' : 'hidden' }} px-4 py-3 border-b border-orange-100 bg-orange-50"
    >
      @if($activeLiveQuizSession && $liveQuizUrl)
        <a href="{{ $liveQuizUrl }}" class="block">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p data-live-quiz-label class="text-xs font-semibold text-orange-700">Session en cours</p>
              <p data-live-quiz-title class="text-xs text-gray-700 mt-1">
                {{ $activeLiveQuizSession->lecture?->lecture_title ?? 'Une session est disponible' }}
              </p>
              <p data-live-quiz-meta class="mt-1 text-[11px] text-gray-500">
                Code {{ $activeLiveQuizSession->access_code }} · Appuyer pour rejoindre
              </p>
            </div>
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-orangeone oneduc-live-pulse"></span>
          </div>
        </a>
      @else
        <a href="#" class="block">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p data-live-quiz-label class="text-xs font-semibold text-orange-700">Session en cours</p>
              <p data-live-quiz-title class="text-xs text-gray-700 mt-1">Une session est disponible</p>
              <p data-live-quiz-meta class="mt-1 text-[11px] text-gray-500">Appuyer pour rejoindre</p>
            </div>
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-orangeone oneduc-live-pulse"></span>
          </div>
        </a>
      @endif
    </div>

    <div
      data-whiteboard-item
      class="{{ $availableWhiteboardGroup && $activeWhiteboardUrl ? '' : 'hidden' }} px-4 py-3 border-b border-teal-100 bg-teal-50"
    >
      @if($availableWhiteboardGroup && $activeWhiteboardUrl)
        <a href="{{ $activeWhiteboardUrl }}" class="block">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold text-teal-700">Tableau blanc disponible</p>
              <p data-whiteboard-title class="text-xs text-gray-700 mt-1">
                {{ $availableWhiteboardGroup->name }}
              </p>
              <p data-whiteboard-meta class="mt-1 text-[11px] text-gray-500">
                Appuyer pour ouvrir le tableau collaboratif
              </p>
            </div>
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-teal-500"></span>
          </div>
        </a>
      @else
        <a href="#" class="block">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold text-teal-700">Tableau blanc disponible</p>
              <p data-whiteboard-title class="text-xs text-gray-700 mt-1">Un tableau collaboratif est disponible</p>
              <p data-whiteboard-meta class="mt-1 text-[11px] text-gray-500">Appuyer pour ouvrir</p>
            </div>
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-teal-500"></span>
          </div>
        </a>
      @endif
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

@if($liveQuizNotificationStatusUrl || $whiteboardNotificationStatusUrl)
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const root = document.querySelector('[data-live-quiz-bell]');
      if (!root) {
        return;
      }

      const statusUrl = root.dataset.statusUrl;
      const whiteboardStatusUrl = root.dataset.whiteboardStatusUrl;

      const bellIcon = root.querySelector('[data-bell-icon]');
      const badge = root.querySelector('[data-bell-badge]');
      const liveItem = root.querySelector('[data-live-quiz-item]');
      const liveLink = liveItem ? liveItem.querySelector('a') : null;
      const liveTitle = root.querySelector('[data-live-quiz-title]');
      const liveMeta = root.querySelector('[data-live-quiz-meta]');
      const whiteboardItem = root.querySelector('[data-whiteboard-item]');
      const whiteboardLink = whiteboardItem ? whiteboardItem.querySelector('a') : null;
      const whiteboardTitle = root.querySelector('[data-whiteboard-title]');
      const whiteboardMeta = root.querySelector('[data-whiteboard-meta]');
      const baseCount = Number.parseInt(root.dataset.baseCount || '0', 10) || 0;
      let liveQuizCount = {{ $activeLiveQuizSession ? 1 : 0 }};
      let whiteboardCount = {{ $availableWhiteboardGroup ? 1 : 0 }};

      const renderBadge = function () {
        const totalCount = baseCount + liveQuizCount + whiteboardCount;
        const hasLiveQuiz = liveQuizCount > 0;
        const hasWhiteboard = whiteboardCount > 0;

        if (bellIcon) {
          bellIcon.classList.toggle('oneduc-bell-live', hasLiveQuiz);
          bellIcon.classList.toggle('text-orangeone', hasLiveQuiz);
        }

        if (badge) {
          badge.classList.toggle('hidden', totalCount === 0);
          badge.classList.toggle('flex', totalCount > 0);
          badge.classList.toggle('bg-orangeone', hasLiveQuiz);
          badge.classList.toggle('bg-teal-600', !hasLiveQuiz && hasWhiteboard);
          badge.classList.toggle('bg-red-600', !hasLiveQuiz && !hasWhiteboard);
          badge.classList.toggle('oneduc-live-pulse', hasLiveQuiz);
          badge.textContent = totalCount > 9 ? '9+' : String(totalCount);
        }
      };

      const updateLiveQuiz = function (data) {
        const hasActiveSession = Boolean(data && data.has_active_session);
        liveQuizCount = hasActiveSession ? 1 : 0;
        renderBadge();

        if (!liveItem || !liveLink) {
          return;
        }

        liveItem.classList.toggle('hidden', !hasActiveSession);

        if (!hasActiveSession) {
          liveLink.setAttribute('href', '#');
          return;
        }

        liveLink.setAttribute('href', data.join_url || '#');

        if (liveTitle) {
          liveTitle.textContent = data.lecture_title || 'Une session est disponible';
        }

        if (liveMeta) {
          liveMeta.textContent = data.access_code
            ? `Code ${data.access_code} · Rejoindre maintenant`
            : 'Rejoindre maintenant';
        }
      };

      const updateWhiteboard = function (data) {
        const hasWhiteboard = Boolean(data && data.has_available_whiteboard);
        whiteboardCount = hasWhiteboard ? 1 : 0;
        renderBadge();

        if (!whiteboardItem || !whiteboardLink) {
          return;
        }

        whiteboardItem.classList.toggle('hidden', !hasWhiteboard);

        if (!hasWhiteboard) {
          whiteboardLink.setAttribute('href', '#');
          return;
        }

        whiteboardLink.setAttribute('href', data.join_url || '#');

        if (whiteboardTitle) {
          whiteboardTitle.textContent = data.group_name || 'Un tableau collaboratif est disponible';
        }

        if (whiteboardMeta) {
          whiteboardMeta.textContent = data.updated_at_human
            ? `Mis a jour ${data.updated_at_human} · Ouvrir le tableau`
            : 'Ouvrir le tableau collaboratif';
        }
      };

      const pollLiveQuizStatus = function () {
        if (!statusUrl) {
          return;
        }

        fetch(statusUrl, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Live quiz status request failed');
            }

            return response.json();
          })
          .then(updateLiveQuiz)
          .catch(function () {});
      };

      const pollWhiteboardStatus = function () {
        if (!whiteboardStatusUrl) {
          return;
        }

        fetch(whiteboardStatusUrl, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Whiteboard status request failed');
            }

            return response.json();
          })
          .then(updateWhiteboard)
          .catch(function () {});
      };

      renderBadge();
      window.setInterval(pollLiveQuizStatus, 5000);
      window.setInterval(pollWhiteboardStatus, 5000);
    });
  </script>
@endif
