@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="text-center mb-6">
            <div class="w-32 h-32 mx-auto relative group">
                <img src="{{ !empty($profileData->photo) ? asset('upload/admin_images/'.$profileData->photo) : asset('upload/admin_images/NoPhoto.png') }}"
                     class="w-full h-full object-cover rounded-full border-4 border-orangeone shadow-md"
                     alt="Avatar Administrateur">
            </div>
            <h2 class="text-2xl font-bold mt-4">{{ $profileData->username }}</h2>
            <p class="text-sm text-gray-600">{{ $profileData->name }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <p class="text-sm text-gray-500 mb-1">📧 Email</p>
                <p class="text-base font-medium text-gray-900">{{ $profileData->email }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">📞 Téléphone</p>
                <p class="text-base font-medium text-gray-900">{{ $profileData->phone }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">🏠 Adresse</p>
                <p class="text-base font-medium text-gray-900">{{ $profileData->address }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">👤 Rôle</p>
                <p class="text-base font-medium text-gray-900 capitalize">{{ $profileData->role }}</p>
            </div>
        </div>
    </div>
</div>

@endsection
