import { cn } from "@/lib/utils"

interface ProgressBarProps {
  value: number
  max?: number
  showLabel?: boolean
  className?: string
}

export function ProgressBar({
  value,
  max = 100,
  showLabel = true,
  className,
}: ProgressBarProps) {
  const percentage = Math.min(Math.max(0, ((value ?? 0) / max) * 100), 100)

  return (
    <div
      className={cn("relative border-3 border-foreground h-8 overflow-hidden", className)}
      role="progressbar"
      aria-valuenow={value ?? 0}
      aria-valuemin={0}
      aria-valuemax={max}
    >
      <div
        className="h-full bg-brand transition-all duration-500 ease-out"
        style={{ width: `${percentage}%` }}
      >
        {showLabel && percentage > 15 && (
          <span className="absolute inset-0 flex items-center justify-end px-3 text-xs font-bold text-brand-foreground">
            {Math.round(percentage)}%
          </span>
        )}
      </div>
      {percentage >= 100 && (
        <div className="absolute inset-y-0 right-0 flex items-center pr-2">
          <span className="text-xs font-bold text-foreground">100%+</span>
        </div>
      )}
    </div>
  )
}
