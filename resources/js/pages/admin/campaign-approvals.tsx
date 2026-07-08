import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { approve, reject } from '@/routes/admin/campaigns';
import { show } from '@/routes/campaigns';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Campaign {
    id: number;
    title: string;
    slug: string;
    status: string;
    goal_amount: number;
    raised_amount: number;
    owner: { id: number; name: string };
    category: { name: string };
    created_at: string;
}

type Props = {
    campaigns: {
        data: Campaign[];
        meta: any;
    };
};

function formatStatusLabel(status: string): string {
    return status.replace(/_/g, ' ');
}

export default function CampaignApprovals({ campaigns }: Props) {
    const { data, setData, post, processing } = useForm({ reason: '' });
    const [rejectingId, setRejectingId] = useState<number | null>(null);

    function handleApprove(campaign: Campaign) {
        post(approve.url({ campaign: campaign.id }));
    }

    function handleReject(campaign: Campaign) {
        post(reject.url({ campaign: campaign.id }), {
            onSuccess: () => { setRejectingId(null); setData('reason', ''); },
        });
    }

    return (
        <>
            <Head title="Campaign Approvals" />
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="flex items-end justify-between gap-4">
                    <div>
                        <h1 className="font-display text-display-lg text-foreground">Campaign Approvals</h1>
                        <p className="mt-2 text-body-sm text-muted-foreground">
                            Review new campaign submissions, flag risky content, and keep the platform safe.
                        </p>
                    </div>
                    <div className="rounded-none border-3 border-foreground bg-card px-4 py-3 text-body-sm shadow-hard">
                        {campaigns.data.length} pending review
                    </div>
                </div>

                <div className="mt-8 flex flex-col gap-4">
                    {campaigns.data.length === 0 ? (
                        <div className="border-3 border-foreground bg-card p-8 text-center shadow-hard">
                            <p className="text-body-lg text-muted-foreground">No campaigns awaiting review.</p>
                        </div>
                    ) : (
                        campaigns.data.map((campaign) => {
                            const statusColor: Record<string, string> = {
                                pending_review: 'bg-yellow-500',
                                active: 'bg-brand',
                                flagged: 'bg-red-500',
                            };

                            return (
                                <div key={campaign.id} className="border-3 border-foreground bg-card p-5 shadow-hard">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <div className="flex items-center gap-3">
                                                <h3 className="font-display text-display-sm text-foreground">
                                                    {campaign.title}
                                                </h3>
                                                <span className={`rounded-none px-2 py-0.5 text-body-xs font-semibold text-white ${statusColor[campaign.status] ?? 'bg-muted'}`}>
                                                    {formatStatusLabel(campaign.status)}
                                                </span>
                                            </div>
                                            <p className="mt-1 text-body-sm text-muted-foreground">
                                                by {campaign.owner.name} &middot; {campaign.category.name}
                                            </p>
                                            <p className="text-body-sm text-muted-foreground">
                                                MK {Math.floor(campaign.goal_amount).toLocaleString()} goal &middot; Created {new Date(campaign.created_at).toLocaleDateString()}
                                            </p>
                                            <p className="mt-1 text-body-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                                Raised {Math.floor(campaign.raised_amount).toLocaleString()} so far
                                            </p>
                                        </div>
                                        <div className="flex gap-2">
                                            <Link href={show(campaign.id)}>
                                                <Button variant="outline" size="sm">Preview</Button>
                                            </Link>
                                            {campaign.status === 'pending_review' || campaign.status === 'flagged' ? (
                                                <>
                                                    <Button variant="fundme" size="sm" onClick={() => handleApprove(campaign)} disabled={processing}>
                                                        Approve
                                                    </Button>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        className="border-3 border-red-500 text-red-500 hover:bg-red-500 hover:text-white"
                                                        onClick={() => setRejectingId(rejectingId === campaign.id ? null : campaign.id)}
                                                    >
                                                        Reject
                                                    </Button>
                                                </>
                                            ) : null}
                                        </div>
                                    </div>
                                    {rejectingId === campaign.id && (
                                        <div className="mt-4 flex items-end gap-3 border-t-2 border-border pt-4">
                                            <div className="flex-1">
                                                <Label htmlFor="reason">Reason for rejection</Label>
                                                <Input
                                                    id="reason"
                                                    value={data.reason}
                                                    onChange={(e) => setData('reason', e.target.value)}
                                                    placeholder="Explain why this campaign is rejected..."
                                                />
                                            </div>
                                            <Button variant="fundme" size="sm" onClick={() => handleReject(campaign)} disabled={processing || !data.reason}>
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

