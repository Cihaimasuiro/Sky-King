# Product Requirements Document (PRD): Project Sky King
**Version:** 2.0 | **Updated:** May 2026

---

## 1. Executive Summary

**Current State:** Originally developed as an academic database management assignment ("Airline Booking") utilizing monolithic PHP and MySQL. The project has since been partially migrated into a modern SvelteKit + Prisma + Tailwind stack. Phase 1 (backend) and Phase 2 (frontend scaffold) are complete; however, **desktop layout regressions have been identified** and must be resolved before the project is portfolio-ready.

**Vision:** "Sky King" — a modern, decoupled flight reservation platform serving as a premier portfolio piece demonstrating API design, reactive frontend development, and robust database management.

---

## 2. Project Identity & Branding

- **Name:** Sky King
- **Theme:** Modern, professional flight reservation platform.
- **Visual Identity:** Clean, minimalist UI — accessibility-first, intuitive state management, seamless transitions.
- **Target Audience:** Engineering managers and recruiters evaluating technical proficiency in SvelteKit, component architecture, and API integration.

---

## 3. Tech Stack & Architecture

| Layer | Technology |
|---|---|
| Frontend | Svelte 5 / SvelteKit + Tailwind CSS |
| Backend | SvelteKit API Routes (server endpoints) |
| ORM | Prisma |
| Database | MySQL (`uasbasisdata` schema) |
| Auth | JWT (user + admin roles) |

Architecture is decoupled: UI components consume typed server data via SvelteKit's `load()` functions and `+server.ts` API routes.

---

## 4. Core Features & Unique Selling Points (USPs)

- **Interactive User Dashboard** — Reactive totals for orders placed and total expenditure; no page reloads.
- **Financial Tracking** — Real-time calculation and display of overall ticket spend.
- **Booking Analytics** — Sortable, paginated booking history with flight route and schedule detail.
- **Frictionless Navigation** — SPA-feel routing with instant transitions between search, booking, and dashboard.
- **PDF Ticket Download** — Users can export their booking receipt as a dynamically generated PDF.
- **Flight Recommendations** — Logic module suggesting routes based on past booking history.

---

## 5. Desktop Layout Specification (New — v2.0)

> **Context:** The desktop page layout is currently broken. This section defines the correct layout contract so that Gemini CLI (or any other AI coding agent) has an unambiguous spec to implement against.

### 5.1 Layout Grid

The application uses a **two-column layout** on desktop (≥ 1024px) and a **single-column stacked layout** on mobile (< 768px).

```
Desktop (≥ 1024px)
┌─────────────────────────────────────────────────┐
│  NAVBAR  (full-width, sticky, h-16)             │
├──────────────┬──────────────────────────────────┤
│  SIDEBAR     │  MAIN CONTENT AREA               │
│  w-64        │  flex-1, overflow-y-auto          │
│  (fixed)     │  px-8 py-6                        │
├──────────────┴──────────────────────────────────┤
│  FOOTER  (full-width)                           │
└─────────────────────────────────────────────────┘

Mobile (< 768px)
┌─────────────────────┐
│  NAVBAR (h-14)      │
│  MAIN CONTENT       │
│  (full-width, px-4) │
│  BOTTOM NAV BAR     │
└─────────────────────┘
```

### 5.2 Navbar

- **Height:** `h-16` (64px) desktop, `h-14` (56px) mobile.
- **Position:** `sticky top-0 z-50`.
- **Contents (left → right):** Logo mark + "Sky King" wordmark | spacer | Search bar (desktop only, hidden on mobile) | Notification icon | User avatar dropdown.
- **Background:** Semi-transparent with `backdrop-blur-md`; solid on scroll.
- **Bug to fix:** On desktop the navbar currently overflows its container and stacks vertically — must use `flex items-center justify-between w-full`.

### 5.3 Sidebar (Desktop Only)

- **Width:** `w-64` (256px), fixed position, full viewport height minus navbar.
- **Hidden on mobile** — replaced by a bottom navigation bar.
- Navigation items: Dashboard, Search Flights, My Bookings, Passengers, Payments, Settings.
- Active item highlighted with primary brand color (`bg-sky-600 text-white`).
- **Bug to fix:** Sidebar is currently `position: absolute` causing it to overlap main content — must be `fixed` with main content having `ml-64` offset on desktop.

### 5.4 Main Content Area

- **Desktop:** `ml-64 pt-16` (offset for sidebar + navbar), `min-h-screen`, `px-8 py-6`.
- **Mobile:** `pt-14 pb-20` (offset for top navbar + bottom nav bar), `px-4`.
- **Max content width:** `max-w-7xl mx-auto` inside the content area to prevent over-stretching on ultrawide screens.
- **Bug to fix:** Content area currently has no left margin on desktop, causing content to render underneath the sidebar.

### 5.5 Dashboard Page Layout

The dashboard (`/dashboard`) is the most layout-sensitive page. Correct structure:

```
WELCOME BANNER  (full-width card, gradient background)

STATS ROW       (3-column grid on desktop, 1-column on mobile)
  ┌──────────┐ ┌──────────┐ ┌──────────┐
  │ Total    │ │ Total    │ │ Upcoming │
  │ Bookings │ │ Spend    │ │ Flights  │
  └──────────┘ └──────────┘ └──────────┘

MAIN GRID       (2-column on desktop: 60/40 split, 1-column on mobile)
  ┌────────────────────┐ ┌──────────────┐
  │  Recent Bookings   │ │  Quick       │
  │  (table/list)      │ │  Actions     │
  │                    │ │  Panel       │
  └────────────────────┘ └──────────────┘
```

