@extends('admin.admin_dashboard')

@section('admin')
<div class="w-full px-6 lg:px-8" x-data="{ openModal: false, selectedFeedback: null, generatedResponse: '', submitResponse(){ this.openModal = false; } }">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Retours stagiaires</h1>
                <p class="text-sm text-gray-600">Commentaires, avis et notes sur les leçons suivies.</p>
            </div>
        </div>

        @if ($feedbacks->count())
            <div class="overflow-x-auto">
                <table class="table-oneduc w-full text-sm text-left text-gray-700">
                    <thead class="text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Auteur</th>
                            <th class="px-4 py-3">Leçon</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Note</th>
                            <th class="px-4 py-3">Commentaire</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feedbacks as $feedback)
                            <tr class="border-b border-gray-100 transition">
                                <td class="px-4 py-3">{{ $feedback->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $feedback->user->name }}</td>
                                <td class="px-4 py-3">{{ $feedback->lesson->lecture_title ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($feedback->type)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-semibold">{{ ucfirst($feedback->type) }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Non précisé</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($feedback->rating)
                                        <span class="text-yellow-600 font-semibold">{{ $feedback->rating }} ★</span>
                                    @else
                                        <span class="text-gray-400 italic">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ Str::limit($feedback->comment, 100) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="openModal = true; selectedFeedback = {{ $feedback->toJson() }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                                        <i class="ti ti-message"></i>
                                        Répondre
                                    </button>
                                    <button type="button" x-on:click="$dispatch('open-modal', 'delete-feedback-{{ $feedback->id }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition text-xs font-varela cursor-pointer ml-2">
                                        <i class="ti ti-trash"></i>
                                        Supprimer
                                    </button>
                                    <x-confirm-modal
                                        name="delete-feedback-{{ $feedback->id }}"
                                        title="Supprimer ce commentaire ?"
                                        :action="route('admin.retours.delete', $feedback->id)"
                                        method="DELETE"
                                        confirm-label="Supprimer"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div x-show="openModal" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" style="display: none;">
                <div class="bg-white rounded-lg p-6 w-full max-w-xl" @click.away="openModal = false">
                    <h3 class="text-lg font-semibold mb-2">Répondre au retour</h3>

                    <template x-if="selectedFeedback">
                        <p class="text-sm text-gray-700 mb-4">"<span x-text="selectedFeedback.comment"></span>"</p>
                    </template>

                    <textarea class="w-full border p-2 rounded mb-4" x-model="generatedResponse" placeholder="Rédigez votre réponse ici..." rows="4"></textarea>

                    <div class="flex justify-between">
                        <button class="px-4 py-2 bg-gray-200 rounded" @click="openModal = false">Annuler</button>
                        <button class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700" @click="submitResponse()">Envoyer</button>
                    </div>
                </div>
            </div>

            <div class="mt-6">{{ $feedbacks->links() }}</div>
        @else
            <p class="text-gray-600">Aucun commentaire pour le moment.</p>
        @endif
    </div>
</div>
@endsection
