import { Head, Link } from "@inertiajs/react"
import { ShieldCheck, Flag, UserCheck, Lock, FileText, Scale } from "lucide-react"

const sections = [
  { title: "Fraud Prevention", href: "/trust/fraud-prevention", icon: ShieldCheck, desc: "How we prevent and handle fraudulent campaigns" },
  { title: "Campaign Verification", href: "/trust/campaign-verification", icon: UserCheck, desc: "Our verification process for campaigns and organizers" },
  { title: "Donor Protection", href: "/trust/donor-protection", icon: Lock, desc: "How your donations are protected" },
  { title: "Refund Policy", href: "/trust/refund-policy", icon: FileText, desc: "Our refund and dispute resolution process" },
  { title: "Platform Safety Guidelines", href: "/trust/safety-guidelines", icon: Scale, desc: "Community standards and safety rules" },
  { title: "Report Abuse", href: "/trust/report", icon: Flag, desc: "Report suspicious campaigns or activity" },
]

export default function TrustCenter() {
  return (
    <>
      <Head title="Trust Center" />
      <div className="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 className="font-display text-display-lg text-foreground">Trust Center</h1>
        <p className="mt-3 text-body-lg text-muted-foreground">
          FundMe Malawi is committed to keeping our platform safe and transparent for everyone.
        </p>
        <div className="mt-12 grid gap-6 sm:grid-cols-2">
          {sections.map((s) => {
            const Icon = s.icon
            return (
              <Link
                key={s.href}
                href={s.href}
                className="flex items-start gap-4 border-2 border-foreground p-6 transition-shadow hover:shadow-hard"
              >
                <Icon className="mt-1 size-6 shrink-0" />
                <div>
                  <h2 className="font-display text-display-xs">{s.title}</h2>
                  <p className="mt-1 text-body-sm text-muted-foreground">{s.desc}</p>
                </div>
              </Link>
            )
          })}
        </div>
      </div>
    </>
  )
}
