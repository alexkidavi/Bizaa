import axios from "axios";
import { attachSessionHandling } from "./attachSessionHandling.js";

const api = axios.create({ baseURL: `${import.meta.env.VITE_APP_URL}/api/superadmin` });

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

attachSessionHandling(api);

export default {
  // Dashboard
  getDashboard: ()                  => api.get("/dashboard").then(r => r.data),

  // Businesses
  getBusinesses: (params = {})      => api.get("/businesses", { params }).then(r => r.data),
  getBusiness:   (id)               => api.get(`/businesses/${id}`).then(r => r.data),
  suspendBusiness:  (id)            => api.post(`/businesses/${id}/suspend`).then(r => r.data),
  reinstateBusiness:(id)            => api.post(`/businesses/${id}/reinstate`).then(r => r.data),

  // Subscriptions
  getSubscriptions: (params = {})   => api.get("/subscriptions", { params }).then(r => r.data),
  getPlans:         ()              => api.get("/subscription-plans").then(r => r.data),
  grantSubscription:(data)          => api.post("/subscriptions/grant", data).then(r => r.data),
  revokeSubscription:(id)           => api.post(`/subscriptions/${id}/revoke`).then(r => r.data),
  extendSubscription:(id, days)     => api.post(`/subscriptions/${id}/extend`, { days }).then(r => r.data),

  // Stores
  getStores:        (params = {})   => api.get("/stores", { params }).then(r => r.data),
  toggleFeatured:   (id)            => api.post(`/stores/${id}/toggle-featured`).then(r => r.data),
  toggleActive:     (id)            => api.post(`/stores/${id}/toggle-active`).then(r => r.data),

  // Reports
  getReports:       ()              => api.get("/reports/overview").then(r => r.data),
};
