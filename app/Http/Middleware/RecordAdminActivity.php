<?php

namespace App\Http\Middleware;

use App\Models\ActivityJournalEntry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RecordAdminActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$request->user()) {
            return $response;
        }

        if (!$request->routeIs('admin.*')) {
            return $response;
        }

        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $routeName = $request->route()?->getName();

        ActivityJournalEntry::create([
            'user_id' => $request->user()->id,
            'action' => $this->resolveActionLabel($routeName, $request->method()),
            'route_name' => $routeName,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'context' => $this->sanitizeContext($request),
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return $response;
    }

    private function resolveActionLabel(?string $routeName, string $method): string
    {
        if (!$routeName) {
            return 'Action administration';
        }

        return match (true) {
            str_starts_with($routeName, 'admin.modules.') => 'Gestion modules',
            str_starts_with($routeName, 'admin.lectures.') => 'Gestion lecons',
            str_starts_with($routeName, 'admin.sections.') => 'Gestion sections',
            str_starts_with($routeName, 'admin.evaluations.') => 'Gestion evaluations',
            str_starts_with($routeName, 'admin.scorm.') => 'Action SCORM',
            str_starts_with($routeName, 'admin.pilotage.') => 'Action pilotage',
            str_starts_with($routeName, 'admin.quiz.') => 'Gestion questionnaire',
            str_starts_with($routeName, 'admin.categories.') => 'Gestion categories',
            str_starts_with($routeName, 'admin.subcategories.') => 'Gestion sous-categories',
            str_starts_with($routeName, 'admin.utilisateurs.') => 'Gestion utilisateurs',
            default => Str::headline($method . ' ' . str_replace('admin.', '', $routeName)),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function sanitizeContext(Request $request): array
    {
        $excluded = [
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'currentPassword',
            'newPassword',
            'newPassword_confirmation',
            'code_acces',
            'zip',
        ];

        if ($request->routeIs(
            'admin.utilisateurs.*',
            'admin.observateurs.*',
            'admin.profil.store'
        )) {
            $excluded = array_merge($excluded, [
                'prenom',
                'name',
                'username',
                'email',
                'phone',
                'phoneNumber',
                'address',
                'societe',
                'firstName',
                'lastName',
                'photo',
            ]);
        }

        $context = Arr::except($request->all(), $excluded);

        foreach ($request->allFiles() as $key => $file) {
            if (in_array($key, $excluded, true)) {
                continue;
            }

            if (is_array($file)) {
                $context[$key] = array_map(fn ($item) => $item->getClientOriginalName(), $file);

                continue;
            }

            $context[$key] = $file->getClientOriginalName();
        }

        foreach (['description', 'body', 'comment'] as $longField) {
            if (!empty($context[$longField]) && is_string($context[$longField])) {
                $context[$longField] = Str::limit($context[$longField], 180);
            }
        }

        return $context;
    }
}
