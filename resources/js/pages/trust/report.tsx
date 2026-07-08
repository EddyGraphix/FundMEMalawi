import { Head } from "@inertiajs/react"
import { Link } from "@inertiajs/react"
import { Flag } from "lucide-react"

export default function Report() {
  return (
    <>
      <Head title="Report Abuse" />
      <div className="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 className="font-display text-display-lg">Report Abuse</h1>
        <div className="mt-8 space-y-6 text-body-base text-muted-foreground">
          <p>If you have seen something suspicious on a campaign, you can report it directly from the campaign page.</p>
          <div className="flex items-start gap-4 border-2 border-foreground p-6">
            <Flag className="mt-1 size-6 shrink-0" />
            <div>
              <h2 className="font-display text-display-xs text-foreground">To Report a Campaign</h2>
              <p className="mt-2">Navigate to the campaign page and click the "Report" button, or use the link below.</p>
              <Link href={route("campaigns.index")} className="mt-3 inline-block font-semibold text-gold underline underline-offset-4">
                Browse Campaigns →
              </Link>
            </div>
          </div>
          <h2 className="font-display text-display-xs text-foreground">What Happens After You Report</h2>
          <ol className="list-decimal pl-6 space-y-2">
            <li>Our moderation team reviews the report within 24 hours</li>
            <li>The campaign may be temporarily paused during investigation</li>
            <li>You will be notified of the outcome</li>
            <li>All reports are handled confidentially</li>
          </ol>
        </div>
      </div>
    </>
  )
}
