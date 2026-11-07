import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
import '../css/register.css';

const APP_BASE_URL = new URL('./', window.location.href);
const REGISTER_ENDPOINT = new URL('api/register', APP_BASE_URL);

function RegisterApp() {
    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isRegistered, setIsRegistered] = useState(false);

    async function handleSubmit(event) {
        event.preventDefault();
        setError('');

        const formData = new FormData(event.currentTarget);
        const password = formData.get('password');
        const passwordConfirmation = formData.get('password_confirmation');

        if (password !== passwordConfirmation) {
            setError('Konfirmasi password belum sama.');
            return;
        }

        setIsSubmitting(true);

        try {
            const response = await fetch(REGISTER_ENDPOINT, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: formData.get('name'),
                    username: formData.get('username'),
                    email: formData.get('email'),
                    password,
                    password_confirmation: passwordConfirmation,
                }),
            });
            const payload = await response.json();

            if (!response.ok) {
                const validationMessage = payload.errors
                    ? Object.values(payload.errors).flat().join(' ')
                    : null;
                throw new Error(validationMessage || payload.message || 'Registrasi gagal. Silakan coba lagi.');
            }

            setIsRegistered(true);
        } catch (requestError) {
            setError(requestError.message || 'Tidak dapat terhubung ke server. Silakan coba lagi.');
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <main className="register-page">
            <section className="register-story" aria-labelledby="story-title">
                <a className="register-brand" href={APP_BASE_URL.href} aria-label="BookSales, kembali ke halaman utama">
                    <span className="register-brand-mark">b<span>.</span></span>
                    <span>booksales</span>
                </a>
                <div className="story-copy">
                    <span className="story-eyebrow">RUANG BACA BARUMU</span>
                    <h1 id="story-title">Setiap cerita hebat dimulai dari satu halaman.</h1>
                    <p>Temukan bacaan berikutnya, simpan cerita favoritmu, dan nikmati dunia buku bersama BookSales.</p>
                    <div className="book-art" aria-hidden="true">
                        <div className="book book-back"><span /></div>
                        <div className="book book-middle"><span /></div>
                        <div className="book book-front"><span>BS</span><i /></div>
                        <div className="book-shadow" />
                    </div>
                    <div className="story-note"><span>✦</span> Cerita baru menunggumu di sini.</div>
                </div>
                <span className="story-footer">BOOKSALES · TEMUKAN CERITAMU</span>
            </section>

            <section className="register-panel" aria-labelledby="register-title">
                <div className="register-card">
                    {isRegistered ? (
                        <div className="register-success" role="status">
                            <span className="success-icon" aria-hidden="true">✓</span>
                            <span className="form-eyebrow">AKUN SIAP DIGUNAKAN</span>
                            <h2 id="register-title">Selamat bergabung!</h2>
                            <p>Akun BookSales-mu berhasil dibuat. Sekarang kamu bisa mulai menjelajahi katalog buku.</p>
                            <a className="submit-button" href={APP_BASE_URL.href}>Kembali ke halaman utama <span aria-hidden="true">→</span></a>
                        </div>
                    ) : (
                        <>
                            <div className="register-heading">
                                <span className="form-eyebrow">MULAI PETUALANGANMU</span>
                                <h2 id="register-title">Buat akun baru</h2>
                                <p>Isi data berikut untuk bergabung sebagai customer.</p>
                            </div>
                            <form className="register-form" onSubmit={handleSubmit}>
                                <label className="register-field">
                                    <span>Nama lengkap</span>
                                    <input
                                        autoComplete="name"
                                        maxLength={255}
                                        name="name"
                                        placeholder="Contoh: Dewi Lestari"
                                        required
                                    />
                                </label>
                                <label className="register-field">
                                    <span>Email</span>
                                    <input
                                        autoComplete="email"
                                        name="email"
                                        placeholder="nama@email.com"
                                        required
                                        type="email"
                                    />
                                </label>
                                <label className="register-field">
                                    <span>Username</span>
                                    <input
                                        autoComplete="username"
                                        maxLength={30}
                                        minLength={3}
                                        name="username"
                                        pattern="[A-Za-z0-9_-]{3,30}"
                                        placeholder="nama_pengguna"
                                        required
                                        title="Gunakan 3-30 karakter: huruf, angka, garis bawah, atau tanda hubung."
                                    />
                                    <small>3-30 karakter, gunakan huruf, angka, _ atau -.</small>
                                </label>
                                <div className="password-fields">
                                    <label className="register-field">
                                        <span>Password</span>
                                        <input
                                            autoComplete="new-password"
                                            minLength={8}
                                            name="password"
                                            placeholder="Minimal 8 karakter"
                                            required
                                            type="password"
                                        />
                                    </label>
                                    <label className="register-field">
                                        <span>Ulangi password</span>
                                        <input
                                            autoComplete="new-password"
                                            minLength={8}
                                            name="password_confirmation"
                                            placeholder="Masukkan kembali"
                                            required
                                            type="password"
                                        />
                                    </label>
                                </div>
                                {error && <p className="register-error" role="alert">{error}</p>}
                                <button className="submit-button" disabled={isSubmitting} type="submit">
                                    {isSubmitting ? 'Membuat akun...' : 'Daftar sebagai customer'}
                                    <span aria-hidden="true">→</span>
                                </button>
                            </form>
                            <p className="register-terms">Dengan mendaftar, kamu siap membuka halaman pertamamu bersama kami.</p>
                        </>
                    )}
                </div>
                <p className="register-help">Butuh bantuan? <a href={APP_BASE_URL.href}>Kunjungi halaman utama</a></p>
            </section>
        </main>
    );
}

createRoot(document.getElementById('register-root')).render(<RegisterApp />);
