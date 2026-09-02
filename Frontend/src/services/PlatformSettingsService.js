import axios from "axios";
import { API_BASE_URL } from "@/config";

const API_URL = `${API_BASE_URL}/platform-settings`;

// Public, unauthenticated — used by the landing page / Privacy page, so
// this deliberately does NOT go through the app's authenticated axios
// instance (no token needed, and none should be sent to a public page).
export default {
  get() {
    return axios.get(API_URL);
  },
};
