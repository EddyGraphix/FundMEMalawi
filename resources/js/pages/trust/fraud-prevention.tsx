import { Head } from "@inertiajs/react"

export default function FraudPrevention() {
  return (
    <>
      <Head title="Fraud Prevention" />
      <div className="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 className="font-display text-display-lg">Fraud Prevention</h1>
        <div className="mt-8 space-y-6 text-body-base text-muted-foreground">
          <p>We take fraud seriously. Our team reviews every campaign before it goes live and monitors activity for suspicious behavior.</p>
          <h2 className="font-display text-display-xs text-foreground">How We Prevent Fraud</h2>
          <ul className="list-disc pl-6 space-y-2">
            <li>Identity verification for all organizers</li>
            <li>Campaign review before activation</li>
            <li>Automated fraud detection algorithms</li>
            <li>Manual review of flagged campaigns</li>
            <li>Community reporting system</li>
          </ul>
          <h2 className="font-display text-display-xs text-foreground">What to Do If You Suspect Fraud</h2>
          <p>Use the Report Abuse feature on any campaign page or contact our support team immediately at support@fundme.mw.</p>
        </div>
      </div>
    </>
  )
}
