# AGENTS.md — Song Village Theme Repository

## Repository Scope

This repository root IS:

wp-content/themes/song-village/

This is a Git-managed WordPress child theme.

Production pulls directly from this repository.

Only theme-level code should exist here.

---

## Parent Theme

- Parent: Avada
- This is a child theme.
- Do not modify parent theme files.
- Use overrides and hooks.

---

## Allowed Changes

- Theme PHP
- Theme CSS
- Theme JS
- Template overrides
- WooCommerce template overrides
- Plugin template overrides (via copying into theme)

---

## Template Override Rules

When copying templates:

- Preserve directory structure (example: woocommerce/...)
- Add header comment:

  Source: plugin/path/to/template.php  
  Copied: YYYY-MM-DD  
  Reason: Short explanation  

Never edit plugin files directly.

---

## Code Discipline

- Keep changes small and focused.
- Avoid unnecessary dependencies.
- No build systems unless explicitly requested.
- Do not create files unrelated to theme behavior.

---

## If a Request Requires External Changes

Do not modify:

- Plugins
- Core
- Database
- Server config

Propose a child-theme-based solution instead.