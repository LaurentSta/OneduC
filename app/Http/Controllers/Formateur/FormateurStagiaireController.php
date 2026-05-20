<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\FormateurMessage;
use App\Models\Group;
use App\Models\User;
use App\Notifications\FormateurMessageNotification;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class FormateurStagiaireController extends Controller
{
    private function accessibleTrainerGroupIds(int $formateurId): Collection
    {
        return Group::query()
            ->accessibleByTrainer($formateurId)
            ->pluck('groups.id')
            ->map(fn ($groupId) => (int) $groupId)
            ->values();
    }

    public function indexStagiaires(Request $request)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 10);

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $groupes = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->orderBy('name')
            ->get(['id', 'name']);

        $query = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($q) use ($accessibleGroupIds, $formateurId) {
                $q->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($gq) use ($accessibleGroupIds) {
                        $gq->whereIn('groups.id', $accessibleGroupIds->all());
                    });
            });

        if ($groupId = $request->input('group_id')) {
            $query->whereHas('groupesStagiaire', function ($gq) use ($groupId, $accessibleGroupIds) {
                $gq->where('groups.id', $groupId)
                    ->whereIn('groups.id', $accessibleGroupIds->all());
            });
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('prenom', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $stagiaires = $query
            ->with(['groupesStagiaire' => function ($q) use ($accessibleGroupIds) {
                $q->whereIn('groups.id', $accessibleGroupIds->all())->orderBy('name');
            }])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('formateur.backend.stagiaires.all_stagiaires', compact('stagiaires', 'groupes', 'perPage', 'allowedPerPage'));
    }

    public function createStagiaire()
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $groupes = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedGroupId = request()->integer('group_id') ?: null;

        if ($selectedGroupId && !$groupes->contains('id', $selectedGroupId)) {
            $selectedGroupId = null;
        }

        return view('formateur.backend.stagiaires.add_stagiaire', compact('groupes', 'selectedGroupId'));
    }

    public function storeStagiaire(Request $request)
    {
        $request->validate([
            'prenom'   => ['required', 'string', 'max:255'],
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'group_id' => ['nullable', 'integer', 'exists:groups,id'],
        ]);

        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);
        $email  = strtolower(trim($request->email));
        $prenom = $request->prenom;
        $nom    = $request->name;
        $gid    = $request->integer('group_id') ?: null;

        $group = null;
        if ($gid) {
            $group = Group::query()
                ->where('id', $gid)
                ->whereIn('id', $accessibleGroupIds->all())
                ->firstOrFail();
        }

        $user = User::withTrashed()->where('email', $email)->first();

        if ($user && $user->role !== 'stagiaire') {
            return back()
                ->withErrors(['email' => 'Adresse déjà utilisée par un autre type de compte.'])
                ->withInput();
        }

        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }

            if (!$user->formateur_id) {
                $user->formateur_id = $formateurId;
            }

            $user->prenom = $user->prenom ?: $prenom;
            $user->name   = $user->name ?: $nom;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();
        } else {
            $user = User::create([
                'prenom'       => $prenom,
                'name'         => $nom,
                'email'        => $email,
                'password'     => $request->filled('password')
                    ? Hash::make($request->password)
                    : bcrypt(str()->password(12)),
                'role'         => 'stagiaire',
                'formateur_id' => $formateurId,
                'status'       => 1,
                'code_acces'   => CodeGeneratorService::generateUniqueAccessCode(),
            ]);
        }

        if ($group) {
            $group->students()->syncWithoutDetaching([
                $user->id => ['role_in_group' => 'stagiaire'],
            ]);
        }

        $redirectTo = (string) $request->input('redirect_to', '');
        $redirectTarget = str_starts_with($redirectTo, url('/formateur/parcours-formateur/'))
            ? $redirectTo
            : route('formateur.stagiaires.index');

        return redirect()
            ->to($redirectTarget)
            ->with('success', $user->wasRecentlyCreated
                ? 'Stagiaire créé et rattaché si un groupe a été fourni.'
                : 'Stagiaire existant réutilisé et rattaché si un groupe a été fourni.');
    }

    public function editStagiaire($id)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $groupes = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->orderBy('name')
            ->get(['id', 'name']);

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($accessibleGroupIds, $formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($accessibleGroupIds) {
                        $q->whereIn('groups.id', $accessibleGroupIds->all());
                    });
            })
            ->with(['groupesStagiaire' => function ($query) use ($accessibleGroupIds) {
                $query->whereIn('groups.id', $accessibleGroupIds->all())->orderBy('name');
            }])
            ->findOrFail($id);

        $messages = FormateurMessage::where('stagiaire_id', $stagiaire->id)
            ->where('formateur_id', $formateurId)
            ->latest()
            ->get();

        return view('formateur.backend.stagiaires.edit_stagiaire', compact('stagiaire', 'groupes', 'messages'));
    }

    public function updateStagiaire(Request $request, $id)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($accessibleGroupIds, $formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($accessibleGroupIds) {
                        $q->whereIn('groups.id', $accessibleGroupIds->all());
                    });
            })
            ->findOrFail($id);

        $request->validate([
            'prenom'      => 'required|string|max:255',
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $stagiaire->id,
            'code_acces'  => ['nullable', 'string', 'size:6', Rule::unique('users', 'code_acces')->ignore($stagiaire->id)],
            'password'    => 'nullable|string|min:8',
            'group_ids'   => ['nullable', 'array'],
            'group_ids.*' => [
                'integer',
                Rule::exists('groups', 'id')->where(fn ($query) => $query->whereIn('id', $accessibleGroupIds->all())),
            ],
        ]);

        $selectedGroupIds = collect($request->input('group_ids', []))
            ->map(fn ($groupId) => (int) $groupId)
            ->filter()
            ->unique()
            ->values();

        $trainerGroupIds = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->pluck('id')
            ->map(fn ($groupId) => (int) $groupId);

        DB::transaction(function () use ($request, $stagiaire, $trainerGroupIds, $selectedGroupIds): void {
            $stagiaire->prenom = $request->prenom;
            $stagiaire->name   = $request->name;
            $stagiaire->email  = strtolower(trim((string) $request->email));
            $stagiaire->code_acces = $request->filled('code_acces')
                ? strtoupper(trim((string) $request->code_acces))
                : null;

            if ($request->filled('password')) {
                $stagiaire->password = Hash::make($request->password);
            }

            $stagiaire->save();

            if ($trainerGroupIds->isEmpty()) {
                return;
            }

            DB::table('group_user')
                ->where('user_id', $stagiaire->id)
                ->where('role_in_group', 'stagiaire')
                ->whereIn('group_id', $trainerGroupIds->all())
                ->delete();

            foreach ($selectedGroupIds as $groupId) {
                DB::table('group_user')->updateOrInsert(
                    [
                        'group_id'      => $groupId,
                        'user_id'       => $stagiaire->id,
                        'role_in_group' => 'stagiaire',
                    ],
                    []
                );
            }
        });

        return redirect()
            ->route('formateur.stagiaires.index')
            ->with('success', 'Stagiaire modifié avec succès.');
    }

    public function sendMessage(Request $request, $id)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($accessibleGroupIds, $formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($accessibleGroupIds) {
                        $q->whereIn('groups.id', $accessibleGroupIds->all());
                    });
            })
            ->findOrFail($id);

        $request->validate([
            'subject'           => ['nullable', 'string', 'max:255'],
            'body'              => ['required', 'string', 'max:5000'],
            'send_notification' => ['nullable', 'boolean'],
            'send_email'        => ['nullable', 'boolean'],
            'include_access_code' => ['nullable', 'boolean'],
        ]);

        $sendNotification = (bool) $request->input('send_notification', false);
        $sendEmail = (bool) $request->input('send_email', false);
        $includeAccessCode = (bool) $request->input('include_access_code', false);

        if (!$sendNotification && !$sendEmail) {
            return back()
                ->withErrors(['channels' => 'Veuillez sélectionner au moins un canal d\'envoi (notification ou email).'])
                ->withInput();
        }

        $body = $request->input('body');

        if ($includeAccessCode) {
            if (blank($stagiaire->code_acces)) {
                $stagiaire->forceFill([
                    'code_acces' => CodeGeneratorService::generateUniqueAccessCode(),
                ])->save();
            }

            $body .= "\n\n---\n";
            $body .= "Votre accès Onéduc\n";
            $body .= 'Lien de connexion : ' . route('stagiaire.code.form.legacy') . "\n";
            $body .= "Code d'accès : " . $stagiaire->code_acces;
        }

        $message = FormateurMessage::create([
            'formateur_id'        => $formateurId,
            'stagiaire_id'        => $stagiaire->id,
            'subject'             => $request->input('subject') ?: null,
            'body'                => $body,
            'sent_as_notification' => $sendNotification,
            'sent_as_email'       => $sendEmail,
        ]);

        $channels = [];
        if ($sendNotification) {
            $channels[] = 'database';
        }
        if ($sendEmail) {
            $channels[] = 'mail';
        }

        $stagiaire->notify(new FormateurMessageNotification($message, $channels));

        return back()->with('message_sent', 'Message envoyé à ' . trim($stagiaire->prenom . ' ' . $stagiaire->name) . '.');
    }

    public function destroyStagiaire($id)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($accessibleGroupIds, $formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($accessibleGroupIds) {
                        $q->whereIn('groups.id', $accessibleGroupIds->all());
                    });
            })
            ->findOrFail($id);

        $stagiaire->delete();

        return redirect()
            ->route('formateur.stagiaires.index')
            ->with('success', 'Stagiaire supprimé avec succès.');
    }
}
