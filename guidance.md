# IT Club of CCT: Digital Governance & Innovation Platform
## Project Guidance & Documentation

This document serves as the master guide for the development of the **IT Club of CCT Digital Ecosystem**. It synthesizes the core vision, technical requirements, and design philosophies outlined in the reference documentation.

---

### 🚀 1. Core Vision
The platform is defined as the **Digital Backbone** of the IT Club at Crimson College of Technology. It moves beyond a traditional "club website" to become a centralized platform for governance, transparency, and innovation.

**Primary Objectives:**
*   **Digital Governance:** Transforming club operations through digital elections and role-based management.
*   **Legacy Preservation:** Documenting the club's history through the Hall of Fame.
*   **Financial Transparency:** Providing real-time insights into club finances for members and stakeholders.
*   **Innovation & Learning:** Integrated tools for technical growth (Learning Hub & Coding Playground).

---

### 🎨 2. Design System & Aesthetics
The design follows a **"GitHub × Stripe × Apple"** philosophy: Clean, confident, and professional.

*   **Primary Palette:** 
    *   🔵 **Primary Blue:** `#2B6CB0` (Headers, buttons, highlights)
    *   🐬 **Secondary Teal:** `#00A8E8` (Accents, links)
    *   🟢 **Accent Green:** `#2ECC71` (Success states, active indicators)
    *   🌑 **Dark Mode (Default):** `#0F172A` / `#1E293B`
*   **UI Characteristics:** Glassmorphism cards, subtle gradients, rounded corners (`rounded-2xl`), and modern typography (Inter/Poppins).
*   **Motion Design:** Use of **Three.js** or **Spline** for 3D elements (rotating globes, circuit lines) and **GSAP/Framer Motion** for smooth 2D transitions.

---

### 🛠 3. Technical Architecture
*   **Stack:** Laravel + Vue + Blade + Tailwind CSS + MySQL.
*   **API-First:** Built with a RESTful JSON API layer (Laravel Sanctum) to support future mobile application development (Flutter/React Native) without backend rewrites.
*   **Role-Based Access Control (RBAC):**
    *   **SuperAdmin:** Invisible, full override permissions, system logs.
    *   **Executive Roles:** President, VP, Secretary, Treasurer.
    *   **Members:** Standard access to learning and participation modules.
*   **Authentication:** Symbol number-based login. No public registration; members are added by administrators.

---

### 📦 4. Core Modules Breakdown

#### **A. Digital Election System**
*   **Features:** Candidate registration, manifesto uploads (PDF), campaign video integration, and secure encrypted voting.
*   **Security:** One vote per user per role, non-editable votes, and detailed activity logs.

#### **B. Financial Transparency System**
*   **Treasurer Panel:** Manage income (fees, sponsorships) and expenses with receipt uploads.
*   **Public Portal:** Visual charts showing net balance and event breakdowns.

#### **C. Learning & Innovation**
*   **Learning Hub:** Markdown-supported tutorials with progress tracking.
*   **Coding Playground:** Integrated **Monaco Editor** with a Docker-based sandbox to execute C, C++, Java, Python, and JavaScript.

#### **D. Internal Communication**
*   **Chat System:** Real-time messaging using Laravel WebSockets, featuring role-restricted channels and file sharing.

---

### 📏 5. Development Guidelines (Dos & Don'ts)

| **✅ DO** | **❌ DON'T** |
| :--- | :--- |
| Keep business logic in Services/Controllers | Write DB queries inside Blade views |
| Use **Policies** for all role-based controls | Hardcode roles or permissions |
| Implement **Form Requests** for validation | Use inline CSS or random JS in views |
| Test extensively in **mobile view** | Use huge uncompressed images |
| Use `.env` for all configurations | Use external libraries without permission |
| Maintain clean, documented code | Make unexpected UI or logic changes |

---

### 📁 6. Recommended Folder Structure
Following a modular approach for views and components:
*   `resources/views/layouts/`: `app`, `guest`, `admin`
*   `resources/views/components/`: `ui` (buttons, cards), `navigation` (navbar, sidebar), `animations` (3D/2D)
*   `resources/views/pages/`: Modularized by feature (events, election, etc.)

---

