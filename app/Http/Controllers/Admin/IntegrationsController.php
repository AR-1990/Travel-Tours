<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportIntegrationConfig;
use App\Services\Travelport\TravelportSystemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class IntegrationsController extends Controller
{
    protected function ensureSuperAdmin(): void
    {
        $user = Auth::user();
        if (! $user || $user->user_type !== 'super_admin') {
            abort(403, 'Only super admin can manage integrations.');
        }
    }

    protected function catalog(): array
    {
        $c = config('integrations.catalog', []);

        return is_array($c) ? $c : [];
    }

    protected function assertEditableSlug(string $slug): array
    {
        $def = $this->catalog()[$slug] ?? null;
        abort_if($def === null, 404);
        abort_if($def['coming_soon'] ?? false, 404);

        return $def;
    }

    public function index()
    {
        $this->ensureSuperAdmin();

        $catalog = $this->catalog();
        $slugs = array_keys($catalog);
        $rows = Integration::query()
            ->whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug');

        $items = [];
        foreach ($catalog as $slug => $meta) {
            $row = $rows->get($slug);
            $items[] = [
                'slug' => $slug,
                'name' => $meta['name'] ?? $slug,
                'description' => $meta['description'] ?? '',
                'coming_soon' => (bool) ($meta['coming_soon'] ?? false),
                'configured' => $row !== null,
                'is_enabled' => $row?->is_enabled ?? false,
            ];
        }

        return view('admin.integrations.index', [
            'items' => $items,
        ]);
    }

    public function edit(string $slug, TravelportSystemService $system)
    {
        $this->ensureSuperAdmin();
        $this->assertEditableSlug($slug);

        return match ($slug) {
            Integration::SLUG_TRAVELPORT => $this->viewTravelportEdit($system),
            default => abort(404),
        };
    }

    private function viewTravelportEdit(TravelportSystemService $system)
    {
        $tp = TravelportIntegrationConfig::merged();
        $row = Integration::query()
            ->where('slug', Integration::SLUG_TRAVELPORT)
            ->first();

        return view('admin.integrations.travelport.edit', [
            'travelport' => $tp,
            'travelportRow' => $row,
            'travelportHasDbRow' => $row !== null,
            'systemServiceUrl' => $system->systemServiceUrl(),
            'samplePingXml' => $system->samplePingXml(),
            'usernameSet' => (string) ($tp['username'] ?? '') !== '',
            'passwordSet' => (string) ($tp['password'] ?? '') !== '',
            'branchSet' => (string) ($tp['branch'] ?? '') !== '',
        ]);
    }

    public function update(Request $request, string $slug)
    {
        $this->ensureSuperAdmin();
        $this->assertEditableSlug($slug);

        return match ($slug) {
            Integration::SLUG_TRAVELPORT => $this->updateTravelport($request),
            default => abort(404),
        };
    }

    private function updateTravelport(Request $request)
    {
        $existing = Integration::query()
            ->where('slug', Integration::SLUG_TRAVELPORT)
            ->first();
        $prev = is_array($existing?->payload) ? $existing->payload : [];

        $hasStoredPassword = isset($prev['password']) && (string) $prev['password'] !== '';
        $hasEnvPassword = (string) config('travelport.password', '') !== '';

        $request->validate([
            'travelport.region' => ['required', Rule::in(['emea', 'americas', 'apac'])],
            'travelport.environment' => ['required', Rule::in(['pp', 'production'])],
            'travelport.username' => ['required', 'string', 'max:255'],
            'travelport.password' => [
                'nullable',
                'string',
                'max:500',
                Rule::requiredIf(! $hasStoredPassword && ! $hasEnvPassword),
            ],
            'travelport.branch' => ['nullable', 'string', 'max:32'],
            'travelport.gds' => ['nullable', 'string', 'max:8'],
            'travelport.target_branch' => ['nullable', 'string', 'max:32'],
            'travelport.schema_major_version' => ['required', 'integer', 'min:30', 'max:99'],
            'travelport.timeout' => ['required', 'integer', 'min:5', 'max:120'],
            'travelport.base_url_override' => ['nullable', 'string', 'max:512'],
            'travelport.origin_application' => ['nullable', 'string', 'max:64'],
            'is_enabled' => ['nullable', 'boolean'],
        ], [], [
            'travelport.region' => 'region',
            'travelport.environment' => 'environment',
            'travelport.username' => 'API username',
            'travelport.password' => 'API password',
            'travelport.branch' => 'PCC',
            'travelport.gds' => 'GDS',
            'travelport.target_branch' => 'target branch',
            'travelport.schema_major_version' => 'schema major version',
            'travelport.timeout' => 'timeout',
            'travelport.base_url_override' => 'base URL override',
        ]);

        $t = $request->input('travelport', []);

        $updates = [
            'region' => $t['region'],
            'environment' => $t['environment'],
            'username' => $t['username'],
            'branch' => (string) ($t['branch'] ?? ''),
            'gds' => (string) ($t['gds'] ?? ''),
            'target_branch' => (string) ($t['target_branch'] ?? ''),
            'schema_major_version' => (int) $t['schema_major_version'],
            'timeout' => (int) $t['timeout'],
            'base_url_override' => TravelportSystemService::normalizeHostOnly((string) ($t['base_url_override'] ?? '')),
            'origin_application' => (string) ($t['origin_application'] ?? 'UAPI'),
        ];

        if ($request->filled('travelport.password')) {
            $updates['password'] = $t['password'];
        }

        $newPayload = array_merge($prev, $updates);

        $catalogName = $this->catalog()[Integration::SLUG_TRAVELPORT]['name'] ?? 'Travelport Universal API';

        Integration::query()->updateOrCreate(
            ['slug' => Integration::SLUG_TRAVELPORT],
            [
                'name' => $catalogName,
                'is_enabled' => $request->boolean('is_enabled', true),
                'payload' => $newPayload,
            ]
        );

        return redirect()
            ->route('admin.integrations.edit', ['slug' => Integration::SLUG_TRAVELPORT])
            ->with('success', 'Travelport integration settings saved. They are stored encrypted in the `integrations` table.');
    }

    public function ping(Request $request, string $slug, TravelportSystemService $system)
    {
        $this->ensureSuperAdmin();
        $this->assertEditableSlug($slug);

        if ($slug !== Integration::SLUG_TRAVELPORT) {
            abort(404);
        }

        $result = $system->ping();

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        $flash = $result['ok'] ? 'success' : 'error';

        return redirect()
            ->route('admin.integrations.edit', ['slug' => $slug])
            ->with($flash, $result['message'])
            ->with('travelport_ping', $result);
    }

    public function testSearch(Request $request, string $slug, TravelportAirService $air)
    {
        $this->ensureSuperAdmin();
        $this->assertEditableSlug($slug);

        if ($slug !== Integration::SLUG_TRAVELPORT) {
            abort(404);
        }

        $result = $air->lowFareSearch([
            'origin' => strtoupper((string) $request->input('origin', 'LHR')),
            'destination' => strtoupper((string) $request->input('destination', 'JFK')),
            'departure_date' => (string) $request->input('departure_date', now()->addDays(21)->format('Y-m-d')),
            'return_date' => $request->input('return_date'),
            'adults' => (int) $request->input('adults', 1),
        ]);

        $flash = $result['ok'] ? 'success' : 'error';

        return redirect()
            ->route('admin.integrations.edit', ['slug' => $slug])
            ->with($flash, $result['message'])
            ->with('travelport_lfs', $result);
    }
}
