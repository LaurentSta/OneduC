@extends('formateur.dashboard')
@section('formateur')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="flex justify-end mb-6">
        <a href="{{ route('formateur.groupes.create') }}" class="btn-oneduc">Créer votre groupe</a>
    </div>


    <!-- Hour chart -->
    <div class="card bg-transparent shadow-none my-6 border-0">
      <div class="card-body row p-0 pb-6 g-6">
        <div class="col-12 col-lg-8">
          <h5 class="mb-2">Bienvenue,<span class="h4"> {{ Auth::user()->prenom ?? 'Formateur' }}





        <div class="col-12 col-lg-4 mt-6 lg:mt-0">
          <div class="bg-white rounded-lg shadow p-6">
            <h5 class="mb-2 font-semibold">Weekly Report</h5>
            <p class="text-sm text-gray-500">Time Spent</p>
            <div class="flex items-center justify-between mt-4">
              <h4 class="text-2xl font-bold">231h 14m</h4>
              <span class="text-green-600 bg-green-100 text-xs px-2 py-1 rounded-full">+18.4%</span>
            </div>
            <div id="leadsReportChart" class="mt-4"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Additional content (topics, popular instructors, etc.) peut suivre ici... -->
</div>

@endsection
