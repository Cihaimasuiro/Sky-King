# Product Requirements Document (PRD): Project Sky King

## 1. Executive Summary

**Current State:** Originally developed as an academic database management assignment ("Airline Booking") utilizing monolithic PHP and MySQL.
**Vision:** Rebrand and rebuild the application as "Sky King"—a tribute to Richard Russell (2018). The core objective is to transition from a legacy monolith into a modern, decoupled full-stack application. This project will serve as a premier portfolio piece demonstrating API design, reactive frontend development, and robust database management.

## 2. Project Identity & Branding

- **Name:** Sky King
- **Theme:** A modern, professional flight reservation platform honoring aviation enthusiasm.
- **Visual Identity:** Clean, minimalist UI with a focus on accessibility, intuitive state management, and seamless transitions.
- **Target Audience:** Engineering managers and recruiters evaluating technical proficiency in modern web frameworks, component architecture, and API integration.

## 3. Tech Stack & Architecture

To meet industry standards, the architecture will be decoupled into a dedicated frontend client and a RESTful backend API.

- **Frontend:** Svelte 5 / SvelteKit. Chosen for its reactive component model, minimal boilerplate, and exceptional performance. Tailwind CSS will be used for rapid, consistent styling.
- **Backend:** Node.js (Express or Hono) OR SvelteKit API Routes.
- **Database:** MySQL. We will retain the foundational `uasbasisdata` schema but integrate an ORM (Object-Relational Mapper) like Prisma or Drizzle to ensure type safety and clean database queries.

## 4. Core Features & Unique Selling Points (USPs)

The application will leverage modern state management to make the Interactive User Dashboard lightning-fast and highly responsive without page reloads.

- **Financial Tracking Dashboard:** Real-time calculation and display of total overall expenses for flight tickets.
- **Booking Analytics:** Reactive components displaying the total number of orders placed.
- **Comprehensive History:** A complete, sortable log of past destinations and flight schedules.
- **Frictionless Navigation:** Single Page Application (SPA) feel for quick-access actions to book new tickets or review itineraries instantly.

## 5. Development Roadmap

### Phase 1: Backend API & Database Modernization

- [x] Initialize Node.js/SvelteKit project and connect to the existing MySQL database.
- [x] Implement an ORM (Prisma/Drizzle) and map existing tables (`users`, `flight`, `booking`, `passengers`, `payment`, `admin`).
- [x] Create secure RESTful API endpoints for flight listing and booking creation/history (`GET /api/flights`, `GET /api/bookings`, `POST /api/bookings`).
- [x] Implement JWT-based authentication for user and admin roles.

### Phase 2: Reactive Frontend Rebuild (Svelte 5)

- [x] Setup SvelteKit with Tailwind CSS.
- [x] Build reusable UI components (Flight Cards, dashboard stats, booking form surface).
- [x] Develop the Interactive Dashboard consuming backend-backed flight and booking data.
- [x] Implement client-side form validation and error handling for a seamless booking flow.

### Phase 3: Advanced Portfolio Features

- [x] **Mobile Optimization:** Ensure fluid, responsive design across all breakpoints.
- [x] **Flight Recommendations:** Build a logic module to suggest flights based on past booking history.
- [x] **Automated PDF Generation:** Allow users to download their flight tickets or booking receipts as dynamically generated PDFs.

## 6. Success Metrics

- **Code Architecture:** Clear separation of concerns between UI components, server logic, and database queries.
- **Performance:** High Lighthouse scores due to Svelte's optimized build and efficient API calls.
- **Repository Health:** Comprehensive `README.md` containing local setup instructions, API endpoint documentation, and an entity-relationship diagram (ERD) of the database.
