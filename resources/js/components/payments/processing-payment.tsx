import { useEffect, useRef, useState, useCallback } from 'react';
import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { toast } from 'sonner';
import { Spinner } from '@/components/ui/spinner';
import { Button } from '@/components/ui/button';
import {
    CheckCircleIcon,
    XCircleIcon,
    SmartphoneIcon,
    BanknoteIcon,
    CreditCardIcon,
    ExternalLinkIcon,
    ClockIcon,
    ArrowLeftIcon,
    ReceiptIcon,
    HomeIcon,
    RotateCcwIcon,
    AlertCircleIcon,
} from 'lucide-react';
import { BankDetailsCard } from '@/components/payments/bank-transfer-details';

type ProcessingState = 'preparing' | 'awaiting_customer' | 'processing' | 'completed' | 'failed' | 'timeout';

interface CampaignInfo {
    title: string;
    cover_image?: string | null;
    owner: { name: string };
}

interface DonationInfo {
    amount: number;
    tip_amount: number;
    total: number;
    donor_phone?: string | null;
    created_at?: string;
}

interface ProcessingPaymentProps {
    donationId: number;
    paymentMethod: string;
    providerRef: string | null;
    completed?: boolean;
    cardRedirectUrl?: string | null;
    bankTransferDetails?: Record<string, unknown> | null;
    campaign: CampaignInfo;
    donation: DonationInfo;
    onReset: () => void;
}

const POLL_INTERVAL_MS = 2500;
const MAX_POLLS_BEFORE_VERIFY = 4;
const MAX_POLLS_BEFORE_TIMEOUT = 8;

type StepId = 'created' | 'initiated' | 'awaiting' | 'confirmed';

interface Step {
    id: StepId;
    label: string;
}

const STEPS: Step[] = [
    { id: 'created', label: 'Donation Created' },
    { id: 'initiated', label: 'Payment Initiated' },
    { id: 'awaiting', label: 'Awaiting Confirmation' },
    { id: 'confirmed', label: 'Payment Confirmed' },
];

function getCurrentStep(state: ProcessingState): StepId {
    switch (state) {
        case 'preparing': return 'created';
        case 'awaiting_customer': return 'awaiting';
        case 'processing': return 'awaiting';
        case 'completed': return 'confirmed';
        case 'failed': return 'awaiting';
        case 'timeout': return 'awaiting';
    }
}

function getStepStatus(stepId: StepId, state: ProcessingState): 'done' | 'current' | 'pending' | 'error' {
    const current = getCurrentStep(state);
    const stepIndex = STEPS.findIndex((s) => s.id === stepId);
    const currentIndex = STEPS.findIndex((s) => s.id === current);

    if (state === 'failed' && stepId === 'awaiting') return 'error';
    if (state === 'preparing' && stepId === 'created') return 'current';
    if (stepIndex < currentIndex) return 'done';
    if (stepIndex === currentIndex) return 'current';
    return 'pending';
}

