import React, { useState } from 'react';
import { api } from './api';

export default function LoginScreen() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleLogin = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        try {
            const response = await api.post('/login', { email, password });
            const token = response.data.token;
            window.alert(`Login successful (token: ${String(token).substring(0, 8)}…)`);
        } catch (err) {
            const msg = err.response?.data?.message || err.message;
            setError(msg);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div style={styles.container}>
            <h1 style={styles.title}>FamLedger – Login</h1>
            <form style={styles.form} onSubmit={handleLogin}>
                <input
                    type="email"
                    style={styles.input}
                    placeholder="Email"
                    autoCapitalize="none"
                    autoComplete="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                />
                <input
                    type="password"
                    style={styles.input}
                    placeholder="Password"
                    autoComplete="current-password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                />
                {error && <p style={styles.error}>{error}</p>}
                <button type="submit" style={styles.button} disabled={loading}>
                    {loading ? 'Signing in…' : 'Log in'}
                </button>
            </form>
        </div>
    );
}

const styles = {
    container: {
        maxWidth: '24rem',
        margin: '0 auto',
        padding: '1.5rem',
        backgroundColor: '#f5fbff',
        borderRadius: '8px',
    },
    title: {
        fontSize: '1.5rem',
        fontWeight: '600',
        marginBottom: '1.5rem',
        textAlign: 'center',
        color: '#333',
    },
    form: {
        display: 'flex',
        flexDirection: 'column',
        gap: '0.75rem',
    },
    input: {
        height: '48px',
        border: '1px solid #cbd5e0',
        borderRadius: '8px',
        padding: '0 12px',
        backgroundColor: '#fff',
        fontSize: '1rem',
    },
    error: {
        color: '#e53935',
        margin: 0,
        textAlign: 'center',
        fontSize: '0.875rem',
    },
    button: {
        height: '48px',
        border: 'none',
        borderRadius: '8px',
        backgroundColor: '#009EF7',
        color: '#fff',
        fontWeight: '600',
        fontSize: '1rem',
        cursor: 'pointer',
    },
};
