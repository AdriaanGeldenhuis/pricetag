# Pricetag Technical Stack - LOCKED

This document defines the core technical stack for the Pricetag e-commerce platform.
**These decisions are final and should not be changed without thorough review.**

---

## Backend Stack

| Component | Technology | Version | Status |
|-----------|------------|---------|--------|
| Language | PHP | 8.1+ | **LOCKED** |
| Database Access | PDO | - | **LOCKED** |
| SQL Security | Prepared Statements Only | - | **LOCKED** |
| Architecture | MVC (Custom Lightweight) | - | **LOCKED** |
| API Style | REST-ready Controllers | - | **LOCKED** |

### Backend Rules
1. **NO raw SQL queries** - All database operations must use prepared statements
2. **NO string concatenation in SQL** - Use parameter binding exclusively
3. **NO direct $_GET/$_POST access in models** - Sanitize in controllers
4. **ALL user input must be validated** - Use the Controller::validate() method

---

## Frontend Stack

| Component | Technology | Version | Status |
|-----------|------------|---------|--------|
| Markup | HTML5 | - | **LOCKED** |
| Styling | CSS3 | - | **LOCKED** |
| Layout | CSS Grid + Flexbox | - | **LOCKED** |
| JavaScript | Vanilla JS (ES6+) | - | **LOCKED** |
| JS Framework | Alpine.js (optional) | 3.x | OPTIONAL |

### Frontend Rules
1. **Mobile-first CSS** - All styles start with mobile and scale up
2. **NO jQuery** - Use vanilla JavaScript or Alpine.js
3. **NO heavy frameworks** - No React, Vue, Angular for the main site
4. **Progressive enhancement** - Core functionality works without JS
5. **CSS Variables** - Use design system tokens from design-system.css

---

## Infrastructure Stack

| Component | Technology | Version | Status |
|-----------|------------|---------|--------|
| Database | MySQL | 8.0+ | **LOCKED** |
| Caching | File-based (Cache service) | - | **LOCKED** |
| Scheduling | Cron (Scheduler service) | - | **LOCKED** |
| Configuration | .env file | - | **LOCKED** |
| Assets | CDN-ready | - | **LOCKED** |
| Hosting | Shared hosting compatible | - | **LOCKED** |

### Infrastructure Rules
1. **Server-side caching** - Use App\Services\Cache for all caching needs
2. **Scheduled tasks** - Use App\Services\Scheduler, run via cron
3. **Environment variables** - All config via .env, never hardcode credentials
4. **CDN support** - Use asset() helper for all static files

---

## Required PHP Extensions

```
pdo
pdo_mysql
json
mbstring
openssl
curl
```

---

## Directory Structure

```
/app                    - Application code (MVC)
  /Core                 - Framework core (Router, Model, Controller, etc.)
  /Controllers          - HTTP Controllers
  /Models               - Data Models
  /Views                - View templates
  /Services             - Business logic services
  /Middleware           - Request middleware

/admin                  - Admin panel (separate namespace)
  /Controllers          - Admin controllers
  /Views                - Admin views

/config                 - Configuration files
  app.php
  database.php
  payment.php
  seo.php

/cron                   - Cron job entry points
  scheduler.php

/database               - Database files
  schema.sql
  /migrations           - Database migrations

/public                 - Web root
  index.php
  /assets
    /css
    /js
    /images
    /icons

/storage                - Runtime storage (writable)
  /cache
  /logs
  /sessions
  /uploads
  /invoices
```

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2024 | Initial tech stack definition |
| 1.0.1 | 2024 | Added Cache service, Scheduler service |

---

## Change Log Policy

To modify this tech stack:
1. Create a proposal document explaining the change
2. Evaluate impact on existing code
3. Get stakeholder approval
4. Update this document
5. Update bootstrap.php version checks if needed
