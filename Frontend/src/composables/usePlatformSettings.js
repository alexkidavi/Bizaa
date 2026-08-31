import { ref } from "vue";
import PlatformSettingsService from "@/services/PlatformSettingsService";

// Module-level singleton — Navbar, Footer, and Privacy can all use this
// composable on the same page load without each firing its own request;
// the fetch only actually runs once per page load.
const settings = ref(null);
const loaded = ref(false);
let inFlight = null;

const BASE_URL = import.meta.env.VITE_APP_URL;

const FALLBACK = {
  company_name: "Bizaa",
  tagline: "Stock. Sale. Grow.",
  logo: null,
  support_email: "brimsoftsolutions@gmail.com",
  support_phone: "+254705030613",
  whatsapp_number: "254705030613",
  address: null,
  facebook_url: null,
  twitter_url: null,
  linkedin_url: null,
  instagram_url: null,
};

export function usePlatformSettings() {
  const fetchSettings = () => {
    if (loaded.value) return Promise.resolve(settings.value);
    if (inFlight) return inFlight;

    inFlight = PlatformSettingsService.get()
      .then((res) => {
        settings.value = res?.data?.data ?? FALLBACK;
        loaded.value = true;
        return settings.value;
      })
      .catch(() => {
        // A visitor on the landing page should never see a broken footer
        // just because this one request failed — fall back to the same
        // real values that used to be hardcoded here.
        settings.value = FALLBACK;
        loaded.value = true;
        return settings.value;
      })
      .finally(() => {
        inFlight = null;
      });

    return inFlight;
  };

  const logoUrl = () => {
    const path = settings.value?.logo;
    if (!path) return "/bizaa-logo.png"; // existing static asset, unchanged default
    return /^(https?:\/\/|data:)/i.test(path) ? path : `${BASE_URL}/storage/${path}`;
  };

  const waLink = (text = "") => {
    const n = settings.value?.whatsapp_number || FALLBACK.whatsapp_number;
    const base = `https://wa.me/${n.replace(/\D/g, "")}`;
    return text ? `${base}?text=${encodeURIComponent(text)}` : base;
  };

  return { settings, fetchSettings, logoUrl, waLink };
}
