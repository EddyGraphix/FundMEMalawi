import { Slot } from "@radix-ui/react-slot"
import { cva, type VariantProps } from "class-variance-authority"
import * as React from "react"

import { cn } from "@/lib/utils"

const donateButtonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap font-display transition-shadow disabled:pointer-events-none disabled:opacity-50 outline-none",
  {
    variants: {
      size: {
        default: "h-12 px-6 text-display-sm",
        lg: "h-14 px-8 text-display-sm",
        sm: "h-10 px-4 text-body-base",
      },
    },
    defaultVariants: {
      size: "default",
    },
  }
)

function DonateButton({
  className,
  variant,
  size,
  asChild = false,
  ...props
}: React.ComponentProps<"button"> &
  VariantProps<typeof donateButtonVariants> & {
    asChild?: boolean
  }) {
  const Comp = asChild ? Slot : "button"

  return (
    <Comp
      data-slot="donate-button"
      className={cn(
        "border-3 border-foreground bg-brand text-brand-foreground shadow-hard hover:shadow-[6px_6px_0px_0px] hover:shadow-foreground active:shadow-[2px_2px_0px_0px] active:shadow-foreground active:translate-x-[2px] active:translate-y-[2px]",
        donateButtonVariants({ size, className })
      )}
      {...props}
    />
  )
}

export { DonateButton, donateButtonVariants }
