import { Head, Link, useForm } from '@inertiajs/react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { show as campaignShow } from '@/routes/campaigns';

interface Donation {
    id: number;
    amount: number;
    tip_amount: number;
    currency: string;
    status: string;
    payment_method: string;
    payment_provider_ref: string | null;
    donor_name: string;
    created_at: string;
    campaign: { id: number; title: string };
    donor: { id: number; name: string } | null;
}

type Props = {
    donations: {
        data: Donation[];
        meta: any;
    };
    totals: {
        total_donations: number;
        total_fees: number;
        total_count: number;
    };
    filters: {
        status?: string;
        from?: string;
        to?: string;
    };
};

function formatMWK(amount: number, currency: string = 'MWK'): string {
    if (currency === 'MWK') {
        return `MK ${Math.floor(amount).toLocaleString()}`;
    }
    return `${currency} ${amount.toFixed(2)}`;
}

export default function AdminTransactions({ donations, totals, filters }: Props) {
    const { data, setData, get, processing } = useForm({
        status: filters.status ?? '',
        from: filters.from ?? '',
        to: filters.to ?? '',
    });

    function filter() {
        get('/admin/transactions', { preserveState: true, preserveScroll: true });
    }

    return (
        <>
            <Head title="Transactions" />
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 className="font-display text-display-lg text-foreground">Transactions</h1>
                        <p className="mt-2 text-body-sm text-muted-foreground">
                            Review recent revenue activity, filter the ledger, and spot payment issues quickly.
                        </p>
                    </div>
                    <div className="rounded-none border-3 border-foreground bg-card px-4 py-3 text-body-sm shadow-hard">
                        {totals.total_count} completed transactions
                    </div>
                </div>

                <div className="mt-8 grid gap-4 md:grid-cols-3">
                    <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                        <p className="text-body-sm text-muted-foreground">Total donations</p>
                        <p className="mt-1 font-display text-display-lg text-brand">
                            {formatMWK(totals.total_donations)}
                        </p>
                    </Card>
                    <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                        <p className="text-body-sm text-muted-foreground">Total fees</p>
                        <p className="mt-1 font-display text-display-lg text-gold">
                            {formatMWK(totals.total_fees)}
                        </p>
                    </Card>
                    <Card data-variant="campaign" className="border-3 border-foreground p-5 shadow-hard">
                        <p className="text-body-sm text-muted-foreground">Completed transactions</p>
                        <p className="mt-1 font-display text-display-lg text-foreground">{totals.total_count}</p>
                    </Card>
                </div>

                <div className="mt-8 rounded-none border-3 border-foreground bg-card p-5 shadow-hard">
                    <div className="flex flex-wrap items-end gap-4">
                        <div>
                            <Label htmlFor="status">Status</Label>
                            <select
                                id="status"
                                className="mt-1 min-w-[140px] rounded-none border-2 border-foreground bg-background p-2 text-body-sm"
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value)}
                            >
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        <div>
                            <Label htmlFor="from">From</Label>
                            <Input id="from" type="date" value={data.from} onChange={(e) => setData('from', e.target.value)} />
                        </div>
                        <div>
                            <Label htmlFor="to">To</Label>
                            <Input id="to" type="date" value={data.to} onChange={(e) => setData('to', e.target.value)} />
                        </div>
                        <Button variant="fundme" size="sm" onClick={filter} disabled={processing}>
                            Apply filters
                        </Button>
                    </div>
                </div>

                {donations.data.length === 0 ? (
                    <div className="mt-8 rounded-none border-3 border-foreground bg-card p-8 text-center shadow-hard">
                        <p className="text-body-lg text-muted-foreground">No matching transactions found for the selected filters.</p>
                    </div>
                ) : (
                    <div className="mt-8 overflow-x-auto rounded-none border-3 border-foreground bg-card shadow-hard">
                        <table className="w-full border-collapse">
                            <thead>
                                <tr className="bg-muted/70">
                                    <th className="border-b-2 border-foreground p-3 text-left text-body-sm font-semibold">ID</th>
                                    <th className="border-b-2 border-foreground p-3 text-left text-body-sm font-semibold">Campaign</th>
                                    <th className="border-b-2 border-foreground p-3 text-left text-body-sm font-semibold">Donor</th>
                                    <th className="border-b-2 border-foreground p-3 text-left text-body-sm font-semibold">Amount</th>
                                    <th className="border-b-2 border-foreground p-3 text-left text-body-sm font-semibold">Fee</th>
                                    <th className="border-b-2 border-foreground p-3 text-left text-body-sm font-semibold">Status</th>
                                    <th className="border-b-2 border-foreground p-3 text-left text-body-sm font-semibold">Method</th>
                                    <th className="border-b-2 border-foreground p-3 text-left text-body-sm font-semibold">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                {donations.data.map((donation) => {
                                    const statusColor: Record<string, string> = {
                                        pending: 'text-yellow-500',
                                        completed: 'text-brand',
                                        failed: 'text-red-500',
                                        refunded: 'text-blue-500',
                                    };

                                    return (
                                        <tr key={donation.id} className="border-t border-foreground/10">
                                            <td className="p-3 text-body-sm text-muted-foreground">#{donation.id}</td>
                                            <td className="p-3 text-body-sm">
                                                <Link href={campaignShow(donation.campaign.id)} className="font-medium text-foreground hover:text-brand">
                                                    {donation.campaign.title}
                                                </Link>
                                            </td>
                                            <td className="p-3 text-body-sm">{donation.donor?.name ?? donation.donor_name}</td>
                                            <td className="p-3 font-semibold">{formatMWK(donation.amount, donation.currency)}</td>
                                            <td className="p-3 text-body-sm text-muted-foreground">{formatMWK(donation.tip_amount)}</td>
                                            <td className={`p-3 font-semibold ${statusColor[donation.status] ?? ''}`}>{donation.status}</td>
                                            <td className="p-3 text-body-sm text-muted-foreground">{donation.payment_method}</td>
                                            <td className="p-3 text-body-sm text-muted-foreground">
                                                {new Date(donation.created_at).toLocaleDateString()}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}
