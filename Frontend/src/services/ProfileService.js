import api from './api'

export default {
  getProfile() {
    return api.get('/me')
  },

  changePassword(data) {
    return api.post('/auth/change-password', data)
  },

  updateProfile(data) {
    return api.patch('/auth/profile', data)
  },
}
