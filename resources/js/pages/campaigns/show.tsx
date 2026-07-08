import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { DonateButton } from '@/components/donate-button';
import { DonationFeedItem } from '@/components/donation-feed-item';
import { ProgressBar } from '@/components/progress-bar';
import { RichTextEditor } from '@/components/rich-text-editor';
import { ShareButtons } from '@/components/share-buttons';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { login } from '@/routes';
import { donate } from '@/routes/campaigns';
import { store as commentStore } from '@/routes/campaigns/comments';
import { store as updateStore } from '@/routes/campaigns/updates';

interface Donation {
    id: number;
    donor_name?: string;
    is_anonymous: boolean;
    amount: number;
    currency: string;
    message?: string;
    created_at: string;
}

interface Campaign {
    id: number;
    title: string;
    slug: string;
    story: string;
    goal_amount: number;
    raised_amount: number;
    currency: string;
    cover_url: string | null;
    status: string;
    owner: { id: number; name: string };
    beneficiary: { id: number; name: string } | null;
    category: { name: string; slug: string };
    updates: { id: number; title: string; body: string; created_at: string }[];
    comments: { id: number; body: string; user: { name: string }; created_at: string }[];
    starts_at: string | null;
    ends_at: string | null;
}

type Props = {
    campaign: Campaign;
    donations: Donation[];
};

