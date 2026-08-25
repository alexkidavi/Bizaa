// src/services/Branchesservice.js
import api from "./api";

const RESOURCE = "/branches";

export default {
  async getAllBranches() {
    const res = await api.get(`${RESOURCE}/get`);
    return res.data;
  },

  async createBranch(payload) {
    const res = await api.post(`${RESOURCE}/register`, payload);
    return res.data;
  },
  async updateBranch(id, payload) {
    const res = await api.patch(`${RESOURCE}/${id}`, payload);
    return res.data;
  },
  async updateBranchstatus(id, status) {
    const res = await api.patch(`${RESOURCE}/${id}/status`, { status });
    return res.data;
  },

  async deleteBranch(id) {
    const res = await api.delete(`${RESOURCE}/${id}/delete`);
    return res.data;
  },
};