export default function ProcessingPayment({
    donationId,
    paymentMethod,
    providerRef,
    completed: initialCompleted,
    cardRedirectUrl,
    bankTransferDetails,
    campaign,
    donation,
    onReset,
}: ProcessingPaymentProps) {
    const [state, setState] = useState<ProcessingState>(
        initialCompleted ? 'completed' : 'preparing',
    );
    const [attempt, setAttempt] = useState(0);
    const [verifyMsg, setVerifyMsg] = useState<string | null>(null);
    const [statusMessage, setStatusMessage] = useState<string | null>(null);
    const pollRef = useRef<ReturnType<typeof setInterval> | null>(null);
    const mountedRef = useRef(true);
    const toastShownRef = useRef(false);

    const isMobileMoney = paymentMethod === 'airtel_money' || paymentMethod === 'tnm_mpamba';
    const isCard = paymentMethod === 'card';
    const isBankTransfer = paymentMethod === 'bank_transfer';

    const cleanup = useCallback(() => {
        if (pollRef.current) {
            clearInterval(pollRef.current);
            pollRef.current = null;
        }
    }, []);

    const pollStatus = useCallback(async () => {
        if (!mountedRef.current) return;

        try {
            const res = await fetch(`/donations/${donationId}/status`);
            if (!res.ok) return;
            const data = await res.json();
            if (!mountedRef.current) return;

            if (data.message) {
                setStatusMessage(data.message);
            }

            if (data.status === 'completed') {
                cleanup();
                setState('completed');
                return;
            }

            if (data.status === 'failed') {
                cleanup();
                setState('failed');
                return;
            }

            setAttempt((prev) => {
                const next = prev + 1;

                if (next >= MAX_POLLS_BEFORE_TIMEOUT) {
                    cleanup();
                    setState('timeout');
                    return next;
                }

                if (next === 1) {
                    setState('awaiting_customer');
                }

                if (next >= 3) {
                    setState('processing');
                }

                if (next === MAX_POLLS_BEFORE_VERIFY) {
                    fetch(`/donations/${donationId}/verify`, { method: 'POST' })
                        .then((r) => r.json())
                        .then((v) => {
                            if (mountedRef.current) {
                                if (v.message) {
                                    setStatusMessage(v.message);
                                }
                                if (v.status === 'completed') {
                                    cleanup();
                                    setState('completed');
                                } else {
                                    setVerifyMsg('Still waiting for payment confirmation...');
                                }
                            }
                        })
                        .catch(() => {});
                }

                return next;
            });
        } catch {
            // Connection error, retry on next poll
        }
    }, [donationId, cleanup]);

    const handleCancel = useCallback(async () => {
        cleanup();
        setState('failed');
        setStatusMessage('Payment cancelled.');
        try {
            await fetch(`/donations/${donationId}/verify`, { method: 'POST' });
        } catch {
            // Silently verify — status is already set to failed
        }
    }, [donationId, cleanup]);

    useEffect(() => {
        mountedRef.current = true;
        toastShownRef.current = false;

        if (initialCompleted) {
            setState('completed');
            return;
        }

        if (isCard && cardRedirectUrl) {
            window.location.href = cardRedirectUrl;
            return;
        }

        if (isBankTransfer) {
            setState('awaiting_customer');
            pollRef.current = setInterval(pollStatus, POLL_INTERVAL_MS);
            return;
        }

        pollRef.current = setInterval(pollStatus, POLL_INTERVAL_MS);
        pollStatus();

        return () => {
            mountedRef.current = false;
            cleanup();
        };
    }, [donationId, initialCompleted, cardRedirectUrl, isCard, isBankTransfer, pollStatus, cleanup]);

    useEffect(() => {
        if (toastShownRef.current) return;

        if (state === 'completed') {
            toastShownRef.current = true;
            toast.success('Payment confirmed! Thank you for your donation.');
        } else if (state === 'failed') {
            toastShownRef.current = true;
            toast.error(statusMessage ?? 'Payment failed. Please try again.');
        }
    }, [state, statusMessage]);

    const methodLabel = {
        airtel_money: 'Airtel Money',
        tnm_mpamba: 'TNM Mpamba',
        card: 'Debit / Credit Card',
        bank_transfer: 'Bank Transfer',
    }[paymentMethod] ?? paymentMethod;

    const methodIcon = isMobileMoney
        ? SmartphoneIcon
        : isCard
            ? CreditCardIcon
            : BanknoteIcon;

    const MethodIcon = methodIcon;

    const completedAt = donation.created_at
        ? new Date(donation.created_at).toLocaleDateString('en-MW', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        })
        : null;

    function renderStatusCard() {
        switch (state) {
            case 'preparing':
                return (
                    <div className="space-y-6" role="status" aria-live="polite">
                        <div className="flex items-center justify-center">
                            <div className="relative flex size-16 items-center justify-center">
                                <Spinner className="size-8 text-brand" />
                            </div>
                        </div>
                        <div className="text-center">
                            <p className="font-display text-display-sm text-foreground">Preparing Payment</p>
                            <p className="mt-2 text-body-base text-muted-foreground">Preparing your secure payment...</p>
                            <p className="mt-1 text-body-sm text-muted-foreground">Please wait.</p>
                        </div>
                    </div>
                );

            case 'awaiting_customer':
                return (
                    <div className="space-y-6" role="status" aria-live="polite">
                        {isMobileMoney && (
                            <>
                                <div className="flex items-center justify-center">
                                    <div className="flex size-16 items-center justify-center border-3 border-brand bg-brand/5">
                                        <SmartphoneIcon className="size-8 text-brand" />
                                    </div>
                                </div>
                                <div className="text-center">
                                    <p className="font-display text-display-sm text-foreground">Check Your Phone</p>
                                    <p className="mt-3 text-body-base text-muted-foreground">
                                        A payment request has been sent to your phone.
                                    </p>
                                    <p className="mt-1 text-body-base text-muted-foreground">
                                        Please approve the payment using your Mobile Money PIN.
                                    </p>
                                    {donation.donor_phone && (
                                        <p className="mt-4 font-display text-display-sm text-foreground">
                                            {donation.donor_phone}
                                        </p>
                                    )}
                                </div>
                                <div className="flex justify-center">
                                    <button
                                        type="button"
                                        className="text-body-sm text-destructive underline underline-offset-4 hover:no-underline"
                                        onClick={handleCancel}
                                    >
                                        I cancelled on my phone
                                    </button>
                                </div>
                            </>
                        )}
                        {isCard && (
                            <>
                                <div className="flex items-center justify-center">
                                    <div className="flex size-16 items-center justify-center border-3 border-brand bg-brand/5">
                                        <ExternalLinkIcon className="size-8 text-brand" />
                                    </div>
                                </div>
                                <div className="text-center">
                                    <p className="font-display text-display-sm text-foreground">Complete Authentication</p>
                                    <p className="mt-3 text-body-base text-muted-foreground">
                                        Please complete the authentication in the opened window.
                                    </p>
                                </div>
                            </>
                        )}
                        {isBankTransfer && (
                            <>
                                <div className="flex items-center justify-center">
                                    <div className="flex size-16 items-center justify-center border-3 border-brand bg-brand/5">
                                        <BanknoteIcon className="size-8 text-brand" />
                                    </div>
                                </div>
                                <div className="text-center">
                                    <p className="font-display text-display-sm text-foreground">Complete Your Transfer</p>
                                    <p className="mt-1 text-body-sm text-muted-foreground">
                                        Your donation will be confirmed automatically once received.
                                    </p>
                                </div>
                                {bankTransferDetails && (
                                    <div className="text-left">
                                        <BankDetailsCard details={bankTransferDetails} />
                                    </div>
                                )}
                            </>
                        )}
                    </div>
                );

            case 'processing':
                return (
                    <div className="space-y-6" role="status" aria-live="polite">
                        <div className="flex items-center justify-center">
                            <div className="relative flex size-16 items-center justify-center">
                                <Spinner className="size-8 text-brand" />
                                <span className="sr-only">Verifying payment</span>
                            </div>
                        </div>
                        <div className="text-center">
                            <p className="font-display text-display-sm text-foreground">Confirming Payment</p>
                            <p className="mt-2 text-body-base text-muted-foreground">
                                We're securely confirming your payment.
                            </p>
                            {verifyMsg && (
                                <p className="mt-3 text-body-sm text-amber-600">{verifyMsg}</p>
                            )}
                        </div>
                        {isMobileMoney && (
                            <div className="flex justify-center pt-2">
                                <button
                                    type="button"
                                    className="text-body-sm text-destructive underline underline-offset-4 hover:no-underline"
                                    onClick={handleCancel}
                                >
                                    I cancelled on my phone
                                </button>
                            </div>
                        )}
                    </div>
                );

            case 'completed':
                return (
                    <div className="space-y-6" role="status" aria-live="assertive">
                        <div className="flex items-center justify-center">
                            <div className="flex size-16 items-center justify-center border-3 border-brand bg-brand/5">
                                <CheckCircleIcon className="size-8 text-brand" />
                            </div>
                        </div>
                        <div className="text-center">
                            <p className="font-display text-display-sm text-foreground">Payment Successful</p>
                            <p className="mt-2 text-body-base text-muted-foreground">
                                Thank you for supporting this campaign.
                            </p>
                            <div className="mx-auto mt-6 max-w-xs space-y-2 border-3 border-foreground bg-card p-4 shadow-hard">
                                <div className="flex justify-between text-body-sm">
                                    <span className="text-muted-foreground">Amount</span>
                                    <span className="font-semibold text-foreground">
                                        MWK {donation.total.toLocaleString()}
                                    </span>
                                </div>
                                <div className="flex justify-between text-body-sm">
                                    <span className="text-muted-foreground">Campaign</span>
                                    <span className="text-right font-semibold text-foreground">{campaign.title}</span>
                                </div>
                                {providerRef && (
                                    <div className="flex justify-between text-body-sm">
                                        <span className="text-muted-foreground">Reference</span>
                                        <span className="font-mono text-body-xs text-foreground">{providerRef}</span>
                                    </div>
                                )}
                                {completedAt && (
                                    <div className="flex justify-between text-body-sm">
                                        <span className="text-muted-foreground">Date</span>
                                        <span className="text-right text-foreground">{completedAt}</span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                );

            case 'failed':
                return (
                    <div className="space-y-6" role="alert" aria-live="assertive">
                        <div className="flex items-center justify-center">
                            <div className="flex size-16 items-center justify-center border-3 border-destructive bg-destructive/5">
                                <XCircleIcon className="size-8 text-destructive" />
                            </div>
                        </div>
                        <div className="text-center">
                            <p className="font-display text-display-sm text-foreground">Payment Failed</p>
                            <p className="mt-2 text-body-base text-muted-foreground">
                                {statusMessage ?? 'The payment could not be processed. Please try again.'}
                            </p>
                        </div>
                    </div>
                );

            case 'timeout':
                return (
                    <div className="space-y-6" role="alert" aria-live="assertive">
                        <div className="flex items-center justify-center">
                            <div className="flex size-16 items-center justify-center border-3 border-destructive bg-destructive/5">
                                <ClockIcon className="size-8 text-destructive" />
                            </div>
                        </div>
                        <div className="text-center">
                            <p className="font-display text-display-sm text-foreground">Timed Out</p>
                            <p className="mt-2 text-body-base text-muted-foreground">
                                The payment didn't arrive in time. This usually means the request was declined or cancelled on your phone.
                            </p>
                            {statusMessage && (
                                <p className="mt-3 text-body-sm text-muted-foreground">{statusMessage}</p>
                            )}
                        </div>
                    </div>
                );
        }
    }

    function renderTimeline() {
        return (
            <nav aria-label="Payment progress" className="w-full">
                <ol className="relative space-y-4">
                    {STEPS.map((step) => {
                        const stepStatus = getStepStatus(step.id, state);

                        let dot: React.ReactNode;
                        let line: React.ReactNode;

                        if (stepStatus === 'done') {
                            dot = (
                                <span className="flex size-6 items-center justify-center border-3 border-brand bg-brand">
                                    <CheckCircleIcon className="size-4 text-brand-foreground" />
                                </span>
                            );
                        } else if (stepStatus === 'current') {
                            dot = (
                                <span className="flex size-6 items-center justify-center border-3 border-foreground bg-card">
                                    {state === 'processing' || state === 'preparing' ? (
                                        <Spinner className="size-3 text-foreground" />
                                    ) : (
                                        <span className="size-2 bg-foreground" />
                                    )}
                                </span>
                            );
                        } else if (stepStatus === 'error') {
                            dot = (
                                <span className="flex size-6 items-center justify-center border-3 border-destructive bg-destructive/5">
                                    <XCircleIcon className="size-4 text-destructive" />
                                </span>
                            );
                        } else {
                            dot = (
                                <span className="flex size-6 items-center justify-center border-3 border-foreground/20 bg-card">
                                    <span className="size-2 bg-foreground/20" />
                                </span>
                            );
                        }

                        if (step.id !== 'confirmed') {
                            const nextStatus = getStepStatus(
                                STEPS[STEPS.findIndex((s) => s.id === step.id) + 1].id,
                                state,
                            );
                            const isLineDone = nextStatus === 'done' || nextStatus === 'current' || nextStatus === 'error';
                            line = (
                                <div
                                    className={`ml-[11px] mt-1 h-8 w-[3px] ${
                                        isLineDone ? 'bg-brand' : 'bg-foreground/10'
                                    }`}
                                    aria-hidden="true"
                                />
                            );
                        }

                        return (
                            <li key={step.id} className="flex items-start gap-3">
                                <div className="flex flex-col items-center">
                                    {dot}
                                    {line}
                                </div>
                                <div className="pt-0.5">
                                    <span
                                        className={`text-body-sm font-semibold ${
                                            stepStatus === 'done' || stepStatus === 'current'
                                                ? 'text-foreground'
                                                : stepStatus === 'error'
                                                    ? 'text-destructive'
                                                    : 'text-muted-foreground'
                                        }`}
                                    >
                                        {step.label}
                                    </span>
                                </div>
                            </li>
                        );
                    })}
                </ol>
            </nav>
        );
    }

    function renderActions() {
        switch (state) {
            case 'completed':
                return (
                    <div className="flex flex-wrap items-center justify-center gap-3">
                        <Button
                            variant="fundme"
                            size="xl"
                            onClick={() => {
                                router.visit(
                                    window.location.pathname.replace(/\/donate.*/, ''),
                                    {
                                        onSuccess: () => {
                                            toast.success('Your donation has been received. Thank you!');
                                        },
                                    },
                                );
                            }}
                        >
                            View Campaign
                        </Button>
                        <Button
                            variant="outline"
                            size="xl"
                            className="border-3 border-foreground bg-card font-display text-body-base text-foreground shadow-hard rounded-none"
                            onClick={() => toast.info('Receipt download coming soon.')}
                        >
                            <ReceiptIcon className="size-5" />
                            Download Receipt
                        </Button>
                        <Link
                            href="/"
                            className="inline-flex items-center gap-2 text-body-sm text-muted-foreground underline underline-offset-4 hover:text-foreground"
                        >
                            <HomeIcon className="size-4" />
                            Return Home
                        </Link>
                    </div>
                );

            case 'failed':
                return (
                    <div className="flex flex-wrap items-center justify-center gap-3">
                        <Button variant="fundme" size="xl" onClick={onReset}>
                            <RotateCcwIcon className="size-5" />
                            Try Again
                        </Button>
                        <Button
                            variant="outline"
                            size="xl"
                            className="border-3 border-foreground bg-card font-display text-body-base text-foreground shadow-hard rounded-none"
                            onClick={() => {
                                window.location.href = window.location.pathname.replace(/\/donate.*/, '');
                            }}
                        >
                            <ArrowLeftIcon className="size-5" />
                            Return to Campaign
                        </Button>
                    </div>
                );

            case 'timeout':
                return (
                    <div className="flex flex-wrap items-center justify-center gap-3">
                        <Button
                            variant="fundme"
                            size="xl"
                            onClick={() => {
                                setState('processing');
                                setAttempt(0);
                                toastShownRef.current = false;
                                setVerifyMsg(null);
                                pollRef.current = setInterval(pollStatus, POLL_INTERVAL_MS);
                            }}
                        >
                            <RotateCcwIcon className="size-5" />
                            Check Again
                        </Button>
                        <Button
                            variant="outline"
                            size="xl"
                            className="border-3 border-foreground bg-card font-display text-body-base text-foreground shadow-hard rounded-none"
                            onClick={onReset}
                        >
                            Close
                        </Button>
                    </div>
                );

            default:
                return null;
        }
    }

    if (state === 'completed') {
        return (
            <div className="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8" role="main" aria-label="Payment completed">
                <div className="text-center">
                    <Link href="/" className="inline-block font-display text-display-sm text-foreground">
                        FundMe Malawi
                    </Link>
                </div>

                <div className="mt-8 border-3 border-foreground bg-card p-8 shadow-hard">
                    {renderStatusCard()}
                </div>

                <div className="mt-8">
                    {renderTimeline()}
                </div>

                <div className="mt-10">
                    {renderActions()}
                </div>
            </div>
        );
    }

    return (
        <div className="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8" role="main" aria-label="Payment processing">
            <div className="text-center">
                <Link href="/" className="inline-block font-display text-display-sm text-foreground">
                    FundMe Malawi
                </Link>
            </div>

            <div className="mt-6 border-3 border-foreground bg-card p-6 shadow-hard">
                <div className="flex items-center gap-4">
                    <MethodIcon className="size-10 shrink-0 text-muted-foreground" />
                    <div className="min-w-0">
                        <p className="font-display text-display-xs text-foreground truncate">
                            {campaign.title}
                        </p>
                        <p className="text-body-sm text-muted-foreground">
                            by {campaign.owner.name}
                        </p>
                    </div>
                </div>
                <div className="mt-4 flex items-baseline justify-between border-t-3 border-foreground/10 pt-4">
                    <div>
                        <p className="text-body-sm text-muted-foreground">
                            {methodLabel}
                        </p>
                        <p className="font-display text-display-sm text-foreground">
                            MWK {donation.total.toLocaleString()}
                        </p>
                    </div>
                    {state !== 'preparing' && (
                        <span className="text-body-sm font-semibold text-brand">
                            {state === 'awaiting_customer' && 'Awaiting Approval'}
                            {state === 'processing' && 'Confirming'}
                            {state === 'timeout' && 'Still Processing'}
                            {state === 'failed' && 'Failed'}
                        </span>
                    )}
                </div>
            </div>

            <div className="mt-6 border-3 border-foreground bg-card p-8 shadow-hard">
                {renderStatusCard()}
            </div>

            <div className="mt-8">
                {renderTimeline()}
            </div>

            {state === 'preparing' && (
                <div className="mt-6 flex items-center justify-center gap-2 text-body-sm text-muted-foreground">
                    <div className="size-2 animate-pulse rounded-none bg-brand" aria-hidden="true" />
                    Checking payment status...
                </div>
            )}

            <div className="mt-8">
                {renderActions()}
            </div>
        </div>
    );
}