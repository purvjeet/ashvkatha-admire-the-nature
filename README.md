# Ashvkatha – Admire the Nature
## Hotel Booking & Management System

> **A luxury nature retreat and wildlife sanctuary booking experience designed with natural elegance, ambient sanctuary media, and robust enterprise inventory management.**

---

## 🌿 Project Overview

- **Project Name:** Ashvkatha
- **Tagline:** Admire the Nature
- **Identity & Aesthetics:** Luxury Nature Retreat inspired by [ashvkatha.vercel.app](https://ashvkatha.vercel.app)
- **Palette:**
  - Deep Forest Green (`#183D32`)
  - Warm Sunlight Gold (`#D5B987`)
  - Ivory Cream (`#F5F1E8`)
  - Midnight Charcoal (`#343B36`)
- **Technology Stack:** Native PHP 8.2+, MySQL 8+, HTML5, CSS3, JavaScript (Apache / XAMPP; zero external heavy frameworks).

---

## 🏛️ System Architecture

### 1. Customer Website (Public & Guest Portal)
- [index.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/index.php): Immersive homepage with ambient nature meditation audio streaming, 4K background video with pause/play controls, quick reservation search, healing philosophy, room category showcase, curated experiences, guest testimonials, "Get in Touch" card, and full inquiry footer.
- [rooms.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/rooms.php): Catalog with price/capacity filtering, sorting, and real-time live availability calculations across all dates.
- [room-details.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/room-details.php): Category gallery, architectural specs, bespoke amenities, verified guest reviews, and sticky booking card.
- [booking.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/booking.php): Multi-step checkout with real-time tax & nights calculation, optional experience add-ons (Forest Spa, Guided Safari, Organic Dining, Luxury Chauffeur, Stargazing), and payment method choices.
- [actions/booking-action.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/actions/booking-action.php): MySQL Transaction processor with strict double-booking prevention, automatic available physical room allocation, booking services linking, and payment ledger recording.
- [about.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/about.php): Sanctuary vision, conservation ethos, architecture, and pillars of wellness.
- [contact.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/contact.php): Inquiry form that persists messages directly to the `contact_messages` table for admin moderation.
- [my-bookings.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/my-bookings.php): Customer stay history, active booking cancellation handling, and interactive 5-star review submission modal.
- [login.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/login.php) & [register.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/register.php): Secure patron authentication with Bcrypt password hashing.

### 2. Admin Management Panel (`admin/`)
- [admin/index.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/admin/index.php): Secure admin sign-in gateway.
- [admin/dashboard.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/admin/dashboard.php): Live business KPIs (Active inventory, Occupancy rate, Lifetime revenue, Pending inquiries, Category breakdown).
- [admin/bookings.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/admin/bookings.php): Reservation management, search, status transitions (`confirmed`, `checked_in`, `checked_out`, `cancelled`, `rejected`), room reassignment with conflict detection, and full guest modal.
- [admin/rooms.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/admin/rooms.php): 24-room inventory control, floor assignment, operational status (`available`, `cleaning`, `maintenance`, `inactive`), and safe deactivation.
- [admin/room-types.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/admin/room-types.php): 6 room categories, pricing, capacity, dimensions, amenities, and imagery.
- [admin/customers.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/admin/customers.php): Registered patron directory, stay counts, lifetime spend, and account status toggling.
- [admin/payments.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/admin/payments.php): Audited financial transactions, payment methods (`Online Payment`, `UPI`, `Cash`), and status updates (`completed`, `pending`, `refunded`, `failed`).
- [admin/services.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/admin/services.php): Hotel experiences and add-on pricing management.
- [admin/reviews.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/admin/reviews.php): Guest rating moderation with approval workflow.
- [admin/settings.php](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/admin/settings.php): Sanctuary configuration, media stream URLs, tax rate, and customer inquiry inbox.

---

## 🛏️ Room Inventory (24 Physical Rooms)

| Category | Slug | Capacity | Base Price | Inventory | Room Numbers |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Standard Room** | `standard-room` | 2 Guests | ₹4,200/night | 4 Rooms | 101, 102, 103, 104 |
| **Deluxe Room** | `deluxe-room` | 2 Guests | ₹6,500/night | 4 Rooms | 201, 202, 203, 204 |
| **Premium Deluxe** | `premium-deluxe` | 3 Guests | ₹9,200/night | 4 Rooms | 301, 302, 303, 304 |
| **Executive Room** | `executive-room` | 3 Guests | ₹13,500/night | 4 Rooms | 401, 402, 403, 404 |
| **Luxury Suite** | `luxury-suite` | 4 Guests | ₹19,800/night | 4 Rooms | 501, 502, 503, 504 |
| **Presidential Suite** | `presidential-suite` | 5 Guests | ₹32,000/night | 4 Rooms | 601, 602, 603, 604 |

---

## 🔐 Credentials

### Administrator
- **URL:** [http://localhost/HOTEL%20PROJECT/ashvkatha-hotel/admin/index.php](http://localhost/HOTEL%20PROJECT/ashvkatha-hotel/admin/index.php)
- **Email:** `admin@ashvkatha.com`
- **Password:** `admin123`

### Demo Patron (Customer)
- **URL:** [http://localhost/HOTEL%20PROJECT/ashvkatha-hotel/login.php](http://localhost/HOTEL%20PROJECT/ashvkatha-hotel/login.php)
- **Email:** `guest@ashvkatha.com`
- **Password:** `guest123`

---

## 💾 Database Configuration

- **Database Name:** `ashvkatha_hotel`
- **Tables (10):**
  1. `users`
  2. `room_types`
  3. `rooms`
  4. `bookings`
  5. `payments`
  6. `services`
  7. `booking_services`
  8. `reviews`
  9. `contact_messages`
  10. `settings`
- **SQL Seed File:** [`database/ashvkatha_hotel.sql`](file:///c:/xampp/htdocs/HOTEL%20PROJECT/ashvkatha-hotel/database/ashvkatha_hotel.sql)
