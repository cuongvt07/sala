# Booking Grid Implementation Plan

## Goal
Implement a visual booking calendar (Scheduler View) where users can see bookings on a timeline (Rows: Rooms, Columns: Days) and interact with them.

## Components
1.  **Filament Page:** `BookingCalendar` - Main container.
2.  **Livewire Component:** `BookingGrid` (embedded in page) - Handles logic.
3.  **Blade View:** `resources/views/filament/pages/booking-calendar.blade.php`.

## Features
-   **Header:**
    -   Month/Year Selector.
    -   Area Filter (Select).
-   **Grid:**
    -   Left Column: List of Rooms (grouped by Area if "All Areas" selected, or filtered).
    -   Top Row: Days of the selected month.
    -   Cells: Booking blocks positioned by `grid-column-start` / `grid-column-end`.
-   **Interactions:**
    -   Click empty cell -> Open `CreateBooking` modal.
    -   Click booking -> Open `EditBooking` modal.

## Tech Details
-   **CSS Grid:** Used for layout alignment.
-   **Alpine.js:** Used for drag-and-drop and simple interactivity.
-   **Livewire:** Fetch data, handle form submissions.
