# Atrio — Multipurpose Business Template

Custom-designed HTML template built on the Bootstrap 5 grid with jQuery interactions.
Design system: Pine green (#0F3D33) + Saffron (#F2B441), Sora / Inter / IBM Plex Mono fonts,
signature notched-corner card style. No default Bootstrap look.

## Pages
- index.html      — Home (hero, stats counters, marquee, services, portfolio, testimonial slider, CTA)
- about.html      — Story, values, team, counters
- services.html   — Services grid, process, pricing, FAQ accordion
- portfolio.html  — Filterable work grid (jQuery)
- blog.html       — Post grid + pagination
- contact.html    — Info cards, validated contact form (jQuery), map placeholder

## Structure
assets/css/style.css   — all custom styles (CSS variables at the top for easy rebranding)
assets/js/main.js      — jQuery: sticky header, mobile nav, counters, slider, filter, form validation, back-to-top, scroll reveal
assets/img/            — put your images here

## Customizing
1. Colors: edit the :root variables at the top of style.css.
2. Fonts: swap the Google Fonts link in each page's <head> and the --font-* variables.
3. Images: replace every `.img-ph` placeholder div with an <img> tag (keep the `notched` class for the corner cut).
4. Contact form: in main.js, replace the success block inside #contactForm submit with your AJAX/backend call.

## Dependencies (CDN)
- Bootstrap 5.3.3 (grid + accordion only)
- jQuery 3.7.1
- Google Fonts: Sora, Inter, IBM Plex Mono

Open index.html directly in a browser — no build step needed.
