# ADR-0005: Design System Strategy

**Status:** Accepted

**Date:** 01 July 2026

**Decision Makers:**
- Product Team
- Engineering Team

---

# Context

YachtOS aims to become a long-term commercial SaaS platform.

As the application grows, the number of screens, developers and UI components will increase significantly.

Using raw Tailwind utility classes throughout the application would eventually lead to inconsistent user interfaces, duplicated code and difficult maintenance.

A consistent design system is required.

---

# Decision

YachtOS will adopt a component-first design system.

Tailwind CSS will be treated as a low-level styling engine.

Application developers should primarily build interfaces using reusable design system components instead of raw Tailwind classes.

---

# Design Philosophy

The UI should be constructed using reusable components.

Examples include:

- Button
- Input
- Select
- Card
- Modal
- Drawer
- Badge
- Avatar
- Table
- Form
- Tabs

Business pages should be composed from these components.

---

# Tailwind Usage

Tailwind CSS remains the styling engine.

However:

Business pages should avoid large utility class blocks whenever possible.

Instead of:

<div className="flex items-center justify-between p-6 rounded-xl ...">

Developers should prefer:

<Card>
    <CardHeader />
    <CardContent />
</Card>

The design system owns the Tailwind implementation.

---

# Component Ownership

UI components become part of the YachtOS repository.

Third-party libraries provide the starting point, but YachtOS owns the implementation.

Components may be modified when required.

The project should never depend on external UI libraries for business-specific components.

---

# Shared Components

Reusable UI components belong in:

src/components/ui

Business-specific components belong inside their corresponding module.

Example:

src/modules/yachts/components

src/modules/maintenance/components

---

# Design Tokens

The design system should centralize visual decisions.

Examples include:

- Colors
- Typography
- Border radius
- Shadows
- Spacing
- Animations
- Z-index
- Breakpoints

Business pages must not define their own design tokens.

---

# Accessibility

Every shared component should support:

- Keyboard navigation
- Focus management
- Screen readers
- Proper semantic HTML

Accessibility is implemented once inside the shared component.

---

# Benefits

The selected approach provides:

- Consistent UI
- Faster development
- Easier maintenance
- Lower technical debt
- Better onboarding
- Easier redesigns

---

# Future Compatibility

The design system supports future expansion for:

- Dark Mode
- White-label themes
- Customer branding
- Mobile design parity
- Shared component documentation

---

# Decision

Tailwind CSS is the styling engine.

The YachtOS Design System is the development standard.

Developers build applications using components, not raw utility classes.