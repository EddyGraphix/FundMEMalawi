import * as React from "react"

import { cn } from "@/lib/utils"

function Progress({
  className,
  value,
  max = 100,
  variant = "default",
  ...props
}: React.ComponentProps<"div"> & {
  value?: number
  max?: number
  variant?: "default" | "campaign"
}) {
  const percentage = Math.min(Math.max(0, ((value ?? 0) / max) * 100), 100)

  return (
    <div
      data-slot="progress"
      data-variant={variant}
      className={cn(
        "relative overflow-hidden",
        variant === "campaign"
          ? "border-3 border-foreground h-8"
          : "rounded-full h-2.5 bg-muted",
        className
      )}
      role="progressbar"
      aria-valuenow={value ?? 0}
      aria-valuemin={0}
      aria-valuemax={max}
      {...props}
    >
      <div
        data-slot="progress-fill"
        className={cn(
          "h-full transition-all duration-500 ease-out",
          variant === "campaign"
            ? "bg-brand"
            : "bg-primary rounded-full"
        )}
        style={{ width: `${percentage}%` }}
      >
        {variant === "campaign" && percentage > 15 && (
          <span className="absolute inset-0 flex items-center justify-end px-3 text-xs font-semibold text-brand-foreground">
            {Math.round(percentage)}%
          </span>
        )}
      </div>
      {variant === "campaign" && percentage >= 100 && (
        <div className="absolute inset-y-0 right-0 flex items-center pr-2">
          <span className="text-xs font-bold text-foreground">100%+</span>
        </div>
      )}
    </div>
  )
}

export { Progress }
