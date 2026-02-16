# IT Club of CCT - Project Analysis

## Project Overview

**Project Name:** IT Club of CCT Digital Governance & Innovation Platform  
**Type:** Full-stack Web Application (Laravel + Vue.js)  
**Description:** A comprehensive digital ecosystem for the IT Club at Crimson College of Technology, featuring a unique "Web Desktop Environment" (WebDE) that mimics a desktop OS in the browser.

---

## Technical Stack

### Backend
- **Framework:** Laravel 12.x
- **PHP Version:** 8.2+
- **Authentication:** Laravel Sanctum (API), Session-based (Web)
- **Real-time:** Laravel Reverb / WebSockets
- **Database:** SQLite (development), MySQL-ready

### Frontend
- **Framework:** Vue 3 (Composition API)
- **State Management:** Inertia.js
- **Styling:** Tailwind CSS 4.x
- **Build Tool:** Vite 7.x
- **Additional Libraries:**
  - Monaco Editor (code playground)
  - Chart.js (data visualization)
  - Interact.js (window drag/resize)

---

## Architecture Overview

### 1. Web Desktop Environment (WebDE)

The core innovation is a desktop-like interface in the browser:

- **Desktop.vue** - Main desktop container managing windows, icons, taskbar
- **Window.vue** - Reusable window component with drag, resize, minimize, maximize
- **App Launcher** - Click icons to open "apps" in windows
- **Time-based Themes** - Scene changes based on time of day (morning/day/evening/night)

### 2. Available Apps (in WebDE)

| App | Description |
|-----|-------------|
| Nexus | Social feed (posts, news, events) |
| Chat | Real-time messaging with channels |
| Elections | Digital voting system |
| Events | Event management |
| News | News/Blog articles |
| Hall of Fame | Alumni recognition |
| Transparency | Financial transparency charts |
| Learning | Learning resources hub |
| Code Playground | Monaco Editor for coding |
| Terminal | Terminal emulator |
| Governance | Admin governance panel |
| UserMan | User management (admin) |
| LogApp | System logs viewer |
| ProfileVerify | Email verification |

---

## Role-Based Access Control (RBAC)

### User Roles (from DesktopController)

| Role | Permissions |
|------|-------------|
| Member | Basic access: nexus, terminal, elections (vote), learning, finance (view), code-playground |
| Treasurer | Finance management + member access |
| Secretary | Events & news management + member access |
| Vice-President | View-only elevated access |
| President | Governance management + all views |
| SuperAdmin | Full system access + system logs |

### Role Permission Matrix

Defined granularly in [`DesktopController.php`](app/Http/Controllers/Api/DesktopController.php:65):

- **Elections:** vote, view_results, create, manage
- **Events:** view, create, edit, delete
- **Finance:** view, create, edit, delete
- **News:** view, create, edit, delete
- **Learning:** view, submit
- **Code Playground:** execute

---

## API Endpoints (RESTful)

### Authentication
- `POST /v1/auth/login` - Mobile/Web login
- `POST /api/v1/auth/logout` - Logout
- `GET /api/v1/auth/user` - Get current user

### Desktop
- `GET /api/v1/desktop/icons` - Get user's available apps based on role

### Elections
- `GET /api/v1/elections` - List elections
- `GET /api/v1/elections/{id}` - Get election details with candidates
- `POST /api/v1/elections/{id}/vote` - Cast vote

### Nexus (Social)
- `GET /api/v1/nexus/feed` - Combined feed (posts, news, events)
- `POST /api/v1/nexus/posts` - Create post
- `POST /api/v1/nexus/like` - Toggle like
- `POST /api/v1/nexus/comments` - Add comment
- `GET /api/v1/nexus/notifications` - Get notifications

### Chat
- `GET /api/v1/chat/channels` - List available channels
- `GET /api/v1/chat/history` - Get message history
- `GET /api/v1/chat/members` - List users
- `POST /api/v1/chat/send` - Send message
- `POST /api/v1/chat/groups` - Create chat group
- `POST /api/v1/chat/call` - Initiate audio/video call

### Content Management
- `GETPOST//DELETE /api/v1/news` - News CRUD
- `GET/POST/DELETE /api/v1/events` - Events CRUD
- `GET/POST/DELETE /api/v1/documents` - Documents
- `GET/POST/DELETE /api/v1/resources` - Learning resources

### Admin
- `GET /api/v1/admin/users` - List users
- `POST /api/v1/admin/users/{id}/role` - Update role
- `POST /api/v1/admin/users/{id}/toggle-status` - Toggle user status

### Other
- `GET /api/v1/hall-of-fame` - Hall of Fame entries
- `GET/POST /api/v1/transparency/data` - Financial data
- `POST /api/v1/code/execute` - Execute code in sandbox
- `GET /api/v1/system-logs` - View system logs

---

## Database Schema

### Core Tables

| Table | Purpose |
|-------|---------|
| users | User accounts with roles |
| roles | Role definitions |
| posts | Nexus social posts |
| comments | Polymorphic comments |
| likes | Polymorphic likes |
| messages | Chat messages |
| chat_groups | Chat group conversations |
| elections | Election metadata |
| candidates | Election candidates |
| votes | Vote records |
| events | Club events |
| news | News articles |
| transactions | Financial transactions |
| hall_of_fames | Alumni records |
| resources | Learning resources |
| documents | File documents |
| vault_files | User file vault |
| activity_logs | User activity tracking |
| system_logs | System-level logging |
| notifications | User notifications |

