# Mkulima design system

One set of tokens feeds the public website and the Flutter app. Filament keeps its own theme and only borrows the brand green. The React admin is unchanged.

## Principles

- Swahili first. Labels are short. Swahili is Latin script, so the UI uses the device font (Roboto on Android) and does not download a webfont.
- Phones first, from 360px. Tap targets are at least 44px. Primary buttons are 48px. Body text is 16px. Nothing the farmer must read is below 13px.
- White page, agricultural green for the action, earth and amber only for warnings. Text colours in `design/tokens.json` clear 7:1 on white so they stay readable in sun.
- A feature that is not live wears the badge **Inakuja hivi karibuni**. Weather, recorded prices, and reading the forum are the live path.
- Motion is short (120–180ms). Pages do not wait on a font or an icon font.

## Tokens

`design/tokens.json` is the source. Regenerate the consumers after a change:

```bash
php design/build.php
```

That writes:

| File | Used by |
|---|---|
| `public/css/mk-tokens.css` | Blade layouts (`--mk-*` and the older names the pages already use) |
| `mkulima_app/lib/core/mk_tokens.dart` | `MkColors` and `MkRadii` in `mkulima_app/lib/core/theme.dart` |

`public/css/mk-components.css` is the web control CSS (buttons, fields, cards, rows, badges, alerts, empty states, the mobile tab bar). It is written by hand and only references the variables.

## Components

Web, under `resources/views/components/mk/`:

| Component | Use |
|---|---|
| `<x-mk.badge>` | Status. `live` marks something that works today. The default copy for unbuilt work is `Inakuja hivi karibuni`. |
| `<x-mk.alert tone="ok\|warn\|danger">` | A message after an action. |
| `<x-mk.empty title="...">` | No rows yet, or data we refuse to invent. |
| `<x-mk.note>` | The honesty line on company pages: what works today, and what does not. |
| Classes `.mk-btn`, `.mk-field`, `.mk-card`, `.mk-row` | Buttons, inputs, cards, list rows. |

Flutter already has `MkButton`, `MkCard`, `MkEmptyState`, and `MkErrorState` in `mkulima_app/lib/widgets/`. New screens should take colours from `MkColors`, which reads `MkTokens`. The home tab uses the same “Inakuja hivi karibuni” wording as the web badge.

The mobile web bar is Nyumbani, Bei, Hewa, Jukwaa, Ingia. The app bar is Nyumbani, Bei, Hewa, Jukwaa, Wasifu. Bei and Hewa open those screens. The scanner and the shop are not in that bar.

## Add a component on both platforms

1. Add the value to `design/tokens.json` if it is a colour, size, radius, or space. Run `php design/build.php`.
2. On the web, add a class in `public/css/mk-components.css` that uses `var(--mk-…)`, and a Blade file in `resources/views/components/mk/` if the markup should be shared.
3. On Flutter, add a widget in `mkulima_app/lib/widgets/` that uses `MkColors` / `MkTokens`. Do not paste a new hex value into a screen.
4. Use the same Swahili label on both sides.
