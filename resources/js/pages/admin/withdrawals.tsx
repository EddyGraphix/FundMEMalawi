import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { approve as approveRoute, reject as rejectRoute } from '@/routes/admin/withdrawals';

interface Withdrawal {
    id: number;
    amount: number;
    method: string;
    status: string;
    provider_ref: string | null;
    admin_notes: string | null;
    created_at: string;
    campaign: { id: number; title: string };
    requester: { id: number; name: string };
}

type Props = {
    withdrawals: { data: Withdrawal[]; meta: any };
};

export default function AdminWithdrawals({ withdrawals }: Props) {
    const [rejectId, setRejectId] = useState<number | null>(null);
    const { post, processing } = useForm({});

    const { data, setData, errors } = useForm({ reason: '' });

    function handleReject(id: number) {
        post(rejectRoute.url({ withdrawal: id }), {
            data: { reason: data.reason },
            onSuccess: () => { setRejectId(null); setData('reason', ''); },
        });
    }

    return (
        <>
            <Head title="Withdrawals" />
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <h1 className="font-display text-display-lg text-foreground">Withdrawals</h1>

                <div className="mt-8 flex flex-col gap-4">
                    {withdrawals.data.length === 0 ? (
                        <div className="border-3 border-foreground bg-card p-8 text-center shadow-hard">
                            <p className="text-body-lg text-muted-foreground">No withdrawal requests yet.</p>
                        </div>
                    ) : (
                        withdrawals.data.map((w) => (
                            <div key={w.id} className="border-3 border-foreground bg-card p-5 shadow-hard">
                                <div className="flex items-start justify-between">
                                    <div className="flex-1">
                                        <div className="flex items-center gap-3">
                                            <h3 className="font-display text-display-sm text-foreground">
                                                <Link href={`/admin/campaigns`} className="hover:text-brand">
                                                    {w.campaign.title}
                                                </Link>
                                            </h3>
                                            <span className={`rounded-none px-2 py-0.5 text-body-xs font-semibold text-white ${
                                                w.status === 'paid' ? 'bg-brand' :
                                                w.status === 'approved' ? 'bg-blue-500' :
                                                w.status === 'rejected' ? 'bg-red-500' :
                                                'bg-yellow-500'
                                            }`}>
                                                {w.status}
                                            </span>
                                        </div>
                                        <div className="mt-2 flex gap-6 text-body-sm text-muted-foreground">
                                            <span>MK {Math.floor(w.amount).toLocaleString()}</span>
                                            <span>by {w.requester.name}</span>
                                            <span>{w.method}</span>
                                            <span>{new Date(w.created_at).toLocaleDateString()}</span>
                                        </div>
                                    </div>
                                    <div className="flex gap-2">
                                        <Link
                                            href={approveRoute.url({ withdrawal: w.id })}
                                            method="post"
                                            as="button"
                                            className="border-3 border-foreground bg-brand px-4 py-2 text-body-sm font-semibold text-brand-foreground shadow-hard hover:opacity-90"
                                        >
                                            Approve
                                        </Link>
                                        <button
                                            onClick={() => setRejectId(rejectId === w.id ? null : w.id)}
                                            className="border-3 border-foreground bg-foreground px-4 py-2 text-body-sm font-semibold text-background shadow-hard hover:opacity-90"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                </div>
                                {rejectId === w.id && (
                                    <div className="mt-4 border-t-3 border-foreground pt-4">
                                        <p className="text-body-sm font-semibold text-foreground">Reason for rejection</p>
                                        <textarea
                                            className="mt-2 w-full border-3 border-foreground bg-background p-3 text-body-sm"
                                            rows={3}
                                            value={data.reason}
                                            onChange={(e) => setData('reason', e.target.value)}
                                            placeholder="Explain why this withdrawal is being rejected..."
                                        />
                                        {errors.reason && <p className="mt-1 text-body-xs text-red-500">{errors.reason}</p>}
                                        <div className="mt-3 flex gap-2">
                                            <Button variant="fundme" onClick={() => handleReject(w.id)} disabled={processing || !data.reason}>
                                                Confirm rejection
                                            </Button>
                                            <Button variant="outline" onClick={() => setRejectId(null)}>Cancel</Button>
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))
                    )}
                </div>
            </div>
        </>
    );
}
