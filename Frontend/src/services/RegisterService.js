import axios from 'axios';
import { API_BASE_URL } from '@/config';

const API = `${API_BASE_URL}/auth/`;

export default {
  register: (data) => {
    return axios.post(API + 'register', data); 
  }
};