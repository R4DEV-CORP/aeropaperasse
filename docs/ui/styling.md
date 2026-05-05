# Styling — Tailwind v4

All styling lives in `resources/css/app.css` and per-component utility classes. No external CSS framework is used (Flux removed).

## Stack

- Tailwind CSS v4 (CSS-first config via `@theme`)
- Inter font (loaded as `--font-sans`, served from Bunny Fonts)
- **Single light theme** — dark mode is NOT supported in this project

## `resources/css/app.css` structure

```css
@import 'tailwindcss';

@theme {
    --font-sans: Inter, sans-serif;

    /* Accent — primary action color (dark navy, per design system) */
    --color-accent: var(--color-slate-900);
    --color-accent-content: var(--color-slate-800);
    --color-accent-foreground: var(--color-white);

    /* Brand — small decorative blue (logo, brand pill, badges) */
    --color-brand: var(--color-blue-500);
    --color-brand-content: var(--color-blue-600);

    /* Borders — default subtle border used in inputs, cards, dividers */
    --color-border: var(--color-slate-200);

    /* Foreground (text hierarchy) */
    --color-foreground: var(--color-slate-900);
    --color-foreground-muted: var(--color-slate-600);
    --color-foreground-subtle: var(--color-slate-500);

    /* Status — request workflow states (badges + bullets) */
    --color-status-draft: var(--color-slate-500);
    --color-status-pending: var(--color-amber-500);
    --color-status-approved: var(--color-emerald-500);
    --color-status-rejected: var(--color-red-500);
    --color-status-in-progress: var(--color-violet-500);
    --color-status-ready: var(--color-blue-500);
}

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';
```

## Design tokens

Define semantic tokens in `@theme`. Components reference tokens, not raw palettes, when the meaning is semantic (action, status, brand). Tailwind v4 auto-generates utilities from each token (e.g. `--color-accent` produces `bg-accent`, `text-accent`, `border-accent`, `ring-accent`, etc.).

| Token | Use | Default value |
|---|---|---|
| `--color-accent` | primary actions (button bg, focus rings, primary CTAs) | `slate-900` |
| `--color-accent-content` | hover state of primary actions | `slate-800` |
| `--color-accent-foreground` | text on accent background | `white` |
| `--color-brand` | small decorative branding (logo bg, brand pill) — **not** for actions | `blue-500` |
| `--color-brand-content` | hover/strong variant of brand | `blue-600` |
| `--color-border` | default subtle border (inputs, cards, dividers) | `slate-200` |
| `--color-foreground` | primary text (body, headings, input values) | `slate-900` |
| `--color-foreground-muted` | secondary text (labels, intros, descriptions) | `slate-600` |
| `--color-foreground-subtle` | tertiary text (hints, captions, copyright) | `slate-500` |
| `--color-status-draft` | draft/neutral state | `slate-500` |
| `--color-status-pending` | awaiting validation | `amber-500` |
| `--color-status-approved` | approved/success | `emerald-500` |
| `--color-status-rejected` | rejected/error | `red-500` |
| `--color-status-in-progress` | in fabrication / processing | `violet-500` |
| `--color-status-ready` | ready / available | `blue-500` |

**Rule:** when a new color is needed in 3+ places, **add a token** rather than repeating a raw palette value.

**Important:** the primary action color is **navy (slate-900)**, not blue. Blue is reserved for the brand mark and the "ready" status. Don't use `bg-blue-*` for actions.

## Theme

This project uses a **single light theme**. The `dark:*` variant prefix MUST NOT be used anywhere — primitives, components, pages, layouts.

Use the **slate** palette, not zinc — slate has the cool blue tint that matches the design system. **Prefer the semantic token utilities over raw slate** when one exists.

| Convention | Class to use | Notes |
|---|---|---|
| Surface (cards, modals) | `bg-white` | |
| Page background / muted bg | `bg-slate-50` | not tokenized — sub-uses are too distinct |
| Subtle border | `border-border` | from `--color-border` |
| Body / heading / input value | `text-foreground` | from `--color-foreground` |
| Label / intro / description | `text-foreground-muted` | from `--color-foreground-muted` |
| Hint / caption / copyright | `text-foreground-subtle` | from `--color-foreground-subtle` |
| Placeholder | `text-slate-400` | not tokenized — single use |
| Stronger border (rare) | `border-slate-300` | use sparingly |

If a future need for dark mode emerges, it will be a deliberate, scoped initiative — not something to retrofit organically class-by-class.

## Spacing

Use **gap utilities** for spacing between siblings, not margins.

✅ `<div class="flex gap-4">…</div>`
❌ `<div class="flex"><span class="mr-4">…</span></div>`

## Border radius

The design system uses **subtle, square-leaning** corners. Use `rounded` (4px) as the default — `rounded-md` and above are too soft for this UI.

| Use | Class | Rationale |
|---|---|---|
| Buttons, inputs, alerts, cards, badges | `rounded` (4px) | Default — matches the design system |
| Avatars, circular indicators | `rounded-full` | |
| Specific large surfaces | `rounded-lg` (8px) | Use sparingly, only when explicitly needed |

Inputs use `bg-slate-50` (subtle blue tint) by default, and switch to `bg-white` on focus.

## Class organization

Order utilities consistently within an element. Suggested order:

1. Layout (`flex`, `grid`, `block`)
2. Box model (`w-`, `h-`, `p-`, `m-`)
3. Typography (`text-`, `font-`, `leading-`)
4. Color (`bg-`, `text-`, `border-`)
5. Effects (`shadow-`, `rounded-`)
6. State variants (`hover:`, `focus:`, `disabled:`)

## Forbidden

- Inline `<style>` blocks
- Adding utilities Tailwind already provides via custom CSS
- Using `@apply` in primitives (defeats per-component utility composition)
- Raw color values in components — go through theme tokens for semantic colors
- The deprecated v3 utilities (`bg-opacity-*`, `flex-shrink-*`, etc. — see Tailwind v4 rules in root `CLAUDE.md`)
- `dark:*` variant classes — single-theme project
- The **zinc** palette — this project uses **slate** for grayscale (cool blue tint matches the design system)
- `bg-blue-*` for actions — primary action is navy (`bg-accent`); blue is reserved for brand and "ready" status

## Rebuilding

```bash
npm run build      # production
npm run dev        # watch mode
composer run dev   # full stack (queue + vite + horizon if configured)
```

If a class change isn't reflected in the UI, the dev server is likely not running. Ask the user before assuming.
