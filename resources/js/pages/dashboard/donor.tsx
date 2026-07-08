import { Head, Link, usePage } from '@inertiajs/react';
import { Card } from '@/components/ui/card';
import { show } from '@/routes/campaigns';
import verify from '@/routes/verify';

interface Donation {
    id: number;
    amount: number;
    currency: string;
    is_anonymous: boolean;
    status: string;
    created_at: string;
    campaign: {
        id: number;
        title: string;
        slug: string;
    };
}

type Props = {
    donations: {
        data: Donation[];
        meta: any;
    };
};

function formatMWK(amount: number, currency: string = 'MWK'): string {
    if (currency === 'MWK') {
        return `MK ${Math.floor(amount).toLocaleString()}`;
    }
    return `${currency} ${amount.toFixed(2)}`;
}

export default function DonorDashboard({ donations }: Props) {
    const { auth } = usePage().props;
    const totalDonated = donations.data.reduce((sum, d) => sum + d.amount, 0);

    const verifBanner = (() => {
        const status = (auth.user as any)?.verification_status;
        if (status === 'approved') return null;
        if (status === 'pending') return {
            variant: 'info' as const,
            title: 'Verification pending',
            message: 'Your identity verification is under review. You\'ll be able to create campaigns once it\'s approved.',
        };
        if (status === 'rejected') return {
            variant: 'error' as const,
            title: 'Verification rejected',
            message: 'Your identity verification was rejected.',
            action: { label: 'Re-submit verification', href: verify.identity.url() },
        };
        return {
            variant: 'warning' as const,
            title: 'Verify your identity',
            message: 'Verify your account to create campaigns, become a beneficiary, and request withdrawals.',
            action: { label: 'Verify now', href: verify.identity.url() },
        };
    })();

    return (
        <>
            <Head title="My Donations" />
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

                <h1 className="font-display text-display-lg text-foreground">My Donations</h1>

                <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                    <p className="text-body-sm text-muted-foreground">Total donated</p>
                    <p className="mt-1 font-display text-display-lg text-brand">
                        {formatMWK(totalDonated)}
                    </p>
                    <p className="text-body-sm text-muted-foreground">{donations.meta?.total ?? donations.data.length} donations</p>
                </Card>

                <div className="flex flex-col gap-4">
                    {donations.data.length === 0 ? (
                        <div className="border-3 border-foreground bg-card p-8 text-center shadow-hard">
                            <p className="text-body-lg text-muted-foreground">
                                You haven't made any donations yet.
                            </p>
                            <Link href="/campaigns" className="mt-4 inline-block">
                                <button className="border-3 border-foreground bg-brand px-6 py-3 font-display text-display-sm text-brand-foreground shadow-hard">
                                    Browse campaigns
                                </button>
                            </Link>
                        </div>
                    ) : (
                        donations.data.map((donation) => (
                            <div key={donation.id} className="border-3 border-foreground bg-card p-5 shadow-hard">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <Link
                                            href={show(donation.campaign.id)}
                                            className="font-display text-display-sm text-foreground hover:text-brand"
                                        >
                                            {donation.campaign.title}
                                        </Link>
                                        <div className="mt-1 flex gap-4 text-body-sm text-muted-foreground">
                                            <span>{new Date(donation.created_at).toLocaleDateString()}</span>
                                            <span className={`font-semibold ${
                                                donation.status === 'completed' ? 'text-brand' :
                                                donation.status === 'pending' ? 'text-yellow-500' : 'text-red-500'
                                            }`}>
                                                {donation.status}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <p className="font-display text-display-sm text-foreground">
                                            {formatMWK(donation.amount, donation.currency)}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </>
    );
}

