# QuantStock Frontend

Premium dark glassmorphism frontend for QuantStock built with Next.js App Router and TypeScript.

## Stack

- Next.js (App Router)
- React 19
- Tailwind CSS
- TanStack Query
- Axios
- React Hook Form + Zod
- Framer Motion
- Recharts
- next-themes

## Setup

1. Copy env file:
   - `cp .env.example .env.local`
2. Set your FastAPI base URL in `.env.local`.
3. Install dependencies:
   - `npm install`
4. Run development server:
   - `npm run dev`

## API Integration

All API requests go through:

- `api/client.ts`
- `services/*.service.ts`

No mocked data is used. UI modules render loading/empty/error states until backend responses are returned.
