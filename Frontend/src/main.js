import { createApp } from "vue";
import App from "./App.vue";
import "./assets/main.css";
import router from "./router/router.js";
import { createPinia } from "pinia";
import VueToast from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css"; // make sure this is imported#
import 'vue-multiselect/dist/vue-multiselect.css'
import axios from "axios";
import { useAuthStore } from "./stores/auth";
import { initPwa } from "./pwa.js";
import { APP_ORIGIN } from "./config.js";

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// ✅ Use top-right toasts globally
app.use(VueToast, {
  position: "top",
  duration: 3000,
  dismissible: true,
});

const auth = useAuthStore(pinia);

// ✅ Set axios base URL — APP_ORIGIN is '' for same-origin (relative), see config.js
axios.defaults.baseURL = APP_ORIGIN ? `${APP_ORIGIN}/` : "/";

// ✅ Attach token automatically if available
axios.interceptors.request.use((config) => {
  if (auth.token) {
    config.headers.Authorization = `Bearer ${auth.token}`;
  }
  return config;
});

// ✅ Handle 401 (session expired) and 402 (subscription required) globally
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      auth.clearToken();
      router.push("/login");
    }

    if (error.response?.status === 402 && error.response.data?.subscription_required) {
      // Avoid redirect loop if already on subscription page
      if (router.currentRoute.value?.path !== "/subscription") {
        router.push("/subscription");
      }
    }

    return Promise.reject(error);
  }
);

// ✅ Make axios globally available
app.config.globalProperties.$axios = axios;

app.mount("#app");

// Register the POS service worker (production builds only).
initPwa();
