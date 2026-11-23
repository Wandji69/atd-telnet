
import { Head, Link } from '@inertiajs/react';

type LandingProps = {
    title: string;
};

const steps = [
    {
        number: '01',
        title: 'Record attendance',
        description: 'Check-ins are recorded on the terminal.',
    },
    {
        number: '02',
        title: 'Stay operational',
        description: 'Events remain on the device while offline.',
    },
    {
        number: '03',
        title: 'Sync automatically',
        description: 'Managers see events when connectivity returns.',
    },
];

export default function Landing({ title }: LandingProps) {
    return (
        <>
            <Head title={title} />

            <div className="relative isolate min-h-screen overflow-hidden bg-[#f7f9fe] text-slate-900">
                {/* Soft background decorations */}
                <div
                    aria-hidden="true"
                    className="pointer-events-none absolute -right-48 -top-48 h-[600px] w-[600px] rounded-full bg-blue-200/50 blur-3xl"
                />
                <div
                    aria-hidden="true"
                    className="pointer-events-none absolute -bottom-48 -left-40 h-[450px] w-[450px] rounded-full bg-indigo-100/70 blur-3xl"
                />

                {/* Navigation */}
                <header className="relative z-10 border-b border-slate-200/70 bg-white/75 backdrop-blur-lg">
                    <nav className="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-10">
                        <div className="flex items-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-700 text-lg font-black text-white shadow-lg shadow-blue-700/20">
                                T
                            </div>

                            <div className="flex flex-col">
                                <span className="text-lg font-extrabold tracking-tight text-slate-950">
                                    TELNET
                                </span>
                                <span className="text-xs font-medium text-slate-500">
                                    Intern Attendance
                                </span>
                            </div>
                        </div>

                        <Link
                            href="/login"
                            className="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-blue-300 hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                        >
                            Manager sign-in
                        </Link>
                    </nav>
                </header>

                {/* Main hero */}
                <main className="relative z-10 mx-auto grid min-h-[calc(100vh-170px)] max-w-7xl items-center gap-16 px-6 py-20 lg:grid-cols-2 lg:px-10 lg:py-28">
                    {/* Left content */}
                    <section className="max-w-2xl">
                        <div className="mb-8 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-bold tracking-wide text-blue-700">
                            <span className="h-2 w-2 rounded-full bg-blue-600" />
                            OFFLINE-FIRST ATTENDANCE
                        </div>

                        <h1 className="mb-7 text-5xl font-extrabold leading-[1.08] tracking-tight text-slate-950 sm:text-6xl lg:text-[4.25rem]">
                            {title}
                        </h1>

                        <p className="mb-6 max-w-xl text-xl font-semibold leading-relaxed text-slate-700">
                            Attendance for interns that keeps working through power cuts and network outages.
                        </p>

                        <p className="mb-10 max-w-xl text-base leading-8 text-slate-600">
                            Terminals record check-ins on the device and sync them when a connection returns. Managers see those events here, including the last time each terminal synced. A missing card uses a PIN and a photo. A remote check-in must fall inside the intern's assigned site.
                        </p>

                        <Link
                            href="/login"
                            className="inline-flex items-center justify-center gap-3 rounded-xl bg-blue-700 px-7 py-4 text-base font-semibold text-white shadow-xl shadow-blue-700/20 transition duration-200 hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-2xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-700"
                        >
                            Manager sign-in

                            <svg
                                aria-hidden="true"
                                width="19"
                                height="19"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="2"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            >
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </section>

                    {/* Decorative offline attendance workflow */}
                    <div
                        aria-label="Attendance workflow: record, store offline, then sync"
                        className="relative mx-auto w-full max-w-lg"
                    >
                        <div
                            aria-hidden="true"
                            className="absolute -inset-5 rounded-[40px] bg-gradient-to-br from-blue-200/60 to-indigo-100/50 blur-2xl"
                        />

                        <div className="relative overflow-hidden rounded-[28px] border border-white bg-white/95 p-6 shadow-[0_30px_90px_-25px_rgba(30,64,175,0.22)] sm:p-8">
                            <div className="mb-8 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">
                                        How it works
                                    </p>
                                    <h2 className="mt-2 text-2xl font-bold tracking-tight text-slate-950">
                                        Attendance flow
                                    </h2>
                                </div>

                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                                    <svg
                                        aria-hidden="true"
                                        width="25"
                                        height="25"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="1.8"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    >
                                        <rect x="3" y="3" width="18" height="18" rx="3" />
                                        <path d="M7 9h10M7 13h7M7 17h4" />
                                    </svg>
                                </div>
                            </div>

                            <div className="space-y-4">
                                {steps.map((step, index) => (
                                    <div
                                        key={step.number}
                                        className="relative flex items-start gap-4 rounded-2xl border border-slate-100 bg-slate-50/80 p-5 transition-colors hover:border-blue-200 hover:bg-blue-50/50"
                                    >
                                        <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-700 text-sm font-bold text-white">
                                            {step.number}
                                        </div>

                                        <div className="min-w-0">
                                            <h3 className="font-bold text-slate-900">
                                                {step.title}
                                            </h3>
                                            <p className="mt-1 text-sm leading-6 text-slate-500">
                                                {step.description}
                                            </p>
                                        </div>

                                        {index < steps.length - 1 && (
                                            <div
                                                aria-hidden="true"
                                                className="absolute -bottom-4 left-[41px] z-10 h-4 border-l-2 border-dashed border-blue-300"
                                            />
                                        )}
                                    </div>
                                ))}
                            </div>

                            <div className="mt-7 flex items-center gap-3 rounded-xl border border-blue-100 bg-blue-50 p-4">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-700 text-white">
                                    <svg
                                        aria-hidden="true"
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="2"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    >
                                        <path d="m5 12 4 4L19 6" />
                                    </svg>
                                </div>

                                <span className="text-sm font-medium leading-6 text-blue-900">
                                    Attendance continuity, even when connectivity is interrupted.
                                </span>
                            </div>
                        </div>
                    </div>
                </main>

                {/* Footer */}
                <footer className="relative z-10 border-t border-slate-200/70 bg-white/70">
                    <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-6 py-6 text-sm text-slate-500 sm:flex-row lg:px-10">
                        <span className="font-semibold text-slate-700">
                            TELNET · Intern Attendance
                        </span>
                        <span>Offline-first attendance management</span>
                    </div>
                </footer>
            </div>
        </>
    );
}
