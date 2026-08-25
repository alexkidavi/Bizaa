// src/services/RoleService.js
import axios from 'axios';

const RESOURCE = '/api/permissions';

const RoleService = {
  getAll:      (params = {}) => axios.get(`${RESOURCE}/get`, { params }),
  getById:     (id)          => axios.get(`${RESOURCE}/${id}`),
  create:      (data)        => axios.post(RESOURCE, data),
  update:      (id, data)    => axios.put(`${RESOURCE}/${id}`, data),
  delete:      (id)          => axios.delete(`${RESOURCE}/${id}`),
  toggleStatus:(id)          => axios.patch(`${RESOURCE}/${id}/toggle-status`),
};

export default RoleService;