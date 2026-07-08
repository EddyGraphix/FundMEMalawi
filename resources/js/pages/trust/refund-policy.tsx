import { Head } from "@inertiajs/react"

export default function RefundPolicy() {
  return (
    <>
      <Head title="Refund Policy" />
      <div className="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 className="font-display text-display-lg">Refund Policy</h1>
        <div className="mt-8 space-y-6 text-body-base text-muted-foreground">
          <h2 className="font-display text-display-xs text-foreground">When Refunds Are Available</h2>
          <ul className="list-disc pl-6 space-y-2">
            <li>Campaign was fraudulent — full refund</li>
            <li>Technical error caused duplicate donation — full refund</li>
            <li>Campaign cancelled before funds were withdrawn — full refund</li>
          </ul>
          <h2 className="font-display text-display-xs text-foreground">Refund Process</h2>
          <p>Contact support@fundme.mw with your donation reference. Refunds are processed within 5-10 business days.</p>
          <h2 className="font-display text-display-xs text-foreground">Non-Refundable Situations</h2>
          <p>Once a campaign organizer has withdrawn funds, refunds are at the organizer's discretion. We facilitate communication but cannot guarantee refunds.</p>
        </div>
      </div>
    </>
  )
}
