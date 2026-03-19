import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import LoginScreen from './LoginScreen';
import { api } from './api'; // ensure api is imported and base URL logged

// Log the API base URL for verification
console.log('🚀 API base URL →', api.defaults.baseURL);

const container = document.getElementById('app');
if (container) {
  const root = createRoot(container);
  root.render(<LoginScreen />);
}
