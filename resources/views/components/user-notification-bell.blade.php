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
  $activeWordCloud = null;
  $availableWhiteboardGroup = null;
  $activeQuestionWall = null;
  $openSeanceForStagiaire = null;

  if ($authUser && ($authUser->role ?? null) === 'stagiaire') {
      $lectureIds = $authUser->groupesStagiaire()
          ->active()
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
          ->active()
          ->whereHas('whiteboard')
          ->with('whiteboard')
          ->get()
          ->sortByDesc(fn ($group) => optional($group->whiteboard?->updated_at)->timestamp ?? 0)
          ->first();

      $activeQuestionWall = \App\Models\QuestionWall::query()
          ->where('is_active', true)
          ->whereHas('group.students', fn ($query) => $query->where('users.id', (int) $authUser->id))
          ->with('group')
          ->latest('updated_at')
          ->first();

      $activeWordCloud = \App\Models\WordCloud::query()
          ->where('is_active', true)
          ->whereHas('group', function ($query) use ($authUser): void {
              $query
                  ->active()
                  ->whereHas('students', fn ($studentQuery) => $studentQuery->where('users.id', (int) $authUser->id));
          })
          ->with('group')
          ->latest('opened_at')
          ->latest('id')
          ->first();

      $openSeanceForStagiaire = \App\Models\Seance::query()
          ->where('statut', 'ouverte')
          ->whereIn('group_id', $authUser->groupesStagiaire()->active()->pluck('groups.id'))
          ->whereHas('presences', fn ($query) => $query->where('user_id', (int) $authUser->id)->where('statut', 'en_attente'))
          ->with('group')
          ->latest('opened_at')
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
  $questionWallNotificationStatusUrl = $authUser
      && ($authUser->role ?? null) === 'stagiaire'
      && \Illuminate\Support\Facades\Route::has('stagiaire.question-wall.notification-status')
      ? route('stagiaire.question-wall.notification-status')
      : null;
  $activeQuestionWallUrl = $activeQuestionWall
      ? route('questions.join.code', ['code' => $activeQuestionWall->access_code])
      : null;
  $activeWordCloudUrl = $activeWordCloud
      ? route('wordcloud.join.code', ['code' => $activeWordCloud->access_code])
      : null;
  $wordCloudNotificationStatusUrl = $authUser
      && ($authUser->role ?? null) === 'stagiaire'
      && \Illuminate\Support\Facades\Route::has('stagiaire.wordcloud.notification-status')
      ? route('stagiaire.wordcloud.notification-status')
      : null;
  $activeEmargementUrl = $openSeanceForStagiaire
      ? route('stagiaire.emargement.show', ['group' => $openSeanceForStagiaire->group_id])
      : null;
  $emargementNotificationStatusUrl = $authUser
      && ($authUser->role ?? null) === 'stagiaire'
      && \Illuminate\Support\Facades\Route::has('stagiaire.emargement.notification-status')
      ? route('stagiaire.emargement.notification-status')
      : null;
  $activeToolIndicatorCount = ($activeLiveQuizSession ? 1 : 0) + ($activeWordCloud ? 1 : 0) + ($availableWhiteboardGroup ? 1 : 0) + ($activeQuestionWall ? 1 : 0) + ($openSeanceForStagiaire ? 1 : 0);
  $bellIndicatorCount = $unreadCount > 0 ? $unreadCount : $activeToolIndicatorCount;
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
  data-wordcloud-status-url="{{ $wordCloudNotificationStatusUrl }}"
  data-whiteboard-status-url="{{ $whiteboardNotificationStatusUrl }}"
  data-question-wall-status-url="{{ $questionWallNotificationStatusUrl }}"
  data-emargement-status-url="{{ $emargementNotificationStatusUrl }}"
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
      class="absolute top-[10px] right-0 translate-x-1/2 {{ $bellIndicatorCount > 0 ? 'flex' : 'hidden' }} {{ $unreadCount > 0 ? 'bg-orangeone' : ($activeLiveQuizSession ? 'bg-orangeone oneduc-live-pulse' : ($activeWordCloud ? 'bg-amber-600' : ($availableWhiteboardGroup ? 'bg-teal-600' : ($activeQuestionWall ? 'bg-indigo-600' : 'bg-red-600')))) }} text-white text-[10px] min-w-[18px] h-[18px] px-1 rounded-full items-center justify-center"
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
      data-wordcloud-item
      class="{{ $activeWordCloud && $activeWordCloudUrl ? '' : 'hidden' }} px-4 py-3 border-b border-amber-100 bg-amber-50"
    >
      @if($activeWordCloud && $activeWordCloudUrl)
        <a href="{{ $activeWordCloudUrl }}" class="block">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold text-amber-700">Nuage de mots en cours</p>
              <p data-wordcloud-title class="text-xs text-gray-700 mt-1">
                {{ $activeWordCloud->title }}
              </p>
              <p data-wordcloud-meta class="mt-1 text-[11px] text-gray-500">
                Code {{ $activeWordCloud->access_code }} · Ajouter mes mots
              </p>
            </div>
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
          </div>
        </a>
      @else
        <a href="#" class="block">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold text-amber-700">Nuage de mots en cours</p>
              <p data-wordcloud-title class="text-xs text-gray-700 mt-1">Un nuage de mots est disponible</p>
              <p data-wordcloud-meta class="mt-1 text-[11px] text-gray-500">Ajouter mes mots</p>
            </div>
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
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

    <div
      data-question-wall-item
      class="{{ $activeQuestionWall && $activeQuestionWallUrl ? '' : 'hidden' }} px-4 py-3 border-b border-indigo-100 bg-indigo-50"
    >
      @if($activeQuestionWall && $activeQuestionWallUrl)
        <a href="{{ $activeQuestionWallUrl }}" class="block">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold text-indigo-700">Mur de questions en cours</p>
              <p data-question-wall-title class="text-xs text-gray-700 mt-1">
                {{ $activeQuestionWall->title }}
              </p>
              <p data-question-wall-meta class="mt-1 text-[11px] text-gray-500">
                Code {{ $activeQuestionWall->access_code }} · Poser une question ou voter
              </p>
            </div>
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
          </div>
        </a>
      @else
        <a href="#" class="block">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold text-indigo-700">Mur de questions en cours</p>
              <p data-question-wall-title class="text-xs text-gray-700 mt-1">Un mur de questions est disponible</p>
              <p data-question-wall-meta class="mt-1 text-[11px] text-gray-500">Poser une question ou voter</p>
            </div>
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
          </div>
        </a>
      @endif
    </div>

    <div
      data-emargement-item
      class="{{ $openSeanceForStagiaire && $activeEmargementUrl ? '' : 'hidden' }} px-4 py-3 border-b border-slate-200 bg-slate-50"
    >
      @if($openSeanceForStagiaire && $activeEmargementUrl)
        <a href="{{ $activeEmargementUrl }}" class="block">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold text-slate-700">Émargement à signer</p>
              <p data-emargement-title class="text-xs text-gray-700 mt-1">
                {{ $openSeanceForStagiaire->group?->name }}
              </p>
              <p data-emargement-meta class="mt-1 text-[11px] text-gray-500">
                Ouvert {{ $openSeanceForStagiaire->opened_at?->diffForHumans() }} · Appuyer pour signer
              </p>
            </div>
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-slate-500"></span>
          </div>
        </a>
      @else
        <a href="#" class="block">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold text-slate-700">Émargement à signer</p>
              <p data-emargement-title class="text-xs text-gray-700 mt-1">Une feuille de présence est ouverte</p>
              <p data-emargement-meta class="mt-1 text-[11px] text-gray-500">Appuyer pour signer</p>
            </div>
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-slate-500"></span>
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

@if($liveQuizNotificationStatusUrl || $wordCloudNotificationStatusUrl || $whiteboardNotificationStatusUrl || $questionWallNotificationStatusUrl || $emargementNotificationStatusUrl)
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const root = document.querySelector('[data-live-quiz-bell]');
      if (!root) {
        return;
      }

      const statusUrl = root.dataset.statusUrl;
      const wordCloudStatusUrl = root.dataset.wordcloudStatusUrl;
      const whiteboardStatusUrl = root.dataset.whiteboardStatusUrl;
      const questionWallStatusUrl = root.dataset.questionWallStatusUrl;
      const emargementStatusUrl = root.dataset.emargementStatusUrl;

      const bellIcon = root.querySelector('[data-bell-icon]');
      const badge = root.querySelector('[data-bell-badge]');
      const liveItem = root.querySelector('[data-live-quiz-item]');
      const liveLink = liveItem ? liveItem.querySelector('a') : null;
      const liveTitle = root.querySelector('[data-live-quiz-title]');
      const liveMeta = root.querySelector('[data-live-quiz-meta]');
      const wordCloudItem = root.querySelector('[data-wordcloud-item]');
      const wordCloudLink = wordCloudItem ? wordCloudItem.querySelector('a') : null;
      const wordCloudTitle = root.querySelector('[data-wordcloud-title]');
      const wordCloudMeta = root.querySelector('[data-wordcloud-meta]');
      const whiteboardItem = root.querySelector('[data-whiteboard-item]');
      const whiteboardLink = whiteboardItem ? whiteboardItem.querySelector('a') : null;
      const whiteboardTitle = root.querySelector('[data-whiteboard-title]');
      const whiteboardMeta = root.querySelector('[data-whiteboard-meta]');
      const questionWallItem = root.querySelector('[data-question-wall-item]');
      const questionWallLink = questionWallItem ? questionWallItem.querySelector('a') : null;
      const questionWallTitle = root.querySelector('[data-question-wall-title]');
      const questionWallMeta = root.querySelector('[data-question-wall-meta]');
      const emargementItem = root.querySelector('[data-emargement-item]');
      const emargementLink = emargementItem ? emargementItem.querySelector('a') : null;
      const emargementTitle = root.querySelector('[data-emargement-title]');
      const emargementMeta = root.querySelector('[data-emargement-meta]');
      const baseCount = Number.parseInt(root.dataset.baseCount || '0', 10) || 0;
      let liveQuizCount = {{ $activeLiveQuizSession ? 1 : 0 }};
      let wordCloudCount = {{ $activeWordCloud ? 1 : 0 }};
      let whiteboardCount = {{ $availableWhiteboardGroup ? 1 : 0 }};
      let questionWallCount = {{ $activeQuestionWall ? 1 : 0 }};
      let emargementCount = {{ $openSeanceForStagiaire ? 1 : 0 }};

      const renderBadge = function () {
        const hasUnreadNotifications = baseCount > 0;
        const activeToolCount = liveQuizCount + wordCloudCount + whiteboardCount + questionWallCount + emargementCount;
        const totalCount = hasUnreadNotifications ? baseCount : activeToolCount;
        const hasLiveQuiz = liveQuizCount > 0;
        const hasWordCloud = wordCloudCount > 0;
        const hasWhiteboard = whiteboardCount > 0;
        const hasQuestionWall = questionWallCount > 0;
        const hasEmargement = emargementCount > 0;

        if (bellIcon) {
          bellIcon.classList.toggle('oneduc-bell-live', hasLiveQuiz);
          bellIcon.classList.toggle('text-orangeone', hasLiveQuiz);
        }

        if (badge) {
          badge.classList.toggle('hidden', totalCount === 0);
          badge.classList.toggle('flex', totalCount > 0);
          badge.classList.toggle('bg-orangeone', hasUnreadNotifications || (!hasUnreadNotifications && hasLiveQuiz));
          badge.classList.toggle('bg-amber-600', !hasUnreadNotifications && !hasLiveQuiz && hasWordCloud);
          badge.classList.toggle('bg-teal-600', !hasUnreadNotifications && !hasLiveQuiz && !hasWordCloud && hasWhiteboard);
          badge.classList.toggle('bg-indigo-600', !hasUnreadNotifications && !hasLiveQuiz && !hasWordCloud && !hasWhiteboard && hasQuestionWall);
          badge.classList.toggle('bg-slate-600', !hasUnreadNotifications && !hasLiveQuiz && !hasWordCloud && !hasWhiteboard && !hasQuestionWall && hasEmargement);
          badge.classList.toggle('bg-red-600', !hasUnreadNotifications && !hasLiveQuiz && !hasWordCloud && !hasWhiteboard && !hasQuestionWall && !hasEmargement);
          badge.classList.toggle('oneduc-live-pulse', !hasUnreadNotifications && hasLiveQuiz);
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

      const updateWordCloud = function (data) {
        const hasActiveWordCloud = Boolean(data && data.has_active_wordcloud);
        wordCloudCount = hasActiveWordCloud ? 1 : 0;
        renderBadge();

        if (!wordCloudItem || !wordCloudLink) {
          return;
        }

        wordCloudItem.classList.toggle('hidden', !hasActiveWordCloud);

        if (!hasActiveWordCloud) {
          wordCloudLink.setAttribute('href', '#');
          return;
        }

        wordCloudLink.setAttribute('href', data.join_url || '#');

        if (wordCloudTitle) {
          wordCloudTitle.textContent = data.title || 'Un nuage de mots est disponible';
        }

        if (wordCloudMeta) {
          wordCloudMeta.textContent = data.access_code
            ? `Code ${data.access_code} · Ajouter mes mots`
            : 'Ajouter mes mots';
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

      const updateQuestionWall = function (data) {
        const hasQuestionWall = Boolean(data && data.has_active_question_wall);
        questionWallCount = hasQuestionWall ? 1 : 0;
        renderBadge();

        if (!questionWallItem || !questionWallLink) {
          return;
        }

        questionWallItem.classList.toggle('hidden', !hasQuestionWall);

        if (!hasQuestionWall) {
          questionWallLink.setAttribute('href', '#');
          return;
        }

        questionWallLink.setAttribute('href', data.join_url || '#');

        if (questionWallTitle) {
          questionWallTitle.textContent = data.title || 'Un mur de questions est disponible';
        }

        if (questionWallMeta) {
          questionWallMeta.textContent = data.access_code
            ? `Code ${data.access_code} · Poser une question ou voter`
            : 'Poser une question ou voter';
        }
      };

      const updateEmargement = function (data) {
        const hasOpenSeance = Boolean(data && data.has_open_seance);
        emargementCount = hasOpenSeance ? 1 : 0;
        renderBadge();

        if (!emargementItem || !emargementLink) {
          return;
        }

        emargementItem.classList.toggle('hidden', !hasOpenSeance);

        if (!hasOpenSeance) {
          emargementLink.setAttribute('href', '#');
          return;
        }

        emargementLink.setAttribute('href', data.join_url || '#');

        if (emargementTitle) {
          emargementTitle.textContent = data.group_name || 'Une feuille de présence est ouverte';
        }

        if (emargementMeta) {
          emargementMeta.textContent = data.opened_at_human
            ? `Ouvert ${data.opened_at_human} · Appuyer pour signer`
            : 'Appuyer pour signer';
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

      const pollWordCloudStatus = function () {
        if (!wordCloudStatusUrl) {
          return;
        }

        fetch(wordCloudStatusUrl, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Word cloud status request failed');
            }

            return response.json();
          })
          .then(updateWordCloud)
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

      const pollQuestionWallStatus = function () {
        if (!questionWallStatusUrl) {
          return;
        }

        fetch(questionWallStatusUrl, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Question wall status request failed');
            }

            return response.json();
          })
          .then(updateQuestionWall)
          .catch(function () {});
      };

      const pollEmargementStatus = function () {
        if (!emargementStatusUrl) {
          return;
        }

        fetch(emargementStatusUrl, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Emargement status request failed');
            }

            return response.json();
          })
          .then(updateEmargement)
          .catch(function () {});
      };

      renderBadge();
      window.setInterval(pollLiveQuizStatus, 5000);
      window.setInterval(pollWordCloudStatus, 5000);
      window.setInterval(pollWhiteboardStatus, 5000);
      window.setInterval(pollQuestionWallStatus, 5000);
      window.setInterval(pollEmargementStatus, 5000);
    });
  </script>
@endif
