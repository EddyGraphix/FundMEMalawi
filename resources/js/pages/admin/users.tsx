import { Head } from '@inertiajs/react';

interface User {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    created_at: string;
}

type Props = {
    users: {
        data: User[];
        meta: any;
    };
};

export default function AdminUsers({ users }: Props) {
    return (
        <>
            <Head title="User Management" />
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <h1 className="font-display text-display-lg text-foreground">User Management</h1>

                <div className="overflow-x-auto">
                    <table className="w-full border-collapse border-3 border-foreground">
                        <thead>
                            <tr className="bg-muted">
                                <th className="border-2 border-foreground p-3 text-left text-body-sm font-semibold">Name</th>
                                <th className="border-2 border-foreground p-3 text-left text-body-sm font-semibold">Email</th>
                                <th className="border-2 border-foreground p-3 text-left text-body-sm font-semibold">Phone</th>
                                <th className="border-2 border-foreground p-3 text-left text-body-sm font-semibold">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.data.map((user) => (
                                <tr key={user.id} className="border-2 border-foreground">
                                    <td className="p-3 text-body-sm">{user.name}</td>
                                    <td className="p-3 text-body-sm text-muted-foreground">{user.email}</td>
                                    <td className="p-3 text-body-sm text-muted-foreground">{user.phone ?? '—'}</td>
                                    <td className="p-3 text-body-sm text-muted-foreground">
                                        {new Date(user.created_at).toLocaleDateString()}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}
