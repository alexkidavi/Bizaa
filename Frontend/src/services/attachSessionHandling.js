import router from "@/router/router.js";
import { useAuthStore } from "@/stores/auth";

/**
 * Every axios instance in this app needs the same 401 (session expired)
 * and 402 (subscription required) handling that main.js registers on the
 * default axios singleton — but axios.create() instances don't inherit
 * interceptors from the default instance or from each other. Several
 * service files build their own instance (services/api.js,
 * SuperAdminService.js) and silently missed this, so a 401 on those
 * calls just rejected the promise instead of logging the user out.
 */
export function attachSessionHandling(instance) {
  instance.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 401) {
        const auth = useAuthStore();
        auth.clearToken();
        router.push("/login");
      }

      if (error.response?.status === 402 && error.response?.data?.subscription_required) {
        if (router.currentRoute.value?.path !== "/subscription") {
          router.push("/subscription");
        }
      }

      return Promise.reject(error);
    }
  );

  return instance;
}
