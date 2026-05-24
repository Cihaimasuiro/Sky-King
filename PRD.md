# Product Requirements Document (PRD): Project Sky King

**Version:** 4.0 | **Updated:** May 2026

---

## 1. Executive Summary

**Origin:** Academic database assignment ("Airline Booking") — monolithic PHP + MySQL.
**Current State:** Phase 4 is complete. The app is a fully functional SvelteKit 2 + Svelte 5 + Tailwind 4 + Prisma + MariaDB stack with JWT auth, PDF ticket generation, WebSocket notifications, and a flight recommendation engine. One functional gap remains: the booking flow has no UI — the API exists but users have no page to actually create a booking. The remaining work is Phase 6 (booking UI) and three visual assets to finalize the README.

---

## 2. Project Identity & Branding

- **Name:** Sky King — a tribute to aviation enthusiasm (Richard Russell, 2018).
- **Visual Identity:** Dark theme (`slate-950` base), `sky-400` accent, clean minimalist layout.
- **Target Audience:** Engineering managers and recruiters evaluating SvelteKit, full-stack TypeScript, and API design proficiency.

---

## 3. Tech Stack (Locked)

| Layer     | Technology                          | Version   |
| --------- | ----------------------------------- | --------- |
| Frontend  | Svelte / SvelteKit                  | 5.x / 2.x |
| Styling   | Tailwind CSS                        | 4.x       |
| Backend   | SvelteKit API Routes (`+server.ts`) | —         |
| ORM       | Prisma                              | 6.x       |
| Database  | MariaDB/MySQL                       | —         |
| Auth      | JWT + bcrypt                        | —         |
| PDF       | pdfkit                              | 0.18.x    |
| Real-time | ws (standalone `websocket.mjs`)     | 8.x       |

---

## 4. Actual Feature Status (Verified from Source)

### ✅ Complete

- JWT auth: login, register, session cookies, admin role, `hooks.server.ts` middleware
- Prisma schema: `User`, `Airport`, `Flight`, `Booking`, `Passenger`, `Admin`, `Payment` — all migrated
- Seed data: 6 airports (CGK, DPS, SIN, HND, ICN, SYD), 5 seeded flights
- REST API: `GET /api/flights`, `GET /api/bookings`, `POST /api/bookings`, `GET /api/flights/recommended`, `POST /api/login`, `POST /api/register`, `POST /api/logout`
- PDF ticket: `GET /api/bookings/[id]/ticket.pdf` — pdfkit streaming, boarding pass layout, QR placeholder, `Content-Disposition: attachment`
- WebSocket: standalone `websocket.mjs` on port 8080, subscription model, per-flight broadcast; `Notifications.svelte` subscribes on load and shows dismissible toasts
- Admin notification page: `/admin/notifications` — browser sends WS message directly to trigger a status broadcast
- Recommendation engine: history-based (onward connections from past destinations) with popular-flight fallback; rendered on dashboard and `/search`
- Dashboard: stats row (total bookings, total spend, upcoming), bookings table with PDF download links, quick actions panel, recommendations carousel
- Desktop layout: sidebar (`lg:block w-64`) + `lg:pl-64` main offset + fixed header — correctly implemented
- Mobile layout: sidebar hidden, single-column content
- JSDoc on `prisma.ts`, `session.ts`, `ticket.pdf/+server.ts`
- Consistent API error shape: `{ error: string, code: number }` across all routes
- `.env.example` with `JWT_SECRET` and `DATABASE_URL`
- README: setup instructions, API table, known limitations

### 🔴 Gaps (Not Yet Built)

| #   | Gap                                                                                                                                                       | Impact                                          |
| --- | --------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------- |
| 1   | **No booking UI page** — `POST /api/bookings` exists but there's no `/book` page; `FlightCard.svelte` has an `onselect` prop but no page actually uses it | Users cannot create bookings                    |
| 2   | **Search form is non-functional** — `/search` has the UI but no query logic; it only shows recommendations                                                | Core user journey broken                        |
| 3   | **Payment creation missing** — `Payment` table exists but no payment step in booking flow                                                                 | Bookings have no attached payment record        |
| 4   | **README has 3 `<!-- TODO -->` placeholders** — screenshot, tech stack badges, ERD image                                                                  | Looks unfinished to a recruiter                 |
| 5   | **PicoCSS loaded alongside Tailwind** in `app.html` — CDN import causes style conflicts                                                                   | Visual inconsistency                            |
| 6   | **Recommendation logic triplicated** — identical code in `+page.server.ts`, `search/+page.server.ts`, and `/api/flights/recommended`                      | Maintenance burden, inconsistent if one changes |

---

## 5. Layout Reference (Do Not Regress)

The current working layout pattern — keep exactly as-is:

```
+layout.svelte
  <div class="min-h-screen bg-slate-950">
    <Sidebar />                        <!-- hidden on mobile, w-64 block on lg -->
    <div class="lg:pl-64">
      <Header />                       <!-- fixed top-0 z-30 h-16 w-full -->
      <main class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          {children}
        </div>
      </main>
    </div>
    <Notifications />
  </div>
```

---

## 6. Phase 6 — Booking Flow (Current Priority)

This is the most important remaining work. The API is ready; only the UI is missing.

### 6.1 Flight Detail + Booking Page `/flights/[id]`

**Route:** `/flights/[id]`
**Load function:** `GET /api/flights` or direct Prisma query for the single flight by `params.id`.
**Page content:**

