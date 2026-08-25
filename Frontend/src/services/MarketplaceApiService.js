import api from "./api";

const BASE = "/marketplace";

export default {
  async getStores() {
    const res = await api.get(`${BASE}/stores`);
    return res.data?.data ?? [];
  },

  async getStoreBySlug(slug) {
    const res = await api.get(`${BASE}/stores/${slug}`);
    return res.data?.data ?? null;
  },

  async getProduct(id) {
    const res = await api.get(`${BASE}/products/${id}`);
    return res.data?.data ?? null;
  },
};
