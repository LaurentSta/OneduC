{{-- Utilisé uniquement sur les pages mes-parcours/* (show, create, edit) --}}
<div class="flex gap-2 mb-6">
    <a href="{{ route('formateur.formations.index') }}"
       title="Formations disponibles pour vos groupes"
       class="px-4 py-2 rounded-full text-sm font-medium transition bg-white text-gray-600 border border-gray-200 hover:border-[#E94D2A] hover:text-[#E94D2A]">
        Catalogue
    </a>
    <a href="{{ route('formateur.formations.index') }}?tab=creations"
       title="Créez vos propres formations"
       class="px-4 py-2 rounded-full text-sm font-medium transition bg-white text-gray-600 border border-gray-200 hover:border-[#E94D2A] hover:text-[#E94D2A]">
        Créations
    </a>
    <a href="{{ route('formateur.formations.index') }}?tab=parcours"
       title="Ordonnez les formations suivies par un groupe"
       class="px-4 py-2 rounded-full text-sm font-medium transition bg-[#E94D2A] text-white shadow">
        Parcours
    </a>
    @if(Route::has('formateur.modeles-parcours.index'))
        <a href="{{ route('formateur.modeles-parcours.index') }}"
           title="Consultez et dupliquez les modèles de parcours proposés par Oneduc"
           class="px-4 py-2 rounded-full text-sm font-medium transition bg-white text-gray-600 border border-gray-200 hover:border-[#E94D2A] hover:text-[#E94D2A] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orangeone">
            Modèles Oneduc
        </a>
    @endif
</div>
