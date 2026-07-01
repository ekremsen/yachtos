# ADR-0004: Frontend Technology Stack

**Status:** Accepted

**Date:** 01 July 2026

**Decision Makers:**
- Product Team
- Engineering Team

---

# Context

YachtOS requires a modern frontend architecture that is maintainable, scalable and suitable for long-term commercial development.

The frontend should provide an excellent developer experience while remaining flexible enough to support future expansion.

Potential options included:

- React + Vite
- Next.js
- Material UI
- Tailwind CSS
- Bootstrap
- Ant Design
- Chakra UI
- shadcn/ui

---

# Decision

YachtOS will use the following frontend technology stack.

| Area | Technology |
|-------|------------|
| Framework | Next.js |
| Language | TypeScript |
| Styling | Tailwind CSS |
| UI Components | shadcn/ui |
| Icons | Lucide React |

---

# Rationale

## Next.js

Next.js provides:

- Excellent React ecosystem support
- App Router
- Server Components
- Future SSR capabilities
- Excellent performance
- Long-term community support

Although YachtOS is primarily an internal SaaS application, the flexibility offered by Next.js outweighs its additional complexity.

---

## TypeScript

TypeScript improves:

- Code quality
- Refactoring safety
- Team scalability
- IDE support
- Long-term maintainability

TypeScript is mandatory for all frontend code.

---

## Tailwind CSS

Tailwind CSS was selected because it provides:

- Utility-first styling
- Excellent performance
- Highly customizable design system
- Minimal CSS maintenance
- Strong community adoption

The project will not rely on pre-built visual themes.

---

## shadcn/ui

shadcn/ui was selected because it provides:

- Accessible components
- Full ownership of component code
- Excellent Tailwind integration
- High customizability
- Modern SaaS design patterns

Unlike traditional component libraries, components become part of the project and may be customized without vendor lock-in.

---

## Lucide React

Lucide React will be used as the standard icon library because it provides:

- Consistent icon style
- Tree shaking support
- Excellent TypeScript integration
- Active maintenance

---

# Alternatives Considered

## React + Vite

Advantages:

- Lightweight
- Fast development

Rejected because:

- Next.js provides greater long-term flexibility with minimal additional complexity.

---

## Material UI

Advantages:

- Large component library
- Mature ecosystem

Rejected because:

- Material Design imposes a strong visual identity.
- Advanced commercial components require paid licensing.
- Limited flexibility for building a distinctive YachtOS design language.

---

## Bootstrap

Rejected because:

- Outdated component philosophy
- Difficult to build a premium SaaS interface
- Heavy reliance on predefined styles

---

## Ant Design

Rejected because:

- Strong design language
- Less suitable for highly customized interfaces

---

# Consequences

Positive:

- Modern frontend architecture
- Full control over UI components
- Consistent design system
- Excellent scalability
- Strong developer experience

Negative:

- Slightly higher initial learning curve
- More responsibility for maintaining custom components

---

# Future Considerations

The selected stack supports future expansion for:

- Progressive Web App (PWA)
- Mobile applications
- White-label deployments
- Theme support
- Localization
- Component library extraction

---

# Decision

This decision is accepted and will be used throughout YachtOS unless superseded by a future ADR.