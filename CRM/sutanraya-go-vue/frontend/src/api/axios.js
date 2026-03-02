import axios from 'axios';

const api = axios.create({
    baseURL: '/api', // Proxy will handle this to localhost:8080
    headers: {
        'Content-Type': 'application/json',
    },
    withCredentials: true
});

export default api;
