<template>
  <div class="sa-shell">
    <!-- Sidebar -->
    <aside class="sa-sidebar" :class="{ 'sa-sidebar--collapsed': sidebarCollapsed }">
      <div class="sa-sidebar__brand">
        <div class="sa-sidebar__logo">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#60a5fa" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>
        <span v-if="!sidebarCollapsed" class="sa-sidebar__brand-name">Bizaa <span>Admin</span></span>
        <button class="sa-sidebar__collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round">
            <path v-if="!sidebarCollapsed" d="M15 18l-6-6 6-6" />
            <path v-else d="M9 18l6-6-6-6" />
          </svg>
        </button>
      </div>

      <nav class="sa-sidebar__nav">
        <router-link to="/superadmin/dashboard" class="sa-nav-item" active-class="sa-nav-item--active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <rect x="3" y="3" width="7" height="7" rx="1" />
            <rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" />
            <rect x="14" y="14" width="7" height="7" rx="1" />
          </svg>
          <span v-if="!sidebarCollapsed">Dashboard</span>
        </router-link>

        <router-link to="/superadmin/businesses" class="sa-nav-item" active-class="sa-nav-item--active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            <polyline points="9 22 9 12 15 12 15 22" />
          </svg>
          <span v-if="!sidebarCollapsed">Businesses</span>
        </router-link>

        <router-link to="/superadmin/subscriptions" class="sa-nav-item" active-class="sa-nav-item--active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2" />
            <line x1="1" y1="10" x2="23" y2="10" />
          </svg>
          <span v-if="!sidebarCollapsed">Subscriptions</span>
        </router-link>
        <router-link to="/superadmin/subscriptions-approval" class="sa-nav-item" active-class="sa-nav-item--active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2" />
            <line x1="1" y1="10" x2="23" y2="10" />
          </svg>
          <span v-if="!sidebarCollapsed">Subscriptions Approval</span>
        </router-link>



        <router-link to="/superadmin/plans" class="sa-nav-item" active-class="sa-nav-item--active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
          </svg>
          <span v-if="!sidebarCollapsed">Plans</span>
        </router-link>

        <router-link to="/superadmin/stores" class="sa-nav-item" active-class="sa-nav-item--active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
            <line x1="3" y1="6" x2="21" y2="6" />
            <path d="M16 10a4 4 0 0 1-8 0" />
          </svg>
          <span v-if="!sidebarCollapsed">Stores</span>
        </router-link>

        <router-link to="/superadmin/reports" class="sa-nav-item" active-class="sa-nav-item--active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <line x1="18" y1="20" x2="18" y2="10" />
            <line x1="12" y1="20" x2="12" y2="4" />
            <line x1="6" y1="20" x2="6" y2="14" />
          </svg>
          <span v-if="!sidebarCollapsed">Reports</span>
        </router-link>

        <router-link to="/superadmin/settings" class="sa-nav-item" active-class="sa-nav-item--active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <circle cx="12" cy="12" r="3" />
            <path
              d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
          </svg>
          <span v-if="!sidebarCollapsed">Settings</span>
        </router-link>
      </nav>

      <div class="sa-sidebar__footer">
        <div class="sa-user-card" v-if="!sidebarCollapsed">
          <div class="sa-user-card__avatar">{{ auth.user?.name?.charAt(0) ?? 'S' }}</div>
          <div class="sa-user-card__info">
            <p class="sa-user-card__name">{{ auth.user?.name }}</p>
            <p class="sa-user-card__role">Super Admin</p>
          </div>
        </div>
        <button class="sa-logout-btn" @click="logout" :title="sidebarCollapsed ? 'Logout' : ''">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>
          <span v-if="!sidebarCollapsed">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main -->
    <div class="sa-main">
      <header class="sa-topbar">
        <div class="sa-topbar__left">
          <h1 class="sa-topbar__title">{{ pageTitle }}</h1>
        </div>
        <div class="sa-topbar__right">
          <div class="sa-superadmin-badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 1l3.09 6.26L22 8.27l-5 4.87 1.18 6.88L12 16.77l-6.18 3.25L7 13.14 2 8.27l6.91-1.01L12 1z" />
            </svg>
            Super Admin
          </div>
        </div>
      </header>

      <main class="sa-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const sidebarCollapsed = ref(false);

