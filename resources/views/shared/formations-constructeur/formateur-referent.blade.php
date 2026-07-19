@if(($constructeurAdmin ?? false) && isset($formateurs))
  <div>
    <label for="{{ $idChampReferent }}" class="block text-xs font-semibold text-gray-600 mb-1">Formateur référent (optionnel)</label>
    <select id="{{ $idChampReferent }}" name="formateur_id"
            class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">
      <option value="">Catalogue Oneduc, sans référent</option>
      @foreach($formateurs as $formateur)
        <option value="{{ $formateur->id }}" @selected((string) old('formateur_id', $formateurIdSelectionne ?? '') === (string) $formateur->id)>
          {{ $formateur->name ?? $formateur->username }}
        </option>
      @endforeach
    </select>
    <p class="mt-1 text-xs text-gray-400">Le référent pourra utiliser la formation, mais devra la copier pour la personnaliser.</p>
    @error('formateur_id')
      <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
  </div>
@endif
