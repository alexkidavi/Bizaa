
import axios from 'axios';
const API = `${import.meta.env.VITE_APP_URL}/api/auth/`;
export default {
  post: (data) => {
    return axios.post(API + 'login', data); 
  }
  
};
