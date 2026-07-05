# PlayMate UI Guidelines

> **Design System Documentation**  
> Last updated: May 2026  
> Version: 1.0 — Dark Luxury Sporty Aesthetic

---

## 1. Design Philosophy

PlayMate's visual identity combines **elite sports club exclusivity** with **futuristic tech precision**. The design language speaks to serious athletes who demand premium experiences.

### Core Principles

1. **Dark Luxury** — Dominance of deep blacks and navy with strategic neon accents
2. **Editorial Composition** — Asymmetrical layouts inspired by high-end sports magazines
3. **Cinematic Photography** — Full-bleed athlete imagery with dramatic lighting
4. **Glassmorphism** — Subtle transparency and blur for depth
5. **Bold Typography** — Oversized headlines that command attention
6. **Intentional Whitespace** — Breathing room that signals premium quality
7. **Motion Design** — Smooth, purposeful animations that enhance UX

---

## 2. Color System

### Primary Palette

| Color | Hex | Usage |
|---|---|---|
| **Deep Black** | `#0a0a0a` | Primary background |
| **Dark Navy** | `#0f172a` | Secondary background, cards |
| **Slate Dark** | `#1e293b` | Elevated surfaces |
| **Neon Red** | `#ef4444` | Primary CTA, accents |
| **Neon Pink** | `#ec4899` | Hover states, highlights |
| **Electric Blue** | `#3b82f6` | Links, secondary actions |

### Neutral Palette

| Color | Hex | Usage |
|---|---|---|
| **Pure White** | `#ffffff` | Headlines, primary text |
| **Soft White** | `#f8fafc` | Body text on dark |
| **Slate 400** | `#94a3b8` | Secondary text |
| **Slate 600** | `#475569` | Tertiary text, captions |

### Accent Palette

| Color | Hex | Usage |
|---|---|---|
| **Amber Glow** | `#f59e0b` | Badges, notifications |
| **Emerald** | `#10b981` | Success states |
| **Violet** | `#8b5cf6` | Premium features |

### Gradient System

```css
/* Hero Gradient Overlay */
background: linear-gradient(135deg, rgba(10,10,10,0.95) 0%, rgba(15,23,42,0.85) 100%);

/* Neon Glow Gradient */
background: linear-gradient(90deg, #ef4444 0%, #ec4899 100%);

/* Card Hover Gradient */
background: linear-gradient(180deg, rgba(239,68,68,0.1) 0%, transparent 100%);

/* Spotlight Effect */
background: radial-gradient(circle at 50% 0%, rgba(239,68,68,0.15) 0%, transparent 60%);
```

---

## 3. Typography

### Font Stack

```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
```

**Fallback:** System UI fonts for performance

### Type Scale

| Element | Size | Weight | Line Height | Letter Spacing |
|---|---|---|---|---|
| **Hero Display** | 72px / 4.5rem | 800 (ExtraBold) | 1.1 | -0.02em |
| **H1** | 56px / 3.5rem | 700 (Bold) | 1.15 | -0.01em |
| **H2** | 40px / 2.5rem | 700 (Bold) | 1.2 | -0.01em |
| **H3** | 32px / 2rem | 600 (SemiBold) | 1.3 | 0 |
| **H4** | 24px / 1.5rem | 600 (SemiBold) | 1.4 | 0 |
| **Body Large** | 18px / 1.125rem | 400 (Regular) | 1.6 | 0 |
| **Body** | 16px / 1rem | 400 (Regular) | 1.6 | 0 |
| **Small** | 14px / 0.875rem | 400 (Regular) | 1.5 | 0 |
| **Caption** | 12px / 0.75rem | 500 (Medium) | 1.4 | 0.02em |

### Typography Rules

1. **Headlines** — Always bold (700+), use uppercase sparingly for impact
2. **Body Text** — Never below 16px on desktop, 14px minimum on mobile
3. **Contrast** — White text on dark backgrounds, minimum 4.5:1 ratio
4. **Hierarchy** — Use size + weight + color to establish clear hierarchy
5. **Line Length** — Max 75 characters per line for readability

---

## 4. Spacing System

### Base Unit: 4px

| Token | Value | Usage |
|---|---|---|
| `xs` | 4px | Tight spacing, icon gaps |
| `sm` | 8px | Small padding, compact layouts |
| `md` | 16px | Default spacing |
| `lg` | 24px | Section padding |
| `xl` | 32px | Large gaps |
| `2xl` | 48px | Section margins |
| `3xl` | 64px | Hero spacing |
| `4xl` | 96px | Major section breaks |

### Layout Grid

- **Desktop:** 12-column grid, 24px gutters
- **Tablet:** 8-column grid, 16px gutters
- **Mobile:** 4-column grid, 16px gutters

### Container Widths

| Breakpoint | Max Width |
|---|---|
| `sm` | 640px |
| `md` | 768px |
| `lg` | 1024px |
| `xl` | 1280px |
| `2xl` | 1536px |

