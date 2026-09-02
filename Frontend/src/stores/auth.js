// src/stores/auth.js
import { defineStore } from "pinia";
import axios from "axios";
import { API_BASE_URL } from "@/config";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    token: localStorage.getItem("token") || null,
    user: JSON.parse(localStorage.getItem("user")) || null,
    permissions: JSON.parse(localStorage.getItem("permissions")) || [],
    subscription: JSON.parse(localStorage.getItem("subscription")) || null,
    // Memory-only: never persisted to localStorage. Resolved via a backend check each session.
    // null = not yet checked, true = confirmed, false = not
    _superAdminVerified: null,
  }),

  getters: {
    isLoggedIn:  (state) => !!state.token,
    isSuperAdmin:(state) => state._superAdminVerified === true,
  },

  actions: {
    setToken(token) {
      this.token = token;
      localStorage.setItem("token", token);
    },

    setUser(user) {
      this.user = user;
      this.permissions = user?.permissions || [];

      localStorage.setItem("user", JSON.stringify(user));
      localStorage.setItem("permissions", JSON.stringify(this.permissions));
    },

    /**
     * Merge partial fields (e.g. a profile edit's name/email/avatar) into
     * the current user without touching permissions/subscription — unlike
     * setUser(), which replaces the whole object and would silently wipe
     * permissions if the caller's response doesn't include them.
     */
    patchUser(partial) {
      if (!this.user) return;
      this.user = { ...this.user, ...partial };
      localStorage.setItem("user", JSON.stringify(this.user));
    },

    setSubscription(sub) {
      this.subscription = sub;
      localStorage.setItem("subscription", JSON.stringify(sub));
    },

    clearToken() {
      this.token = null;
      this.user = null;
      this.permissions = [];
      this.subscription = null;
      this._superAdminVerified = null;

      localStorage.removeItem("token");
      localStorage.removeItem("user");
      localStorage.removeItem("permissions");
      localStorage.removeItem("subscription");
    },

    // Verifies superadmin status against the backend. Result cached in memory for the session.
    // Returns true if superadmin, false otherwise. Never reads from or writes to localStorage.
    async checkSuperAdmin() {
      if (!this.token) return false;
      if (this._superAdminVerified !== null) return this._superAdminVerified;

      try {
        await axios.get(`${API_BASE_URL}/who/check`, {
          headers: { Authorization: `Bearer ${this.token}` },
        });
        this._superAdminVerified = true;
      } catch {
        this._superAdminVerified = false;
      }
      return this._superAdminVerified;
    },

    hasPermission(permission) {
      return this.permissions.includes(permission);
    },

    hasAnyPermission(perms = []) {
      return perms.some((p) => this.permissions.includes(p));
    },

    // ✅ FIX FOR YOUR ERROR
    async refreshUser() {
      if (!this.token) return;

      try {
        const api = axios.create({
          baseURL: API_BASE_URL,
          headers: {
            Authorization: `Bearer ${this.token}`,
          },
        });

        const { data } = await api.get("/me");
        this.setUser(data);

        // Also refresh subscription status
        try {
          const subRes = await api.get("/subscription/current");
          this.setSubscription(subRes.data?.data ?? null);
        } catch {
          // subscription endpoint may 402 — that's fine here, just clear it
          this.setSubscription(null);
        }

        return data;
      } catch (error) {
        console.error("refreshUser failed:", error);

        if (error?.response?.status === 401) {
          this.clearToken();
          window.location.href = "/login";
        }
      }
    },
  },
});