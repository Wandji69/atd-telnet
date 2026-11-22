<?php

namespace App\Http\Controllers;

use App\Actions\Enrollment\CreateAssignment;
use App\Http\Requests\Enrollment\StoreAssignmentRequest;
use App\Models\Assignment;
use App\Models\Intern;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    /**
     * List assignments and show the form that links an intern to a site.
     */
    public function index(): Response
    {
        $assignments = Assignment::query()
            ->with(['intern', 'site'])
            ->latest()
            ->get()
            ->map(fn (Assignment $assignment) => [
                'id' => $assignment->id,
                'intern' => $assignment->intern?->name,
                'site' => $assignment->site?->name,
            ]);

        return Inertia::render('assignments/index', [
            'assignments' => $assignments,
            'interns' => Intern::query()->orderBy('name')->get(['id', 'name']),
            'sites' => Site::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Assign one intern to one site.
     */
    public function store(StoreAssignmentRequest $request, CreateAssignment $createAssignment): RedirectResponse
    {
        $createAssignment->handle(
            internId: $request->validated('intern_id'),
            siteId: $request->validated('site_id'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Intern assigned.')]);

        return to_route('assignments.index');
    }
}
