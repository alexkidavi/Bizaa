// src/services/StaffService.js
import api from "./api";

const RESOURCE = "/staff";

export default {
  async getAllStaff(params = {}) {
    const res = await api.get(RESOURCE, { params });
    return res.data;
  },

  async getStaffById(id) {
    const res = await api.get(`${RESOURCE}/${id}`);
    return res.data;
  },

  async create(payload) {
    const res = await api.post(RESOURCE, payload);
    return res.data;
  },

  async updateStaff(id, payload) {
    const res = await api.put(`${RESOURCE}/${id}`, payload);
    return res.data;
  },

  async toggleStaffStatus(id) {
    const res = await api.patch(`${RESOURCE}/${id}/toggle-status`);
    return res.data;
  },

  async toggleStatus(id) {
    const res = await api.patch(`${RESOURCE}/${id}/toggle-status`);
    return res.data;
  },

  async delete(id) {
    const res = await api.delete(`${RESOURCE}/${id}`);
    return res.data;
  },
};