# Users Module

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

The Users module manages all platform users within YachtOS.

Every person who can access YachtOS is represented as a user.

The module is responsible for user information, user lifecycle and profile management.

Authentication and authorization are not responsibilities of this module.

---

## Business Goal

Provide a centralized and consistent user management system that supports every business module across the platform.

---

## Responsibilities

The Users module is responsible for:

- User profiles
- Personal information
- Contact information
- User status
- User preferences
- User lifecycle

The Users module is **not** responsible for:

- Authentication
- Authorization
- Yacht assignments
- Role permissions

These responsibilities belong to other modules.

---

## Initial Scope

The MVP should allow users to:

- Create a user
- Update user information
- Archive a user
- View user details
- List users

---

## Out of Scope

The MVP will not include:

- Social login
- Multi-factor authentication
- API tokens
- Single Sign-On (SSO)
- External identity providers

These capabilities may be introduced in future versions.

---

## Success Criteria

Every platform user can be managed independently of yacht assignments.

A user's operational responsibilities are defined by other modules, not by the Users module.