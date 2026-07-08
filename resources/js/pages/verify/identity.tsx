import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import { store } from '@/routes/verify/identity';

type Props = {
    latestVerification?: {
        id?: number;
        status?: string | null;
        admin_notes?: string | null;
        type?: string | null;
        created_at?: string | null;
    } | null;
};

export default function IdentityVerification({ latestVerification }: Props) {
    const { auth } = usePage().props as { auth?: { user?: { verification_status?: string | null } } };
    const { data, setData, post, processing, errors } = useForm({
        document_type: 'national_id',
        document_number: '',
        document_image: null as File | null,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(store.post().url);
    }

    const verificationStatus = latestVerification?.status ?? auth?.user?.verification_status ?? null;
    const statusMeta = (() => {
        switch (verificationStatus) {
            case 'approved':
                return {
                    label: 'Verified',
                    title: 'Your identity is verified.',
                    description: 'You can now create campaigns and request withdrawals with confidence.',
                    className: 'border-blue-500 bg-blue-50 text-blue-900',
                    badge: 'Approved',
                };
            case 'rejected':
                return {
                    label: 'Needs attention',
                    title: 'Your last submission was not accepted.',
                    description: 'Please review the note below and upload a clearer document so we can complete the check.',
                    className: 'border-amber-500 bg-amber-50 text-amber-900',
                    badge: 'Rejected',
                };
            case 'pending':
            default:
                return {
                    label: 'Pending review',
                    title: 'A review is already in progress.',
                    description: 'We will notify you as soon as the decision is made. You can still update your submission if needed.',
                    className: 'border-sky-500 bg-sky-50 text-sky-900',
                    badge: 'Pending',
                };
        }
    })();

    return (
        <>
            <Head title="Verify Your Identity" />

            <div className="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
                <h1 className="font-display text-display-md text-foreground">Verify Your Identity</h1>
                <p className="mt-2 text-body-base text-muted-foreground">
                    To create campaigns and request withdrawals, we need to verify your identity first.
                    Upload a clear image of your government-issued ID.
                </p>
                {verificationStatus && (
                    <div className={`mt-6 rounded-none border-3 p-4 text-body-sm shadow-hard ${statusMeta.className}`}>
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p className="text-body-xs font-semibold uppercase tracking-[0.2em]">{statusMeta.label}</p>
                                <p className="mt-1 text-body-base font-medium">{statusMeta.title}</p>
                                <p className="mt-2 max-w-2xl">{statusMeta.description}</p>
                                {latestVerification?.admin_notes && (
                                    <p className="mt-3 rounded-none border border-current/20 bg-white/60 p-3 text-body-sm">
                                        {latestVerification.admin_notes}
                                    </p>
                                )}
                            </div>
                            <span className="rounded-none border-2 border-current/30 bg-white/70 px-3 py-1 text-body-xs font-semibold uppercase">
                                {statusMeta.badge}
                            </span>
                        </div>
                    </div>
                )}

                <form onSubmit={submit} className="mt-8 space-y-6">
                    <div className="space-y-2">
                        <Label htmlFor="document_type">Document Type</Label>
                        <select
                            id="document_type"
                            value={data.document_type}
                            onChange={(e) => setData('document_type', e.target.value)}
                            className="block w-full rounded-none border-2 border-foreground bg-background px-3 py-2 text-body-base focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                            <option value="national_id">National ID</option>
                            <option value="passport">Passport</option>
                        </select>
                        <InputError message={errors.document_type} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="document_number">ID Number</Label>
                        <Input
                            id="document_number"
                            type="text"
                            placeholder="Enter your ID number"
                            value={data.document_number}
                            onChange={(e) => setData('document_number', e.target.value)}
                        />
                        <InputError message={errors.document_number} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="document_image">Upload ID Document</Label>
                        <Input
                            id="document_image"
                            type="file"
                            accept="image/*"
                            onChange={(e) => setData('document_image', e.target.files?.[0] ?? null)}
                        />
                        <p className="text-body-xs text-muted-foreground">Accepted formats: JPG, PNG, WEBP. Max size: 5MB.</p>
                        <InputError message={errors.document_image} />
                    </div>

                    <div className="rounded-none border-2 border-foreground/10 bg-card p-4 text-body-sm text-muted-foreground">
                        Please upload a clear, readable image. We may ask for a follow-up if the document is not legible.
                    </div>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing} variant="fundme" size="lg">
                            Submit Verification
                        </Button>
                        <Link href="/" className="text-body-sm text-muted-foreground hover:text-foreground">
                            Skip for now
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}
