@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-[1248px] mx-auto px-4 py-10">
  <h1 class="text-2xl font-semibold text-bleuone mb-6">👋 Bonjour {{ Auth::user()->username }}</h1>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

    <!-- Catégories -->
    <div class="flex items-center gap-4 p-4 border rounded-xl shadow-sm bg-white">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-orangeone">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h5l2 3h11v9H3V7z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-600 font-medium">Nombre de catégories</p>
            <h4 class="text-xl font-bold text-blue-600">{{ $categoryCount ?? 'N/A' }}</h4>
        </div>
    </div>

    <!-- Sous-catégories -->
    <div class="flex items-center gap-4 p-4 border rounded-xl shadow-sm bg-white">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-cyan-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-600 font-medium">Nombre de sous-catégories</p>
            <h4 class="text-xl font-bold text-cyan-600">{{ $subCategoryCount ?? 'N/A' }}</h4>
        </div>
    </div>

    <!-- Modules -->
    <div class="flex items-center gap-4 p-4 border rounded-xl shadow-sm bg-white">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-yellow-500">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z" />
              </svg>

        </div>
        <div>
            <p class="text-sm text-gray-600 font-medium">Nombre de modules</p>
            <h4 class="text-xl font-bold text-yellow-500">{{ $moduleCount ?? 'Aucune' }}</h4>
        </div>
    </div>

    <!-- Formateurs -->
    <div class="flex items-center gap-4 p-4 border rounded-xl shadow-sm bg-white">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-vertone">


              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>

        </div>
        <div>
            <p class="text-sm text-gray-600 font-medium">Nombre de formateurs</p>
            <h4 class="text-xl font-bold text-green-600">{{ $formateurCount ?? 'Aucune' }}</h4>
        </div>
    </div>

    <!-- Stagiaires -->
    <div class="flex items-center gap-4 p-4 border rounded-xl shadow-sm bg-white">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
              </svg>
        </div>
        <div>
            <p class="text-sm text-gray-600 font-medium">Nombre de stagiaires</p>
            <h4 class="text-xl font-bold text-red-600">{{ $stagiaireCount ?? 'Aucune' }}</h4>
        </div>
    </div>

    <!-- Groupes -->
    <div class="flex items-center gap-4 p-4 border rounded-xl shadow-sm bg-white">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-purple-500">

              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white" >
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
              </svg>
        </div>
        <div>
            <p class="text-sm text-gray-600 font-medium">Nombre de groupes</p>
            <h4 class="text-xl font-bold text-purple-500">{{ $groupCount ?? 'Aucune' }}</h4>
        </div>
    </div>

    <!-- Sections -->
    <div class="flex items-center gap-4 p-4 border rounded-xl shadow-sm bg-white">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-600 font-medium">Nombre de sections</p>
            <h4 class="text-xl font-bold text-blue-500">{{ $sectionCount ?? 'Aucune' }}</h4>
        </div>
    </div>

    <!-- Lectures -->
    <div class="flex items-center gap-4 p-4 border rounded-xl shadow-sm bg-white">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-600 font-medium">Nombre de leçons</p>
            <h4 class="text-xl font-bold text-indigo-600">{{ $lectureCount ?? 'Aucune' }}</h4>
        </div>
    </div>



</div>





  <!-- Tableau des cours -->
  <div class="mt-12">
    <h2 class="text-xl font-semibold mb-4">📚 Cours suivis</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left font-lisible datatables-academy-course">
        <thead class="border-b text-gray-700">
          <tr>
            <th class="px-2 py-2"></th>
            <th class="px-2 py-2"></th>
            <th class="px-2 py-2">Nom du cours</th>
            <th class="px-2 py-2">Durée</th>
            <th class="px-2 py-2 w-1/4">Progression</th>
            <th class="px-2 py-2 w-1/4">Statut</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

@endsection
