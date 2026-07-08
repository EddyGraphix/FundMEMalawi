import { Link, usePage } from '@inertiajs/react';
import {
    Megaphone, CreditCard, Users, BadgeCheck, Landmark, Wallet, Tags,
    Settings, BarChart3, Bell, ScrollText, Mail, Link2, Flag, LifeBuoy,
    Shield, ChevronLeft, Menu,
} from 'lucide-react';
import { useState } from 'react';
import {
    campaigns, users, verifications, transactions, withdrawals, categories, settings as adminSettings,
} from '@/routes/admin';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import type { PropsWithChildren } from 'react';

const NAV_ITEMS = [
    { title: 'Campaigns', href: campaigns(), icon: Megaphone, status: 'active' as const },
    { title: 'Transactions', href: transactions(), icon: CreditCard, status: 'active' as const },
    { title: 'Users', href: users(), icon: Users, status: 'active' as const },
    { title: 'Verifications', href: verifications(), icon: BadgeCheck, status: 'active' as const },
    { title: 'Withdrawals', href: withdrawals(), icon: Landmark, status: 'active' as const },
    { title: 'Wallets', href: '#', icon: Wallet, status: 'coming-soon' as const },
    { title: 'Categories', href: categories(), icon: Tags, status: 'active' as const },
    { title: 'Platform Settings', href: adminSettings(), icon: Settings, status: 'active' as const },
    { title: 'Reports', href: '#', icon: BarChart3, status: 'coming-soon' as const },
    { title: 'Notifications', href: '#', icon: Bell, status: 'coming-soon' as const },
    { title: 'Audit Log', href: '#', icon: ScrollText, status: 'coming-soon' as const },
    { title: 'Email Templates', href: '#', icon: Mail, status: 'coming-soon' as const },
    { title: 'Payment Providers', href: '#', icon: Link2, status: 'coming-soon' as const },
    { title: 'Fraud Flags', href: '#', icon: Flag, status: 'coming-soon' as const },
    { title: 'Support', href: '#', icon: LifeBuoy, status: 'coming-soon' as const },
    { title: 'Security', href: '#', icon: Shield, status: 'coming-soon' as const },
];

export default function AdminLayout({ children }: PropsWithChildren) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { url } = usePage();
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <div className="flex">
            {/* Mobile overlay */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 z-40 bg-black/50 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* Sidebar */}
            <aside
                className={cn(
                    'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r-3 border-foreground bg-card shadow-hard transition-transform duration-200 lg:sticky lg:top-0 lg:translate-x-0 lg:self-start lg:min-h-screen',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                )}
            >
                {/* Sidebar header */}
                <div className="flex items-center justify-between border-b-3 border-foreground px-4 py-4">
                    <Link
                        href="/admin"
                        className="font-display text-display-sm text-foreground"
                        onClick={() => setSidebarOpen(false)}
                    >
                        Admin
                    </Link>
                    <button
                        onClick={() => setSidebarOpen(false)}
                        className="flex h-8 w-8 items-center justify-center rounded-none border-2 border-foreground lg:hidden"
                    >
                        <ChevronLeft className="h-4 w-4" />
                    </button>
                </div>

                {/* Navigation */}
                <nav className="flex-1 overflow-y-auto px-2 py-4">
                    {NAV_ITEMS.map((item) => (
                        <Link
                            key={item.title}
                            href={item.href}
                            onClick={() => setSidebarOpen(false)}
                            className={cn(
                                'flex items-center gap-3 px-3 py-2.5 text-body-sm font-medium transition-colors',
                                item.status === 'coming-soon'
                                    ? 'pointer-events-none opacity-40'
                                    : isCurrentUrl(item.href) || (item.href !== '/admin' && url.startsWith(item.href))
                                        ? 'bg-foreground text-background'
                                        : 'text-foreground hover:bg-muted',
                            )}
                        >
                            <item.icon className="h-4 w-4 shrink-0" />
                            <span>{item.title}</span>
                            {item.status === 'coming-soon' && (
                                <span className="ml-auto rounded-none bg-muted px-1.5 py-0.5 text-[10px] font-bold uppercase text-muted-foreground">
                                    Soon
                                </span>
                            )}
                        </Link>
                    ))}
                </nav>
            </aside>

            {/* Main content */}
            <div className="flex min-w-0 flex-1 flex-col">
                {/* Mobile sidebar toggle */}
                <div className="sticky top-0 z-30 border-b-3 border-foreground bg-card px-4 py-3 shadow-hard lg:hidden">
                    <button
                        onClick={() => setSidebarOpen(true)}
                        className="flex h-9 w-9 items-center justify-center rounded-none border-2 border-foreground"
                    >
                        <Menu className="h-4 w-4" />
                    </button>
                </div>

                <div className="flex-1">
                    {children}
                </div>
            </div>
        </div>
    );
}
