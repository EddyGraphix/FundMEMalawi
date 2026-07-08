import { Head, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import { store, update as updateRoute, destroy as destroyRoute } from '@/routes/admin/categories';

interface Category {
    id: number;
    name: string;
    slug: string;
    campaigns_count: number;
}

type Props = {
    categories: Category[];
};

export default function AdminCategories({ categories }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);

    const { data, setData, post, put, delete: destroy, processing, errors, reset } = useForm({ name: '' });

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        post(store.url(), {
            onSuccess: () => reset(),
        });
    }

    function handleUpdate(id: number) {
        put(updateRoute.url({ category: id }), {
            onSuccess: () => setEditingId(null),
        });
    }

    function handleDelete(id: number) {
        if (confirm('Delete this category?')) {
            destroy(destroyRoute.url({ category: id }));
        }
    }

    return (
        <>
            <Head title="Categories" />
            <div className="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
                <h1 className="font-display text-display-lg text-foreground">Categories</h1>

                <form onSubmit={handleCreate} className="mt-8 flex items-end gap-4">
                    <div className="flex-1">
                        <Label htmlFor="name">New category</Label>
                        <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="Category name" />
                        <InputError message={errors.name} />
                    </div>
                    <Button type="submit" variant="fundme" disabled={processing || !data.name}>Add</Button>
                </form>

                <div className="mt-8 flex flex-col gap-3">
                    {categories.map((cat) => (
                        <div key={cat.id} className="border-3 border-foreground bg-card p-5 shadow-hard">
                            {editingId === cat.id ? (
                                <div className="flex items-end gap-4">
                                    <div className="flex-1">
                                        <Label htmlFor={`edit-${cat.id}`}>Name</Label>
                                        <Input id={`edit-${cat.id}`} value={data.name} onChange={(e) => setData('name', e.target.value)} />
                                        <InputError message={errors.name} />
                                    </div>
                                    <Button variant="fundme" onClick={() => handleUpdate(cat.id)} disabled={processing}>Save</Button>
                                    <Button variant="outline" onClick={() => setEditingId(null)}>Cancel</Button>
                                </div>
                            ) : (
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h3 className="font-display text-display-sm text-foreground">{cat.name}</h3>
                                        <p className="text-body-sm text-muted-foreground">{cat.campaigns_count} campaigns</p>
                                    </div>
                                    <div className="flex gap-2">
                                        <Button variant="outline" size="sm" onClick={() => { setEditingId(cat.id); setData('name', cat.name); }}>Edit</Button>
                                        <Button variant="outline" size="sm" onClick={() => handleDelete(cat.id)} disabled={cat.campaigns_count > 0}>Delete</Button>
                                    </div>
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        </>
    );
}