---

## Key Features Implemented

### 1. Digital Election System
- Multiple position voting
- One vote per user per position
- Vote caching for performance
- IP/User-Agent logging for audit
- President excluded from voting
- Manifesto PDF uploads

### 2. Real-time Chat
- Public channels (general, announcements)
- Role-restricted channels (core-team, dev-terminal)
- Private DMs
- Group chats
- Audio/Video call initiation (WebRTC-ready events)
- File attachments

### 3. Nexus Social Feed
- Combined feed: Posts + News + Events
- Polymorphic likes/comments
- Announcement posts
- Notifications system

### 4. Financial Transparency
- Transaction tracking (income/expenses)
- Visual charts (Chart.js)
- Role-based access (Treasurer can edit)

### 5. Code Playground
- Monaco Editor integration
- Multiple language support
- Backend execution endpoint

---

## Frontend Component Structure

```
resources/js/
├── Pages/
│   ├── Desktop.vue          # Main WebDE container
│   ├── Dashboard.vue        # Traditional dashboard
│   └── ...
├── Components/
│   └── WebDE/
│       ├── Window.vue       # Window frame component
│       └── Apps/
│           ├── NexusApp.vue
│           ├── Nexus/
│           │   ├── NexusFeed.vue
│           │   ├── FacebookFeed.vue
│           │   └── NexusNotifications.vue
│           ├── ChatApp.vue
│           ├── ElectionApp.vue
│           ├── EventsApp.vue
│           ├── NewsApp.vue
│           ├── HallOfFameApp.vue
│           ├── TransparencyApp.vue
│           ├── LearningApp.vue
│           ├── CodePlayground.vue
│           ├── TerminalApp.vue
│           ├── GovernanceApp.vue
│           ├── UserManApp.vue
│           ├── LogApp.vue
│           └── ProfileVerifyApp.vue
```

---

## Event System (Real-time)

### Events Defined
- [`MessageSent.php`](app/Events/MessageSent.php) - New chat message
- [`ChatCallSent.php`](app/Events/ChatCallSent.php) - Incoming call
- [`ChatCallResponse.php`](app/Events/ChatCallResponse.php) - Call response

---

## Security Features

1. **Authentication:** Symbol number-based login (no public registration)
2. **CSRF Protection:** Laravel built-in
3. **Rate Limiting:** Configured for API routes
4. **Role Middleware:** Route-level role enforcement
5. **Vote Encryption:** IP/User-Agent logging for audit
6. **Hidden Role Scope:** SuperAdmin hidden from regular queries

---

## Design System

### Color Palette (from guidance.md)
- Primary Blue: `#2B6CB0`
- Secondary Teal: `#00A8E8`
- Accent Green: `#2ECC71`
- Dark Mode: `#0F172A` / `#1E293B`

### UI Characteristics
- Glassmorphism cards
- Subtle gradients
- Rounded corners (`rounded-2xl`)
- Modern typography (Inter/Poppins)

---

## Development Status (Based on Roadmap)

| Phase | Status |
|-------|--------|
| Phase 1: Environment & Setup | ✅ Complete |
| Phase 2: Authentication & Security | ✅ Complete |
| Phase 3: The "Legendary" UI Base | ✅ Complete |
| Phase 4: Public & Legacy Modules | ✅ Complete |
| Phase 5: Digital Governance & Finance | ✅ Complete |
| Phase 6: Innovation Hub | ✅ Complete |
| Phase 7: Real-time Communication | ✅ Complete |
| Phase 8: Hardening & Deployment | 🔄 Partial |

---

## Configuration Files

- [`.env`](.env) - Environment configuration
- [`config/sanctum.php`](config/sanctum.php) - API token settings
- [`config/reverb.php`](config/reverb.php) - WebSocket configuration
- [`config/database.php`](config/database.php) - Database settings
- [`vite.config.js`](vite.config.js) - Build configuration
- [`tailwind.config.js`](tailwind.config.js) - Styling configuration

---

## Key Files Reference

| File | Purpose |
|------|---------|
| [`guidance.md`](guidance.md) | Master project documentation |
| [`Desktop.vue`](resources/js/Pages/Desktop.vue) | Main desktop interface |
| [`DesktopController.php`](app/Http/Controllers/Api/DesktopController.php) | App access control |
| [`NexusController.php`](app/Http/Controllers/Api/NexusController.php) | Social features |
| [`ChatController.php`](app/Http/Controllers/Api/ChatController.php) | Messaging system |
| [`ElectionController.php`](app/Http/Controllers/Api/ElectionController.php) | Voting system |
| [`User.php`](app/Models/User.php) | User model with roles |

---

## Summary

This is a well-structured, feature-rich application with a unique UI/UX approach (WebDE). It includes:

- ✅ Full RBAC implementation
- ✅ Real-time chat with WebSockets
- ✅ Digital election system
- ✅ Social feed (Nexus)
- ✅ Financial transparency
- ✅ Code playground
- ✅ Learning hub
- ✅ Admin governance panels

The codebase follows Laravel best practices with clean controller organization, proper model relationships, and a modern Vue 3 frontend with Inertia.js.
