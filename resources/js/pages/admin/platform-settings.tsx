import { Head, useForm, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';

interface Setting {
    id: number;
    key: string;
    value: string;
    type: string;
    group: string;
    label: string;
    description: string | null;
}

type Props = {
    settings: Record<string, Setting[]>;
};

export default function AdminPlatformSettings({ settings }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        settings: Object.values(settings).flat().map((s) => ({ key: s.key, value: s.value })),
    });

    function getValue(key: string): string {
        return data.settings.find((s) => s.key === key)?.value ?? '';
    }

    function setValue(key: string, value: string) {
        setData('settings', data.settings.map((s) => s.key === key ? { ...s, value } : s));
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put('/admin/settings');
    }

    return (
        <>
            <Head title="Platform Settings" />
            <div className="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
                <h1 className="font-display text-display-lg text-foreground">Platform Settings</h1>

                <form onSubmit={handleSubmit} className="mt-8 space-y-10">
                    {Object.entries(settings).map(([group, groupSettings]) => (
                        <div key={group}>
                            <h2 className="font-display text-display-sm text-foreground capitalize">{group}</h2>
                            <div className="mt-4 space-y-4">
                                {groupSettings.map((setting) => (
                                    <div key={setting.id} className="border-3 border-foreground bg-card p-5 shadow-hard">
                                        <Label htmlFor={setting.key}>{setting.label}</Label>
                                        {setting.description && (
                                            <p className="text-body-xs text-muted-foreground">{setting.description}</p>
                                        )}
                                        <div className="mt-2">
                                            {setting.type === 'boolean' ? (
                                                <select
                                                    id={setting.key}
                                                    value={getValue(setting.key)}
                                                    onChange={(e) => setValue(setting.key, e.target.value)}
                                                    className="w-full border-3 border-foreground bg-background p-3"
                                                >
                                                    <option value="true">Enabled</option>
                                                    <option value="false">Disabled</option>
                                                </select>
                                            ) : setting.type === 'json' ? (
                                                <textarea
                                                    id={setting.key}
                                                    value={getValue(setting.key)}
                                                    onChange={(e) => setValue(setting.key, e.target.value)}
                                                    className="w-full border-3 border-foreground bg-background p-3 font-mono text-body-sm"
                                                    rows={3}
                                                />
                                            ) : (
                                                <Input
                                                    id={setting.key}
                                                    type={setting.type === 'integer' || setting.type === 'float' ? 'number' : 'text'}
                                                    value={getValue(setting.key)}
                                                    onChange={(e) => setValue(setting.key, e.target.value)}
                                                    step={setting.type === 'float' ? '0.01' : undefined}
                                                />
                                            )}
                                        </div>
                                        <InputError message={errors[`settings.${setting.key}`]} />
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}

                    <Button type="submit" variant="fundme" disabled={processing}>
                        {processing ? 'Saving...' : 'Save all settings'}
                    </Button>
                </form>
            </div>
        </>
    );
}