---

## 5. Component Patterns

### Cards

**Glass Card (Primary)**
```css
background: rgba(15, 23, 42, 0.6);
backdrop-filter: blur(20px);
border: 1px solid rgba(255, 255, 255, 0.1);
border-radius: 24px;
box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
```

**Elevated Card**
```css
background: #1e293b;
border-radius: 24px;
box-shadow: 0 4px 24px rgba(0, 0, 0, 0.5);
transition: transform 0.3s ease, box-shadow 0.3s ease;
```

**Hover State**
```css
transform: translateY(-4px);
box-shadow: 0 12px 40px rgba(239, 68, 68, 0.3);
```

### Buttons

**Primary CTA**
```css
background: linear-gradient(90deg, #ef4444 0%, #ec4899 100%);
border-radius: 9999px; /* Full rounded */
padding: 16px 32px;
font-weight: 700;
color: #ffffff;
box-shadow: 0 0 24px rgba(239, 68, 68, 0.5);
transition: all 0.3s ease;
```

**Secondary Button**
```css
background: transparent;
border: 2px solid rgba(255, 255, 255, 0.2);
border-radius: 9999px;
padding: 14px 30px;
color: #ffffff;
backdrop-filter: blur(10px);
```

### Badges

**Neon Badge**
```css
background: rgba(239, 68, 68, 0.15);
border: 1px solid rgba(239, 68, 68, 0.3);
border-radius: 9999px;
padding: 6px 16px;
font-size: 12px;
font-weight: 600;
color: #ef4444;
text-transform: uppercase;
letter-spacing: 0.05em;
```

---

## 6. Layout Patterns

### Hero Section (Asymmetrical)

**Structure:**
- Left: 65% width — Full-bleed cinematic image with gradient overlay
- Right: 35% width — Stacked modular cards

**Key Elements:**
- Oversized headline (72px+)
- Floating stat cards with glassmorphism
- Dual CTA buttons (primary + secondary)
- Subtle grain texture overlay

### Editorial Grid

**Pattern:** Alternating 60/40 and 40/60 splits

**Example:**
```
Row 1: [Large Feature 60%] [Stats Card 40%]
Row 2: [Image 40%] [Content 60%]
Row 3: [Content 60%] [Image 40%]
```

### Card Grid

**Desktop:** 3 columns, 24px gap  
**Tablet:** 2 columns, 16px gap  
**Mobile:** 1 column, 16px gap

---

## 7. Image Treatment

### Photography Style

1. **Cinematic Sports Action** — High-contrast, dramatic lighting
2. **Athlete Focus** — Close-ups showing intensity and emotion
3. **Venue Shots** — Wide angles with depth of field
4. **Color Grading** — Desaturated with selective color pops

### Overlay System

**Dark Gradient Overlay**
```css
background: linear-gradient(180deg, rgba(10,10,10,0.7) 0%, rgba(10,10,10,0.9) 100%);
```

**Spotlight Overlay**
```css
background: radial-gradient(circle at 30% 20%, rgba(239,68,68,0.2) 0%, transparent 50%);
```

### Image Specs

- **Hero Images:** 1920x1080px minimum, WebP format
- **Card Images:** 800x600px, WebP format
- **Thumbnails:** 400x400px, WebP format
- **Compression:** 80% quality for balance

---

## 8. Animation System

### Timing Functions

```css
--ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
--ease-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
--ease-in-out: cubic-bezier(0.645, 0.045, 0.355, 1);
```

### Standard Animations

**Fade Up**
```css
@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(24px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
```

**Glow Pulse**
```css
@keyframes glowPulse {
  0%, 100% {
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
  }
  50% {
    box-shadow: 0 0 40px rgba(239, 68, 68, 0.8);
  }
}
```

**Hover Lift**
```css
transition: transform 0.3s ease, box-shadow 0.3s ease;
&:hover {
  transform: translateY(-8px);
}
```

### Duration Guidelines

| Interaction | Duration |
|---|---|
| Micro-interactions | 150ms |
| Button hover | 250ms |
| Card hover | 300ms |
| Page transitions | 400ms |
| Modal open/close | 300ms |

---

## 9. Iconography

### Icon Style

- **Line icons** — 2px stroke weight
- **Size:** 24x24px default, 32x32px for emphasis
- **Color:** Match text color or use accent colors
- **Spacing:** 8px gap from adjacent text

### Icon Sources

- Heroicons (primary)
- Lucide Icons (secondary)
- Custom SVG for sport-specific icons

---

## 10. Responsive Breakpoints

```css
/* Mobile First Approach */
@media (min-width: 640px)  { /* sm */ }
@media (min-width: 768px)  { /* md */ }
@media (min-width: 1024px) { /* lg */ }
@media (min-width: 1280px) { /* xl */ }
@media (min-width: 1536px) { /* 2xl */ }
```

### Mobile Adaptations

