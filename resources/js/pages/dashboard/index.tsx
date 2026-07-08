import { Head, Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { ProgressBar } from '@/components/progress-bar';
import { create, show } from '@/routes/campaigns';
import verify from '@/routes/verify';

interface Campaign {
    id: number;
    title: string;
    slug: string;
    status: string;
    goal_amount: number;
    raised_amount: number;
    donations_count: number;
    completed_donations_count: number;
    created_at: string;
}

type Props = {
    campaigns: Campaign[];
    totalRaised: number;
    totalWithdrawn: number;
    activeCampaignsCount: number;
    pendingCampaignsCount: number;
    completedDonationsCount: number;
    averageDonationAmount: number;
    completionRate: number;
    totalDonated: number;
    recentDonations: Array<{
        id: number;
        amount: number;
        status: string;
        created_at: string;
        donor_name?: string | null;
        campaign?: {
            id: number;
            title: string;
            slug: string;
        } | null;
    }>;
};

function formatMWK(amount: number): string {
    return amount.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

export default function Dashboard({ campaigns, totalRaised, totalWithdrawn, activeCampaignsCount, pendingCampaignsCount, completedDonationsCount, averageDonationAmount, completionRate, totalDonated, recentDonations }: Props) {
    const { auth } = usePage().props;
    const hasCampaigns = campaigns.length > 0;
    const hasDonations = totalDonated > 0;

    const verifBanner = (() => {
        const status = (auth.user as any)?.verification_status;
        if (status === 'approved') return null;
        if (status === 'pending') return {
            variant: 'info' as const,
            title: 'Verification pending',
            message: 'Your identity verification is under review.',
        };
        if (status === 'rejected') return {
            variant: 'error' as const,
            title: 'Verification rejected',
            message: 'Your identity verification was rejected.',
            action: { label: 'Re-submit', href: verify.identity.url() },
        };
        return {
            variant: 'warning' as const,
            title: 'Verify your identity',
            message: 'Verify your account to create campaigns and request withdrawals.',
            action: { label: 'Verify now', href: verify.identity.url() },
        };
    })();

    const statusColor: Record<string, string> = {
        pending_review: 'bg-yellow-500',
        active: 'bg-brand',
        rejected: 'bg-red-500',
        completed: 'bg-blue-500',
        flagged: 'bg-red-500',
    };

    return (
        <>
            <Head title="Dashboard" />
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                {verifBanner && (
                    <div className={`mb-8 border-3 p-5 shadow-hard ${
                        verifBanner.variant === 'warning' ? 'border-yellow-500 bg-yellow-50' :
                        verifBanner.variant === 'error' ? 'border-red-500 bg-red-50' :
                        'border-blue-500 bg-blue-50'
                    }`}>
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-display text-display-sm text-foreground">{verifBanner.title}</p>
                                <p className="mt-1 text-body-sm text-muted-foreground">{verifBanner.message}</p>
                            </div>
                            {'action' in verifBanner && verifBanner.action && (
                                <Link href={verifBanner.action.href}>
                                    <button className="shrink-0 border-3 border-foreground bg-foreground px-4 py-2 text-body-sm font-semibold text-background shadow-hard hover:opacity-90">
                                        {verifBanner.action.label}
                                    </button>
                                </Link>
                            )}
                        </div>
                    </div>
                )}

                <h1 className="font-display text-display-lg text-foreground">Dashboard</h1>

                {hasCampaigns && (
                    <div className="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                            <p className="text-body-sm text-muted-foreground">Total raised</p>
                            <p className="mt-1 font-display text-display-lg text-brand">MK {formatMWK(totalRaised)}</p>
                        </Card>
                        <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                            <p className="text-body-sm text-muted-foreground">Withdrawn</p>
                            <p className="mt-1 font-display text-display-lg text-foreground">MK {formatMWK(totalWithdrawn)}</p>
                        </Card>
                        <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                            <p className="text-body-sm text-muted-foreground">Active campaigns</p>
                            <p className="mt-1 font-display text-display-lg text-gold">{activeCampaignsCount}</p>
                        </Card>
                        <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                            <p className="text-body-sm text-muted-foreground">Pending review</p>
                            <p className="mt-1 font-display text-display-lg text-foreground">{pendingCampaignsCount}</p>
                        </Card>
                    </div>
                )}

                {(hasCampaigns || hasDonations) && (
                    <div className="mt-6 grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                        <div className="grid gap-4 md:grid-cols-3">
                            <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                                <p className="text-body-sm text-muted-foreground">Completed donations</p>
                                <p className="mt-1 font-display text-display-md text-foreground">{completedDonationsCount}</p>
                            </Card>
                            <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                                <p className="text-body-sm text-muted-foreground">Average donation</p>
                                <p className="mt-1 font-display text-display-md text-foreground">MK {formatMWK(averageDonationAmount)}</p>
                            </Card>
                            <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                                <p className="text-body-sm text-muted-foreground">Campaign completion</p>
                                <p className="mt-1 font-display text-display-md text-foreground">{completionRate}%</p>
                            </Card>
                        </div>
                        <div className="border-3 border-foreground bg-card p-5 shadow-hard">
                            <p className="font-display text-display-sm text-foreground">Quick actions</p>
                            <div className="mt-4 flex flex-col gap-3">
                                <Link href={create()} className="rounded-none border-2 border-foreground/15 bg-background p-3 text-body-sm font-semibold text-foreground hover:border-brand">
                                    Create a new campaign
                                </Link>
                                <Link href="/dashboard/campaigns" className="rounded-none border-2 border-foreground/15 bg-background p-3 text-body-sm font-semibold text-foreground hover:border-brand">
                                    Review your campaign performance
                                </Link>
                                <Link href="/dashboard/donations" className="rounded-none border-2 border-foreground/15 bg-background p-3 text-body-sm font-semibold text-foreground hover:border-brand">
                                    Track your donation history
                                </Link>
                            </div>
                        </div>
                    </div>
                )}

                {hasDonations && (
                    <div className="mt-8 grid gap-4 md:grid-cols-4">
                        <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                            <p className="text-body-sm text-muted-foreground">Total donated</p>
                            <p className="mt-1 font-display text-display-lg text-brand">MK {formatMWK(totalDonated)}</p>
                        </Card>
                    </div>
                )}

                {!hasCampaigns && !hasDonations && (
                    <div className="mt-8 border-3 border-foreground bg-card p-8 text-center shadow-hard">
                        <p className="text-body-lg text-muted-foreground">
                            Welcome to your dashboard! Start by exploring campaigns or creating your own.
                        </p>
                        <div className="mt-6 flex justify-center gap-4">
                            <Link href="/campaigns">
                                <Button variant="outline">Browse campaigns</Button>
                            </Link>
                            <Link href={create()}>
                                <Button variant="fundme">Start a campaign</Button>
                            </Link>
                        </div>
                    </div>
                )}

                {recentDonations.length > 0 && (
                    <div className="mt-12 border-3 border-foreground bg-card p-6 shadow-hard">
                        <div className="flex items-center justify-between">
                            <h2 className="font-display text-display-sm text-foreground">Recent activity</h2>
                            <Link href="/dashboard/donations" className="text-body-sm font-semibold text-brand underline underline-offset-4">
                                View donations
                            </Link>
                        </div>
                        <div className="mt-4 grid gap-3">
                            {recentDonations.map((donation) => (
                                <div key={donation.id} className="flex items-center justify-between border-2 border-foreground/10 bg-background p-4">
                                    <div>
                                        <p className="font-semibold text-foreground">
                                            {donation.donor_name ?? 'Anonymous donor'}
                                        </p>
                                        <p className="text-body-sm text-muted-foreground">
                                            {donation.campaign?.title ?? 'Campaign'} • {new Date(donation.created_at).toLocaleDateString()}
                                        </p>
                                    </div>
                                    <div className="text-right">
                                        <p className="font-display text-display-sm text-foreground">MK {formatMWK(donation.amount)}</p>
                                        <p className="text-body-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                            {donation.status}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {hasCampaigns && (
                    <div className="mt-12">
                        <div className="flex items-center justify-between">
                            <h2 className="font-display text-display-sm text-foreground">Your campaigns</h2>
                            <Link href={create()}>
                                <Button variant="fundme" size="default">Start a campaign</Button>
                            </Link>
                        </div>
                        <div className="mt-4 flex flex-col gap-4">
                            {campaigns.map((campaign) => {
                                const percentage = campaign.goal_amount > 0
                                    ? (campaign.raised_amount / campaign.goal_amount) * 100
                                    : 0;

                                return (
                                    <div key={campaign.id} className="border-3 border-foreground bg-card p-5 shadow-hard">
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <div className="flex items-center gap-3">
                                                    <h3 className="font-display text-display-sm text-foreground">
                                                        {campaign.title}
                                                    </h3>
                                                    <span className={`rounded-none px-2 py-0.5 text-body-xs font-semibold text-white ${statusColor[campaign.status] ?? 'bg-muted'}`}>
                                                        {campaign.status.replace('_', ' ')}
                                                    </span>
                                                </div>
                                                <div className="mt-3 max-w-md">
                                                    <ProgressBar value={percentage} />
                                                </div>
                                                <div className="mt-2 flex gap-6 text-body-sm text-muted-foreground">
                                                    <span>MK {formatMWK(campaign.raised_amount)} raised</span>
                                                    <span>MK {formatMWK(campaign.goal_amount)} goal</span>
                                                    <span>{campaign.completed_donations_count} donations</span>
                                                </div>
                                            </div>
                                            <div className="flex gap-2">
                                                <Link href={show(campaign.id)}>
                                                    <Button variant="outline" size="sm">View</Button>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                )}

                <div className="mt-12 grid gap-4 md:grid-cols-3">
                    <Link href={create()} className="border-3 border-foreground bg-card p-6 shadow-hard transition-shadow hover:shadow-lg">
                        <h3 className="font-display text-display-sm text-foreground">Start a campaign</h3>
                        <p className="mt-1 text-body-sm text-muted-foreground">Raise money for a cause you care about.</p>
                    </Link>
                    <Link href="/campaigns" className="border-3 border-foreground bg-card p-6 shadow-hard transition-shadow hover:shadow-lg">
                        <h3 className="font-display text-display-sm text-foreground">Explore campaigns</h3>
                        <p className="mt-1 text-body-sm text-muted-foreground">Discover and support fundraisers.</p>
                    </Link>
                    <Link href="/campaigns" className="border-3 border-foreground bg-card p-6 shadow-hard transition-shadow hover:shadow-lg">
                        <h3 className="font-display text-display-sm text-foreground">Saved campaigns</h3>
                        <p className="mt-1 text-body-sm text-muted-foreground">View campaigns you've saved.</p>
                    </Link>
                </div>
            </div>
        </>
    );
}
