// src/services/DashboardService.js
import api from "./api";

const RESOURCE = "/dashboard";

export default {
  /**
   * Fetch all dashboard data for a given filter period.
   * @param {{period: 'today'|'week'|'month'|'year'|'custom', branch_id?: number, start_date?: string, end_date?: string}} filter
   * @returns {Promise<{stats, chart, top_products, recent_transactions, low_stock, user}>}
   */
  async getDashboardData(filter = { period: "today" }) {
    const res = await api.get(`${RESOURCE}`, { params: { filter } });
    return res.data;
  },
};