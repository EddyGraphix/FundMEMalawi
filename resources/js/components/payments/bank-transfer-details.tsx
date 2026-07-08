import { toast } from 'sonner';
import { CopyIcon } from 'lucide-react';
import { useClipboard } from '@/hooks/use-clipboard';

interface Field {
    label: string;
    value: string;
}

const LABEL_MAP: Record<string, string> = {
    bank_name: 'Bank',
    account_name: 'Account Name',
    account_number: 'Account Number',
    sort_code: 'Sort Code',
    branch: 'Branch',
    reference: 'Reference',
    narration: 'Narration',
    amount: 'Amount',
    currency: 'Currency',
    account_expiration_timestamp: 'Expires',
};

function formatTimestamp(ts: unknown): string {
    if (!ts) return '';
    const num = Number(ts);
    if (isNaN(num)) return String(ts);
    return new Date(num * 1000).toLocaleString('en-MW', {
        dateStyle: 'long',
        timeStyle: 'short',
    });
}

function formatValue(key: string, value: unknown): string {
    if (key === 'account_expiration_timestamp') {
        return formatTimestamp(value);
    }
    return String(value);
}

function formatBankDetails(details: Record<string, unknown>): Field[] {
    return Object.entries(LABEL_MAP)
        .filter(([key]) => details[key] !== undefined && details[key] !== null)
        .map(([key, label]) => ({
            label,
            value: formatValue(key, details[key]),
        }));
}

export function BankDetailsCard({ details }: { details: Record<string, unknown> }) {
    const { copy } = useClipboard();
    const fields = formatBankDetails(details);

    function handleCopyAll() {
        const text = fields.map((f) => `${f.label}: ${f.value}`).join('\n');
        copy(text);
        toast.success('Bank details copied');
    }

    return (
        <div className="rounded-md border-2 border-foreground/20 bg-muted p-4 text-left">
            <div className="mb-3 flex items-center justify-between">
                <h3 className="font-display text-display-xs text-foreground">Bank Transfer Details</h3>
                <button
                    onClick={handleCopyAll}
                    className="flex items-center gap-1 text-body-xs text-muted-foreground underline underline-offset-2 hover:text-foreground"
                >
                    <CopyIcon className="size-3" />
                    Copy all
                </button>
            </div>
            <dl className="space-y-2">
                {fields.map((field) => (
                    <div key={field.label} className="flex items-center justify-between gap-4">
                        <dt className="shrink-0 text-body-xs font-medium text-muted-foreground">
                            {field.label}
                        </dt>
                        <dd className="font-mono text-body-sm font-semibold text-foreground">
                            {field.value}
                        </dd>
                    </div>
                ))}
            </dl>
        </div>
    );
}
