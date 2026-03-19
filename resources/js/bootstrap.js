import axios from 'axios';

window.axios = axios;

const API_HOST = 'http://10.0.2.2:8000';
window.axios.defaults.baseURL = `${API_HOST}/api`;

// Ensure JSON responses are expected
window.axios.defaults.headers.common['Accept'] = 'application/json';

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Send cookies (for Sanctum authentication)
window.axios.defaults.withCredentials = true;
