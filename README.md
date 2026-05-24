# Sky King

A modern SvelteKit rebuild of an airline booking system with live booking history, spend tracking, and Prisma-backed API routes.

## Screenshot

<!-- TODO: Add a screenshot or GIF of the dashboard at desktop breakpoint -->

## Tech Stack

<!-- TODO: Add tech stack badges -->

## Local Setup

To get Sky King up and running on your local machine, follow these steps:

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/Cihaimasuiro/Sky-King
    cd Sky-King
    ```

2.  **Install dependencies:**
    ```bash
    npm install
    ```

3.  **Configure environment variables:**
    Create a `.env` file in the root of the project by copying the example:
    ```bash
    cp .env.example .env
    ```
    Then, open `.env` and fill in your database connection string and a JWT secret.

4.  **Run Prisma migrations:**
    ```bash
    npx prisma migrate deploy
    ```

5.  **Seed the database (optional):**
    ```bash
    npm run db:seed
    ```

6.  **Start the development server:**
    ```bash
    npm run dev
    ```

7.  **Start the WebSocket server:**
    In a separate terminal, run:
    ```bash
    node websocket.mjs
    ```

The application should now be running at `http://localhost:5173/`.

## API Endpoint Reference

| Method | Route | Auth Required | Description |
|---|---|---|---|
| `GET` | `/api/flights` | No | Get all available flights. |
| `GET` | `/api/flights/recommended` | User | Get personalized flight recommendations based on booking history. |
| `GET` | `/api/bookings` | User | Get the authenticated user's booking list. |
| `POST` | `/api/bookings` | User | Create a new booking. |
| `GET` | `/api/bookings/[id]/pdf` | User | Stream a PDF boarding pass/receipt for a specific booking. |
| `POST` | `/api/auth/login` | No | Authenticate a user and set a JWT cookie. |
| `POST` | `/api/auth/register` | No | Register a new user. |
| `GET` | `/api/ws` | User | Establish a WebSocket connection for real-time notifications. |

## ERD Diagram

<!-- TODO: Add an ERD diagram image -->

## Known Limitations / Future Improvements

-   **Admin Panel:** The admin panel is currently a placeholder. Full functionality for managing flights, users, and bookings would be a future improvement.
-   **Payment Gateway Integration:** The payment process is simulated. Integration with a real payment gateway (e.g., Stripe, PayPal) would enhance the application.
-   **Real-time Flight Data:** Flight status updates are currently simulated. Integration with a real-time flight data API would provide more accurate notifications.
-   **Advanced Recommendation Logic:** The recommendation engine uses a simple heuristic. More sophisticated algorithms could be implemented for better recommendations.
-   **Search Functionality:** The flight search form is basic. Implementing actual search logic and filtering would be a key improvement.
-   **Mobile Navigation:** A bottom navigation bar for mobile is specified in the PRD but not yet implemented.