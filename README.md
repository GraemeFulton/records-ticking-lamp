# Trionda Lamp — Live Shade Preview

A standalone, zero-dependency web viewer for the World Cup Trionda table lamp.
One HTML file, CSS-3D rendered: four rotatable shade panels (no top/bottom),
the Trionda ball base, and a plinth. Drag to spin, auto-rotates when idle.

## Files

- `index.html` — the whole app. No build step, no libraries. Host it anywhere
  static (Vercel, Netlify, even the WP uploads folder).
- `wordpress-embed.html` — snippet to paste into a Custom HTML block on the
  WooCommerce product page. Mirrors the four "Shade Panel" dropdowns into the
  iframe live.

## URL parameters

| Param | Meaning | Example |
|-------|---------|---------|
| `p1`–`p4` | Panel player (roster key or free text) | `?p1=kane&p2=bellingham` |
| `n1`–`n4` | Number, for free-text names | `?p1=SMITH&n1=8` |
| `k1`–`k4` | Colourway for free-text names | `&k1=scotland` |
| `embed=1` | Hide header/controls — iframe mode | |
| `autorotate=0` | Disable idle auto-spin | |

Kits: `england, keeper, keeperdark, germany, france, spain, portugal, brazil,
norway, argentina, scotland`.

Roster names, default shirt numbers, and colourways live in one `ROSTER` /
`KITS` block near the top of the `<script>` in `index.html` — edit there.

## Live updates from a form (postMessage)

```js
iframe.contentWindow.postMessage({
  type: "lamp:set",
  panels: [{ name: "kane" }, { name: "SMITH", number: 8, kit: "england" }]
}, "*");
```

## Why standalone-first (vs WordPress plugin)

The viewer is a static page driven entirely by query params / postMessage, so:

1. Develop and deploy it independently of the WP install (no PHP, no plugin
   review, no theme conflicts, cache-safe).
2. The WP side is just an iframe + 20 lines of JS reading the WooCommerce
   variation selects (`wordpress-embed.html`).
3. If a plugin is ever wanted, it's a thin shell: a `[lamp_preview]` shortcode
   that prints the same iframe + script. Nothing to rewrite.

## Later ideas

- Real photo textures per panel (swap the CSS kit backgrounds for images).
- `<model-viewer>` / Three.js version with a proper GLB if exactness matters.
- Screenshot-to-cart: render the chosen config to a PNG for order confirmation.