function formatMWK(amount: number, currency: string = 'MWK'): string {
    if (currency === 'MWK') {
        return `MK ${amount.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
    }
    
    return `${currency} ${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export default function CampaignShow({ campaign, donations }: Props) {
    const { auth } = usePage().props;
    const currentUrl = window.location.href;
    const [showUpdateForm, setShowUpdateForm] = useState(false);

    const isOwner = auth.user?.id === campaign.owner.id;
    const percentage = campaign.goal_amount > 0
        ? (campaign.raised_amount / campaign.goal_amount) * 100
        : 0;

    const { data: updateData, setData: setUpdateData, post: postUpdate, processing: updateProcessing, reset: resetUpdate } = useForm({ title: '', body: '' });
    const { data: commentData, setData: setCommentData, post: postComment, processing: commentProcessing, reset: resetComment } = useForm({ body: '' });

    function submitUpdate(e: React.FormEvent) {
        e.preventDefault();
        postUpdate(updateStore.url(campaign.id), { preserveScroll: true, onSuccess: () => { resetUpdate(); setShowUpdateForm(false); } });
    }

    function submitComment(e: React.FormEvent) {
        e.preventDefault();
        postComment(commentStore.url(campaign.id), { preserveScroll: true, onSuccess: () => resetComment() });
    }

    return (
        <>
            <Head title={campaign.title} />
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="grid gap-8 lg:grid-cols-3">
                    <div className="lg:col-span-2 space-y-8">
                        {campaign.cover_url ? (
                            <div className="aspect-[16/9] overflow-hidden border-3 border-foreground">
                                <img src={campaign.cover_url} alt={campaign.title} className="h-full w-full object-cover" />
                            </div>
                        ) : (
                            <div className="aspect-[16/9] border-3 border-foreground bg-muted" />
                        )}

                        <div>
                            <div className="flex items-center gap-2">
                                <span className="inline-block rounded-none bg-gold px-2 py-0.5 text-xs font-semibold text-gold-foreground">
                                    {campaign.category.name}
                                </span>
                                {campaign.status !== 'active' && (
                                    <span className="inline-block rounded-none bg-muted px-2 py-0.5 text-xs font-semibold text-muted-foreground uppercase">
                                        {campaign.status}
                                    </span>
                                )}
                            </div>
                            <h1 className="mt-3 font-display text-display-lg text-foreground">{campaign.title}</h1>
                            <div className="mt-2 flex items-center gap-4 text-body-sm text-muted-foreground">
                                <span>by {campaign.owner.name}</span>
                                {campaign.beneficiary && <span>for {campaign.beneficiary.name}</span>}
                            </div>
                        </div>

                        <Separator />

                        <div className="prose prose-lg max-w-none" dangerouslySetInnerHTML={{ __html: campaign.story }} />

                        <Separator />

                        {isOwner && (
                            <section>
                                <div className="flex items-center justify-between">
                                    <h2 className="font-display text-display-sm">Updates</h2>
                                    <Button variant="fundme" size="sm" onClick={() => setShowUpdateForm(!showUpdateForm)}>
                                        {showUpdateForm ? 'Cancel' : 'Post Update'}
                                    </Button>
                                </div>
                                {showUpdateForm && (
                                    <form onSubmit={submitUpdate} className="mt-4 space-y-4">
                                        <div>
                                            <Label htmlFor="update-title">Title</Label>
                                            <Input id="update-title" value={updateData.title} onChange={(e) => setUpdateData('title', e.target.value)} required />
                                        </div>
                                        <div>
                                            <Label htmlFor="update-body">Body</Label>
                                            <RichTextEditor value={updateData.body} onChange={(v) => setUpdateData('body', v)} />
                                        </div>
                                        <Button type="submit" variant="fundme" disabled={updateProcessing}>Publish Update</Button>
                                    </form>
                                )}
                                {campaign.updates.length > 0 ? (
                                    <div className="mt-6 space-y-6">
                                        {campaign.updates.map((update) => (
                                            <div key={update.id} className="border-l-3 border-foreground pl-4">
                                                <h3 className="font-display text-display-sm">{update.title}</h3>
                                                <p className="mt-1 text-body-xs text-muted-foreground">{new Date(update.created_at).toLocaleDateString()}</p>
                                                <div className="mt-2 text-body-base" dangerouslySetInnerHTML={{ __html: update.body }} />
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="mt-4 text-body-sm text-muted-foreground">No updates yet.</p>
                                )}
                            </section>
                        )}

                        <section>
                            <h2 className="font-display text-display-sm">Comments</h2>
                            {auth.user ? (
                                <form onSubmit={submitComment} className="mt-4 flex gap-3">
                                    <Input
                                        value={commentData.body}
                                        onChange={(e) => setCommentData('body', e.target.value)}
                                        placeholder="Share your thoughts..."
                                        required
                                    />
                                    <Button type="submit" variant="fundme" disabled={commentProcessing}>Post</Button>
                                </form>
                            ) : (
                                <p className="mt-4 text-body-sm text-muted-foreground">
                                    <Link href={login()} className="underline underline-offset-4">Log in</Link> to leave a comment.
                                </p>
                            )}
                            {campaign.comments.length > 0 ? (
                                <div className="mt-6 space-y-4">
                                    {campaign.comments.map((comment) => (
                                        <div key={comment.id} className="border-2 border-foreground p-4">
                                            <div className="flex items-center gap-2">
                                                <span className="font-semibold text-body-sm">{comment.user.name}</span>
                                                <span className="text-body-xs text-muted-foreground">{new Date(comment.created_at).toLocaleDateString()}</span>
                                            </div>
                                            <p className="mt-1 text-body-sm">{comment.body}</p>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="mt-4 text-body-sm text-muted-foreground">No comments yet.</p>
                            )}
                        </section>
                    </div>

                    <div className="lg:col-span-1">
                        <div className="sticky top-24 space-y-6">
                            <div className="border-3 border-foreground bg-card p-6 shadow-hard">
                                <ProgressBar value={percentage} />
                                <div className="mt-4">
                                    <p className="font-display text-display-sm text-foreground">
                                        {formatMWK(campaign.raised_amount, campaign.currency)}
                                    </p>
                                    <p className="text-body-sm text-muted-foreground">
                                        raised of {formatMWK(campaign.goal_amount, campaign.currency)}
                                    </p>
                                </div>
                                <div className="mt-4 text-body-sm text-muted-foreground">
                                    {campaign.ends_at ? (
                                        <span>{Math.max(0, Math.ceil((new Date(campaign.ends_at).getTime() - Date.now()) / (1000 * 60 * 60 * 24)))} days left</span>
                                    ) : (
                                        <span>Open-ended campaign</span>
                                    )}
                                </div>

                                <div className="mt-6">
<DonateButton size="lg" className="w-full" asChild>
    <Link href={donate(campaign.id)}>
        Donate now
    </Link>
</DonateButton>
                                </div>
                            </div>

                            <div className="border-3 border-foreground bg-card p-6 shadow-hard">
                                <h3 className="font-display text-display-sm text-foreground">Share this campaign</h3>
                                <div className="mt-4">
                                    <ShareButtons url={currentUrl} title={campaign.title} />
                                </div>
                            </div>

                            {donations.length > 0 && (
                                <div className="border-3 border-foreground bg-card p-6 shadow-hard">
                                    <h3 className="font-display text-display-sm text-foreground">Recent supporters</h3>
                                    <div className="mt-4 divide-y divide-foreground/10">
                                        {donations.slice(0, 10).map((d) => (
                                            <DonationFeedItem key={d.id} donation={d} />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
