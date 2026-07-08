import { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { approve, reject } from '@/routes/admin/verifications';

interface Verification {
    id: number;
    type: string;
    status: string;
    admin_notes?: string | null;
    reviewer?: { id: number; name: string } | null;
    reviewed_at?: string | null;
    user: { id: number; name: string; email: string };
    created_at: string;
}

type Props = {
    verifications: {
        data: Verification[];
        meta: any;
    };
    stats: {
        pending: number;
        approved: number;
        rejected: number;
    };
    statusFilter: string;
};

export default function AdminVerifications({ verifications, stats, statusFilter }: Props) {
    const { data, setData, post, processing } = useForm({ notes: '' });
    const [rejectingId, setRejectingId] = useState<number | null>(null);
    const [filter, setFilter] = useState(statusFilter);

    function handleApprove(verification: Verification) {
        post(approve.url({ verification: verification.id }));
    }

    function handleReject(verification: Verification) {
        post(reject.url({ verification: verification.id }), {
            onSuccess: () => { setRejectingId(null); setData('notes', ''); },
        });
    }

    function handleFilterChange(value: string) {
        setFilter(value);
        router.get('/admin/verifications', value === 'all' ? {} : { status: value }, {
            preserveState: true,
            replace: true,
        });
    }

    return (
        <>
            <Head title="Verification Review" />
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="flex items-end justify-between gap-4">
                    <div>
                        <h1 className="font-display text-display-lg text-foreground">Verification Review</h1>
                        <p className="mt-2 text-body-sm text-muted-foreground">
                            Review submitted identity documents and approve or reject them with clear notes.
                        </p>
                    </div>
                    <div className="rounded-none border-3 border-foreground bg-card px-4 py-3 text-body-sm shadow-hard">
                        {stats.pending} pending review
                    </div>
                </div>

                <div className="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-none border-3 border-foreground bg-card p-4 shadow-hard">
                    <div className="text-body-sm text-muted-foreground">
                        Filter by review state to keep the queue focused.
                    </div>
                    <select
                        value={filter}
                        onChange={(event) => handleFilterChange(event.target.value)}
                        className="rounded-none border-2 border-foreground bg-background px-3 py-2 text-body-sm"
                    >
                        <option value="all">All requests</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div className="mt-6 grid gap-3 md:grid-cols-3">
                    <div className="border-3 border-foreground bg-card p-4 shadow-hard">
                        <p className="text-body-sm text-muted-foreground">Pending</p>
                        <p className="mt-1 font-display text-display-sm text-foreground">{stats.pending}</p>
                    </div>
                    <div className="border-3 border-foreground bg-card p-4 shadow-hard">
                        <p className="text-body-sm text-muted-foreground">Approved</p>
                        <p className="mt-1 font-display text-display-sm text-foreground">{stats.approved}</p>
                    </div>
                    <div className="border-3 border-foreground bg-card p-4 shadow-hard">
                        <p className="text-body-sm text-muted-foreground">Rejected</p>
                        <p className="mt-1 font-display text-display-sm text-foreground">{stats.rejected}</p>
                    </div>
                </div>

                <div className="mt-8 flex flex-col gap-4">
                    {verifications.data.length === 0 ? (
                        <div className="border-3 border-foreground bg-card p-8 text-center shadow-hard">
                            <p className="text-body-lg text-muted-foreground">No verification requests.</p>
                        </div>
                    ) : (
                        verifications.data.map((verification) => {
                            const statusColor: Record<string, string> = {
                                pending: 'bg-yellow-500',
                                approved: 'bg-brand',
                                rejected: 'bg-red-500',
                            };

                            return (
                                <div key={verification.id} className="border-3 border-foreground bg-card p-5 shadow-hard">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <div className="flex items-center gap-3">
                                                <h3 className="font-display text-display-sm text-foreground">
                                                    {verification.user.name}
                                                </h3>
                                                <span className={`rounded-none px-2 py-0.5 text-body-xs font-semibold text-white ${statusColor[verification.status] ?? 'bg-muted'}`}>
                                                    {verification.status}
                                                </span>
                                            </div>
                                            <p className="mt-1 text-body-sm text-muted-foreground">
                                                {verification.user.email} &middot; {verification.type}
                                            </p>
                                            <p className="text-body-sm text-muted-foreground">
                                                Requested {new Date(verification.created_at).toLocaleDateString()}
                                            </p>
                                            {verification.status !== 'pending' && (
                                                <div className="mt-3 rounded-none border-2 border-border bg-background/70 p-3 text-body-sm text-muted-foreground">
                                                    <p className="font-medium text-foreground">Review outcome</p>
                                                    <p className="mt-1">
                                                        {verification.admin_notes || 'No notes were added for this review.'}
                                                    </p>
                                                    {verification.reviewer?.name && (
                                                        <p className="mt-2 text-body-xs">
                                                            Reviewed by {verification.reviewer.name}
                                                            {verification.reviewed_at ? ` • ${new Date(verification.reviewed_at).toLocaleDateString()}` : ''}
                                                        </p>
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                        <div className="flex gap-2">
                                            {verification.status === 'pending' ? (
                                                <>
                                                    <Button variant="fundme" size="sm" onClick={() => handleApprove(verification)} disabled={processing}>
                                                        Approve
                                                    </Button>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        className="border-3 border-red-500 text-red-500 hover:bg-red-500 hover:text-white"
                                                        onClick={() => setRejectingId(rejectingId === verification.id ? null : verification.id)}
                                                    >
                                                        Reject
                                                    </Button>
                                                </>
                                            ) : null}
                                        </div>
                                    </div>
                                    {rejectingId === verification.id && (
                                        <div className="mt-4 flex items-end gap-3 border-t-2 border-border pt-4">
                                            <div className="flex-1">
                                                <Label htmlFor="notes">Rejection notes</Label>
                                                <Input
                                                    id="notes"
                                                    value={data.notes}
                                                    onChange={(e) => setData('notes', e.target.value)}
                                                    placeholder="Explain why this verification is rejected..."
                                                />
                                            </div>
                                            <Button variant="fundme" size="sm" onClick={() => handleReject(verification)} disabled={processing || !data.notes}>
                                                Confirm reject
                                            </Button>
                                        </div>
                                    )}
                                </div>
                            );
                        })
                    )}
                </div>
            </div>
        </>
    );
}

