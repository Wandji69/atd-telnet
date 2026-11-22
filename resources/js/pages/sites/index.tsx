import { Form, Head } from '@inertiajs/react';
import SiteController from '@/actions/App/Http/Controllers/SiteController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index } from '@/routes/sites';

type SiteRow = {
    id: string;
    name: string;
    latitude: number;
    longitude: number;
    radius_meters: number;
};

export default function Sites({ sites }: { sites: SiteRow[] }) {
    return (
        <>
            <Head title="Sites" />

            <div className="flex h-full flex-1 flex-col gap-10 p-4">
                <section className="max-w-xl space-y-6">
                    <Heading
                        variant="small"
                        title="Create a site"
                        description="A site is a place with a circular boundary. Leave the radius empty to use 100 meters."
                    />

                    <Form
                        {...SiteController.store.form()}
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
                                        placeholder="Site name"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-6 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="latitude">
                                            Latitude
                                        </Label>
                                        <Input
                                            id="latitude"
                                            name="latitude"
                                            type="number"
                                            step="any"
                                            min={-90}
                                            max={90}
                                            required
                                            placeholder="4.1559"
                                        />
                                        <InputError message={errors.latitude} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="longitude">
                                            Longitude
                                        </Label>
                                        <Input
                                            id="longitude"
                                            name="longitude"
                                            type="number"
                                            step="any"
                                            min={-180}
                                            max={180}
                                            required
                                            placeholder="9.2417"
                                        />
                                        <InputError
                                            message={errors.longitude}
                                        />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="radius_meters">
                                        Radius (meters)
                                    </Label>
                                    <Input
                                        id="radius_meters"
                                        name="radius_meters"
                                        type="number"
                                        min={1}
                                        step={1}
                                        placeholder="100"
                                    />
                                    <InputError message={errors.radius_meters} />
                                </div>

                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="create-site-button"
                                >
                                    {processing && <Spinner />}
                                    Create site
                                </Button>
                            </>
                        )}
                    </Form>
                </section>

                <section className="space-y-4">
                    <Heading
                        variant="small"
                        title="Sites"
                        description={`${sites.length} in total`}
                    />

                    {sites.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No sites yet.
                        </p>
                    ) : (
                        <div className="overflow-x-auto rounded-lg border">
                            <table
                                className="w-full text-left text-sm"
                                data-test="sites-table"
                            >
                                <thead className="bg-muted/50 text-muted-foreground">
                                    <tr>
                                        <th className="px-4 py-2 font-medium">
                                            Name
                                        </th>
                                        <th className="px-4 py-2 font-medium">
                                            Latitude
                                        </th>
                                        <th className="px-4 py-2 font-medium">
                                            Longitude
                                        </th>
                                        <th className="px-4 py-2 font-medium">
                                            Radius
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {sites.map((site) => (
                                        <tr key={site.id} className="border-t">
                                            <td className="px-4 py-2">
                                                {site.name}
                                            </td>
                                            <td className="px-4 py-2">
                                                {site.latitude}
                                            </td>
                                            <td className="px-4 py-2">
                                                {site.longitude}
                                            </td>
                                            <td className="px-4 py-2">
                                                {site.radius_meters} m
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

Sites.layout = {
    breadcrumbs: [
        {
            title: 'Sites',
            href: index(),
        },
    ],
};