### 📈 7. Performance & Security Strategy
*   **Performance:** Enable Laravel caching (routes, config, views), use lazy loading for assets, and compress all images/videos.
*   **Security:** CSRF/XSS protection, rate limiting, vote encryption, and daily automated backups.

---

## 🗺️ Execution Roadmap (Step-by-Step)

This roadmap tracks our development progress. Use `[ ]` for pending, `[/]` for in-progress, and `[x]` for completed tasks.

### Phase 1: Environment & Setup 🛠️
- [ ] Initialize Laravel 11 project with Vite <!-- id: 1.1 -->
- [ ] Install & Configure Tailwind CSS <!-- id: 1.2 -->
- [ ] Setup Vue.js 3 with Inertia.js (or Blade + Vue components) <!-- id: 1.3 -->
- [ ] Configure Database (MySQL) & `.env` <!-- id: 1.4 -->
- [ ] Install Core Dependencies (Sanctum, Ziggy, etc.) <!-- id: 1.5 -->

### Phase 2: Authentication & Security Center 🔐
- [ ] Customize Authentication (Symbol Number based login) <!-- id: 2.1 -->
- [ ] Implement Role-Based Access Control (RBAC) Middleware <!-- id: 2.2 -->
- [ ] Create SuperAdmin (Hidden) dashboard & routes <!-- id: 2.3 -->
- [ ] Setup Activity Logging for security auditing <!-- id: 2.4 -->

### Phase 3: The "Legendary" UI Base 🎨
- [ ] Create Base Layouts (Public, Member, Admin) <!-- id: 3.1 -->
- [ ] Design & Implement UI Component Library (Buttons, Cards, Modals) <!-- id: 3.2 -->
- [ ] Build the "Legendary" Landing Page with 3D Hero (Three.js/Spline) <!-- id: 3.3 -->
- [ ] Implement global Dark Mode as default <!-- id: 3.4 -->

### Phase 4: Public & Legacy Modules 🏛️
- [ ] **Hall of Fame:** Interactive vertical timeline of alumni <!-- id: 4.1 -->
- [ ] **Events System:** Public listings, registration, and past gallery <!-- id: 4.2 -->
- [ ] **Transparency Portal:** Real-time financial charts (Public View) <!-- id: 4.3 -->
- [ ] **News/Blog:** Markdown-based news updates <!-- id: 4.4 -->

### Phase 5: Digital Governance & Finance 🗳️
- [ ] **Election Portal:** Candidate profiles, manifesto/video uploads <!-- id: 5.1 -->
- [ ] **Voting Engine:** Secure, encrypted, one-vote-per-user logic <!-- id: 5.2 -->
- [ ] **Finance Manager:** Treasurer panel for income/expense tracking <!-- id: 5.3 -->
- [ ] **Report Generator:** Export PDF reports for financial transparency <!-- id: 5.4 -->

### Phase 6: Innovation Hub 💻
- [ ] **Learning Hub:** Category-based tutorials with progress tracking <!-- id: 6.1 -->
- [ ] **Coding Playground:** Integrated Monaco Editor <!-- id: 6.2 -->
- [ ] **Code Executor:** Setup sandbox for C, C++, Java, Python, JS <!-- id: 6.3 -->
- [ ] **Contribution Tracker:** Gamified points for code/content contributions <!-- id: 6.4 -->

### Phase 7: Real-time Communication 💬
- [ ] **Chat Engine:** Setup Laravel Reverb/WebSockets <!-- id: 7.1 -->
- [ ] **Channels:** Implement public, role-restricted, and private channels <!-- id: 7.2 -->
- [ ] **Notifications:** Real-time toast notifications for system events <!-- id: 7.3 -->

### Phase 8: Hardening & Deployment 🚀
- [ ] **Image/Video Optimization:** Auto-compress uploads <!-- id: 8.1 -->
- [ ] **Performance Pass:** Route/View caching and lazy loading <!-- id: 8.2 -->
- [ ] **Security Audit:** XSS, CSRF, SQLi, and Rate Limiting checks <!-- id: 8.3 -->
- [ ] **Deployment Prep:** SSL, Production `.env`, and Cron setups <!-- id: 8.4 -->

---
*Created by Antigravity (AI Assistant) based on Reference Documentation by Rahul Bashyal.*
