
import axios from 'axios';
import { API_BASE_URL } from '@/config';
const API = `${API_BASE_URL}/auth/`;
export default {
  post: (data) => {
    return axios.post(API + 'login', data); 
  }
  
};
