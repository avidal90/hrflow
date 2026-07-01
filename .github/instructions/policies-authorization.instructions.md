---
name: Hrflow Policy Authorization Rules
description: Use when creating or modifying Laravel policy classes in app/Policies. Covers tenant isolation, role checks, method signatures, and fixed authorization conventions for HRFlow.
applyTo:
  - app/Policies/*.php
  - app/Policies/**/*.php
---
# Policy Authorization Conventions for HRFlow

Apply these rules whenever you edit or create policies under app/Policies.

## Non-Negotiable Rules
- Keep tenant isolation explicit in every model-bound action (view, update, delete, restore, forceDelete).
- Preserve the super-admin short-circuit through before(User $user, string $ability): ?bool returning true for super-admin and null otherwise.
- Do not use inline magic role strings repeatedly if a helper method already exists on User.
- Keep authorization logic boolean and side-effect free. Policies must not perform writes.
- Use strict type hints and explicit bool / ?bool return types.

## Required Method Shape
- Include standard methods when relevant: viewAny, view, create, update, delete, deleteAny, restore, restoreAny, forceDelete, forceDeleteAny.
- For model-bound methods, always type-hint the model argument.
- For list and bulk actions, check tenant presence first (for example user tenant is not null).

## Tenant and Role Pattern
- First guard tenant boundary (shared tenant or same tenant).
- Then evaluate role or responsibility checks.
- Prefer intent-revealing private helpers such as belongsToUsersTenant and canViewX.

## Readability Rules
- Prefer small private helper methods over deeply nested boolean expressions.
- Keep each policy method focused on one decision.
- Reuse existing role helper methods on User when available.

## Security Checklist for Policy Changes
- Verify no cross-tenant access path is introduced.
- Verify elevated actions (delete, forceDelete, restore) stay restricted to admin-level roles.
- Verify no action unintentionally grants permission to unauthenticated or tenant-less users.
