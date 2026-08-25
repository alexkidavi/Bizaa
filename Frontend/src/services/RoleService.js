import axios from 'axios';

const RESOURCE = '/api/roles';

const RoleService = {
  getAll: (params = {}) => axios.get(RESOURCE, { params }),
  getById: (id) => axios.get(`${RESOURCE}/${id}`),
  create: (data) => axios.post(RESOURCE, data),
  update: (id, data) => axios.put(`${RESOURCE}/${id}`, data),
  delete: (id) => axios.delete(`${RESOURCE}/${id}`),
  toggleStatus: (id) => axios.patch(`${RESOURCE}/${id}/toggle-status`),
  getPermissions: (roleId) => axios.get(`${RESOURCE}/${roleId}/permissions`),

  // ✅ ADD THIS
  assignPermissions: (roleId, data) =>
    axios.post(`${RESOURCE}/${roleId}/permissions`, data),
};

export default RoleService;