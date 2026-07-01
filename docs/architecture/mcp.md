# MCP Architecture

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines how YachtOS may use Model Context Protocol (MCP) to connect AI assistants with project tools and data sources.

MCP is not part of the YachtOS MVP product features.

MCP is used to improve development workflow, repository awareness and technical review quality.

---

## Goals

MCP should help the AI assistant access project context more accurately.

The primary goals are:

- Repository review
- Documentation review
- Commit history analysis
- Pull request review
- Issue analysis
- Architecture consistency checks
- Coding standard validation

---

## Initial MCP Targets

The initial MCP targets may include:

- GitHub repository
- Local filesystem
- PostgreSQL database
- Project documentation

---

## Usage Scope

MCP may be used for:

- Reading repository files
- Reviewing documentation
- Analyzing changes
- Checking naming consistency
- Reviewing architecture decisions
- Supporting CTO review workflows

MCP must not be used to make production changes without explicit approval.

---

## Security Rules

MCP access must follow least privilege.

The assistant should receive read-only access by default.

Write access must be explicitly approved and limited to safe development workflows.

Secrets, tokens, credentials and production data must never be exposed unnecessarily.

---

## Repository Access

GitHub MCP access should initially be read-only.

Allowed operations:

- Read files
- Read commits
- Read issues
- Read pull requests

Write operations such as creating branches, commits or pull requests require explicit user approval.

---

## Local Filesystem Access

Filesystem MCP access should be limited to the YachtOS project directory.

The assistant must not access unrelated folders.

---

## Database Access

Database MCP access should initially be disabled.

When enabled, it should use development or staging databases only.

Production database access is prohibited unless a formal security process exists.

---

## Future Possibilities

Future MCP workflows may include:

- Weekly CTO review
- Automated documentation audit
- Pull request quality review
- Migration review
- API consistency review
- Test coverage review
- Security checklist review

---

## MCP Principle

MCP is a productivity layer, not a shortcut around engineering discipline.

The assistant may inspect and recommend, but important changes still require human approval.