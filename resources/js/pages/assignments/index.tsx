import { Form, Head } from '@inertiajs/react';
import AssignmentController from '@/actions/App/Http/Controllers/AssignmentController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index } from '@/routes/assignments';

type Option = { id: string; name: string };

type AssignmentRow = {
    id: string;
    intern: string | null;
    site: string | null;
};

const selectClass =
    'border-input bg-background flex h-9 w-full rounded-md border px-3 py-1 text-base shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm';

export default function Assignments({
    assignments,
    interns,
    sites,
}: {
    assignments: AssignmentRow[];
    interns: Option[];
    sites: Option[];
}) {
    const canAssign = interns.length > 0 && sites.length > 0;

    return (
        <>
            <Head title="Assignments" />

            <div className="flex h-full flex-1 flex-col gap-10 p-4">
                <section className="max-w-xl space-y-6">
                    <Heading
                        variant="small"
                        title="Assign an intern to a site"
                        description="Each assignment links one intern to one site."
                    />

                    {!canAssign && (
                        <p className="text-sm text-muted-foreground">
                            Enroll at least one intern and create at least one
                            site first.
                        </p>
                    )}

                    <Form
                        {...AssignmentController.store.form()}
                        resetOnSuccess
                        options={{ preserveScroll: true }}
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="intern_id">Intern</Label>
                                    <select
                                        id="intern_id"
                                        name="intern_id"
                                        required
                                        defaultValue=""
                                        className={selectClass}
                                    >
                                        <option value="" disabled>
                                            Choose an intern
                                        </option>
                                        {interns.map((intern) => (
                                            <option
                                                key={intern.id}
                                                value={intern.id}
                                            >
                                                {intern.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.intern_id} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="site_id">Site</Label>
                                    <select
                                        id="site_id"
                                        name="site_id"
                                        required
                                        defaultValue=""
                                        className={selectClass}
                                    >
                                        <option value="" disabled>
                                            Choose a site
                                        </option>
                                        {sites.map((site) => (
                                            <option
                                                key={site.id}
                                                value={site.id}
                                            >
                                                {site.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.site_id} />
                                </div>

                                <Button
                                    type="submit"
                                    disabled={processing || !canAssign}
                                    data-test="create-assignment-button"
                                >
                                    {processing && <Spinner />}
                                    Assign
                                </Button>
                            </>
                        )}
                    </Form>
                </section>

                <section className="space-y-4">
                    <Heading
                        variant="small"
                        title="Assignments"
                        description={`${assignments.length} in total`}
                    />

                    {assignments.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No assignments yet.
                        </p>
                    ) : (
                        <div className="overflow-x-auto rounded-lg border">
                            <table
                                className="w-full text-left text-sm"
                                data-test="assignments-table"
                            >
                                <thead className="bg-muted/50 text-muted-foreground">
                                    <tr>
                                        <th className="px-4 py-2 font-medium">
                                            Intern
                                        </th>
                                        <th className="px-4 py-2 font-medium">
                                            Site
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {assignments.map((assignment) => (
                                        <tr
                                            key={assignment.id}
                                            className="border-t"
                                        >
                                            <td className="px-4 py-2">
                                                {assignment.intern ?? '—'}
                                            </td>
                                            <td className="px-4 py-2">
                                                {assignment.site ?? '—'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}

Assignments.layout = {
    breadcrumbs: [
        {
            title: 'Assignments',
            href: index(),
        },
    ],
};
