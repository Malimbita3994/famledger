import './bootstrap';
import Alpine from 'alpinejs';
import { api } from './api'; // new import

// Log the API base URL so we can verify the client is pointing at the correct host
console.log('🚀 API base URL →', api.defaults.baseURL);

window.Alpine = Alpine;

Alpine.start();
