import axios from "axios";

const API_URL = `${import.meta.env.VITE_APP_URL}/api/platform-settings`;

// Public, unauthenticated — used by the landing page / Privacy page, so
// this deliberately does NOT go through the app's authenticated axios
// instance (no token needed, and none should be sent to a public page).
export default {
  get() {
    return axios.get(API_URL);
  },
};
