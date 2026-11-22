<?php

namespace App\Http\Controllers;

use App\Actions\Enrollment\CreateSite;
use App\Http\Requests\Enrollment\StoreSiteRequest;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    /**
     * List sites and show the site form.
     */
    public function index(): Response
    {
        $sites = Site::query()
            ->latest()
            ->get()
            ->map(fn (Site $site) => [
                'id' => $site->id,
                'name' => $site->name,
                'latitude' => $site->latitude,
                'longitude' => $site->longitude,
                'radius_meters' => $site->radius_meters,
            ]);

        return Inertia::render('sites/index', [
            'sites' => $sites,
        ]);
    }

    /**
     * Create a site. A missing radius becomes the default of 100 meters.
     */
    public function store(StoreSiteRequest $request, CreateSite $createSite): RedirectResponse
    {
        $createSite->handle(
            name: $request->validated('name'),
            latitude: (float) $request->validated('latitude'),
            longitude: (float) $request->validated('longitude'),
            radiusMeters: $request->validated('radius_meters') !== null
                ? (int) $request->validated('radius_meters')
                : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Site created.')]);

        return to_route('sites.index');
    }
}
