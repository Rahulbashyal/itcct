# Landing Page Redesign Specification

## Overview
Redesign the Welcome.vue landing page to be professional, clean, minimal while maintaining the IT Club CCT brand identity and theme.

## Design Principles
- **Professional**: Clean lines, clear typography, appropriate spacing
- **Minimal**: Less clutter, focused content, whitespace utilization
- **On-Brand**: Use the established color palette (Primary Blue #3b82f6, Secondary Teal #14b8a6, Accent Green #10b981)

---

## Current Sections (Keep & Improve)

### 1. Hero Section
**Current**: "Building the Digital Future" - very tech/futuristic with system protocol text
**New Version**: 
- Clean headline: "IT Club of CCT"
- Subheadline: "Where Innovation Meets Leadership"
- Brief description of the club
- Two CTAs: "Join Us" (primary) and "Learn More" (secondary)
- Subtle background (keep the dark theme with gradient overlay)

### 2. About Section (NEW)
- Title: "About Us"
- Content: Mission, Vision, Values
- Clean text layout with icons for key points

### 3. Features/Services Section (NEW)
- Title: "What We Do"
- 4-6 cards showing core activities:
  - Digital Governance
  - Technical Events
  - Learning Hub
  - Innovation & Projects

### 4. Stats Section (NEW)
- Clean counter section with key metrics:
  - Members
  - Events Organized
  - Projects Completed
  - Years Active

### 5. Current "Bento Hub" → "Platform Features"
**Keep but simplify**: The card layout showing Origins, Projects, Nexus, Transparency
- Clean up the design
- Better spacing
- Clearer CTAs

### 6. Upcoming Events (ENHANCED)
- Use the `upcomingEvents` data from controller
- Display 3 featured events in cards
- Link to full events page

### 7. Featured Projects (ENHANCED)
- Use the `featuredProjects` data from controller
- Display in card grid
- Link to projects page

### 8. Hall of Fame Preview
**Keep**: The "Elite" section but make it cleaner

### 9. Call to Action Section
**New**: Clear section encouraging membership/engagement

### 10. Contact Section
**Improve**: More professional contact information display

---

## Technical Changes

### PublicController Updates
Add more data to the index() response:
```php
public function index()
{
    return Inertia::render('Welcome', [
        'stats' => [
            'members' => User::count(),
            'events' => Event::count(),
            'projects' => Project::count(),
            'years' => 2024 - 2012, // or store in config
        ],
        'features' => [...],
        'featuredProjects' => Project::where('is_featured', true)->limit(3)->get(),
        'upcomingEvents' => Event::where('is_published', true)
            ->where('event_date', '>=', now())
            ->limit(3)
            ->get(),
    ]);
}
```

### Welcome.vue Structure
```
<template>
  <GuestLayout>
    1. Hero Section
    2. About Section
    3. Stats Section  
    4. Features Grid
    5. Upcoming Events (from data)
    6. Featured Projects (from data)
    7. Hall of Fame Preview
    8. CTA Section
    9. Contact Section
  </GuestLayout>
</template>
```

---

## Color Palette (Keep Current)
- Primary Blue: #3b82f6
- Secondary Teal: #14b8a6  
- Accent Green: #10b981
- Dark Background: #090910
- Text: white/gray-400 for muted

---

## Typography
- Keep using "Outfit" font (already in GuestLayout)
- Clean font sizes and weights
- Consistent spacing

---

## Implementation Priority
1. Update PublicController to pass additional data
2. Rewrite Welcome.vue with all new sections
3. Test responsiveness
4. Verify all links work

---

## Files to Modify
1. `app/Http/Controllers/PublicController.php` - Add stats data
2. `resources/js/Pages/Welcome.vue` - Complete redesign
