import { Head } from "@inertiajs/react"

export default function SafetyGuidelines() {
  return (
    <>
      <Head title="Platform Safety Guidelines" />
      <div className="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 className="font-display text-display-lg">Platform Safety Guidelines</h1>
        <div className="mt-8 space-y-6 text-body-base text-muted-foreground">
          <h2 className="font-display text-display-xs text-foreground">Community Standards</h2>
          <ul className="list-disc pl-6 space-y-2">
            <li>Be honest about your campaign purpose and use of funds</li>
            <li>Do not create misleading or deceptive campaigns</li>
            <li>Respect donor privacy and confidentiality</li>
            <li>Provide regular updates to your supporters</li>
            <li>Use funds only for the stated campaign purpose</li>
          </ul>
          <h2 className="font-display text-display-xs text-foreground">Prohibited Activities</h2>
          <ul className="list-disc pl-6 space-y-2">
            <li>Fraudulent or misleading campaigns</li>
            <li>Hate speech, violence, or illegal activities</li>
            <li>Impersonation of individuals or organizations</li>
            <li>Manipulation of donation metrics</li>
          </ul>
        </div>
      </div>
    </>
  )
}
