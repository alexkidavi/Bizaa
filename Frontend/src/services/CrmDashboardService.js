import api from "./api";

export default {
  async get(params = {}) {
    const res = await api.get("/crm/dashboard", { params });
    return res.data;
  },
};
