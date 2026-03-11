# Page Builder Roadmap: Market-Ready Enhancement Plan

This document outlines the strategic plan to transition the Ekklesia CMS Page Builder from a "Static Marketing Tool" to a "Professional-Grade Church CMS" competing with industry leaders.

## Core Strategy: The "Dynamic & Motion" Update
The goal is to move beyond static manual entry and introduce **Dynamic Data Integration** (pulling from DB) and **Motion Components** (Carousels/Sliders) to make the frontend feel "alive."

---

## Phase 1: The "Motion" Update (Carousel & Sliders)
We will implement a reusable "Carousel" wrapper using **Alpine.js** (native to our stack) to avoid heavy external dependencies.

### 1.1 Hero Carousel
- **Functionality:** Multiple hero slides with individual CTAs.
- **Settings:** Autoplay speed, navigation arrows/dots, fade vs slide transition.

### 1.2 Testimonial & Logo Sliders
- **Functionality:** Infinite loop for testimonials and partner logos.
- **Settings:** Items per view (responsive), pause on hover.

---

## Phase 2: Church-Specific "Pro" Blocks
These blocks provide the "Niche Moat" that makes this CMS specialized for ministries.

### 2.1 Sermon/Media Player (Dynamic)
- **Source:** Pulls from `Sermons` model.
- **Features:** Audio player, video embed, "Last Sermon" auto-update, "Download Notes" button.

### 2.2 Giving & Donations (Functional)
- **Source:** Integrated with `Funds` and `Campaigns`.
- **Features:** Fund selection dropdown, "Quick Give" amount buttons, Stripe/PayPal integration link.

### 2.3 Staff & Leadership Directory (Dynamic)
- **Source:** Pulls from `Users` with 'staff' role or a dedicated `Staff` model.
- **Features:** Photo, Role, Bio, Social links.

---

## Phase 3: Automation & Engagement
Reducing administrative overhead by automating content updates.

### 3.1 Recent Events Feed (Dynamic)
- **Logic:** Automatically displays the next 3 upcoming events.
- **Filtering:** By Campus, Category (Youth, Women, Men), or "Featured" flag.

### 3.2 Live Stream Status (Real-time)
- **Logic:** A "Join Now" banner that only appears when a stream is active (configured in settings).

### 3.3 Countdown Timer
- **Logic:** Fixed date/time target for upcoming conferences or launches.

---

## Phase 4: Advanced Layouts & Structure
Giving users full control over the page rhythm and structural composition.

### 4.1 Flexible Columns (The "Grid" Block)
- **Functionality:** Choose from common layouts (50/50, 70/30, 33/33/33).
- **Content:** Allows adding specific content elements (Text, Image, Button) inside columns.

### 4.2 Spacer & Visual Dividers
- **Functionality:** Control vertical whitespace and add decorative separators (Lines, Waves).

### 4.3 General Purpose Tabs & Accordions
- **Functionality:** Beyond just FAQ - useful for "Service Times" or "What to Expect."

---

## Phase 5: Media & Interactive
Elevating the visual impact with advanced media handling and engagement tools.

### 5.1 Dynamic Gallery (Grid/Slider)
- **Functionality:** Display images from a selected `Gallery` or manual upload.
- **Layouts:** Masonry grid, Slider, or Mosaic.

### 5.2 Logo Cloud / Partner Slider
- **Functionality:** Infinite scrolling loop for partners, affiliations, or sponsors.

### 5.3 Newsletter Signup Form
- **Functionality:** Simple, high-conversion email capture for internal/external lists.

### 5.4 Interactive Map / Location
- **Functionality:** Google Maps/Mapbox integration pulling from `Campus` locations.

---

## Technical Standards
- **Component Pattern:** Use `resources/views/components/blocks/` for all new UI.
- **State Management:** Alpine.js for frontend interactivity (Carousels/Tabs).
- **Data Fetching:** View Composers or Service classes to keep Blade components clean.
- **Styling:** Vanilla Tailwind CSS v4.

---

## Progress Tracker
- [x] Initial 11 Static Blocks
- [x] Live Preview System
- [x] Phase 1: Carousel Enhancements (Hero, Testimonials)
- [x] Phase 2: Church-Specific Blocks (Sermons, Giving, Staff)
- [x] Phase 3: Automation & Engagement (Events, Live Stream, Countdown)
- [x] Phase 4: Advanced Layouts & Structure
- [x] Phase 5: Media & Interactive
