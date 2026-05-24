# Product Requirements Document (PRD): Project Sky King
**Version:** 3.0 | **Updated:** May 2026

---

## 1. Executive Summary

**Origin:** Academic database assignment ("Airline Booking") — monolithic PHP + MySQL.  
**Current State:** Fully migrated to SvelteKit 2 + Svelte 5 + Tailwind 4 + Prisma + MariaDB. Backend auth, API routes, and core booking flow are complete. Desktop layout regression was identified and patched. Advanced feature dependencies (`pdfkit`, `ws`) are installed and ready to implement.  
**Next Milestone:** Ship the three Phase 4 features (PDF tickets, real-time notifications, flight recommendations) and finalize portfolio-readiness with a proper README and deployment config.

---

## 2. Project Identity & Branding

- **Name:** Sky King — a tribute to aviation enthusiasm (Richard Russell, 2018).
- **Visual Identity:** Clean, minimalist UI. Accessibility-first, smooth transitions, no clutter.
- **Target Audience:** Engineering managers and recruiters evaluating SvelteKit, component architecture, API design, and full-stack TypeScript proficiency.

---

## 3. Tech Stack (Locked)

| Layer | Technology | Version |
|---|---|---|
| Frontend | Svelte / SvelteKit | 5.x / 2.x |
| Styling | Tailwind CSS | 4.x |
| Backend | SvelteKit API Routes (`+server.ts`) | — |
| ORM | Prisma | 6.x |
| Database | MariaDB/MySQL (`uasbasisdata` schema) | — |
| Auth | JWT + bcrypt | — |
| PDF | pdfkit | 0.18.x |
| Real-time | ws (WebSocket) | 8.x |

No stack changes planned. All dependencies are already installed.

---

## 4. Feature Status

### ✅ Complete

- JWT authentication (login, register, session cookies)
- Prisma schema mapped to all legacy tables: `users`, `flight`, `booking`, `passengers`, `payment`, `admin`
- REST API endpoints: `GET /api/flights`, `GET /api/bookings`, `POST /api/bookings`
- Interactive User Dashboard (total bookings, total spend, booking history)
- Booking flow with client-side validation
- Admin panel
- Desktop two-column layout (sidebar + main content) — fixed in latest push
- Mobile responsive layout

### 🔴 In Progress / Next Sprint (Phase 4)

See Section 6 for full specs.

---

## 5. Desktop Layout Contract (Reference — v2.0 fix applied)

This section is the source of truth for layout. **Do not regress these.**

### Breakpoints

| Viewport | Layout |
|---|---|
| `< 768px` | Single column, bottom nav bar, compact cards |
| `768–1023px` | Single column, hamburger menu, no sidebar |
| `>= 1024px` | Fixed sidebar (`w-64`) + main content (`ml-64 pt-16`) |

### Key Tailwind Constraints

- Navbar: `sticky top-0 z-50 h-16 flex items-center justify-between`
- Sidebar: `fixed left-0 top-16 h-[calc(100vh-4rem)] w-64` (desktop only, hidden on mobile)
- Main wrapper: `lg:ml-64 pt-16 min-h-screen px-8 py-6`
- Content max-width: `max-w-7xl mx-auto` on all pages

---

## 6. Phase 4 Feature Specifications (Completed)

### 6.1 PDF Ticket / Booking Receipt Download ✅

**Dependency:** `pdfkit` (already installed)  
**Route:** `GET /api/bookings/[id]/pdf`  
**Output:** A downloadable PDF containing:
- Sky King header / logo mark
- Booking reference number
- Passenger name(s) + passport number
- Flight number, route (origin to destination), departure/arrival times
- Seat class, total price paid, payment method
- QR code placeholder (static box is fine for portfolio)

**Frontend trigger:** "Download Ticket" button on `/dashboard/bookings/[id]`.  
**Implementation notes:**
- Server-side only — generate in `+server.ts`, stream with `Content-Disposition: attachment; filename="ticket-[id].pdf"`.
- Pipe `pdfkit` Document to a ReadableStream in the SvelteKit response.
- No client-side PDF generation.

### 6.2 Real-Time Flight Status Notifications ✅

**Dependency:** `ws` (already installed)  
**Goal:** Notify logged-in users when a flight they've booked changes status (delayed, gate change, cancelled) without a page reload.

**Architecture:**
```
Admin updates flight status in /admin/flights
  -> Server broadcasts WS message to all clients subscribed to that flight_id
  -> Client shows dismissible toast notification
```

