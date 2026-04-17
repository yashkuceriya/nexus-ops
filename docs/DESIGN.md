# NexusOps Design System

Stitch-style, Linear-adjacent, B2B data-dense.

## Palette
- **Canvas background**: `#F5F4F9` (soft lilac). Applied on `<main>` container; cards ride on top of it.
- **Card surface**: pure white `#FFF`, 1px `#E5E7EB` border (hairline), 12px radius.
- **Accent (primary)**: indigo `#4F46E5` (`accent-600`). Used for primary buttons, active nav, progress fills, links.
  - Tailwind aliases: `accent-50..900` and `brand-50..900` (mirror). Prefer `accent-*`.
- **Ink (text)**: `#0F172A` primary, `#475569` muted, `#94A3B8` soft.
- **Status**: emerald (`#10B981` pass) · red (`#EF4444` fail) · blue (`#3B82F6` in-progress/run) · amber (`#F59E0B` warn) · slate-400 (pending/none).

## Typography
- **UI**: Inter, sans-serif.
- **Mono / IDs / timestamps**: JetBrains Mono.
- **Scale**: page title `text-2xl font-bold tracking-tight`; section heading `text-[15px] font-semibold`; body `text-[13px]`; label `text-[11px]`; kicker `10px uppercase tracking-wider font-semibold text-ink-soft`.

## Utility classes (defined in `resources/views/layouts/app.blade.php`)
| Class | Use |
|---|---|
| `.card` | white card + hairline + 12px radius |
| `.kpi` / `.kpi-value` | KPI card padding + 30px bold tabular number |
| `.label-kicker` | uppercase tracked 10px gray label |
| `.chip` + `.chip-pass` / `-fail` / `-run` / `-pending` / `-warn` / `-accent` | status pills |
| `.dot` + `.dot-pass` / `-fail` / `-run` / `-pending` / `-warn` | status dots |
| `.cell` + `.cell-pass` / `-fail` / `-run` / `-none` | matrix grid cells (40x32) |
| `.btn-primary` | indigo primary button |
| `.btn-ghost` | white bordered secondary button |
| `.mono` | JetBrains Mono 11px |
| `.hairline` / `.hairline-b` / `.hairline-t` / `.hairline-r` | 1px `#E5E7EB` borders |
| `.fade-in` | 250ms slide-up entrance |
| `.tabular-nums` | `font-variant-numeric: tabular-nums` for KPI numbers |

## Layout rules
- Shell: left sidebar `w-60 bg-white hairline-r`, top bar `h-14 bg-white hairline-b`, content area background `#F5F4F9`.
- Page header: kicker + title + optional subtitle on left; actions (buttons) on right.
- Spacing: `space-y-6` between major page sections; `gap-4` for KPI grids; `gap-5` for card columns.
- Cards: padding `p-5` default; KPI cards `p-[18px 20px]` via `.kpi`.

## Do / Don't
- **Do** use `.card` over ad-hoc `bg-white shadow rounded-lg`. Drop shadows everywhere — use hairline borders.
- **Do** put IDs, timestamps, measurement values in `.mono`.
- **Do** use chips for statuses; never output raw enum values like "in_progress" — replace `_` with space, uppercase.
- **Don't** use emerald as a primary/accent color (it's a status color only now).
- **Don't** use dark backgrounds for content cards. Dark mode targets `html.dark` — keep card surface contrast similar there.
- **Don't** use drop shadows for emphasis — prefer 1px `#E5E7EB` border and a slightly raised card (`.card`).
- **Don't** mix typographic scales ad-hoc — use `text-[11/12/13/15px]` ladder and the kicker class.

## Component recipes

### KPI card
```blade
<div class="card kpi">
    <p class="label-kicker">Label</p>
    <div class="kpi-value text-ink mt-2">88.0<span class="text-lg text-ink-soft">%</span></div>
    <p class="text-[11px] text-ink-soft mt-1 mono">optional subline</p>
</div>
```

### Status chip
```blade
<span class="chip chip-pass">Pass</span>
<span class="chip chip-fail">Fail</span>
```

### Matrix cell
```blade
<span class="cell cell-pass"><svg ...></svg></span>
```

### Primary button / ghost button
```blade
<button class="btn-primary">Save</button>
<button class="btn-ghost">Cancel</button>
```

### Section header
```blade
<div class="flex items-center justify-between mb-4">
    <div>
        <h2 class="text-[15px] font-semibold text-ink">Title</h2>
        <p class="text-[12px] text-ink-muted">Subtitle</p>
    </div>
    <a class="text-[12px] font-semibold text-accent-700 hover:text-accent-800">Action →</a>
</div>
```
