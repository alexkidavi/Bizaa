import axios from 'axios';

const API = `${import.meta.env.VITE_APP_URL}/api/auth/`;

export default {
  register: (data) => {
    return axios.post(API + 'register', data); 
  }
};