1. **Typography:** Reduce hero text to 48px
2. **Spacing:** Reduce section padding to 48px
3. **Grid:** Stack all columns to single column
4. **Images:** Full-width with 16:9 aspect ratio
5. **Navigation:** Hamburger menu with slide-out drawer

---

## 11. Accessibility Standards

### WCAG 2.1 AA Compliance

- **Contrast Ratios:**
  - Normal text: 4.5:1 minimum
  - Large text (18px+): 3:1 minimum
  - UI components: 3:1 minimum

- **Focus States:**
  - Visible outline: 2px solid #ef4444
  - Offset: 2px from element

- **Interactive Elements:**
  - Minimum touch target: 44x44px
  - Keyboard navigable
  - Screen reader friendly labels

### Color Blindness Considerations

- Never rely on color alone for information
- Use icons + text labels
- Test with color blindness simulators

---

## 12. Performance Guidelines

### Optimization Targets

- **First Contentful Paint:** < 1.5s
- **Largest Contentful Paint:** < 2.5s
- **Time to Interactive:** < 3.5s
- **Cumulative Layout Shift:** < 0.1

### Best Practices

1. **Images:** Use WebP, lazy load below fold
2. **Fonts:** Preload critical fonts, use font-display: swap
3. **CSS:** Inline critical CSS, defer non-critical
4. **JavaScript:** Defer non-critical scripts
5. **Animations:** Use transform and opacity only (GPU-accelerated)

---

## 13. Component Library

### Navbar

**Style:** Floating translucent bar
- Height: 72px
- Background: `rgba(10, 10, 10, 0.8)` with `backdrop-blur(20px)`
- Border: 1px solid `rgba(255, 255, 255, 0.1)`
- Border radius: 9999px (full rounded)
- Position: Sticky top with 16px margin

### Footer

**Style:** Minimal dark footer
- Background: `#0a0a0a`
- Border top: 1px solid `rgba(255, 255, 255, 0.1)`
- Padding: 64px vertical
- Text color: `#94a3b8`

### Modals

**Style:** Centered overlay with glassmorphism
- Backdrop: `rgba(0, 0, 0, 0.8)` with blur
- Container: Glass card pattern
- Max width: 600px
- Animation: Fade + scale from 0.95 to 1

---

## 14. Content Guidelines

### Voice & Tone

- **Confident** — We know sports, we know tech
- **Inclusive** — All skill levels welcome
- **Energetic** — Active voice, dynamic language
- **Precise** — No fluff, every word ns its place

### Writing Style

- **Headlines:** Short, punchy, action-oriented
- **Body:** Scannable, use bullet points
- **CTAs:** Clear verbs (Find, Join, Explore, Connect)
- **Microcopy:** Helpful, never condescending

---

## 15. Brand Assets

### Logo Usage

**Primary Logo:** "PlayMate" wordmark + "PM" icon
- Icon: Circular badge with neon red gradient
- Wordmark: Bold sans-serif, white on dark
- Minimum size: 120px width
- Clear space: 16px on all sides

### Logo Variations

1. **Full Logo** — Icon + wordmark (primary)
2. **Icon Only** — For small spaces (32px+)
3. **Wordmark Only** — For horizontal layouts

### Don'ts

- ❌ Don't change logo colors
- ❌ Don't add effects (shadows, outlines)
- ❌ Don't rotate or distort
- ❌ Don't place on busy backgrounds

---

## 16. Implementation Checklist

### Before Launch

- [ ] All images optimized (WebP, compressed)
- [ ] Fonts preloaded
- [ ] Accessibility audit passed
- [ ] Mobile responsive tested
- [ ] Cross-browser tested (Chrome, Safari, Firefox, Edge)
- [ ] Performance metrics meet targets
- [ ] Dark mode tested
- [ ] Animations smooth on low-end devices
- [ ] Forms validated and error states designed
- [ ] Loading states implemented

---

## 17. Design Inspiration References

### Visual Benchmarks

1. **Nike.com** — Bold typography, cinematic imagery
2. **Playtomic** — Sports community UX patterns
3. **Reclub** — Event hosting flows
4. **Awwwards Sports Sites** — Premium aesthetics
5. **Apple Product Pages** — Clean spacing, editorial layouts

### Key Takeaways

- **Nike:** Oversized headlines, athlete-first photography
- **Playtomic:** Smart matchmaking UI, venue discovery
- **Reclub:** Social features, community building
- **Awwwards:** Asymmetrical grids, motion design
- **Apple:** Whitespace mastery, product storytelling

---

## 18. Version History

| Version | Date | Changes |
|---|---|---|
| 1.0 | May 2026 | Initial design system documentation |

---

## 19. Contact & Feedback

For design questions or contributions:
- **Design Lead:** [Your Name]
- **GitHub:** [Repository Link]
- **Figma:** [Design File Link]

---

**End of UI Guidelines**

*This is a living document. Update as the design system evolves.*
