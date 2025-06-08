@extends('admin.admin_dashboard')

@section('admin')
<div class="p-6 max-w-7xl mx-auto">

    <h1 class="text-2xl font-bold text-gray-800 mb-6">Commentaires des stagiaires</h1>

    @if ($feedbacks->count())
        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full text-sm text-left border border-gray-200">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 border-b">Date</th>
                        <th class="px-4 py-3 border-b">Auteur</th>
                        <th class="px-4 py-3 border-b">Leçon</th>
                        <th class="px-4 py-3 border-b">Type</th>
                        <th class="px-4 py-3 border-b">Note</th>
                        <th class="px-4 py-3 border-b">Commentaire</th>

                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @foreach ($feedbacks as $feedback)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 border-b">{{ $feedback->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 border-b">{{ $feedback->user->name }}</td>
                            <td class="px-4 py-3 border-b">
                                {{ $feedback->lesson->lecture_title ?? '—' }}
                            </td>
                            <td class="px-4 py-3 border-b">
                                @if($feedback->type)
                                    <span class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                        {{ ucfirst($feedback->type) }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">Non précisé</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 border-b">
                                @if($feedback->rating)
                                    <span class="text-yellow-500">{{ $feedback->rating }} ★</span>
                                @else
                                    <span class="text-gray-400 italic">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 border-b">
                                {{ Str::limit($feedback->comment, 100) }}
                            </td>
                            <td>
                            <button
                                @click="openModal = true; selectedFeedback = {{ $feedback->toJson() }}"
                                class="text-sm text-blue-600 hover:underline">
                                Répondre
                            </button>
                            <form action="{{ route('admin.retours.delete', $feedback->id) }}" method="POST" onsubmit="return confirm('Supprimer ce commentaire ?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:underline ml-3">
                                    Supprimer
                                </button>
                            </form>

                            </td>
                        </tr>
                    @endforeach
                    <!-- Modal unique -->
            <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" style="display: none;">
                <div class="bg-white rounded-lg p-6 w-full max-w-xl" @click.away="openModal = false">
                    <h3 class="text-lg font-semibold mb-2">Répondre au retour</h3>

                    <template x-if="selectedFeedback">
                        <p class="text-sm text-gray-700 mb-4">
                            "<span x-text="selectedFeedback.comment"></span>"
                        </p>
                    </template>

                    <textarea
                        class="w-full border p-2 rounded mb-4"
                        x-model="generatedResponse"
                        placeholder="Rédigez votre réponse ici..."
                        rows="4"></textarea>

                    <div class="flex justify-between">
                        <button class="px-4 py-2 bg-gray-200 rounded" @click="openModal = false">Annuler</button>
                        <button
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                            @click="submitResponse()">
                            Envoyer
                        </button>
                    </div>
                </div>
            </div>
                </tbody>
            </table>
        </div>


        <!-- Pagination -->
        <div class="mt-6">
            {{ $feedbacks->links() }}
        </div>
    @else
        <p class="text-gray-600">Aucun commentaire pour le moment.</p>
    @endif

</div>
@endsection
