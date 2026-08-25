// src/services/AdminDashboardService.js
import api from "./api";

const RESOURCE = "/admin"; 

export default {
  /**
   * Get global SACCO statistics (Total savings, members, etc.)
   */
  async getStats(filter = 'month') {
    const res = await api.get(`${RESOURCE}/stats`, { params: { filter } });
    return res.data;
  },

  /**
   * Get chart data for the admin overview
   */
  async getCharts(filter = 'month') {
    const res = await api.get(`${RESOURCE}/charts`, { params: { filter } });
    return res.data;
  },

  /**
   * Membership Management (CRUD-like actions)
   */
  async getMembers(params = {}) {
    const res = await api.get(`${RESOURCE}/members`, { params });
    return res.data;
  },

  async getMemberDetails(id) {
    const res = await api.get(`${RESOURCE}/members/${id}`);
    return res.data;
  },

  async updateMemberStatus(id, status) {
    // status: 1 for active, 0 for suspended
    const res = await api.patch(`${RESOURCE}/members/${id}/status`, { status });
    return res.data;
  }
};