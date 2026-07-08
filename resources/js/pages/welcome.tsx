import { Head, Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';
import { register } from '@/routes';
import { index as campaignsIndex } from '@/routes/campaigns';

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="FundMe Malawi — Crowdfunding That Works for Malawi" />
            <div className="flex min-h-screen flex-col bg-background">
                <header className="border-b-3 border-foreground bg-card">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                        <Link href="/" className="font-display text-display-sm text-foreground">
                            FundMe Malawi
                        </Link>
                        <nav className="flex items-center gap-4">
                            <Link
                                href={campaignsIndex()}
                                className="text-body-sm font-medium text-muted-foreground hover:text-foreground"
                            >
                                Browse campaigns
                            </Link>
                            {auth.user ? (
                                <Link href={dashboard()}>
                                    <Button variant="fundme" size="sm">
                                        Dashboard
                                    </Button>
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={login()}
                                        className="text-body-sm font-medium text-muted-foreground hover:text-foreground"
                                    >
                                        Log in
                                    </Link>
                                    <Link href={register()}>
                                        <Button variant="fundme" size="sm">
                                            Get started
                                        </Button>
                                    </Link>
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                <main className="flex-1">
                    <section className="border-b-3 border-foreground bg-background">
                        <div className="mx-auto max-w-6xl px-4 py-20 sm:px-6 lg:px-8 lg:py-32">
                            <div className="max-w-3xl">
                                <h1 className="font-display text-display-xl leading-[1.05] text-foreground">
                                    Raise funds for what
                                    <span className="text-brand"> matters most</span> to Malawi
                                </h1>
                                <p className="mt-6 max-w-xl text-body-xl text-muted-foreground">
                                    FundMe Malawi is the crowdfunding platform built for Malawians —
                                    for medical bills, education, community projects, and dreams that
                                    need a village behind them.
                                </p>
                                <div className="mt-10 flex flex-wrap gap-4">
                                    <Link href={register()}>
                                        <Button variant="fundme" size="xl">
                                            Start a campaign
                                        </Button>
                                    </Link>
                                    <Link href={campaignsIndex()}>
                                        <Button variant="outline" size="xl" className="rounded-none border-3 border-foreground font-display text-display-sm">
                                            Browse campaigns
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section className="border-b-3 border-foreground bg-card py-20">
                        <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                            <h2 className="font-display text-display-lg text-foreground">
                                How it works
                            </h2>
                            <div className="mt-12 grid gap-8 md:grid-cols-3">
                                {[
                                    {
                                        step: '01',
                                        title: 'Tell your story',
                                        desc: 'Create a campaign in minutes. Share what you\'re raising for and why it matters.',
                                    },
                                    {
                                        step: '02',
                                        title: 'Share with your community',
                                        desc: 'Spread the word via WhatsApp, social media, or directly — reach people who care.',
                                    },
                                    {
                                        step: '03',
                                        title: 'Receive funds securely',
                                        desc: 'Donations go straight to your mobile money or bank. Transparent, low fees, no surprises.',
                                    },
                                ].map((item) => (
                                    <div key={item.step} className="border-3 border-foreground bg-background p-6 shadow-hard">
                                        <span className="font-display text-display-md text-brand">{item.step}</span>
                                        <h3 className="mt-4 font-display text-display-sm text-foreground">{item.title}</h3>
                                        <p className="mt-2 text-body-base text-muted-foreground">{item.desc}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </section>

                    <section className="bg-background py-20">
                        <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                            <div className="border-3 border-foreground bg-brand p-8 text-center shadow-hard lg:p-16">
                                <h2 className="font-display text-display-lg text-brand-foreground">
                                    Ready to make a difference?
                                </h2>
                                <p className="mx-auto mt-4 max-w-lg text-body-xl text-brand-foreground/90">
                                    Whether you're raising funds or donating, every contribution
                                    builds a stronger Malawi.
                                </p>
                                <div className="mt-8 flex justify-center gap-4">
                                    <Link href={register()}>
                                        <Button
                                            variant="outline"
                                            size="xl"
                                            className="rounded-none border-3 border-brand-foreground bg-brand-foreground text-brand font-display text-display-sm hover:bg-brand-foreground/90"
                                        >
                                            Start fundraising
                                        </Button>
                                    </Link>
                                    <Link href={campaignsIndex()}>
                                        <Button
                                            variant="outline"
                                            size="xl"
                                            className="rounded-none border-3 border-brand-foreground text-brand-foreground font-display text-display-sm hover:bg-brand-foreground/10"
                                        >
                                            Donate now
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>

                <footer className="border-t-3 border-foreground bg-card py-8">
                    <div className="mx-auto max-w-6xl px-4 text-center text-body-sm text-muted-foreground sm:px-6 lg:px-8">
                        &copy; {new Date().getFullYear()} FundMe Malawi. Built for Malawians, by Malawians.
                    </div>
                </footer>
            </div>
        </>
    );
}
