import { Head } from "@inertiajs/react"

export default function CampaignVerification() {
  return (
    <>
      <Head title="Campaign Verification" />
      <div className="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 className="font-display text-display-lg">Campaign Verification</h1>
        <div className="mt-8 space-y-6 text-body-base text-muted-foreground">
          <p>Campaigns that pass our verification process earn a badge, giving donors confidence that the fundraiser is legitimate.</p>
          <h2 className="font-display text-display-xs text-foreground">Verification Levels</h2>
          <ul className="list-disc pl-6 space-y-2">
            <li><strong>Identity Verified</strong> — Organizer has submitted valid ID documentation</li>
            <li><strong>Campaign Verified</strong> — Campaign details have been reviewed and confirmed</li>
            <li><strong>Beneficiary Verified</strong> — The beneficiary has been identified and confirmed</li>
          </ul>
        </div>
      </div>
    </>
  )
}
