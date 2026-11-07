import React, { useState } from 'react';
import { apiRequest } from '../../api';

export default function CustomerLogin({ onLogin }) {
    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    async function handleSubmit(event) {
        event.preventDefault();
        setError('');
        setIsSubmitting(true);

        const formData = new FormData(event.currentTarget);

        try {
            const result = await apiRequest('login', {
                method: 'POST',
                body: JSON.stringify({
                    email: formData.get('email'),
                    password: formData.get('password'),
                }),
            });

            if (!['customer', 'admin'].includes(result.user?.role)) {
                throw new Error('Akun ini tidak memiliki akses ke BookSales.');
            }

            onLogin(result);
        } catch (loginError) {
            setError(loginError.message);
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <main className="auth-page">
            <section className="auth-story">
                <a className="shop-brand shop-brand-light" href="#/books">
                    <span className="shop-brand-mark">b<span>.</span></span> booksales
                </a>
                <div>
                    <span className="shop-eyebrow">SELAMAT DATANG KEMBALI</span>
                    <h1>Petualangan membaca menunggumu.</h1>
                    <p>Masuk untuk menemukan buku favorit dan melihat katalog BookSales.</p>
                </div>
                <span className="auth-story-caption">BOOKSALES · TEMUKAN CERITAMU</span>
            </section>
            <section className="auth-content">
                <div className="auth-card">
                    <span className="shop-eyebrow">AKUN BOOKSALES</span>
                    <h2>Masuk ke akunmu</h2>
                    <p className="auth-description">Gunakan email dan password saat mendaftar.</p>
                    <form className="shop-form" onSubmit={handleSubmit}>
                        <label>
                            Email
                            <input autoComplete="email" name="email" placeholder="nama@email.com" required type="email" />
                        </label>
                        <label>
                            Password
                            <input autoComplete="current-password" name="password" placeholder="Masukkan password" required type="password" />
                        </label>
                        {error && <p className="shop-error" role="alert">{error}</p>}
                        <button className="shop-button shop-button-primary" disabled={isSubmitting} type="submit">
                            {isSubmitting ? 'Memeriksa akun...' : 'Masuk'}
                            <span aria-hidden="true">→</span>
                        </button>
                    </form>
                    <p className="auth-switch">Belum punya akun? <a href="#/register">Daftar sebagai customer</a></p>
                    <p className="auth-switch auth-admin-link">Administrator? <a href="#/admin">Masuk ke panel admin</a></p>
                </div>
            </section>
        </main>
    );
}
