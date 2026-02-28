@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-4xl px-6 lg:px-8">
  <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 border border-gray-100">
    <h1 class="text-[20px] font-varela text-bleuone mb-4">Nouveau nuage</h1>

    <form method="POST" action="{{ route('admin.nuage.store') }}" class="space-y-5">
      @csrf

      <div>
        <label class="block text-sm font-medium mb-1">Titre</label>
        <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-lg border-gray-300" required>
        @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Question</label>
        <textarea name="question" rows="3" class="w-full rounded-lg border-gray-300" required>{{ old('question') }}</textarea>
        @error('question')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Formation liée (optionnel)</label>
        <select name="module_id" class="w-full rounded-lg border-gray-300">
          <option value="">Aucune</option>
          @foreach($modules as $module)
            <option value="{{ $module->id }}" @selected((string)old('module_id') === (string)$module->id)>{{ $module->module_name }}</option>
          @endforeach
        </select>
      </div>

      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" checked>
        <span class="text-sm">Session ouverte immédiatement</span>
      </label>

      <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 rounded-lg bg-orangeone text-white">Créer</button>
        <a href="{{ route('admin.nuage.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Annuler</a>
      </div>
    </form>
  </div>
</div>

@endsection
