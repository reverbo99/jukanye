# Jukanye — 16 screen inventory

Grid: **4 columns × 4 rows**. Source image: `assets/jukanye-app-screens.png`.

Most screens (except splash / thank-you) show the **bottom nav**: Home, Tickets, Donate, Menu.

---

## Row 1

### 1. Splash / Landing
- Top Kilimanjaro hero + circular **JuKaNye** logo (top-left)
- Gold brand: **JUKANYE** + `— FESTIVAL —`
- Tagline: Honoring Africa's Liberation Heroes, Promoting Patriotism
- Dates: **19 JULY – 01 AUGUST 2027** · **ARUSHA, TANZANIA**
- Countdown boxes: Days / Hours / Mins / Secs (dark chips, gold numbers)
- CTAs: **BUY TICKETS** (gold), **DONATE NOW** (green)
- Bottom nav present; Home active

### 2. Home
- App bar: **hamburger (sidebar)** · title "Home" · profile avatar
- Hero: Kilimanjaro + "Welcome to" / **JUKANYE FESTIVAL** / Pan-African tagline
- Contribution card (gold border): campaign copy, Total Raised, green **Contribute Now**
- **Latest News** + green "View all"; horizontal news rows (thumb + title + time)
- Bottom nav; Home active
- Sidebar: full festival menus (same as Menu list)

### 3. Menu
- Title **Menu**
- Vertical list with gold outlined icons + chevrons
- Typical items: About, Programme, Speakers, Artists, Heroes, Exhibitions, Tourism, Merchandise, Friends/Donate, Awards, Sponsors, News, Map, Contact, Profile

### 4. About
- Hero: people / celebration / flags
- Headline about festival purpose / liberation heritage
- Body copy + **Our Vision** and **Our Mission** sections

---

## Row 2

### 5. Festival Programme
- Tabs/chips: **All Days**, **Main Stage**, **Workshops**
  - Selected: gold text + thin gold border on dark chip (not gold fill)
- Cards with **left date box** (day number + JUL in gold) + title / location / time range
- Sample: Opening Ceremony, Liberation Talks, Youth Empowerment Workshops, Cultural Performances, Exhibition & Museum Day

### 6. Tickets
- Crowd/concert hero
- Tiers with price + **Buy Now**:
  - One Day Pass — TZS 20,000
  - Three Days Pass — TZS 50,000
  - Full Festival Pass — TZS 100,000
  - VIP Pass — TZS 250,000

### 7. Ticket Details
- Focus example: **Full Festival Pass**
- Price, dates/context, checklist of inclusions
- Quantity stepper (− / +)
- Large green **Proceed to Checkout**

### 8. My Tickets
- Tabs: Upcoming / Past / Transferred (as in mockup)
- Digital ticket card: name, ticket ID, large **QR**
- Gold **Transfer Ticket**

---

## Row 3

### 9. Donate
- Hero: hands / globe / giving
- Options list: One-Time, Monthly, Sponsor a Youth, Sponsor a Group, Support Exhibitions/Museum

### 10. One-Time Donation
- Amount chips: TZS 2,000 … 50,000 + Other Amount
- Payment methods: M-Pesa, Airtel Money, Tigo Pesa, Card, etc.

### 11. Payment
- Summary: “You are donating TZS …” (amount emphasized)
- Phone number field (+ method if shown)
- Green **Pay Now**, secondary **Cancel**

### 12. Thank You
- Large green success check circle
- **Thank You!** / Payment Successful
- Reference + amount summary
- Green **Back to Home**
- No bottom nav required

---

## Row 4

### 13. Festival Map
- Illustrated / aerial festival grounds
- Labeled markers: Main Stage, Food Court, VIP, Medical, ATM, Toilets, Prayer Area, Exhibition, etc.

### 14. Merchandise Shop
- 2-column product grid
- Items: T-Shirt, Cap, Scarf, Kitenge
- Image + price + **Buy Now**

### 15. Tourism
- Vertical packages with photo thumbnails
- Serengeti, Mount Kilimanjaro, Zanzibar, Cultural Heritage
- “From TZS …” pricing

### 16. Profile
- Avatar, name, phone, email
- Links: My Profile, My Tickets, My Donations, My Orders, Settings, Help & Support, Log Out

---

## Primary flows to keep wired

```
Splash → Buy Tickets (tab) | Donate (tab) | Skip → Home
Tickets → Details → Checkout amount → Payment → Thank You → Home
Donate → Amount → Payment → Thank You → Home
Menu → About | Programme | Tourism | Shop | Map | Profile | Donate tab
```
