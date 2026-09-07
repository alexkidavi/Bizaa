// Scroll-triggered entrance animation, in the spirit of the WOW.js +
// waypoints.js pattern used across most animated marketing sites (fade/zoom/
// slide in as each section scrolls into view, staggered per item) — built
// natively on IntersectionObserver instead of pulling in jQuery + WOW.js,
// since this is already a Vue 3 SPA.
//
// Usage:
//   <div v-reveal>                                   fade-up, no delay
//   <div v-reveal="{ type: 'zoom', delay: i * 0.1 }"  staggered zoom-in
//   <div v-reveal="{ type: 'left' }">                 slide in from the left
//   <div v-reveal="{ once: true }">                   plays once, then stays
//
// Replays every time (by default): the element re-animates on every pass —
// scroll down past it, back up over it again, it plays again — rather than
// a one-shot "only the first time" reveal. Pass `once: true` for spots where
// a single, permanent reveal reads better (e.g. something that should not
// flicker while a user lingers right at its edge).
//
// One shared observer for every revealed element on the page (not one per
// element) — cheap regardless of how many sections use it.

let observer;

function getObserver() {
  if (!observer) {
    observer = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          const revealed = entry.target.classList.toggle("is-revealed", entry.isIntersecting);
          if (revealed && entry.target.dataset.revealOnce === "true") {
            observer.unobserve(entry.target);
          }
        }
      },
      { threshold: 0.15, rootMargin: "-40px 0px -40px 0px" }
    );
  }
  return observer;
}

export const vReveal = {
  mounted(el, binding) {
    const { type = "up", delay = 0, once = false } = binding.value || {};
    el.classList.add("reveal", `reveal--${type}`);
    if (delay) el.style.setProperty("--reveal-delay", `${delay}s`);
    if (once) el.dataset.revealOnce = "true";
    getObserver().observe(el);
  },
  unmounted(el) {
    observer?.unobserve(el);
  },
};
