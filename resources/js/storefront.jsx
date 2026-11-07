import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import AdminApp from './admin';
import { apiRequest } from './api';
import RegisterPage from './register';
import CustomerLogin from './storefront/components/CustomerLogin';
import CatalogPage from './storefront/components/CatalogPage';
import '../css/storefront.css';

const CUSTOMER_SESSION_KEY = 'booksales-customer-session';
const ADMIN_SESSION_KEY = 'booksales-admin-session';

function readSession(key) {
    try {
        const session = JSON.parse(window.localStorage.getItem(key) || 'null');
        return session?.token && session?.user ? session : null;
    } catch {
        window.localStorage.removeItem(key);
        return null;
    }
}

function getCurrentPage() {
    const hashPage = window.location.hash.replace(/^#\/?/, '');

    if (hashPage) {
        return hashPage.replace(/\/+$/, '');
    }

    const path = window.location.pathname.replace(/\/+$/, '');
    const appPath = new URL('./', window.location.href).pathname.replace(/\/+$/, '');
    const relativePath = path.startsWith(appPath) ? path.slice(appPath.length) : path;

    return relativePath.replace(/^\/+/, '') || 'books';
}

function CustomerHeader({ onLogout, session }) {
    return (
        <header className="shop-header">
            <a className="shop-brand" href="#/books">
                <span className="shop-brand-mark">b<span>.</span></span>
                <span>booksales</span>
            </a>
            <nav aria-label="Navigasi katalog" className="shop-nav">
                <a href="#/books">Buku</a>
                <a href="#/genres">Genre</a>
                <a href="#/authors">Author</a>
                {session && <a href="#/orders">Pesanan</a>}
            </nav>
            <div className="shop-account">
                {session ? (
                    <>
                        <span className="shop-greeting">Hai, {session.user.name}</span>
                        <button className="shop-button shop-button-outline" onClick={onLogout} type="button">Keluar</button>
                    </>
                ) : (
                    <>
                        <a className="shop-login-link" href="#/login">Masuk</a>
                        <a className="shop-button shop-button-primary" href="#/register">Daftar</a>
                    </>
                )}
            </div>
        </header>
    );
}

function OrdersPage({ session }) {
    const [orders, setOrders] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        let isCurrent = true;

        apiRequest('my-transactions', { token: session.token })
            .then((result) => {
                if (isCurrent) {
                    setOrders(result);
                }
            })
            .catch((requestError) => {
                if (isCurrent) {
                    setError(requestError.message);
                }
            })
            .finally(() => {
                if (isCurrent) {
                    setIsLoading(false);
                }
            });

        return () => {
            isCurrent = false;
        };
    }, [session.token]);

    return (
        <main className="shop-main orders-main">
            <section className="shop-hero shop-hero-compact">
                <span className="shop-eyebrow">AKUN BOOKSALES</span>
                <h1>Pesananmu.</h1>
                <p>Lihat kembali buku-buku yang sudah kamu pesan.</p>
            </section>
            <section className="catalog-section">
                {error && <p className="shop-error" role="alert">{error}</p>}
                {isLoading ? (
                    <div className="catalog-state" role="status"><span className="catalog-spinner" />Memuat pesanan...</div>
                ) : orders.length ? (
                    <div className="orders-list">
                        {orders.map((order) => (
                            <article className="order-card" key={order.id}>
                                <div>
                                    <span className="shop-eyebrow">NO. PESANAN</span>
                                    <h2>{order.order_number}</h2>
                                    <p>{order.book?.title} · {order.quantity} buku</p>
                                </div>
                                <strong>{new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(order.total_amount))}</strong>
                            </article>
                        ))}
                    </div>
                ) : (
                    <div className="catalog-state">Kamu belum memiliki pesanan. Jelajahi katalog buku untuk menemukan bacaan berikutnya.</div>
                )}
            </section>
        </main>
    );
}

function StorefrontApp() {
    const [page, setPage] = useState(getCurrentPage);
    const [customerSession, setCustomerSession] = useState(() => readSession(CUSTOMER_SESSION_KEY));
    const [logoutError, setLogoutError] = useState('');

    useEffect(() => {
        function handleNavigation() {
            setPage(getCurrentPage());
        }

        window.addEventListener('hashchange', handleNavigation);
        window.addEventListener('popstate', handleNavigation);

        return () => {
            window.removeEventListener('hashchange', handleNavigation);
            window.removeEventListener('popstate', handleNavigation);
        };
    }, []);

    function handleAuthentication(result) {
        const nextSession = { token: result.token, user: result.user };

        if (result.user.role === 'admin') {
            window.localStorage.setItem(ADMIN_SESSION_KEY, JSON.stringify(nextSession));
            window.location.hash = '/admin';
            return;
        }

        window.localStorage.setItem(CUSTOMER_SESSION_KEY, JSON.stringify(nextSession));
        setCustomerSession(nextSession);
        window.location.hash = '/books';
    }

    async function handleLogout() {
        setLogoutError('');

        try {
            await apiRequest('logout', { token: customerSession.token, method: 'POST' });
        } catch (requestError) {
            setLogoutError(requestError.message);
            return;
        }

        window.localStorage.removeItem(CUSTOMER_SESSION_KEY);
        setCustomerSession(null);
        window.location.hash = '/books';
    }

    if (page === 'admin' || page.startsWith('admin/')) {
        return <AdminApp />;
    }

    if (page === 'register') {
        return <RegisterPage onRegistered={handleAuthentication} />;
    }

    if (page === 'login') {
        return <CustomerLogin onLogin={handleAuthentication} />;
    }

    const section = ['books', 'genres', 'authors'].includes(page) ? page : 'books';
    const isOrdersPage = page === 'orders';
    const visibleSession = customerSession?.user?.role === 'customer' ? customerSession : null;

    return (
        <div className="storefront">
            <CustomerHeader onLogout={handleLogout} session={visibleSession} />
            {logoutError && <p className="shop-global-error" role="alert">{logoutError}</p>}
            {isOrdersPage && visibleSession ? (
                <OrdersPage session={visibleSession} />
            ) : (
                <CatalogPage section={section} />
            )}
            <footer className="shop-footer">
                <a className="shop-brand" href="#/books">
                    <span className="shop-brand-mark">b<span>.</span></span> booksales
                </a>
                <span>Temukan cerita berikutnya di BookSales.</span>
                <a href="#/admin">Panel admin</a>
            </footer>
        </div>
    );
}

createRoot(document.getElementById('app-root')).render(<StorefrontApp />);
