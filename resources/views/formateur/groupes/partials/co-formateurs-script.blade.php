<script>
  (function () {
    const picker = document.querySelector('[data-co-trainer-picker]');
    if (!picker) {
      return;
    }

    const canManage = picker.dataset.canManage === '1';
    if (!canManage) {
      return;
    }

    const searchInput = picker.querySelector('[data-co-trainer-search-input]');
    const resultsBox = picker.querySelector('[data-co-trainer-results]');
    const feedbackBox = picker.querySelector('[data-co-trainer-feedback]');
    const selectedBox = picker.querySelector('[data-co-trainer-selected]');
    const emptyState = picker.querySelector('[data-co-trainer-empty]');
    const form = picker.closest('form');
    const minChars = Number.parseInt(picker.dataset.minChars || '3', 10) || 3;
    const groupId = Number.parseInt(picker.dataset.groupId || '0', 10) || 0;
    const searchUrl = picker.dataset.searchUrl || '';

    let debounceTimer = null;

    function selectedIds() {
      return Array.from(selectedBox.querySelectorAll('[data-co-trainer-chip]'))
        .map((chip) => Number.parseInt(chip.dataset.id || '0', 10))
        .filter((id) => Number.isFinite(id) && id > 0);
    }

    function hideFeedback() {
      feedbackBox.classList.add('hidden');
      feedbackBox.classList.remove('border-red-200', 'bg-red-50', 'text-red-700', 'border-green-200', 'bg-green-50', 'text-green-700', 'border-gray-200', 'bg-white', 'text-gray-700');
      feedbackBox.textContent = '';
    }

    function showFeedback(message, type = 'info') {
      if (!message) {
        hideFeedback();
        return;
      }

      feedbackBox.classList.remove('hidden');
      feedbackBox.classList.remove('border-red-200', 'bg-red-50', 'text-red-700', 'border-green-200', 'bg-green-50', 'text-green-700', 'border-gray-200', 'bg-white', 'text-gray-700');

      if (type === 'error') {
        feedbackBox.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
      } else if (type === 'success') {
        feedbackBox.classList.add('border-green-200', 'bg-green-50', 'text-green-700');
      } else {
        feedbackBox.classList.add('border-gray-200', 'bg-white', 'text-gray-700');
      }

      feedbackBox.textContent = message;
    }

    function clearResults() {
      resultsBox.innerHTML = '';
      resultsBox.classList.add('hidden');
    }

    function syncEmptyState() {
      const hasSelection = selectedBox.querySelector('[data-co-trainer-chip]') !== null;
      if (!emptyState) {
        return;
      }

      emptyState.classList.toggle('hidden', hasSelection);
    }

    function buildChip(id, email) {
      const chip = document.createElement('div');
      chip.dataset.coTrainerChip = '1';
      chip.dataset.id = String(id);
      chip.dataset.email = email;
      chip.className = 'inline-flex items-center gap-2 rounded-full border border-bleuone/20 bg-white px-3 py-2 text-sm font-medium text-bleuone';

      const label = document.createElement('span');
      label.textContent = email;

      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'co_formateurs[]';
      hidden.value = String(id);

      const remove = document.createElement('button');
      remove.type = 'button';
      remove.dataset.coTrainerRemove = '1';
      remove.className = 'inline-flex h-6 w-6 items-center justify-center rounded-full text-gray-400 transition hover:bg-red-50 hover:text-red-600';
      remove.setAttribute('aria-label', 'Retirer ce co-formateur');
      remove.setAttribute('title', 'Retirer ce co-formateur');
      remove.innerHTML = '<span aria-hidden="true">&times;</span>';

      chip.appendChild(label);
      chip.appendChild(hidden);
      chip.appendChild(remove);

      return chip;
    }

    function addCoTrainer(id, email) {
      if (selectedIds().includes(id)) {
        showFeedback('Ce formateur est deja ajoute au groupe.', 'error');
        return;
      }

      selectedBox.appendChild(buildChip(id, email));
      syncEmptyState();
      showFeedback('Co-formateur ajoute. Pensez a enregistrer le formulaire.', 'success');
    }

    function renderResults(items) {
      if (!Array.isArray(items) || items.length === 0) {
        clearResults();
        showFeedback('Aucun formateur trouve. Vous pouvez uniquement ajouter un formateur deja inscrit.', 'info');
        return;
      }

      hideFeedback();
      resultsBox.innerHTML = '';

      items.forEach((item) => {
        const option = document.createElement('button');
        option.type = 'button';
        option.className = 'flex w-full items-center justify-between gap-3 border-b border-gray-100 px-3 py-3 text-left text-sm text-gray-700 transition last:border-b-0 hover:bg-slate-50';
        option.dataset.id = String(item.id);
        option.dataset.email = item.email;
        option.innerHTML = '<span class="truncate"></span><span class="text-xs font-semibold text-gray-400">Ajouter</span>';
        option.querySelector('span').textContent = item.email;
        resultsBox.appendChild(option);
      });

      resultsBox.classList.remove('hidden');
    }

    async function searchCoTrainers(term) {
      const params = new URLSearchParams();
      params.set('q', term);

      if (groupId > 0) {
        params.set('group_id', String(groupId));
      }

      selectedIds().forEach((id) => {
        params.append('exclude[]', String(id));
      });

      const response = await fetch(searchUrl + '?' + params.toString(), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error('search_failed');
      }

      const payload = await response.json();
      return Array.isArray(payload.items) ? payload.items : [];
    }

    searchInput.addEventListener('input', () => {
      const term = (searchInput.value || '').trim().toLowerCase();

      if (debounceTimer) {
        window.clearTimeout(debounceTimer);
      }

      if (term.length === 0) {
        hideFeedback();
        clearResults();
        return;
      }

      if (term.length < minChars) {
        clearResults();
        showFeedback('Saisissez au moins 3 caracteres pour lancer la recherche.', 'info');
        return;
      }

      debounceTimer = window.setTimeout(async () => {
        try {
          const items = await searchCoTrainers(term);
          renderResults(items);
        } catch (error) {
          clearResults();
          showFeedback('La recherche est momentanement indisponible.', 'error');
        }
      }, 250);
    });

    resultsBox.addEventListener('click', (event) => {
      const option = event.target.closest('button[data-id]');
      if (!option) {
        return;
      }

      const id = Number.parseInt(option.dataset.id || '0', 10);
      const email = option.dataset.email || '';
      if (!id || !email) {
        return;
      }

      addCoTrainer(id, email);
      searchInput.value = '';
      clearResults();
    });

    selectedBox.addEventListener('click', (event) => {
      const removeButton = event.target.closest('[data-co-trainer-remove]');
      if (!removeButton) {
        return;
      }

      const chip = removeButton.closest('[data-co-trainer-chip]');
      if (!chip) {
        return;
      }

      chip.remove();
      syncEmptyState();
      hideFeedback();
    });

    document.addEventListener('click', (event) => {
      if (picker.contains(event.target)) {
        return;
      }

      clearResults();
    });

    if (form) {
      form.addEventListener('submit', (event) => {
        const pendingValue = (searchInput.value || '').trim();
        if (pendingValue === '') {
          return;
        }

        event.preventDefault();
        showFeedback('Veuillez selectionner un formateur dans la liste proposee avant d enregistrer.', 'error');
        searchInput.focus();
      });
    }

    syncEmptyState();
  })();
</script>
