import { Link } from "@inertiajs/react"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar"
import { Progress } from "@/components/ui/progress"
import { cn } from "@/lib/utils"
import { show } from "@/routes/campaigns"

interface CampaignCardProps {
  campaign: {
    id: number
    title: string
    slug: string
    goal_amount: number
    raised_amount: number
    currency: string
    cover_url?: string
    owner: { name: string; avatar_url?: string }
    category: { name: string; slug: string }
    days_remaining?: number
    donor_count: number
  }
  className?: string
}

function formatMWK(amount: number, currency: string = "MWK"): string {
  if (currency === "MWK") {
    return `MK ${amount.toLocaleString("en-US", { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`
  }
  return `${currency} ${amount.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

export function CampaignCard({ campaign, className }: CampaignCardProps) {
  const percentage = Math.min(
    (campaign.raised_amount / campaign.goal_amount) * 100,
    100
  )

  return (
    <Link
      href={show.url(campaign.id)}
      className={cn(
        "group block border-3 border-foreground bg-card transition-shadow hover:shadow-hard",
        className
      )}
    >
      {campaign.cover_url && (
        <div className="aspect-[16/9] overflow-hidden border-b-3 border-foreground">
          <img
            src={campaign.cover_url}
            alt={campaign.title}
            className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
          />
        </div>
      )}
      <div className="flex flex-col gap-3 p-5">
        <div className="flex items-center gap-2">
          <span className="inline-block rounded-none bg-gold px-2 py-0.5 text-xs font-semibold text-gold-foreground">
            {campaign.category.name}
          </span>
        </div>

        <h3 className="font-display text-display-sm leading-tight text-foreground">
          {campaign.title}
        </h3>

        <div className="flex items-center gap-2 text-body-sm text-muted-foreground">
          <Avatar className="size-5 rounded-none">
            <AvatarImage src={campaign.owner.avatar_url} />
            <AvatarFallback className="rounded-none text-[10px]">
              {campaign.owner.name.charAt(0)}
            </AvatarFallback>
          </Avatar>
          <span>{campaign.owner.name}</span>
        </div>

        <Progress
          value={percentage}
          variant="campaign"
          className="mt-1"
        />

        <div className="flex items-center justify-between text-body-sm">
          <span className="font-semibold text-foreground">
            {formatMWK(campaign.raised_amount, campaign.currency)}
          </span>
          <span className="text-muted-foreground">
            raised of {formatMWK(campaign.goal_amount, campaign.currency)}
          </span>
        </div>

        <div className="flex items-center gap-4 text-body-xs text-muted-foreground">
          {campaign.days_remaining !== undefined && (
            <span>{campaign.days_remaining} days left</span>
          )}
          <span>{campaign.donor_count} donors</span>
        </div>
      </div>
    </Link>
  )
}
