import { Head, Link, usePage, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import { RichTextEditor } from '@/components/rich-text-editor';
import { store } from '@/routes/campaigns';

interface Category {
    id: number;
    name: string;
    slug: string;
}

type Props = {
    categories: Category[];
};

export default function CampaignCreate({ categories }: Props) {
    const [step, setStep] = useState(1);
    const [coverPreview, setCoverPreview] = useState<string | null>(null);
    const { auth } = usePage().props;

    const { data, setData, post, processing, errors, transform } = useForm({
        category_id: 0,
        title: '',
        story: '',
        goal_amount: '',
        cover_image: null as File | null,
        beneficiary_email: '',
    });

    transform((formData) => {
        const fd = new FormData();
        fd.append('category_id', String(formData.category_id));
        fd.append('title', formData.title);
        fd.append('story', formData.story);
        fd.append('goal_amount', formData.goal_amount);
        if (formData.cover_image) {
            fd.append('cover_image', formData.cover_image);
        }
        if (formData.beneficiary_email) {
            fd.append('beneficiary_email', formData.beneficiary_email);
        }
        return fd;
    });

    function handleSubmit() {
        post(store.url(), {
            forceFormData: true,
            onSuccess: () => window.location.href = '/campaigns',
        });
    }

    return (
        <>
            <Head title="Start a Campaign" />
            <div className="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="mb-8">
                    <h1 className="font-display text-display-lg text-foreground">
                        Start a campaign
                    </h1>
                    <div className="mt-4 flex items-center gap-2">
                        {[1, 2, 3, 4, 5].map((s) => (
                            <div
                                key={s}
                                className={`h-2 flex-1 ${s <= step ? 'bg-brand' : 'bg-muted'}`}
                            />
                        ))}
                    </div>
                    <p className="mt-2 text-body-sm text-muted-foreground">
                        Step {step} of 5
                    </p>
                </div>

                <form
                    onSubmit={(e) => { e.preventDefault(); handleSubmit(); }}
                    className="flex flex-col gap-8"
                    encType="multipart/form-data"
                >
                    {step === 1 && (
                        <div className="space-y-6">
                            <h2 className="font-display text-display-sm text-foreground">Choose a category</h2>
                            <div className="grid grid-cols-2 gap-3">
                                {categories.map((cat) => (
                                    <button
                                        key={cat.id}
                                        type="button"
                                        onClick={() => {
                                            setData('category_id', cat.id);
                                            setStep(2);
                                        }}
                                        className={`border-3 p-4 text-left font-semibold transition-shadow hover:shadow-hard ${
                                            data.category_id === cat.id
                                                ? 'border-brand bg-brand/5 shadow-hard'
                                                : 'border-foreground'
                                        }`}
                                    >
                                        {cat.name}
                                    </button>
                                ))}
                            </div>
                            {errors.category_id && <InputError message={errors.category_id} />}
                        </div>
                    )}

                    {step === 2 && (
                        <div className="space-y-6">
                            <h2 className="font-display text-display-sm text-foreground">Title & story</h2>
                            <div className="grid gap-2">
                                <Label htmlFor="title">Campaign title</Label>
                                <Input id="title" value={data.title} onChange={(e) => setData('title', e.target.value)} required placeholder="Give your campaign a clear, compelling title" />
                                <InputError message={errors.title} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="story">Story</Label>
                                <RichTextEditor
                                    id="story"
                                    value={data.story}
                                    onChange={(value) => setData('story', value)}
                                    placeholder="Tell your story — why are you fundraising, who does it help, and how will the funds be used?"
                                />
                                <InputError message={errors.story} />
                            </div>
                            <div className="flex gap-4">
                                <Button type="button" variant="outline" onClick={() => setStep(1)}>Back</Button>
                                <Button type="button" variant="fundme" onClick={() => setStep(3)}>Next</Button>
                            </div>
                        </div>
                    )}

                    {step === 3 && (
                        <div className="space-y-6">
                            <h2 className="font-display text-display-sm text-foreground">Funding goal</h2>
                            <div className="grid gap-2">
                                <Label htmlFor="goal_amount">Goal amount (MWK)</Label>
                                <Input
                                    id="goal_amount"
                                    type="number"
                                    value={data.goal_amount}
                                    onChange={(e) => setData('goal_amount', e.target.value)}
                                    required
                                    min={1}
                                    placeholder="50000"
                                />
                                <InputError message={errors.goal_amount} />
                            </div>
                            <div className="flex gap-4">
                                <Button type="button" variant="outline" onClick={() => setStep(2)}>Back</Button>
                                <Button type="button" variant="fundme" onClick={() => setStep(4)}>Next</Button>
                            </div>
                        </div>
                    )}

                    {step === 4 && (
                        <div className="space-y-6">
                            <h2 className="font-display text-display-sm text-foreground">Cover image</h2>
                            <div className="grid gap-2">
                                <Label htmlFor="cover_image">Upload a cover image</Label>
                                <Input
                                    id="cover_image"
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) => {
                                        const file = e.target.files?.[0];
                                        if (file) {
                                            setData('cover_image', file);
                                            setCoverPreview(URL.createObjectURL(file));
                                        }
                                    }}
                                />
                                <InputError message={errors.cover_image} />
                            </div>
                            {coverPreview && (
                                <img src={coverPreview} alt="Preview" className="aspect-video w-full border-3 border-foreground object-cover" />
                            )}
                            <div className="flex gap-4">
                                <Button type="button" variant="outline" onClick={() => setStep(3)}>Back</Button>
                                <Button type="button" variant="fundme" onClick={() => setStep(5)}>Next</Button>
                            </div>
                        </div>
                    )}

                    {step === 5 && (
                        <div className="space-y-6">
                            <h2 className="font-display text-display-sm text-foreground">Beneficiary (optional)</h2>
                            <p className="text-body-sm text-muted-foreground">
                                If the funds are for someone else, enter their email. They'll
                                need to create an account and verify their identity before
                                withdrawals can be made.
                            </p>
                            <div className="grid gap-2">
                                <Label htmlFor="beneficiary_email">Beneficiary email</Label>
                                <Input id="beneficiary_email" type="email" value={data.beneficiary_email} onChange={(e) => setData('beneficiary_email', e.target.value)} placeholder="beneficiary@example.com" />
                                <InputError message={errors.beneficiary_email} />
                            </div>
                            <div className="flex gap-4">
                                <Button type="button" variant="outline" onClick={() => setStep(4)}>Back</Button>
                                <Button type="submit" variant="fundme" disabled={processing}>
                                    {processing ? 'Submitting...' : 'Submit for review'}
                                </Button>
                            </div>
                        </div>
                    )}
                </form>
            </div>
        </>
    );
}

