import { Form, Head } from '@inertiajs/react';
import InternController from '@/actions/App/Http/Controllers/InternController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index } from '@/routes/interns';

type InternRow = {
    id: string;
    name: string;
    has_reference_photo: boolean;
    sites: string[];
};

export default function Interns({ interns }: { interns: InternRow[] }) {
    return (
        <>
            <Head title="Interns" />

            <div className="flex h-full flex-1 flex-col gap-10 p-4">
                <section className="max-w-xl space-y-6">
                    <Heading
                        variant="small"
                        title="Enroll an intern"
                        description="Use synthetic data only. The PIN is stored as a hash and cannot be read back."
                    />

                    <Form
                        {...InternController.store.form()}
                        resetOnSuccess
                        options={{ preserveScroll: true }}
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        required
                                        autoComplete="off"
                                        placeholder="Full name"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="pin">6-digit PIN</Label>
                                    <PasswordInput
                                        id="pin"
                                        name="pin"
                                        required
                                        inputMode="numeric"
                                        pattern="[0-9]{6}"
                                        maxLength={6}
                                        autoComplete="off"
                                        placeholder="123456"
                                    />
                                    <InputError message={errors.pin} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="reference_photo">
                                        Reference photo
                                    </Label>
                                    <Input
                                        id="reference_photo"
                                        name="reference_photo"
                                        type="file"
                                        required
                                        accept="image/png,image/jpeg"
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        JPG or PNG, up to 2 MB.
                                    </p>
                                    <InputError
                                        message={errors.reference_photo}
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="enroll-intern-button"
                                >
                                    {processing && <Spinner />}
                                    Enroll intern
                                </Button>
                            </>
                        )}
                    </Form>
                </section>

                <section className="space-y-4">
                    <Heading
                        variant="small"
                        title="Enrolled interns"
                        description={`${interns.length} in total`}
                    />

                    {interns.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No interns yet.
                        </p>
                    ) : (
                        <div className="overflow-x-auto rounded-lg border">
                            <table
                                className="w-full text-left text-sm"
                                data-test="interns-table"
                            >
                                <thead className="bg-muted/50 text-muted-foreground">
                                    <tr>
                                        <th className="px-4 py-2 font-medium">
                                            Name
                                        </th>
                                        <th className="px-4 py-2 font-medium">
                                            Reference photo
                                        </th>
                                        <th className="px-4 py-2 font-medium">
                                            Assigned site
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {interns.map((intern) => (
                                        <tr
                                            key={intern.id}
                                            className="border-t"
                                        >
                                            <td className="px-4 py-2">
                                                {intern.name}
                                            </td>
                                            <td className="px-4 py-2">
                                                {intern.has_reference_photo
                                                    ? 'Stored'
                                                    : 'Missing'}
                                            </td>
                                            <td className="px-4 py-2">
                                                {intern.sites.length > 0
                                                    ? intern.sites.join(', ')
                                                    : 'Not assigned'}
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

Interns.layout = {
    breadcrumbs: [
        {
            title: 'Interns',
            href: index(),
        },
    ],
};
