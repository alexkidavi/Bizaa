import api from "./api";

const BASE = "/ecommerce/stores";

export default {
  // ── Stores ──────────────────────────────────────────────
  async getStores() {
    const res = await api.get(BASE);
    return res.data;
  },

  async getStore(id) {
    const res = await api.get(`${BASE}/${id}`);
    return res.data;
  },

  async createStore(payload) {
    const res = await api.post(BASE, payload);
    return res.data;
  },

  async updateStore(id, payload) {
    const res = await api.patch(`${BASE}/${id}`, payload);
    return res.data;
  },

  async deleteStore(id) {
    const res = await api.delete(`${BASE}/${id}`);
    return res.data;
  },

  // ── Product listing management ───────────────────────────
  async getStoreProducts(storeId) {
    const res = await api.get(`${BASE}/${storeId}/products`);
    return res.data;
  },

  async toggleProductListing(storeId, productBranchId, isListed) {
    const res = await api.patch(`${BASE}/${storeId}/products/${productBranchId}/listing`, {
      is_listed: isListed,
    });
    return res.data;
  },

  async bulkToggleListing(storeId, productBranchIds, isListed) {
    const res = await api.post(`${BASE}/${storeId}/products/bulk-toggle`, {
      product_branch_ids: productBranchIds,
      is_listed: isListed,
    });
    return res.data;
  },
};
