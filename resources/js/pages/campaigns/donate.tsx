import { Head, Link, usePage, useForm } from '@inertiajs/react';
import { useState, useMemo, useEffect } from 'react';
import { toast } from 'sonner';

import { DonateButton } from '@/components/donate-button';
import InputError from '@/components/input-error';
import { BankDetailsCard } from '@/components/payments/bank-transfer-details';
import ProcessingPayment from '@/components/payments/processing-payment';
import { ProgressBar } from '@/components/progress-bar';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { show as campaignShow } from '@/routes/campaigns';
import { store as donateStore } from '@/routes/campaigns/donate';

interface Campaign {
    id: number;
    title: string;
    slug: string;
    goal_amount: number;
    raised_amount: number;
    owner: { name: string };
}

interface PaymentProcessing {
    donation_id: number;
    payment_method: string;
    provider_ref: string | null;
    completed?: boolean;
    amount?: number;
    tip_amount?: number;
    total?: number;
    donor_phone?: string | null;
}

type Props = {
    campaign: Campaign;
    bank_transfer_details?: Record<string, unknown> | null;
    payment_processing?: PaymentProcessing | null;
    card_redirect_url?: string | null;
};

const PRESET_AMOUNTS = [10, 50, 100, 250, 500, 1000];
const TIP_PRESETS = [0, 5, 10, 25];
const PAYMENT_METHODS = [
    { value: 'airtel_money', label: 'Airtel Money' },
    { value: 'tnm_mpamba', label: 'TNM Mpamba' },
    { value: 'card', label: 'Debit/Credit Card' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
] as const;

function isMobileMoney(method: string): boolean {
    return method === 'airtel_money' || method === 'tnm_mpamba';
}

export default function Donate({
    campaign,
    bank_transfer_details,
    payment_processing,
    card_redirect_url,
}: Props) {
    const page = usePage();
    const { auth } = page.props;
    const [customAmount, setCustomAmount] = useState('');
    const [dismissedDonationId, setDismissedDonationId] = useState<number | null>(null);

    const showProcessing = !!payment_processing && payment_processing.donation_id !== dismissedDonationId;

    useEffect(() => {
        const flash = (page.props.flash as { toast?: { type?: string; message: string } } | undefined)?.toast;

        if (!flash?.message) {
            return;
        }

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
                break;
        }
    }, [page.props.flash]);

    const { data, setData, post, processing, errors, clearErrors } = useForm({
        amount: 50,
        payment_method: 'airtel_money',
        donor_name: auth.user?.name ?? '',
        donor_email: auth.user?.email ?? '',
        donor_phone: auth.user?.phone ?? '',
        is_anonymous: false,
        message: '',
        tip_amount: 0,
        card_number: '',
        expiry: '',
        cvv: '',
        cardholder_name: '',
    });

    const methodLabel = useMemo(
        () => PAYMENT_METHODS.find((m) => m.value === data.payment_method)?.label ?? '',
        [data.payment_method],
    );

    const totalAmount = useMemo(
        () => data.amount + data.tip_amount,
        [data.amount, data.tip_amount],
    );

    const pageErrors = usePage().props.errors as Record<string, string> | undefined;
    const paymentError = (errors as Record<string, string>).payment || pageErrors?.payment;

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();

        post(donateStore.url({ campaign: campaign.id }), {
            onError: (errors) => {
                const errMap = errors as Record<string, string>;
                const msg = errMap?.payment
                    ?? Object.values(errMap).find(Boolean)
                    ?? 'Donation could not be processed. Please check the form for errors.';
                toast.error(msg);
            },
        });
    }

    function renderPaymentFields() {
        if (isMobileMoney(data.payment_method)) {
            return (
                <div className="grid gap-2">
                    <Label htmlFor="donor_phone">Mobile Money Number</Label>
                    <p className="text-body-xs text-muted-foreground">
                        The number that will receive the payment prompt
                    </p>
                    <Input
                        id="donor_phone"
                        type="tel"
                        value={data.donor_phone}
                        onChange={(e) => setData('donor_phone', e.target.value)}
                        placeholder="0991234567"
                    />
                    <InputError message={errors.donor_phone} />
                </div>
            );
        }

        if (data.payment_method === 'card') {
            return (
                <div className="grid gap-4">
                    <div className="grid gap-2">
                        <Label htmlFor="cardholder_name">Cardholder Name</Label>
                        <Input
                            id="cardholder_name"
                            value={data.cardholder_name}
                            onChange={(e) => setData('cardholder_name', e.target.value)}
                            placeholder="John Banda"
                        />
                        <InputError message={errors.cardholder_name} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="card_number">Card Number</Label>
                        <Input
                            id="card_number"
                            value={data.card_number}
                            onChange={(e) => setData('card_number', e.target.value)}
                            placeholder="4111111111111111"
                            maxLength={19}
                        />
                        <InputError message={errors.card_number} />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="expiry">Expiry (MM/YY)</Label>
                            <Input
                                id="expiry"
                                value={data.expiry}
                                onChange={(e) => {
                                    const raw = e.target.value.replace(/[^0-9]/g, '').slice(0, 4);
                                    const formatted = raw.length > 2 ? `${raw.slice(0, 2)}/${raw.slice(2)}` : raw;
                                    setData('expiry', formatted);
                                }}
                                placeholder="MM/YY"
                                maxLength={5}
                            />
                            <InputError message={errors.expiry} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="cvv">CVV</Label>
                            <Input
                                id="cvv"
                                type="password"
                                value={data.cvv}
                                onChange={(e) => setData('cvv', e.target.value)}
                                placeholder="123"
                                maxLength={4}
                            />
                            <InputError message={errors.cvv} />
                        </div>
                    </div>
                </div>
            );
        }

        if (data.payment_method === 'bank_transfer') {
            return (
                <div className="rounded-md border-2 border-foreground/20 bg-card p-4">
                    <p className="text-body-sm font-semibold text-foreground">
                        Pay via Bank Transfer
                    </p>
                    <p className="mt-2 text-body-sm text-muted-foreground">
                        After clicking donate, you will receive bank transfer instructions to complete your payment.
                    </p>
                </div>
            );
        }

        return null;
    }

    function renderBankTransferInstructions() {
        if (!bank_transfer_details) {
            return null;
        }

        return (
            <div className="rounded-md border-2 border-brand bg-brand/5 p-4">
                <p className="mb-3 font-semibold text-foreground">Bank Transfer Instructions</p>
                <BankDetailsCard details={bank_transfer_details} />
                <p className="mt-3 text-body-sm text-muted-foreground">
                    Please complete the transfer using the details above. Your donation will be
                    confirmed once the payment is received.
                </p>
            </div>
        );
    }

    const loadingText = {
        airtel_money: 'Initiating mobile money payment...',
        tnm_mpamba: 'Initiating mobile money payment...',
        card: 'Processing card payment...',
        bank_transfer: 'Preparing bank transfer instructions...',
    }[data.payment_method] ?? 'Processing...';

    if (showProcessing && payment_processing) {
        return (
            <ProcessingPayment
                donationId={payment_processing.donation_id}
                paymentMethod={payment_processing.payment_method}
                providerRef={payment_processing.provider_ref}
                completed={payment_processing.completed}
                cardRedirectUrl={card_redirect_url}
                bankTransferDetails={bank_transfer_details}
                campaign={campaign}
                donation={{
                    amount: payment_processing.amount ?? 0,
                    tip_amount: payment_processing.tip_amount ?? 0,
                    total: payment_processing.total ?? 0,
                    donor_phone: payment_processing.donor_phone,
                }}
                onReset={() => setDismissedDonationId(payment_processing?.donation_id ?? null)}
            />
        );
    }

    return (
        <>
            <Head title={`Donate to ${campaign.title}`} />
            <div className="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
                <Link
                    href={campaignShow(campaign.id)}
                    className="text-body-sm text-muted-foreground underline underline-offset-4"
                >
                    ← Back to campaign
                </Link>

                <h1 className="mt-4 font-display text-display-lg text-foreground">
                    Donate to {campaign.title}
                </h1>

                <div className="mt-8 grid gap-8 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        {bank_transfer_details && renderBankTransferInstructions()}

                        <form onSubmit={handleSubmit} className="space-y-8">
                            {(paymentError) && (
                                <div className="border-3 border-red-500 bg-red-50 p-4 shadow-hard">
                                    <div className="flex items-center gap-3">
                                        <span className="flex size-8 shrink-0 items-center justify-center bg-red-500 text-sm font-bold text-white">!</span>
                                        <p className="text-body-sm font-medium text-red-800">
                                            {paymentError}
                                        </p>
                                    </div>
                                </div>
                            )}

                            <div>
                                <Label>Choose amount (MWK)</Label>
                                <div className="mt-3 grid grid-cols-3 gap-2">
                                    {PRESET_AMOUNTS.map((preset) => (
                                        <button
                                            key={preset}
                                            type="button"
                                            onClick={() => {
                                                setData('amount', preset);
                                                setCustomAmount('');
                                            }}
                                            className={`border-3 p-3 text-center font-semibold transition-shadow hover:shadow-hard ${
                                                data.amount === preset && !customAmount
                                                    ? 'border-brand bg-brand/5 shadow-hard'
                                                    : 'border-foreground'
                                            }`}
                                        >
                                            MK {preset}
                                        </button>
                                    ))}
                                </div>
                                <div className="mt-2">
                                    <Input
                                        type="number"
                                        placeholder="Custom amount (MWK)"
                                        min={1}
                                        value={customAmount}
                                        onChange={(e) => {
                                            setCustomAmount(e.target.value);
                                            const val = parseFloat(e.target.value);
                                            
                                            if (!isNaN(val) && val >= 1) {
                                                setData('amount', val);
                                            }
                                        }}
                                    />
                                </div>
                                <InputError message={errors.amount} />
                            </div>

                            <div>
                                <Label>Payment method</Label>
                                <div className="mt-3 grid grid-cols-2 gap-2">
                                    {PAYMENT_METHODS.map((method) => (
                                        <button
                                            key={method.value}
                                            type="button"
                                            onClick={() => {
                                                clearErrors('payment');
                                                setData('payment_method', method.value);

                                                if (!isMobileMoney(method.value)) {
                                                    setData('donor_phone', '');
                                                }

                                                if (method.value !== 'card') {
                                                    setData('card_number', '');
                                                    setData('expiry', '');
                                                    setData('cvv', '');
                                                    setData('cardholder_name', '');
                                                }
                                            }}
                                            className={`border-3 p-3 text-center text-body-sm font-semibold transition-shadow hover:shadow-hard ${
                                                data.payment_method === method.value
                                                    ? 'border-brand bg-brand/5 shadow-hard'
                                                    : 'border-foreground'
                                            }`}
                                        >
                                            {method.label}
                                        </button>
                                    ))}
                                </div>
                                <InputError message={errors.payment_method} />
                            </div>

                            <div className="rounded-md border-2 border-foreground/10 bg-card p-4">
                                {renderPaymentFields()}
                            </div>

                            <Separator />

                            <div className="grid gap-2">
                                <Label htmlFor="donor_name">Your name</Label>
                                <Input
                                    id="donor_name"
                                    value={data.donor_name}
                                    onChange={(e) => setData('donor_name', e.target.value)}
                                    placeholder="John Banda"
                                />
                                <InputError message={errors.donor_name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="donor_email">Email</Label>
                                <Input
                                    id="donor_email"
                                    type="email"
                                    value={data.donor_email}
                                    onChange={(e) => setData('donor_email', e.target.value)}
                                    placeholder="john@example.com"
                                />
                                <InputError message={errors.donor_email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="message">Message (optional)</Label>
                                <Textarea
                                    id="message"
                                    rows={3}
                                    value={data.message}
                                    onChange={(e) => setData('message', e.target.value)}
                                    placeholder="Leave a message of support..."
                                />
                                <InputError message={errors.message} />
                            </div>

                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="is_anonymous"
                                    checked={data.is_anonymous}
                                    onChange={(e) => setData('is_anonymous', e.target.checked)}
                                />
                                <Label htmlFor="is_anonymous" className="mb-0">
                                    Donate anonymously
                                </Label>
                            </div>

                            <Separator />

                            <div>
                                <Label>Tip FundMe Malawi (optional)</Label>
                                <p className="text-body-xs text-muted-foreground">
                                    Help us keep the platform running. 100% optional.
                                </p>
                                <div className="mt-3 flex gap-2">
                                    {TIP_PRESETS.map((preset) => (
                                        <button
                                            key={preset}
                                            type="button"
                                            onClick={() => setData('tip_amount', preset)}
                                            className={`border-2 px-4 py-2 text-body-sm font-semibold ${
                                                data.tip_amount === preset
                                                    ? 'border-brand bg-brand/5'
                                                    : 'border-foreground'
                                            }`}
                                        >
                                            {preset === 0 ? 'No tip' : `MK ${preset}`}
                                        </button>
                                    ))}
                                </div>
                                <InputError message={errors.tip_amount} />
                            </div>

                            <DonateButton type="submit" size="lg" className="w-full" disabled={processing}>
                                {processing ? loadingText : `Donate MK ${totalAmount.toLocaleString()}`}
                            </DonateButton>
                        </form>
                    </div>

                    <div className="lg:col-span-1">
                        <div className="sticky top-8 space-y-6">
                            <div className="border-3 border-foreground bg-card p-6 shadow-hard">
                                <h3 className="font-display text-display-sm text-foreground">{campaign.title}</h3>
                                <div className="mt-4">
                                    <ProgressBar
                                        value={campaign.raised_amount}
                                        max={campaign.goal_amount}
                                        showLabel
                                    />
                                </div>
                                <div className="mt-4 text-body-sm text-muted-foreground">
                                    MK {Math.floor(campaign.raised_amount ?? 0).toLocaleString()} raised of MK{' '}
                                    {Math.floor(campaign.goal_amount ?? 0).toLocaleString()}
                                </div>
                            </div>

                            <div className="border-3 border-foreground bg-card p-6 shadow-hard">
                                <h4 className="font-display text-display-xs text-foreground">Payment Summary</h4>
                                <div className="mt-4 space-y-2 text-body-sm">
                                    <div className="flex items-center justify-between">
                                        <span className="text-muted-foreground">Donation</span>
                                        <span className="font-semibold">MK {(data.amount ?? 0).toLocaleString()}</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-muted-foreground">Platform tip</span>
                                        <span className="font-semibold">
                                            {data.tip_amount > 0 ? `MK ${data.tip_amount.toLocaleString()}` : '—'}
                                        </span>
                                    </div>
                                    <Separator />
                                    <div className="flex items-center justify-between text-body-base">
                                        <span className="font-semibold text-foreground">Total</span>
                                        <span className="font-display text-display-sm text-foreground">
                                            MK {totalAmount.toLocaleString()}
                                        </span>
                                    </div>
                                    <div className="pt-2">
                                        <span className="text-body-xs text-muted-foreground">
                                            via {methodLabel}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
