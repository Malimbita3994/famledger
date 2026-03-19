// resources/js/LoginScreen.js
import React, { useState } from 'react';
import { View, TextInput, Button, Text, StyleSheet, ActivityIndicator, Alert } from 'react-native';
import { api } from './api';

export default function LoginScreen() {
  const [email, setEmail] = useState('admin@famledger.com');
  const [password, setPassword] = useState('SuperAdmin123!');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleLogin = async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await api.post('/login', { email, password });
      const token = response.data.token;
      console.log('🔐 Token received →', token);
      Alert.alert('Login Successful', `Token: ${token.substring(0, 8)}...`);
      // Here you could store the token with SecureStore/AsyncStorage and navigate.
    } catch (err) {
      const msg = err.response?.data?.message || err.message;
      setError(msg);
      console.warn('❌ Login error →', msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>FamLedger – Login</Text>

      <TextInput
        style={styles.input}
        placeholder="Email"
        autoCapitalize="none"
        keyboardType="email-address"
        value={email}
        onChangeText={setEmail}
      />

      <TextInput
        style={styles.input}
        placeholder="Password"
        secureTextEntry
        value={password}
        onChangeText={setPassword}
      />

      {error && <Text style={styles.error}>{error}</Text>}

      {loading ? (
        <ActivityIndicator size="large" color="#009EF7" />
      ) : (
        <Button title="Log In" onPress={handleLogin} color="#009EF7" />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    padding: 24,
    backgroundColor: '#f5fbff',
  },
  title: {
    fontSize: 24,
    fontWeight: '600',
    marginBottom: 24,
    textAlign: 'center',
    color: '#333',
  },
  input: {
    height: 48,
    borderColor: '#cbd5e0',
    borderWidth: 1,
    borderRadius: 8,
    marginBottom: 12,
    paddingHorizontal: 12,
    backgroundColor: '#fff',
  },
  error: {
    color: '#e53935',
    marginBottom: 12,
    textAlign: 'center',
  },
});
