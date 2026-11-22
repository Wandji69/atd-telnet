<?php

namespace App\Http\Controllers;

use App\Actions\Enrollment\CreateIntern;
use App\Http\Requests\Enrollment\StoreInternRequest;
use App\Models\Intern;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InternController extends Controller
{
    /**
     * List interns and show the enrollment form.
     *
     * Only safe fields are sent to the browser. The PIN hash and the photo
     * key stay on the server.
     */
    public function index(): Response
    {
        $interns = Intern::query()
            ->with('assignments.site')
            ->latest()
            ->get()
            ->map(fn (Intern $intern) => [
                'id' => $intern->id,
                'name' => $intern->name,
                'has_reference_photo' => $intern->reference_photo_key !== null,
                'sites' => $intern->assignments
                    ->map(fn ($assignment) => $assignment->site?->name)
                    ->filter()
                    ->values(),
            ]);

        return Inertia::render('interns/index', [
            'interns' => $interns,
        ]);
    }

    /**
     * Enroll a new intern.
     */
    public function store(StoreInternRequest $request, CreateIntern $createIntern): RedirectResponse
    {
        $createIntern->handle(
            name: $request->validated('name'),
            pin: $request->validated('pin'),
            referencePhoto: $request->file('reference_photo'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Intern enrolled.')]);

        return to_route('interns.index');
    }
}
