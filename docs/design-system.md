# FundMe Malawi — Design System

## Philosophy

FundMe Malawi fuses two design languages into one distinct identity:

- **Apple's Human Interface Guidelines** as the foundation: clarity (every element has a purpose), deference (UI steps back so content leads), depth (layering through space and elevation, not decoration). Generous white space, restrained neutral palette, confident typography.
- **Disciplined neo-brutalism** as the accent: raw geometry, sharp 0–2px corners, thick 1–4px solid borders, hard offset shadows (no blur), high-contrast flat color blocks, bold oversized type. Applied deliberately on key moments — not everywhere.

**Golden rule**: The app is Apple-quiet until a moment demands teeth. Donate buttons, progress bars, campaign cards, the hero headline — those hit hard. Story pages, dashboards, settings — those stay clean and deferential.

---

## Color System

### Neutral palette (Apple-influenced — base)

| Token | Light | Dark | Usage |
|-------|-------|------|-------|
| `--background` | `oklch(0.985 0.01 85)` | `oklch(0.145 0.01 85)` | Page background |
| `--foreground` | `oklch(0.2 0.01 85)` | `oklch(0.985 0.01 85)` | Body text |
| `--card` | `oklch(1 0 0)` | `oklch(0.18 0 0)` | Card surfaces |
| `--muted` | `oklch(0.965 0.01 85)` | `oklch(0.25 0 0)` | Subtle backgrounds |
| `--border` | `oklch(0.9 0.01 85)` | `oklch(0.3 0 0)` | Borders (soft areas) |
| `--input` | `oklch(0.92 0.01 85)` | `oklch(0.28 0 0)` | Input fields |

### Accent palette (Brutalist-influenced — deliberate)

| Token | Value | Usage |
|-------|-------|-------|
| `--brand` | `oklch(0.62 0.19 145)` | Primary CTAs, donate button, progress fill, active states |
| `--brand-foreground` | `oklch(0.98 0 0)` | Text on brand backgrounds |
| `--gold` | `oklch(0.85 0.15 85)` | Progress bars, achievements, secondary accents |
| `--gold-foreground` | `oklch(0.2 0.01 85)` | Text on gold backgrounds |
| `--destructive` | `oklch(0.58 0.24 27)` | Errors, cancellations, rejections |

These accent colors are inspired by the Malawi flag (green + gold/black-red) and read as optimistic, not corporate.

---

## Typography

### Typefaces

| Role | Font | Weights | Source |
|------|------|---------|--------|
| Display / Headlines | `Archivo Black` | 400 | Google Fonts (Bunny) |
| Body / UI | `Instrument Sans` | 400, 500, 600 | Google Fonts (Bunny) |

### Type scale

```
--text-display-xl: 4.5rem (72px)    — Hero headline
--text-display-lg: 3rem (48px)      — Section headers
--text-display-md: 2.25rem (36px)   — Campaign title
--text-display-sm: 1.5rem (24px)    — Card titles

--text-body-xl: 1.25rem (20px)      — Lead body
--text-body-base: 1rem (16px)       — Body copy
--text-body-sm: 0.875rem (14px)     — Meta, captions
--text-body-xs: 0.75rem (12px)      — Labels, fine print
```

---

## Border / Shadow / Radius Matrix

Every component falls into one of two modes:

### Soft mode (Apple — minimal, deferential)

| Property | Value |
|----------|-------|
| `border-radius` | `0.625rem` (shadcn default) |
| `border-width` | `1px` |
| `border-color` | `--border` |
| `box-shadow` | `0 1px 3px 0 rgb(0 0 0 / 0.05)` |
| Used on | Story pages, dashboards, settings, auth forms, comments |

### Raw mode (Brutalist — deliberate emphasis)

| Property | Value |
|----------|-------|
| `border-radius` | `0px` (sharp corners) |
| `border-width` | `3px` — always solid |
| `border-color` | `--foreground` (high contrast) |
| `box-shadow` | `4px 4px 0px 0px var(--foreground)` (hard offset, no blur) |
| Used on | Donate button, campaign cards, progress bars, key CTAs, hero blocks |

---

## Component Treatments

### Campaign Card
- Raw mode: sharp corners, 3px solid border, hard offset shadow
- Flat brand-color block for progress bar embedded in footer
- Bold typography for campaign title
- Clean body text for excerpt

### Progress Bar
- Raw mode: thick 3px border, sharp corners
- Brand green fill with gold accent at key milestones (50%, 100%)
- Percentage label inside the bar or just outside

### Donate Button
- Raw mode: sharp corners, 3px brand border, hard shadow
- Brand green background, white bold text (Archivo Black)
- Large size: `h-14 px-8 text-display-sm`
- Hover: shadow shifts, no scale/glow

### Supporter Feed Item
- Soft mode: clean, minimal
- Avatar (small), name, amount, message, timestamp
- Respects anonymity (shows "Anonymous" instead of name)

### Navigation
- Soft mode: clean, typographic
- Active state: bold weight or thick underline, not a pill/chip
- Mobile: sheet (shadcn Sheet) from left

---

## Spacing System

Reuse Tailwind's default spacing scale (`1` = `0.25rem`). Key patterns:

- Page content max-width: `max-w-6xl mx-auto`
- Section gap: `space-y-16` or `gap-16`
- Card grid: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6`
- Content padding: `px-4 sm:px-6 lg:px-8`

---

## Dark Mode

All components support dark mode via the `.dark` class on `html`. The existing cookie-based `use-appearance` hook handles toggling. Dark mode inverts the neutral palette while preserving accent colors (brand green gets slightly more saturated in dark to maintain contrast).

---

## Motion

- No glassmorphism, no floating particles, no parallax backgrounds
- Transitions: `150ms ease-in-out` for hover states, `300ms ease-in-out` for layout changes
- Progress bar: `width` transitions with `transition-all duration-500 ease-out`
- Page transitions: Inertia's default progress bar only
