/**
 * PWA install prompt for the POS.
 *
 * Chrome/Edge (desktop + Android) fire `beforeinstallprompt`, which we stash
 * so a real button can trigger the native install dialog. iOS Safari never
 * fires it — there we just detect iOS and show a "use Share → Add to Home
 * Screen" hint instead.
 *
 * Module-level state so the captured event survives component remounts.
 */

import { ref, computed } from 'vue'

const deferredPrompt = ref(null)
const installed = ref(
  window.matchMedia?.('(display-mode: standalone)').matches ||
    window.navigator.standalone === true,
)

let wired = false

function wire() {
  if (wired) return
  wired = true

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault()
    deferredPrompt.value = e
  })

  window.addEventListener('appinstalled', () => {
    installed.value = true
    deferredPrompt.value = null
  })
}

const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent)

export function usePosInstall() {
  wire()

  const canInstall = computed(() => !installed.value && !!deferredPrompt.value)
  const showIosHint = computed(() => !installed.value && isIos && !deferredPrompt.value)

  async function promptInstall() {
    const e = deferredPrompt.value
    if (!e) return false
    e.prompt()
    const { outcome } = await e.userChoice
    deferredPrompt.value = null
    return outcome === 'accepted'
  }

  return { canInstall, showIosHint, installed, promptInstall }
}
