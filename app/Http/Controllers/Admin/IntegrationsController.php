<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Services\DowntownTravel\DowntownTravelClient;
use App\Services\DowntownTravel\DowntownTravelHotelsClient;
use App\Services\DowntownTravel\DowntownTravelHotelsIntegrationConfig;
use App\Services\DowntownTravel\DowntownTravelIntegrationConfig;
use App\Services\SunSpring\SunSpringAirService;
use App\Services\SunSpring\SunSpringClient;
use App\Services\SunSpring\SunSpringIntegrationConfig;
use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportIntegrationConfig;
use App\Services\Travelport\TravelportSystemService;
use App\Services\Xconnect\XconnectClient;
use App\Services\Xconnect\XconnectHotelService;
use App\Services\Xconnect\XconnectIntegrationConfig;
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

    public function edit(string $slug, TravelportSystemService $system, SunSpringClient $sunspring, XconnectClient $xconnect, DowntownTravelClient $downtown, DowntownTravelHotelsClient $downtownHotels)
    {
        $this->ensureSuperAdmin();
        $this->assertEditableSlug($slug);

        return match ($slug) {
            Integration::SLUG_TRAVELPORT => $this->viewTravelportEdit($system),
            Integration::SLUG_SUNSPRING => $this->viewSunSpringEdit($sunspring),
            Integration::SLUG_XCONNECT => $this->viewXconnectEdit($xconnect),
            Integration::SLUG_DOWNTOWN_TRAVEL => $this->viewDowntownTravelEdit($downtown),
            Integration::SLUG_DOWNTOWN_TRAVEL_HOTELS => $this->viewDowntownTravelHotelsEdit($downtownHotels),
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

    private function viewSunSpringEdit(SunSpringClient $client)
    {
        $ss = SunSpringIntegrationConfig::merged();
        $row = Integration::query()
            ->where('slug', Integration::SLUG_SUNSPRING)
            ->first();

        return view('admin.integrations.sunspring.edit', [
            'sunspring' => $ss,
            'sunspringRow' => $row,
            'sunspringHasDbRow' => $row !== null,
            'baseUrl' => $client->baseUrl(),
            'authorizeUrl' => $client->baseUrl().'/api/v2/accounting/getAuthorizeToken',
            'usernameSet' => (string) ($ss['username'] ?? '') !== '',
            'passwordSet' => (string) ($ss['password'] ?? '') !== '',
        ]);
    }

    private function viewXconnectEdit(XconnectClient $client)
    {
        $xc = XconnectIntegrationConfig::merged();
        $row = Integration::query()
            ->where('slug', Integration::SLUG_XCONNECT)
            ->first();

        return view('admin.integrations.xconnect.edit', [
            'xconnect' => $xc,
            'xconnectRow' => $row,
            'xconnectHasDbRow' => $row !== null,
            'baseUrl' => $client->baseUrl(),
            'tokenSet' => (string) ($xc['token'] ?? '') !== '',
        ]);
    }

    private function viewDowntownTravelEdit(DowntownTravelClient $client)
    {
        $dt = DowntownTravelIntegrationConfig::merged();
        $row = Integration::query()
            ->where('slug', Integration::SLUG_DOWNTOWN_TRAVEL)
            ->first();

        return view('admin.integrations.downtown_travel.edit', [
            'downtown' => $dt,
            'downtownRow' => $row,
            'downtownHasDbRow' => $row !== null,
            'ssoBaseUrl' => $client->ssoBaseUrl(),
            'airBaseUrl' => $client->airBaseUrl(),
            'tokenUrl' => $client->ssoBaseUrl().'/oauth/token',
            'clientIdSet' => (string) ($dt['client_id'] ?? '') !== '',
            'clientSecretSet' => (string) ($dt['client_secret'] ?? '') !== '',
            'usernameSet' => (string) ($dt['username'] ?? '') !== '',
            'passwordSet' => (string) ($dt['password'] ?? '') !== '',
        ]);
    }

    private function viewDowntownTravelHotelsEdit(DowntownTravelHotelsClient $client)
    {
        $dt = DowntownTravelHotelsIntegrationConfig::merged();
        $row = Integration::query()
            ->where('slug', Integration::SLUG_DOWNTOWN_TRAVEL_HOTELS)
            ->first();

        return view('admin.integrations.downtown_travel_hotels.edit', [
            'downtown' => $dt,
            'downtownRow' => $row,
            'downtownHasDbRow' => $row !== null,
            'ssoBaseUrl' => $client->ssoBaseUrl(),
            'hotelsBaseUrl' => $client->hotelsBaseUrl(),
            'tokenUrl' => $client->ssoBaseUrl().'/oauth/token',
            'clientIdSet' => (string) ($dt['client_id'] ?? '') !== '',
            'clientSecretSet' => (string) ($dt['client_secret'] ?? '') !== '',
            'usernameSet' => (string) ($dt['username'] ?? '') !== '',
            'passwordSet' => (string) ($dt['password'] ?? '') !== '',
        ]);
    }

    public function update(Request $request, string $slug)
    {
        $this->ensureSuperAdmin();
        $this->assertEditableSlug($slug);

        return match ($slug) {
            Integration::SLUG_TRAVELPORT => $this->updateTravelport($request),
            Integration::SLUG_SUNSPRING => $this->updateSunSpring($request),
            Integration::SLUG_XCONNECT => $this->updateXconnect($request),
            Integration::SLUG_DOWNTOWN_TRAVEL => $this->updateDowntownTravel($request),
            Integration::SLUG_DOWNTOWN_TRAVEL_HOTELS => $this->updateDowntownTravelHotels($request),
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

    private function updateSunSpring(Request $request)
    {
        $existing = Integration::query()
            ->where('slug', Integration::SLUG_SUNSPRING)
            ->first();
        $prev = is_array($existing?->payload) ? $existing->payload : [];

        $hasStoredPassword = isset($prev['password']) && (string) $prev['password'] !== '';
        $hasEnvPassword = (string) config('sunspring.password', '') !== '';

        $request->validate([
            'sunspring.environment' => ['required', Rule::in(['sandbox', 'production'])],
            'sunspring.username' => ['required', 'string', 'max:255'],
            'sunspring.password' => [
                'nullable',
                'string',
                'max:500',
                Rule::requiredIf(! $hasStoredPassword && ! $hasEnvPassword),
            ],
            'sunspring.agency_code' => ['nullable', 'string', 'max:64'],
            'sunspring.office_id' => ['nullable', 'string', 'max:64'],
            'sunspring.timeout' => ['required', 'integer', 'min:5', 'max:120'],
            'sunspring.base_url_override' => ['nullable', 'string', 'max:512'],
            'is_enabled' => ['nullable', 'boolean'],
        ], [], [
            'sunspring.environment' => 'environment',
            'sunspring.username' => 'API username',
            'sunspring.password' => 'API password',
            'sunspring.agency_code' => 'agency code',
            'sunspring.office_id' => 'office ID',
            'sunspring.timeout' => 'timeout',
            'sunspring.base_url_override' => 'base URL override',
        ]);

        $s = $request->input('sunspring', []);

        $updates = [
            'environment' => $s['environment'],
            'username' => $s['username'],
            'agency_code' => (string) ($s['agency_code'] ?? ''),
            'office_id' => (string) ($s['office_id'] ?? ''),
            'timeout' => (int) $s['timeout'],
            'base_url_override' => SunSpringClient::normalizeHostOnly((string) ($s['base_url_override'] ?? '')),
        ];

        if ($request->filled('sunspring.password')) {
            $updates['password'] = $s['password'];
        }

        $newPayload = array_merge($prev, $updates);
        $catalogName = $this->catalog()[Integration::SLUG_SUNSPRING]['name'] ?? 'SunSpring Airline API';

        Integration::query()->updateOrCreate(
            ['slug' => Integration::SLUG_SUNSPRING],
            [
                'name' => $catalogName,
                'is_enabled' => $request->boolean('is_enabled', true),
                'payload' => $newPayload,
            ]
        );

        SunSpringClient::clearTokenCache();

        return redirect()
            ->route('admin.integrations.edit', ['slug' => Integration::SLUG_SUNSPRING])
            ->with('success', 'SunSpring integration settings saved. They are stored encrypted in the `integrations` table.');
    }

    private function updateXconnect(Request $request)
    {
        $existing = Integration::query()
            ->where('slug', Integration::SLUG_XCONNECT)
            ->first();
        $prev = is_array($existing?->payload) ? $existing->payload : [];

        $hasStoredToken = isset($prev['token']) && (string) $prev['token'] !== '';
        $hasEnvToken = (string) config('xconnect.token', '') !== '';

        $request->validate([
            'xconnect.environment' => ['required', Rule::in(['sandbox', 'production'])],
            'xconnect.base_url' => ['required', 'string', 'max:512'],
            'xconnect.base_url_override' => ['nullable', 'string', 'max:512'],
            'xconnect.token' => [
                'nullable',
                'string',
                'max:2000',
                Rule::requiredIf(! $hasStoredToken && ! $hasEnvToken),
            ],
            'xconnect.timeout' => ['required', 'integer', 'min:5', 'max:180'],
            'xconnect.default_currency' => ['required', 'string', 'max:8'],
            'xconnect.default_nationality' => ['required', 'string', 'max:80'],
            'is_enabled' => ['nullable', 'boolean'],
        ], [], [
            'xconnect.base_url' => 'base URL',
            'xconnect.token' => 'API token',
        ]);

        $x = $request->input('xconnect', []);
        $updates = [
            'environment' => $x['environment'],
            'base_url' => XconnectClient::normalizeHostOnly((string) ($x['base_url'] ?? '')),
            'base_url_override' => XconnectClient::normalizeHostOnly((string) ($x['base_url_override'] ?? '')),
            'timeout' => (int) $x['timeout'],
            'default_currency' => strtoupper((string) $x['default_currency']),
            'default_nationality' => (string) $x['default_nationality'],
        ];

        if ($request->filled('xconnect.token')) {
            $updates['token'] = $x['token'];
        }

        $catalogName = $this->catalog()[Integration::SLUG_XCONNECT]['name'] ?? 'Xconnect Hotel API';

        Integration::query()->updateOrCreate(
            ['slug' => Integration::SLUG_XCONNECT],
            [
                'name' => $catalogName,
                'is_enabled' => $request->boolean('is_enabled', true),
                'payload' => array_merge($prev, $updates),
            ]
        );

        return redirect()
            ->route('admin.integrations.edit', ['slug' => Integration::SLUG_XCONNECT])
            ->with('success', 'Xconnect integration settings saved. They are stored encrypted in the `integrations` table.');
    }

    private function updateDowntownTravel(Request $request)
    {
        $existing = Integration::query()
            ->where('slug', Integration::SLUG_DOWNTOWN_TRAVEL)
            ->first();
        $prev = is_array($existing?->payload) ? $existing->payload : [];

        $hasEnvClientSecret = (string) config('downtown_travel.client_secret', '') !== '';
        $hasDbClientSecret = (string) ($prev['client_secret'] ?? '') !== '';
        $hasEnvPassword = (string) config('downtown_travel.password', '') !== '';
        $hasDbPassword = (string) ($prev['password'] ?? '') !== '';

        $request->validate([
            'downtown.environment' => ['required', Rule::in(['sandbox', 'production'])],
            'downtown.client_id' => ['required', 'string', 'max:255'],
            'downtown.client_secret' => [
                Rule::requiredIf(! $hasEnvClientSecret && ! $hasDbClientSecret),
                'nullable',
                'string',
                'max:512',
            ],
            'downtown.username' => ['required', 'string', 'max:255'],
            'downtown.password' => [
                Rule::requiredIf(! $hasEnvPassword && ! $hasDbPassword),
                'nullable',
                'string',
                'max:512',
            ],
            'downtown.timeout' => ['required', 'integer', 'min:5', 'max:120'],
            'downtown.sso_base_url_override' => ['nullable', 'string', 'max:512'],
            'downtown.air_base_url_override' => ['nullable', 'string', 'max:512'],
        ], [], [
            'downtown.environment' => 'environment',
            'downtown.client_id' => 'client ID',
            'downtown.client_secret' => 'client secret',
            'downtown.username' => 'username',
            'downtown.password' => 'password',
            'downtown.timeout' => 'timeout',
            'downtown.sso_base_url_override' => 'SSO base URL override',
            'downtown.air_base_url_override' => 'Air base URL override',
        ]);

        $d = $request->input('downtown', []);
        $updates = [
            'environment' => (string) ($d['environment'] ?? 'sandbox'),
            'client_id' => (string) ($d['client_id'] ?? ''),
            'username' => (string) ($d['username'] ?? ''),
            'timeout' => (int) ($d['timeout'] ?? 60),
            'sso_base_url_override' => DowntownTravelClient::normalizeHostOnly((string) ($d['sso_base_url_override'] ?? '')),
            'air_base_url_override' => DowntownTravelClient::normalizeHostOnly((string) ($d['air_base_url_override'] ?? '')),
        ];

        if ($request->filled('downtown.client_secret')) {
            $updates['client_secret'] = (string) $request->input('downtown.client_secret');
        } elseif ($hasDbClientSecret) {
            $updates['client_secret'] = (string) $prev['client_secret'];
        }

        if ($request->filled('downtown.password')) {
            $updates['password'] = (string) $request->input('downtown.password');
        } elseif ($hasDbPassword) {
            $updates['password'] = (string) $prev['password'];
        }

        $catalogName = $this->catalog()[Integration::SLUG_DOWNTOWN_TRAVEL]['name'] ?? 'Downtown Travel Air API';

        Integration::query()->updateOrCreate(
            ['slug' => Integration::SLUG_DOWNTOWN_TRAVEL],
            [
                'name' => $catalogName,
                'is_enabled' => $request->boolean('is_enabled'),
                'payload' => array_merge($prev, $updates),
            ]
        );

        DowntownTravelClient::clearTokenCache();

        return redirect()
            ->route('admin.integrations.edit', ['slug' => Integration::SLUG_DOWNTOWN_TRAVEL])
            ->with('success', 'Downtown Travel integration settings saved. They are stored encrypted in the `integrations` table.');
    }

    private function updateDowntownTravelHotels(Request $request)
    {
        $existing = Integration::query()
            ->where('slug', Integration::SLUG_DOWNTOWN_TRAVEL_HOTELS)
            ->first();
        $prev = is_array($existing?->payload) ? $existing->payload : [];

        $hasEnvClientSecret = (string) config('downtown_travel_hotels.client_secret', '') !== '';
        $hasDbClientSecret = (string) ($prev['client_secret'] ?? '') !== '';
        $hasEnvPassword = (string) config('downtown_travel_hotels.password', '') !== '';
        $hasDbPassword = (string) ($prev['password'] ?? '') !== '';

        $request->validate([
            'downtown.environment' => ['required', Rule::in(['sandbox', 'production'])],
            'downtown.client_id' => ['required', 'string', 'max:255'],
            'downtown.client_secret' => [
                Rule::requiredIf(! $hasEnvClientSecret && ! $hasDbClientSecret),
                'nullable',
                'string',
                'max:512',
            ],
            'downtown.username' => ['required', 'string', 'max:255'],
            'downtown.password' => [
                Rule::requiredIf(! $hasEnvPassword && ! $hasDbPassword),
                'nullable',
                'string',
                'max:512',
            ],
            'downtown.timeout' => ['required', 'integer', 'min:5', 'max:120'],
            'downtown.sso_base_url_override' => ['nullable', 'string', 'max:512'],
            'downtown.hotels_base_url_override' => ['nullable', 'string', 'max:512'],
        ], [], [
            'downtown.environment' => 'environment',
            'downtown.client_id' => 'client ID',
            'downtown.client_secret' => 'client secret',
            'downtown.username' => 'username',
            'downtown.password' => 'password',
            'downtown.timeout' => 'timeout',
            'downtown.sso_base_url_override' => 'SSO base URL override',
            'downtown.hotels_base_url_override' => 'Hotels base URL override',
        ]);

        $d = $request->input('downtown', []);
        $updates = [
            'environment' => (string) ($d['environment'] ?? 'sandbox'),
            'client_id' => (string) ($d['client_id'] ?? ''),
            'username' => (string) ($d['username'] ?? ''),
            'timeout' => (int) ($d['timeout'] ?? 90),
            'sso_base_url_override' => DowntownTravelHotelsClient::normalizeHostOnly((string) ($d['sso_base_url_override'] ?? '')),
            'hotels_base_url_override' => DowntownTravelHotelsClient::normalizeHostOnly((string) ($d['hotels_base_url_override'] ?? '')),
        ];

        if ($request->filled('downtown.client_secret')) {
            $updates['client_secret'] = (string) $request->input('downtown.client_secret');
        } elseif ($hasDbClientSecret) {
            $updates['client_secret'] = (string) $prev['client_secret'];
        }

        if ($request->filled('downtown.password')) {
            $updates['password'] = (string) $request->input('downtown.password');
        } elseif ($hasDbPassword) {
            $updates['password'] = (string) $prev['password'];
        }

        $catalogName = $this->catalog()[Integration::SLUG_DOWNTOWN_TRAVEL_HOTELS]['name'] ?? 'Downtown Travel Hotels API';

        Integration::query()->updateOrCreate(
            ['slug' => Integration::SLUG_DOWNTOWN_TRAVEL_HOTELS],
            [
                'name' => $catalogName,
                'is_enabled' => $request->boolean('is_enabled'),
                'payload' => array_merge($prev, $updates),
            ]
        );

        DowntownTravelHotelsClient::clearTokenCache();

        return redirect()
            ->route('admin.integrations.edit', ['slug' => Integration::SLUG_DOWNTOWN_TRAVEL_HOTELS])
            ->with('success', 'Downtown Travel Hotels integration settings saved. They are stored encrypted in the `integrations` table.');
    }

    public function ping(Request $request, string $slug, TravelportSystemService $system, SunSpringClient $sunspring, XconnectClient $xconnect, DowntownTravelClient $downtown, DowntownTravelHotelsClient $downtownHotels)
    {
        $this->ensureSuperAdmin();
        $this->assertEditableSlug($slug);

        if ($slug === Integration::SLUG_TRAVELPORT) {
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

        if ($slug === Integration::SLUG_SUNSPRING) {
            $result = $sunspring->ping();

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            $flash = $result['ok'] ? 'success' : 'error';

            return redirect()
                ->route('admin.integrations.edit', ['slug' => $slug])
                ->with($flash, $result['message'])
                ->with('sunspring_ping', $result);
        }

        if ($slug === Integration::SLUG_XCONNECT) {
            $result = $xconnect->ping();

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            $flash = $result['ok'] ? 'success' : 'error';

            return redirect()
                ->route('admin.integrations.edit', ['slug' => $slug])
                ->with($flash, $result['message'])
                ->with('xconnect_ping', $result);
        }

        if ($slug === Integration::SLUG_DOWNTOWN_TRAVEL) {
            $result = $downtown->ping();

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            $flash = $result['ok'] ? 'success' : 'error';

            return redirect()
                ->route('admin.integrations.edit', ['slug' => $slug])
                ->with($flash, $result['message'])
                ->with('downtown_ping', $result);
        }

        if ($slug === Integration::SLUG_DOWNTOWN_TRAVEL_HOTELS) {
            $result = $downtownHotels->ping();

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            $flash = $result['ok'] ? 'success' : 'error';

            return redirect()
                ->route('admin.integrations.edit', ['slug' => $slug])
                ->with($flash, $result['message'])
                ->with('downtown_hotels_ping', $result);
        }

        abort(404);
    }

    public function testSearch(Request $request, string $slug, TravelportAirService $air, SunSpringAirService $sunspring, XconnectHotelService $xconnectHotels, DowntownTravelClient $downtown, DowntownTravelHotelsClient $downtownHotels)
    {
        $this->ensureSuperAdmin();
        $this->assertEditableSlug($slug);

        if ($slug === Integration::SLUG_TRAVELPORT) {
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

        if ($slug === Integration::SLUG_SUNSPRING) {
            $result = $sunspring->lowFareSearch([
                'origin' => strtoupper((string) $request->input('origin', 'THR')),
                'destination' => strtoupper((string) $request->input('destination', 'MHD')),
                'departure_date' => (string) $request->input('departure_date', now()->addDays(14)->format('Y-m-d')),
                'return_date' => $request->input('return_date'),
                'adults' => (int) $request->input('adults', 1),
                'trip_type' => 'oneway',
            ]);

            $flash = $result['ok'] ? 'success' : 'error';

            return redirect()
                ->route('admin.integrations.edit', ['slug' => $slug])
                ->with($flash, $result['message'])
                ->with('sunspring_lfs', $result);
        }

        if ($slug === Integration::SLUG_XCONNECT) {
            $result = $xconnectHotels->searchAvailability([
                'city_id' => (string) $request->input('city_id', ''),
                'check_in' => (string) $request->input('check_in', now()->addMonths(2)->format('Y-m-d')),
                'check_out' => (string) $request->input('check_out', now()->addMonths(2)->addDay()->format('Y-m-d')),
                'adults' => 2,
                'nationality' => (string) $request->input('nationality', config('xconnect.default_nationality')),
            ]);

            $flash = $result['ok'] ? 'success' : 'error';

            return redirect()
                ->route('admin.integrations.edit', ['slug' => $slug])
                ->with($flash, $result['message'])
                ->with('xconnect_availability', [
                    'ok' => $result['ok'],
                    'message' => $result['message'],
                    'count' => count($result['solutions'] ?? []),
                    'search_key' => $result['search_key'] ?? null,
                    'sample' => array_slice($result['solutions'] ?? [], 0, 3),
                ]);
        }

        if ($slug === Integration::SLUG_DOWNTOWN_TRAVEL) {
            $result = $downtown->testSearch([
                'origin' => strtoupper((string) $request->input('origin', 'NYC')),
                'destination' => strtoupper((string) $request->input('destination', 'ZRH')),
                'departure_date' => (string) $request->input('departure_date', now()->addDays(21)->format('Y-m-d')),
                'adults' => (int) $request->input('adults', 1),
            ]);

            $flash = $result['ok'] ? 'success' : 'error';

            return redirect()
                ->route('admin.integrations.edit', ['slug' => $slug])
                ->with($flash, $result['message'])
                ->with('downtown_search', $result);
        }

        if ($slug === Integration::SLUG_DOWNTOWN_TRAVEL_HOTELS) {
            $result = $downtownHotels->testAvailability([
                'check_in' => (string) $request->input('check_in', now()->addMonths(2)->format('Y-m-d')),
                'check_out' => (string) $request->input('check_out', now()->addMonths(2)->addDays(2)->format('Y-m-d')),
                'latitude' => (float) $request->input('latitude', 51.50735),
                'longitude' => (float) $request->input('longitude', -0.12776),
                'radius' => (int) $request->input('radius', 30000),
                'adults' => (int) $request->input('adults', 1),
            ]);

            $flash = $result['ok'] ? 'success' : 'error';

            return redirect()
                ->route('admin.integrations.edit', ['slug' => $slug])
                ->with($flash, $result['message'])
                ->with('downtown_hotels_availability', $result);
        }

        abort(404);
    }
}
