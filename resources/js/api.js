import axios from 'axios';

const base =
    typeof import.meta !== 'undefined' && import.meta.env?.VITE_API_BASE_URL
        ? String(import.meta.env.VITE_API_BASE_URL).replace(/\/$/, '')
        : '/api';

export const api = axios.create({
    baseURL: base,
    timeout: 8000,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true,
});

api.interceptors.request.use((config) => {
    config.metadata = { startTime: Date.now() };
    return config;
});

api.interceptors.response.use(
    (response) => {
        const duration = Date.now() - response.config.metadata.startTime;
        console.log(
            `${response.config.method.toUpperCase()} ${response.config.url} → ${duration} ms`
        );
        return response;
    },
    (error) => {
        const duration = Date.now() - (error.config?.metadata?.startTime ?? Date.now());
        console.warn(
            `${error.config?.method?.toUpperCase()} ${error.config?.url} → ${duration} ms`
        );
        return Promise.reject(error);
    }
);
