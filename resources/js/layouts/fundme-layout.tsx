import { Link, usePage } from '@inertiajs/react';
import { LogOut, Menu, Settings, LayoutDashboard, Shield } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import { dashboard, login, home, logout as logoutRoute } from '@/routes';
import verify from '@/routes/verify';
import { index as campaignsIndex } from '@/routes/campaigns';
import { edit } from '@/routes/profile';
import { dashboard as adminDashboard, campaigns as adminCampaigns } from '@/routes/admin';
import type { User } from '@/types';

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

interface FundMeLayoutProps {
    children: React.ReactNode;
}

function NavLinks({ isVertical = false, onNavClick }: { isVertical?: boolean; onNavClick?: () => void }) {
    const { isCurrentUrl } = useCurrentUrl();

    const links = [
        { label: 'Explore campaigns', href: campaignsIndex() },
    ];

    if (isVertical) {
        return (
            <div className="flex flex-col space-y-1">
                {links.map((link) => (
                    <Link
                        key={link.label}
                        href={link.href}
                        onClick={onNavClick}
                        className={cn(
                            'rounded-none px-3 py-2 text-body-base font-medium transition-colors',
                            isCurrentUrl(link.href)
                                ? 'bg-foreground text-background'
                                : 'text-foreground hover:bg-muted',
                        )}
                    >
                        {link.label}
                    </Link>
                ))}
            </div>
        );
    }

    return (
        <nav className="flex items-center gap-1">
            {links.map((link) => (
                <Link
                    key={link.label}
                    href={link.href}
                    className={cn(
                        'relative px-3 py-1.5 text-body-sm font-medium transition-colors hover:text-foreground',
                        isCurrentUrl(link.href)
                            ? 'text-foreground'
                            : 'text-muted-foreground',
                    )}
                >
                    {link.label}
                    {isCurrentUrl(link.href) && (
                        <span className="absolute bottom-0 left-1/2 h-0.5 w-3/4 -translate-x-1/2 bg-foreground" />
                    )}
                </Link>
            ))}
        </nav>
    );
}

