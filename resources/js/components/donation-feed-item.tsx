import { Avatar, AvatarFallback } from "@/components/ui/avatar"
import { cn } from "@/lib/utils"

interface DonationFeedItemProps {
  donation: {
    id: number
    donor_name?: string
    is_anonymous: boolean
    amount: number
    currency: string
    message?: string
    created_at: string
  }
  className?: string
}

function formatMWK(amount: number, currency: string = "MWK"): string {
  if (currency === "MWK") {
    return `MK ${amount.toLocaleString("en-US", { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`
  }
  return `${currency} ${amount.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

export function DonationFeedItem({ donation, className }: DonationFeedItemProps) {
  const displayName = donation.is_anonymous ? "Anonymous" : (donation.donor_name ?? "Anonymous")
  const initials = displayName === "Anonymous" ? "?" : displayName.charAt(0)

  return (
    <div className={cn("flex items-start gap-3 py-3", className)}>
      <Avatar className="size-8">
        <AvatarFallback className="text-xs">{initials}</AvatarFallback>
      </Avatar>
      <div className="flex min-w-0 flex-1 flex-col gap-0.5">
        <div className="flex items-center justify-between">
          <span className="text-body-sm font-semibold text-foreground">
            {displayName}
          </span>
          <span className="text-body-sm font-semibold text-brand">
            {formatMWK(donation.amount, donation.currency)}
          </span>
        </div>
        {donation.message && (
          <p className="text-body-sm text-muted-foreground line-clamp-2">
            {donation.message}
          </p>
        )}
        <span className="text-body-xs text-muted-foreground">
          {new Date(donation.created_at).toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
          })}
        </span>
      </div>
    </div>
  )
}
