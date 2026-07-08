# FundMe Malawi 🇲🇼

**Crowdfunding that works for Malawi** — raise funds for medical bills, education, community projects, and dreams that need a village behind them.

Built with **Laravel 13**, **Inertia v3**, **React 19**, **Tailwind CSS v4**, and **PayChangu** payment gateway.

## Features

### For Donors
- Donate via **Airtel Money**, **TNM Mpamba**, **Card**, or **Bank Transfer**
- Guest checkout — no account required
- Donor public profile
- Track your donations on the dashboard
- Claim guest donations to your account

### For Campaign Organizers
- 5-step campaign creation wizard (category, story, goal, cover image, beneficiary)
- Campaign story with rich text editor (TipTap)
- Post updates & respond to comments
- Invite beneficiaries via email
- Organizer dashboard with stats (total raised, active campaigns, recent donations)
- Save & follow campaigns

### Trust & Safety
- **Identity verification** — National ID document upload with admin review
- **Campaign approval workflow** — admin reviews before going live
- **Trust Center** — fraud prevention, campaign verification, donor protection, refund policy, safety guidelines
- Campaign reporting system
- Beneficiary verification

### Authentication
- Email & password login/registration
- Email verification
- Password reset
- **Two-factor authentication (TOTP)** with recovery codes
- **Passkeys (WebAuthn)** for passwordless login

### Admin Panel
- Campaign approvals (approve/reject with reason)
- Identity verification review
- Withdrawal management (approve/reject)
- Transaction ledger
- User management
- Category CRUD
- Platform settings editor

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 13, PHP 8.5 |
| **Frontend** | React 19, Inertia.js v3, TypeScript |
| **Styling** | Tailwind CSS v4, Shadcn UI primitives |
| **Payments** | PayChangu (Airtel Money, TNM Mpamba, Card, Bank Transfer) |
| **Auth** | Laravel Fortify (2FA, Passkeys) |
| **RBAC** | Spatie Permissions |
| **Queues** | Laravel Horizon (Redis) |
| **Monitoring** | Laravel Pulse |
| **Media** | Spatie Media Library |
| **Rich Text** | TipTap |

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+
- SQLite (local) or MySQL (production)
- Redis (for queues & Horizon)

### Installation

```bash
git clone https://github.com/EddyGraphix/FundMEMalawi.git
cd FundMEMalawi

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database
touch database/database.sqlite
php artisan migrate --seed

# Build frontend
npm run build

# Serve (via Herd or artisan)
php artisan serve
```

### Development

```bash
# Start Vite dev server
npm run dev

# Run queue worker (for Horizon)
php artisan horizon

# Run tests
php artisan test --compact
```

## Tests

104 tests with 359 assertions covering:
- Authentication flow (login, registration, password reset, email verification, 2FA)
- Payment processing (mobile money, card, bank transfer, webhooks)
- Campaign CRUD & approval workflow
- Identity verification & admin review
- User settings (profile, security)
- Platform settings

```bash
php artisan test --compact
```

## Environment Variables

| Variable | Description |
|----------|-------------|
| `PAYCHANGU_API_PRIVATE_KEY` | PayChangu secret API key |
| `PAYCHANGU_PUBLIC_KEY` | PayChangu public key |
| `PAYCHANGU_CALLBACK_URL` | Webhook callback URL (tunnel URL for dev) |
| `PAYCHANGU_WEBHOOK_SECRET` | Webhook signature secret |

## License

MIT
