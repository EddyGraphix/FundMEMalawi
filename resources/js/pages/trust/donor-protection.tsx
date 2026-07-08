import { Head } from "@inertiajs/react"

export default function DonorProtection() {
  return (
    <>
      <Head title="Donor Protection" />
      <div className="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 className="font-display text-display-lg">Donor Protection</h1>
        <div className="mt-8 space-y-6 text-body-base text-muted-foreground">
          <p>Your donations are handled securely through PayChangu, Malawi's trusted payment gateway.</p>
          <h2 className="font-display text-display-xs text-foreground">Protection Measures</h2>
          <ul className="list-disc pl-6 space-y-2">
            <li>Encrypted payment processing</li>
            <li>Verified campaigns only</li>
            <li>Transparent fee structure</li>
            <li>Donation receipts for all contributions</li>
            <li>Dispute resolution support</li>
          </ul>
        </div>
      </div>
    </>
  )
}