const pageTitles = {
  SuperAdminDashboard: "Dashboard",
  SuperAdminBusinesses: "Businesses",
  SuperAdminBusinessView: "Business Details",
  SuperAdminSubscriptions: "Subscriptions",
  SuperAdminPlans: "Subscription Plans",
  SuperAdminStores: "Stores",
  SuperAdminReports: "Reports",
  SuperAdminSettings: "Settings",
};

const pageTitle = computed(() => pageTitles[route.name] ?? "Super Admin");

async function logout() {
  auth.clearToken();
  router.push("/login");
}
</script>

<style scoped>
.sa-shell {
  display: flex;
  height: 100vh;
  background: #0f172a;
  color: #e2e8f0;
  font-family: 'Inter', system-ui, sans-serif;
}

/* Sidebar */
.sa-sidebar {
  width: 220px;
  background: #0f172a;
  border-right: 1px solid #1e293b;
  display: flex;
  flex-direction: column;
  transition: width 0.2s;
  flex-shrink: 0;
}

.sa-sidebar--collapsed {
  width: 60px;
}

.sa-sidebar__brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 18px 14px;
  border-bottom: 1px solid #1e293b;
  min-height: 60px;
}

.sa-sidebar__logo {
  width: 32px;
  height: 32px;
  background: #1e3a5f;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.sa-sidebar__brand-name {
  font-size: 15px;
  font-weight: 700;
  color: #f1f5f9;
  white-space: nowrap;
}

.sa-sidebar__brand-name span {
  color: #60a5fa;
}

.sa-sidebar__collapse-btn {
  margin-left: auto;
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  display: flex;
  align-items: center;
}

.sa-sidebar__collapse-btn:hover {
  color: #94a3b8;
}

.sa-sidebar__nav {
  flex: 1;
  padding: 12px 8px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  overflow-y: auto;
}

.sa-nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 10px;
  border-radius: 8px;
  color: #94a3b8;
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.15s;
  white-space: nowrap;
  overflow: hidden;
}

.sa-nav-item:hover {
  background: #1e293b;
  color: #e2e8f0;
}

.sa-nav-item--active {
  background: #1e3a5f;
  color: #60a5fa;
}

.sa-sidebar__footer {
  padding: 12px 8px;
  border-top: 1px solid #1e293b;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.sa-user-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 6px;
}

.sa-user-card__avatar {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #1e3a5f;
  color: #60a5fa;
  font-size: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.sa-user-card__info {
  min-width: 0;
}

.sa-user-card__name {
  font-size: 12px;
  font-weight: 600;
  color: #f1f5f9;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sa-user-card__role {
  font-size: 11px;
  color: #60a5fa;
  margin-top: 1px;
}

.sa-logout-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 8px 10px;
  background: none;
  border: 1px solid #1e293b;
  border-radius: 8px;
  color: #64748b;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.15s;
}

.sa-logout-btn:hover {
  background: #1e293b;
  color: #f87171;
  border-color: #f87171;
}

/* Main */
.sa-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: #0f172a;
}

.sa-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  height: 60px;
  background: #0f172a;
  border-bottom: 1px solid #1e293b;
  flex-shrink: 0;
}

.sa-topbar__title {
  font-size: 18px;
  font-weight: 700;
  color: #f1f5f9;
  letter-spacing: -0.3px;
}

.sa-superadmin-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #1e3a5f;
  color: #60a5fa;
  font-size: 11px;
  font-weight: 700;
  padding: 5px 12px;
  border-radius: 100px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.sa-content {
  flex: 1;
  overflow-y: auto;
  padding: 28px;
  background: #0f172a;
}
</style>