**WebSocket endpoint:** `GET /api/ws`  
**Client subscribe message:**
```json
{ "type": "subscribe", "flight_ids": [42, 87] }
```
**Server broadcast message:**
```json
{
  "type": "flight_status",
  "flight_id": 42,
  "status": "delayed",
  "message": "Flight GA-204 is delayed by 45 minutes."
}
```
**Frontend:** Connect on dashboard load, subscribe to flight IDs from the user's upcoming bookings. Show a toast on message receipt.  
**Scope note:** Simulated status change from the admin panel is sufficient — no real-time flight data API required.

### 6.3 Flight Recommendation Engine ✅

**Goal:** Surface "Recommended for you" flights on the dashboard and search page based on booking history.

**Logic (server-side heuristic, no ML needed):**
1. Extract all past destination airports from the user's bookings.
2. Find available flights departing from those destinations (onward connections).
3. Boost flights to cities the user has visited more than once.
4. Fallback: return top 3 flights by available seat count if no history exists.

**API endpoint:** `GET /api/flights/recommended` (auth required)  
**Response:** Array of up to 4 flight objects, each with an added `reason` string (e.g. "You've flown to Bali before").  
**Frontend:** Render as a horizontal "Recommended" card row on `/dashboard` and as a highlighted section at the top of `/search`.

---

## 7. Phase 5 — Portfolio Finalization (After Phase 4)

Low effort, high impact. Makes the repo actually readable to a recruiter.

### 7.1 README Overhaul

Replace the default SvelteKit scaffold README with:

- Project name + one-line pitch
- Screenshot or short GIF of the dashboard at desktop breakpoint
- Tech stack badges
- Local setup block:
  ```bash
  git clone https://github.com/Cihaimasuiro/Sky-King
  cd Sky-King
  npm install
  cp .env.example .env        # fill in DB_URL, JWT_SECRET
  npx prisma migrate deploy
  npm run dev
  ```
- API endpoint reference table (method, route, auth required, description)
- ERD diagram image (export from existing ERD)
- Known limitations / future improvements section

### 7.2 Environment & Deployment

- Add `.env.example` with all required keys documented (no real values).
- Add a note on the MariaDB vs MySQL `DATABASE_URL` format difference.
- Optional: Dockerfile or a Railway/Vercel deploy note so reviewers can run it without a local database.

### 7.3 Code Quality Pass

- Remove all `console.log` debug statements from production paths.
- Ensure all API routes return consistent error shapes: `{ error: string, code: number }`.
- Add JSDoc/TSDoc comments to the three main server utilities: auth helper, Prisma client singleton, PDF generator.

---

## 8. Full Page & Route Inventory

| Route | Page | Auth |
|---|---|---|
| `/` | Landing / Hero | Public |
| `/login` | Login | Public |
| `/register` | Register | Public |
| `/search` | Flight Search + Recommended section | Public |
| `/flights/[id]` | Flight Detail | Public |
| `/booking/[id]` | Booking Flow (passenger form + payment) | User |
| `/dashboard` | User Dashboard (stats + recommendations) | User |
| `/dashboard/bookings` | Booking History | User |
| `/dashboard/bookings/[id]` | Booking Detail + PDF Download button | User |
| `/dashboard/profile` | Profile & Settings | User |
| `/admin` | Admin Dashboard | Admin |
| `/admin/flights` | Flight Management (triggers WS notifications) | Admin |
| `/admin/bookings` | Booking Management | Admin |
| `GET /api/flights` | All available flights | Public |
| `GET /api/flights/recommended` | Personalized recommendations | User |
| `GET /api/bookings` | User's booking list | User |
| `POST /api/bookings` | Create booking | User |
| `GET /api/bookings/[id]/pdf` | Stream booking PDF | User |
| `POST /api/auth/login` | Login | Public |
| `POST /api/auth/register` | Register | Public |
| `GET /api/ws` | WebSocket connection | User |

---

## 9. Roadmap Summary

| Phase | Status | Description |
|---|---|---|
| 1 — Backend & DB | Done | Prisma + MariaDB + JWT auth + API routes |
| 2 — Frontend Scaffold | Done | SvelteKit + Tailwind + components + booking flow |
| 3 — Desktop Layout Fix | Done | Sidebar, navbar, grid layout corrected |
| 4 — Advanced Features | Done | PDF tickets, WebSocket notifications, recommendations |
| | 5 — Portfolio Polish | Done | README overhaul, .env.example, code cleanup | |

---

## 10. Success Metrics

- All Phase 4 features functional end-to-end with no console errors.
- Desktop Lighthouse score >= 90 (Performance, Accessibility, Best Practices).
- README allows a new reviewer to clone and run locally in under 5 minutes.
- Zero layout regressions at 1280px, 1440px, and 1920px viewport widths.
