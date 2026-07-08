import { Head, Link } from '@inertiajs/react';
import { CampaignCard } from '@/components/campaign-card';
import { Button } from '@/components/ui/button';
import { create, index as campaignsIndex } from '@/routes/campaigns';
import { usePage } from '@inertiajs/react';

interface Campaign {
    id: number;
    title: string;
    slug: string;
    goal_amount: number;
    raised_amount: number;
    currency: string;
    cover_url?: string;
    owner: { name: string; avatar_url?: string };
    category: { name: string; slug: string };
    days_remaining?: number;
    donor_count: number;
}

interface Category {
    id: number;
    name: string;
    slug: string;
}

type Props = {
    campaigns: {
        data: Campaign[];
        meta: any;
    };
    categories: Category[];
};

export default function CampaignIndex({ campaigns, categories }: Props) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Browse Campaigns" />
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="font-display text-display-lg text-foreground">
                            Browse campaigns
                        </h1>
                        <p className="mt-2 text-body-lg text-muted-foreground">
                            Support causes across Malawi that need your help.
                        </p>
                    </div>
                    {auth.user && (
                        <Link href={create()}>
                            <Button variant="fundme" size="default">
                                Start a campaign
                            </Button>
                        </Link>
                    )}
                </div>

                <div className="mt-8 flex flex-wrap gap-2">
                    <Link
                        href={campaignsIndex()}
                        className="rounded-none border-2 border-foreground px-3 py-1.5 text-body-sm font-medium hover:bg-foreground hover:text-background"
                    >
                        All
                    </Link>
                    {categories.map((cat) => (
                        <Link
                            key={cat.id}
                            href={campaignsIndex({ query: { category: cat.slug } })}
                            className="rounded-none border-2 border-foreground px-3 py-1.5 text-body-sm font-medium hover:bg-foreground hover:text-background"
                        >
                            {cat.name}
                        </Link>
                    ))}
                </div>

                {campaigns.data.length === 0 ? (
                    <div className="mt-16 text-center">
                        <p className="text-body-lg text-muted-foreground">
                            No campaigns yet. Be the first to start one!
                        </p>
                        {auth.user && (
                            <Link href={create()} className="mt-4 inline-block">
                                <Button variant="fundme" size="default">
                                    Start a campaign
                                </Button>
                            </Link>
                        )}
                    </div>
                ) : (
                    <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {campaigns.data.map((campaign) => (
                            <CampaignCard key={campaign.id} campaign={campaign} />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
