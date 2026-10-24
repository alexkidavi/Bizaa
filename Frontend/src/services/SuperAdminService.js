import axios from "axios";
import { attachSessionHandling } from "./attachSessionHandling.js";
import { API_BASE_URL } from "@/config";

const api = axios.create({ baseURL: `${API_BASE_URL}/superadmin` });

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
  getBusinessStats: ()              => api.get("/businesses/stats").then(r => r.data),
  getBusiness:   (id)               => api.get(`/businesses/${id}`).then(r => r.data),
  suspendBusiness:  (id)            => api.post(`/businesses/${id}/suspend`).then(r => r.data),
  reinstateBusiness:(id)            => api.post(`/businesses/${id}/reinstate`).then(r => r.data),

  // Subscriptions
  getSubscriptions: (params = {})   => api.get("/subscriptions", { params }).then(r => r.data),
  getSubscriptionsApprovals: (params = {})   => api.get("/subscriptions-approvals", { params }).then(r => r.data),
  renewSubscription: (id, status) =>
  api.post(`/subscription-approvals/${id}/renew`, { status }).then(r => r.data),
  getPlans:         ()              => api.get("/subscription-plans").then(r => r.data),
  grantSubscription:(data)          => api.post("/subscriptions/grant", data).then(r => r.data),
  revokeSubscription:(id)           => api.post(`/subscriptions/${id}/revoke`).then(r => r.data),
  extendSubscription:(id, days)     => api.post(`/subscriptions/${id}/extend`, { days }).then(r => r.data),

  // Subscription PLAN catalog management (create/edit/deactivate/reorder) —
  // the same rows the landing page pricing section and the in-portal
  // Subscription page both read live from.
  listPlanCatalog:  ()              => api.get("/plans").then(r => r.data),
  createPlan:       (data)          => api.post("/plans", data).then(r => r.data),
  updatePlan:       (id, data)      => api.put(`/plans/${id}`, data).then(r => r.data),
  deletePlan:       (id)            => api.delete(`/plans/${id}`).then(r => r.data),
  togglePlanActive: (id)            => api.post(`/plans/${id}/toggle`).then(r => r.data),
  reorderPlans:     (ids)           => api.put("/plans/reorder", { ids }).then(r => r.data),

  // Stores
  getStores:        (params = {})   => api.get("/stores", { params }).then(r => r.data),
  toggleFeatured:   (id)            => api.post(`/stores/${id}/toggle-featured`).then(r => r.data),
  toggleActive:     (id)            => api.post(`/stores/${id}/toggle-active`).then(r => r.data),

  // Reports
  getReports:       ()              => api.get("/reports/overview").then(r => r.data),

  // Platform settings (logo, contact info, socials shown on the public landing page)
  getSettings:      ()              => api.get("/settings").then(r => r.data),
  updateSettings:   (formData)      => api.post("/settings", formData, { headers: { "Content-Type": "multipart/form-data" } }).then(r => r.data),
};
