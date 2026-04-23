{{-- Utilisé uniquement sur les pages mes-formations/* (show, create, edit) --}}
<div class="flex gap-2 mb-6">
    <a href="{{ route('formateur.formations.index') }}"
       class="px-4 py-2 rounded-full text-sm font-medium transition bg-white text-gray-600 border border-gray-200 hover:border-[#E94D2A] hover:text-[#E94D2A]">
        Catalogue des modules
    </a>
    <a href="{{ route('formateur.formations.index') }}?tab=parcours"
       class="px-4 py-2 rounded-full text-sm font-medium transition bg-[#E94D2A] text-white shadow">
        Mes parcours de formation
    </a>
</div>
