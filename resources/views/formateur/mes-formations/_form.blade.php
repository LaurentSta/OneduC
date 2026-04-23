{{--
  Partial partagé create/edit.
  Variables attendues : $parcours (optionnel), $availableModules, $selectedModules, $storeUrl, $method
--}}

<div class="bg-white rounded-[20px] shadow-md px-8 py-6 mb-6">
  <h2 class="text-base font-semibold text-gray-800 mb-4">Informations générales</h2>

  <div class="space-y-4">
    <div>
      <label for="formation-title" class="block text-sm font-medium text-gray-700 mb-1">Titre <span class="text-red-500">*</span></label>
      <input type="text"
             id="formation-title"
             name="title"
             value="{{ old('title', $parcours->title ?? '') }}"
             maxlength="255"
             required
             class="w-full rounded-[10px] border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#E94D2A] focus:border-transparent"
             placeholder="Ex : Formation initiale CAP cuisine">
    </div>

    <div>
      <label for="formation-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
      <textarea id="formation-description"
                name="description"
                rows="3"
                maxlength="2000"
                class="w-full rounded-[10px] border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#E94D2A] focus:border-transparent"
                placeholder="Décrivez l'objectif de cette formation…">{{ old('description', $parcours->description ?? '') }}</textarea>
    </div>
  </div>
</div>

{{-- Builder React Flow --}}
<div
  data-parcours-builder
  data-available-modules='@json($availableModules)'
  data-selected-items='@json($selectedItems)'
  data-csrf-token="{{ csrf_token() }}"
  data-store-url="{{ $storeUrl }}"
  data-method="{{ $method }}"
  data-mode="edit"
  class="min-h-[520px]"
></div>
