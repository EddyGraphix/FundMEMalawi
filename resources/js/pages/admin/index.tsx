import { Head } from '@inertiajs/react';

interface Stat {
    total_campaigns: number;
    pending_campaigns: number;
    total_users: number;
    total_donations: number;
    total_raised: number;
}

type Props = {
    stats: Stat;
};

export default function AdminDashboard({ stats }: Props) {
    return (
        <>
            <Head title="Admin Dashboard" />
            <div className="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                <h1 className="font-display text-display-lg text-foreground">Admin Dashboard</h1>

                <div className="mt-6 grid gap-4 md:grid-cols-5">
                    <div className="border-3 border-foreground bg-card p-5 shadow-hard">
                        <p className="text-body-sm text-muted-foreground">Total campaigns</p>
                        <p className="mt-1 font-display text-display-lg text-foreground">{stats.total_campaigns}</p>
                    </div>
                    <div className="border-3 border-foreground bg-card p-5 shadow-hard">
                        <p className="text-body-sm text-muted-foreground">Pending review</p>
                        <p className="mt-1 font-display text-display-lg text-yellow-500">{stats.pending_campaigns}</p>
                    </div>
                    <div className="border-3 border-foreground bg-card p-5 shadow-hard">
                        <p className="text-body-sm text-muted-foreground">Users</p>
                        <p className="mt-1 font-display text-display-lg text-foreground">{stats.total_users}</p>
                    </div>
                    <div className="border-3 border-foreground bg-card p-5 shadow-hard">
                        <p className="text-body-sm text-muted-foreground">Completed donations</p>
                        <p className="mt-1 font-display text-display-lg text-foreground">{stats.total_donations}</p>
                    </div>
                    <div className="border-3 border-foreground bg-card p-5 shadow-hard">
                        <p className="text-body-sm text-muted-foreground">Total raised</p>
                        <p className="mt-1 font-display text-display-lg text-brand">
                            MK {Math.floor(stats.total_raised).toLocaleString()}
                        </p>
                    </div>
                </div>

                <p className="mt-8 text-body-base text-muted-foreground">
                    Use the sidebar to navigate between management sections.
                </p>
            </div>
        </>
    );
}