Tailwind classes for the stats row: `grid grid-cols-1 md:grid-cols-3 gap-4`.
Tailwind classes for the main grid: `grid grid-cols-1 lg:grid-cols-5 gap-6` with recent bookings as `lg:col-span-3` and quick actions as `lg:col-span-2`.

### 5.6 Responsive Breakpoint Summary

| Breakpoint | Behavior |
|---|---|
| `< 768px` (mobile) | Single column, bottom nav, compact cards |
| `768px – 1023px` (tablet) | Single column, no sidebar (hamburger menu), wider cards |
| `≥ 1024px` (desktop) | Two-column with fixed sidebar, full dashboard grid |

### 5.7 Known Desktop Bugs (To Fix)

| # | Component | Symptom | Root Cause | Fix |
|---|---|---|---|---|
| 1 | Navbar | Items stack vertically | Missing `flex items-center` on nav container | Add `flex items-center justify-between` to `<nav>` |
| 2 | Sidebar | Overlaps main content | `position: absolute` instead of `fixed` | Change to `fixed left-0 top-16` with `ml-64` on `<main>` |
| 3 | Main content | Renders behind sidebar | No left margin offset | Add `lg:ml-64` to main content wrapper |
| 4 | Dashboard stats | Cards overflow viewport width | No grid defined, using flex without wrap | Replace with `grid grid-cols-3` |
| 5 | Search page | Form elements stretch full-width on desktop | No `max-w` constraint | Wrap form in `max-w-2xl mx-auto` |

---

## 6. Development Roadmap

### Phase 1: Backend API & Database Modernization ✅

- [x] Initialize SvelteKit project and connect to MySQL database.
- [x] Implement Prisma ORM mapped to existing tables (`users`, `flight`, `booking`, `passengers`, `payment`, `admin`).
- [x] RESTful API endpoints: `GET /api/flights`, `GET /api/bookings`, `POST /api/bookings`.
- [x] JWT-based authentication for user and admin roles.

### Phase 2: Reactive Frontend Rebuild (Svelte 5) ✅

- [x] SvelteKit + Tailwind CSS setup.
- [x] Reusable UI components (Flight Cards, Booking Form, Dashboard Stats).
- [x] Interactive Dashboard consuming backend data.
- [x] Client-side form validation and error handling.

### Phase 3: Desktop Layout Fix ✅ (Completed)

- [x] Fix navbar layout — `flex items-center justify-between`.
- [x] Fix sidebar positioning — `fixed` + `ml-64` main content offset.
- [x] Fix dashboard grid — `grid grid-cols-1 md:grid-cols-3` for stats row.
- [x] Add `max-w-7xl mx-auto` constraint to all page content areas.
- [x] Fix search form width on desktop.
- [x] Cross-browser QA at 1280px, 1440px, and 1920px viewport widths.

### Phase 4: Advanced Portfolio Features 🔴 (Current Priority)

- [x] **Mobile Optimization:** Full responsive audit post desktop fix.
- [x] **Flight Recommendations:** Logic module based on booking history.
- [x] **PDF Ticket Generation:** Download booking receipt as PDF.
- [x] **Real-time Notifications:** Status updates for flight schedule changes.

---

## 7. Page Inventory

| Route | Page | Auth Required |
|---|---|---|
| `/` | Landing / Hero | No |
| `/login` | User Login | No |
| `/register` | User Registration | No |
| `/search` | Flight Search | No |
| `/flights/[id]` | Flight Detail | No |
| `/booking/[id]` | Booking Flow | Yes (user) |
| `/dashboard` | User Dashboard | Yes (user) |
| `/dashboard/bookings` | Booking History | Yes (user) |
| `/dashboard/profile` | Profile & Settings | Yes (user) |
| `/admin` | Admin Dashboard | Yes (admin) |
| `/admin/flights` | Flight Management | Yes (admin) |
| `/admin/bookings` | Booking Management | Yes (admin) |

---

## 8. Database Schema Reference

Retained from original `uasbasisdata` MySQL schema, managed via Prisma:

| Table | Key Fields |
|---|---|
| `users` | id, nama, email, username, password, telepon |
| `passengers` | id_passenger, booking_id, full_name, nationality, id_passport |
| `booking` | id_booking, id_user, id_flight, booking_date, passengers_count, total_price, payment_status |
| `flight` (penerbangan) | id_penerbangan, no_penerbangan, jam_berangkat, jam_kedatangan, asal, tujuan |
| `airport` (bandara) | id_tiket, nama, kota, negara, maskapai |
| `payment` (pembayaran) | id_pembayaran, id_pemesanan, jumlah, tanggal_pembayaran, jenis_pembayaran |
| `admin` | id_admin, username, password, nama_admin, email |

---

## 9. Success Metrics

- **Layout:** Zero overflow or z-index collisions at all three desktop breakpoints.
- **Code Architecture:** Clean separation — UI components, `+page.server.ts` load functions, and `+server.ts` API handlers.
- **Performance:** Lighthouse score ≥ 90 on desktop (Performance, Accessibility, Best Practices).
- **Repository Health:** `README.md` with local setup guide, API docs, and ERD diagram.
