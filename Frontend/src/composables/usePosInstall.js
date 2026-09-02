/**
 * PWA install affordance for the POS.
 *
 * Chrome/Edge (desktop + Android) fire `beforeinstallprompt` when the app is
 * installable; we stash that event so a button can trigger the native dialog.
 * The listener is attached at MODULE LOAD (this file is in the eager bundle
 * via POS.vue → PosOfflineBar), because the event can fire before any
 * component mounts — wiring it lazily in the composable missed it.
 *
 * iOS Safari never fires the event (Share → Add to Home Screen only). Other
 * cases where the event just never arrives (already dismissed, engagement
 * heuristic not met, Firefox) fall back to a "use the browser menu" hint so
 * there's always a path.
 */

import { ref, computed } from 'vue'

const deferredPrompt = ref(null)
const installed = ref(
  window.matchMedia?.('(display-mode: standalone)').matches ||
    window.navigator.standalone === true,
)

if (typeof window !== 'undefined') {
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault()
    deferredPrompt.value = e
  })
  window.addEventListener('appinstalled', () => {
    installed.value = true
    deferredPrompt.value = null
  })
  window
    .matchMedia?.('(display-mode: standalone)')
    .addEventListener?.('change', (e) => {
      installed.value = e.matches
    })
}

const ua = navigator.userAgent
const isIos = /iphone|ipad|ipod/i.test(ua) || (/Macintosh/.test(ua) && 'ontouchend' in document)
const isFirefox = /firefox/i.test(ua)
const isChromium = /chrome|edg\//i.test(ua) && !isFirefox

export function usePosInstall() {
  // Native prompt is available right now.
  const canInstall = computed(() => !installed.value && !!deferredPrompt.value)

  // No native prompt, but there's still a manual route worth showing.
  const iosHint = computed(() => !installed.value && isIos)
  const menuHint = computed(
    () => !installed.value && !deferredPrompt.value && !isIos && isChromium,
  )

  async function promptInstall() {
    const e = deferredPrompt.value
    if (!e) return false
    e.prompt()
    const { outcome } = await e.userChoice
    deferredPrompt.value = null
    return outcome === 'accepted'
  }

  return { canInstall, iosHint, menuHint, installed, promptInstall }
}