- Flight summary card (reuse `FlightCard.svelte` with `onselect` removed, showing full detail)
- Passenger form: add/remove passenger name inputs (minimum 1)
- "Book Flight" submit button → `POST /api/bookings` with `{ flightId, passengerNames: string[] }`
- On success: redirect to `/` (dashboard) with a success banner

**Link from:** `FlightCard.svelte` "Book this flight" button should navigate to `/flights/{flight.id}` instead of calling `onselect`. Update `FlightCard.svelte` accordingly.

### 6.2 Wire Up Search

**Route:** `/search`
**Current state:** Form exists, does nothing.
**Required change:** On form submit, call `GET /api/flights` and filter client-side (or add query params to the API). Display matching `FlightCard` components with links to `/flights/[id]`.

Minimal viable implementation — no backend changes needed:

1. On mount, load all flights via `+page.server.ts` (already pattern-matched in the rest of the app).
2. Filter by departure city/code and arrival city/code using reactive state.
3. Render filtered results as `FlightCard` list below the form.

### 6.3 Payment Step (Minimal)

The `Payment` table exists in the DB. After a booking is created, insert a payment record automatically with `status: 'pending'` and the flight price as `amount`. This can be done inside `POST /api/bookings` without a separate payment page — the user can be told "payment confirmed" for portfolio purposes.

Add to `POST /api/bookings` after booking creation:

```typescript
await prisma.payment.create({
	data: {
		bookingId: booking.id,
		amount: flight.price,
		paymentMethod: 'card', // default for now
		status: 'confirmed'
	}
});
```

---

## 7. Phase 7 — Final Polish

### 7.1 README Assets (3 remaining TODOs)

- **Screenshot:** Take a screenshot of the dashboard at 1440px width; save as `static/screenshot.png`; replace `<!-- TODO -->` in README.
- **Tech stack badges:** Add shields.io badges for SvelteKit, Prisma, Tailwind, TypeScript, MariaDB.
- **ERD:** Export the Prisma schema as a diagram (use `prisma-erd-generator` or draw manually); save as `static/erd.png`; replace `<!-- TODO -->` in README.

### 7.2 Remove PicoCSS

In `src/app.html`, remove the PicoCSS CDN `<link>` tag. It conflicts with Tailwind's reset and causes inconsistent button/form styling. Tailwind handles everything already.

### 7.3 Extract Recommendation Logic

Create `src/lib/recommendations.ts` with a single exported async function `getRecommendations(userId: number)`. Replace the three copy-pasted implementations in `+page.server.ts`, `search/+page.server.ts`, and `/api/flights/recommended/+server.ts` with a single import. Also delete the now-redundant `/api/recommendations/+server.ts`.

---

## 8. Full Route Inventory (Current Reality)

| Route                               | Status                                                | Auth   |
| ----------------------------------- | ----------------------------------------------------- | ------ |
| `/`                                 | ✅ Dashboard (stats, bookings table, recommendations) | User   |
| `/login`                            | ✅ Login form                                         | Public |
| `/register`                         | ✅ Register form                                      | Public |
| `/profile`                          | ✅ Profile display                                    | User   |
| `/search`                           | 🔴 Recommendations only — search non-functional       | Public |
| `/flights/[id]`                     | 🔴 Does not exist                                     | —      |
| `/admin/notifications`              | ✅ WS broadcast trigger                               | Admin  |
| `GET /api/flights`                  | ✅                                                    | Public |
| `GET /api/flights/recommended`      | ✅                                                    | User   |
| `GET /api/bookings`                 | ✅                                                    | User   |
| `POST /api/bookings`                | ✅                                                    | User   |
| `GET /api/bookings/[id]/ticket.pdf` | ✅                                                    | User   |
| `GET /api/recommendations`          | ⚠️ Duplicate of /api/flights/recommended              | User   |
| `POST /api/login`                   | ✅                                                    | Public |
| `POST /api/register`                | ✅                                                    | Public |
| `POST /api/logout`                  | ✅                                                    | Public |
| `ws://localhost:8080`               | ✅ Standalone websocket.mjs                           | —      |

---

## 9. Roadmap Summary

| Phase                 | Status            | Description                                           |
| --------------------- | ----------------- | ----------------------------------------------------- | ---------------------------------------------------------- | --- |
| 1 — Backend & DB      | ✅ Done           | Prisma + MariaDB + JWT auth + API routes              |
| 2 — Frontend Scaffold | ✅ Done           | SvelteKit + Tailwind + components + dashboard         |
| 3 — Desktop Layout    | ✅ Done           | Sidebar + fixed header + pl-64 offset                 |
| 4 — Advanced Features | ✅ Done           | PDF tickets, WebSocket notifications, recommendations |
| 5 — Portfolio Polish  | ✅ Done           | README, .env.example, JSDoc, error shapes             |
| 6 — Booking Flow      | ✅ Done           | Flight detail page, wired search, payment record      |
|                       | 7 — Final Cleanup | ✅ Done                                               | README assets, remove PicoCSS, deduplicate recommendations |     |

---

## 10. Success Criteria

- A logged-in user can search flights, open a flight detail page, add passenger names, and confirm a booking end-to-end.
- The booking appears in the dashboard table immediately after creation.
- A PDF download link for the new booking works on first click.
- README has no `<!-- TODO -->` comments and renders cleanly on GitHub.
- `npm run lint` passes with zero warnings.
