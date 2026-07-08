import { Head, Link, useForm } from "@inertiajs/react"
import InputError from "@/components/input-error"
import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { show } from "@/routes/campaigns"
import { store as reportStore } from "@/routes/campaigns/report"

interface Campaign {
  id: number
  title: string
  slug: string
}

export default function CampaignReport({ campaign }: { campaign: Campaign }) {
  const { data, setData, post, processing, errors } = useForm({
    type: "",
    description: "",
  })

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    post(`/campaigns/${campaign.id}/report`, { preserveScroll: true })
  }

  return (
    <>
      <Head title={`Report: ${campaign.title}`} />
      <div className="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
        <Link href={show.url(campaign.id)} className="text-body-sm text-muted-foreground underline underline-offset-4">
          ← Back to campaign
        </Link>
        <h1 className="mt-4 font-display text-display-lg">Report Campaign</h1>
        <p className="mt-2 text-body-base text-muted-foreground">
          Tell us why you are reporting <strong>{campaign.title}</strong>.
        </p>
        <form onSubmit={handleSubmit} className="mt-8 space-y-6">
          <div>
            <Label htmlFor="type">Reason for Report</Label>
            <select
              id="type"
              className="mt-1 block w-full border-2 border-foreground bg-background px-3 py-2"
              value={data.type}
              onChange={(e) => setData("type", e.target.value)}
            >
              <option value="">Select a reason...</option>
              <option value="abuse">Abuse</option>
              <option value="scam">Scam / Fraud</option>
              <option value="inappropriate">Inappropriate Content</option>
              <option value="other">Other</option>
            </select>
            <InputError message={errors.type} />
          </div>
          <div>
            <Label htmlFor="description">Additional Details</Label>
            <Textarea
              id="description"
              className="mt-1"
              rows={6}
              value={data.description}
              onChange={(e) => setData("description", e.target.value)}
              placeholder="Please provide any details that support your report..."
            />
            <InputError message={errors.description} />
          </div>
          <Button type="submit" disabled={processing} className="w-full">
            Submit Report
          </Button>
        </form>
      </div>
    </>
  )
}