function UserDropdown({ user }: { user: User }) {
    const isAdmin = user.roles?.includes('admin');

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <button className="flex items-center gap-2 rounded-none border-2 border-foreground px-3 py-1.5 text-body-sm font-medium transition-colors hover:bg-foreground hover:text-background">
                    <span className="flex h-6 w-6 items-center justify-center rounded-none bg-foreground text-[10px] font-bold text-background">
                        {getInitials(user.name)}
                    </span>
                    <span className="hidden md:inline">{user.name.split(' ')[0]}</span>
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-56 rounded-none border-2 border-foreground" align="end">
                <DropdownMenuLabel className="p-0 font-normal">
                    <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                        <span className="flex h-8 w-8 items-center justify-center rounded-none bg-foreground text-xs font-bold text-background">
                            {getInitials(user.name)}
                        </span>
                        <div className="grid flex-1 text-left text-sm leading-tight">
                            <span className="truncate font-medium">{user.name}</span>
                            <span className="truncate text-xs text-muted-foreground">{user.email}</span>
                        </div>
                    </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />

                <DropdownMenuGroup>
                    {!isAdmin && (
                        <DropdownMenuItem asChild>
                            <Link href={dashboard()} className="cursor-pointer">
                                <LayoutDashboard className="mr-2 h-4 w-4" />
                                Dashboard
                            </Link>
                        </DropdownMenuItem>
                    )}

                    {isAdmin && (
                        <DropdownMenuItem asChild>
                            <Link href={adminDashboard()} className="cursor-pointer">
                                <Shield className="mr-2 h-4 w-4" />
                                Admin Panel
                                <span className="ml-auto rounded-none bg-foreground px-1.5 py-0.5 text-[10px] font-bold text-background">
                                    ADMIN
                                </span>
                            </Link>
                        </DropdownMenuItem>
                    )}

                    <DropdownMenuItem asChild>
                        <Link href={edit()} className="cursor-pointer">
                            <Settings className="mr-2 h-4 w-4" />
                            Settings
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuGroup>

                <DropdownMenuSeparator />

                <DropdownMenuItem asChild>
                    <Link href={logoutRoute.url()} method="post" as="button" className="w-full cursor-pointer">
                        <LogOut className="mr-2 h-4 w-4" />
                        Log out
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function MobilePanel({ user, onNavClick }: { user: User | null; onNavClick: () => void }) {
    const isAdmin = user?.roles?.includes('admin');

    const fundraiserLink = user
        ? user.verification_status === 'approved'
            ? campaignsIndex() + '/create'
            : verify.identity.url()
        : '/register';

    const fundraiserLabel = user
        ? user.verification_status === 'approved'
            ? 'Start a Fundraiser'
            : user.verification_status === 'pending'
                ? 'Verification Pending'
                : 'Verify to Fundraise'
        : 'Start a Fundraiser';

    return (
        <>
            <NavLinks isVertical onNavClick={onNavClick} />

            <div className="mt-6 border-t-2 border-border pt-4">
                {user ? (
                    <>
                        <div className="flex items-center gap-2 pb-3">
                            <span className="flex h-8 w-8 items-center justify-center rounded-none bg-foreground text-xs font-bold text-background">
                                {getInitials(user.name)}
                            </span>
                            <div className="text-left text-sm">
                                <p className="font-medium">{user.name}</p>
                                <p className="text-xs text-muted-foreground">{user.email}</p>
                            </div>
                        </div>
                        <div className="flex flex-col gap-1">
                            {!isAdmin && (
                                <Link href={dashboard()} onClick={onNavClick} className="flex items-center gap-2 rounded-none px-3 py-2 text-body-sm font-medium hover:bg-muted">
                                    <LayoutDashboard className="h-4 w-4" /> Dashboard
                                </Link>
                            )}
                            {isAdmin && (
                                <Link href={adminDashboard()} onClick={onNavClick} className="flex items-center gap-2 rounded-none px-3 py-2 text-body-sm font-medium hover:bg-muted">
                                    <Shield className="h-4 w-4" /> Admin Panel
                                    <span className="ml-auto rounded-none bg-foreground px-1.5 py-0.5 text-[10px] font-bold text-background">ADMIN</span>
                                </Link>
                            )}
                            <Link href={fundraiserLink} onClick={onNavClick} className="flex items-center gap-2 rounded-none px-3 py-2 text-body-sm font-medium hover:bg-muted">
                                <span className="text-brand">{fundraiserLabel}</span>
                            </Link>
                            <Link href={edit()} onClick={onNavClick} className="flex items-center gap-2 rounded-none px-3 py-2 text-body-sm font-medium hover:bg-muted">
                                <Settings className="h-4 w-4" /> Settings
                            </Link>
                            <Link href={logoutRoute.url()} method="post" as="button" onClick={onNavClick} className="flex items-center gap-2 rounded-none px-3 py-2 text-body-sm font-medium text-red-500 hover:bg-muted">
                                <LogOut className="h-4 w-4" /> Log out
                            </Link>
                        </div>
                    </>
                ) : (
                    <div className="flex flex-col gap-2">
                        <Link href={login()} onClick={onNavClick} className="block rounded-none border-2 border-foreground px-3 py-2 text-center text-body-sm font-medium hover:bg-foreground hover:text-background">
                            Log in
                        </Link>
                        <Link href="/register" onClick={onNavClick} className="block rounded-none border-3 border-brand bg-brand px-3 py-2 text-center font-display text-body-base font-bold text-brand-foreground shadow-hard">
                            Start a Fundraiser
                        </Link>
                    </div>
                )}
            </div>
        </>
    );
}

export default function FundMeLayout({ children }: FundMeLayoutProps) {
    const { props, url } = usePage();
    const { auth } = props;
    const flash = (props.flash as { toast?: { type?: string; message: string } } | undefined)?.toast;
    const user = auth.user as User | null;
    const hideMobileMenu = url.startsWith('/admin') || url.startsWith('/settings');
    const [mobileOpen, setMobileOpen] = useState(false);
    const closeMobile = () => setMobileOpen(false);

    useEffect(() => {
        if (!flash?.message) return;

        switch (flash.type) {
            case 'error':
                toast.error(flash.message);
                break;
            case 'warning':
                toast.warning(flash.message);
                break;
            case 'info':
                toast.info(flash.message);
                break;
            default:
                toast.success(flash.message);
        }
    }, [flash?.message, flash?.type]);

    const fundraiserLink = user
        ? user.verification_status === 'approved'
            ? campaignsIndex() + '/create'
            : verify.identity.url()
        : '/register';

    const fundraiserLabel = user
        ? user.verification_status === 'approved'
            ? 'Start a Fundraiser'
            : user.verification_status === 'pending'
                ? 'Verification Pending'
                : 'Verify to Fundraise'
        : 'Start a Fundraiser';

    return (
        <div className="flex min-h-screen flex-col bg-background">
            <header className="sticky top-0 z-50 border-b-3 border-foreground bg-card shadow-hard">
                <div className="mx-auto flex h-16 max-w-6xl items-center px-4 sm:px-6 lg:px-8">
                    {!hideMobileMenu && (
                        <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
                            <SheetTrigger asChild>
                                <button className="mr-3 flex h-9 w-9 items-center justify-center rounded-none border-2 border-foreground bg-background md:hidden">
                                    <Menu className="h-4 w-4" />
                                </button>
                            </SheetTrigger>
                            <SheetContent side="left" className="w-72 rounded-none border-r-3 border-foreground">
                                <SheetTitle className="sr-only">Navigation menu</SheetTitle>
                                <SheetHeader className="border-b-3 border-foreground pb-4">
                                    <Link href={home()} onClick={closeMobile} className="font-display text-display-sm text-foreground">
                                        FundMe Malawi
                                    </Link>
                                </SheetHeader>
                                <div className="mt-4">
                                    <MobilePanel user={user} onNavClick={closeMobile} />
                                </div>
                            </SheetContent>
                        </Sheet>
                    )}

                    <Link href={home()} className="shrink-0 font-display text-display-sm text-foreground">
                        FundMe Malawi
                    </Link>

                    <div className="ml-8 hidden md:flex">
                        <NavLinks />
                    </div>

                    <div className="ml-auto flex items-center gap-3">
                        {user ? (
                            <UserDropdown user={user} />
                        ) : (
                            <>
                                <Link href={login()} className="hidden text-body-sm font-medium text-muted-foreground hover:text-foreground md:block">
                                    Log in
                                </Link>
                                <Link href="/register">
                                    <Button variant="fundme" size="default" className="h-9 px-4 text-body-sm">
                                        Start a Fundraiser
                                    </Button>
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </header>

            <main className="flex-1">{children}</main>
        </div>
    );
}
