// resources/js/api.js
import { Platform } from 'react-native';
import axios from 'axios';

let API_HOST;

if (Platform.OS === 'android') {
  // Android emulator → host PC
  API_HOST = 'http://10.0.2.2:8000';
} else if (Platform.OS === 'ios') {
  // iOS simulator
  API_HOST = 'http://127.0.0.1:8000';
} else {
  // Physical device – replace with your LAN IP or an ngrok URL
  API_HOST = 'http://192.168.0.12:8000'; // <‑‑ UPDATE IF NEEDED
}

export const api = axios.create({
  baseURL: `${API_HOST}/api`,
  timeout: 8000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: false,
});

api.interceptors.request.use((config) => {
  config.metadata = { startTime: Date.now() };
  return config;
});

api.interceptors.response.use(
  (response) => {
    const duration = Date.now() - response.config.metadata.startTime;
    console.log(`✅ ${response.config.method.toUpperCase()} ${response.config.url} → ${duration} ms`);
    return response;
  },
  (error) => {
    const duration = Date.now() - (error.config?.metadata?.startTime ?? Date.now());
    console.warn(`❌ ${error.config?.method?.toUpperCase()} ${error.config?.url} → ${duration} ms`);
    return Promise.reject(error);
  }
);
