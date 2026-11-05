import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import '../css/admin.css';

const API_BASE = `${window.location.pathname.replace(/\/$/, '')}/api`;
const SESSION_KEY = 'booksales-admin-session';

const sections = {
    genres: {
        label: 'Genre',
        plural: 'Genre',
        singular: 'genre',
        description: 'Kelola kategori untuk katalog buku.',
        fields: [
            { name: 'name', label: 'Nama genre', placeholder: 'Contoh: Fiksi ilmiah' },
            { name: 'description', label: 'Deskripsi', placeholder: 'Ceritakan tentang genre ini', multiline: true },
        ],
        secondary: (item) => item.description,
    },
    authors: {
        label: 'Author',
        plural: 'Author',
        singular: 'author',
        description: 'Kenali para penulis di katalog buku.',
        fields: [
            { name: 'name', label: 'Nama author', placeholder: 'Contoh: Andrea Hirata' },
            { name: 'photo', label: 'URL foto', placeholder: 'https://contoh.com/foto.jpg' },
            { name: 'bio', label: 'Biografi', placeholder: 'Tuliskan biografi singkat author', multiline: true },
        ],
        secondary: (item) => item.bio,
    },
};

async function apiRequest(path, { token, ...options } = {}) {
    const response = await fetch(`${API_BASE}/${path}`, {
        ...options,
        headers: {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...options.headers,
        },
    });

    const payload = response.status === 204 ? null : await response.json();

    if (!response.ok) {
        const validationMessage = payload?.errors
            ? Object.values(payload.errors).flat().join(' ')
            : null;
        throw new Error(validationMessage || payload?.message || 'Permintaan gagal. Coba lagi.');
    }

    return payload;
}

function getSavedSession() {
    try {
        const savedSession = window.localStorage.getItem(SESSION_KEY);
        return savedSession ? JSON.parse(savedSession) : null;
    } catch {
        window.localStorage.removeItem(SESSION_KEY);
        return null;
    }
}

function App() {
    const [session, setSession] = useState(getSavedSession);
    const [activeSection, setActiveSection] = useState('genres');
    const [records, setRecords] = useState({ genres: [], authors: [] });
    const [search, setSearch] = useState('');
    const [formOpen, setFormOpen] = useState(false);
    const [editingRecord, setEditingRecord] = useState(null);
    const [formValues, setFormValues] = useState({});
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [deletingId, setDeletingId] = useState(null);
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');

    useEffect(() => {
        if (!session?.token) {
            return;
        }

        let isCurrent = true;
        setLoading(true);
        setError('');

        Promise.all([
            apiRequest('genres', { token: session.token }),
            apiRequest('authors', { token: session.token }),
        ])
            .then(([genres, authors]) => {
                if (isCurrent) {
                    setRecords({ genres, authors });
                }
            })
            .catch((requestError) => {
                if (isCurrent) {
                    setError(requestError.message);
                    if (requestError.message.includes('Unauthenticated')) {
                        signOut();
                    }
                }
            })
            .finally(() => {
                if (isCurrent) {
                    setLoading(false);
                }
            });

        return () => {
            isCurrent = false;
        };
    }, [session?.token]);

    function signOut() {
        window.localStorage.removeItem(SESSION_KEY);
        setSession(null);
        setRecords({ genres: [], authors: [] });
        setFormOpen(false);
    }

    async function handleLogin(event) {
        event.preventDefault();
        setError('');
        setSaving(true);

        const formData = new FormData(event.currentTarget);

        try {
            const result = await apiRequest('login', {
                method: 'POST',
                body: JSON.stringify({
                    email: formData.get('email'),
                    password: formData.get('password'),
                }),
            });

            if (result.user?.role !== 'admin') {
                setError('Akun ini tidak memiliki akses admin.');
                return;
            }

            const nextSession = { token: result.token, user: result.user };
            window.localStorage.setItem(SESSION_KEY, JSON.stringify(nextSession));
            setSession(nextSession);
        } catch (loginError) {
            setError(loginError.message);
        } finally {
            setSaving(false);
        }
    }

    function openCreateForm() {
        setEditingRecord(null);
        setFormValues({});
        setError('');
        setFormOpen(true);
    }

    function openEditForm(item) {
        setEditingRecord(item);
        setFormValues(Object.fromEntries(
            sections[activeSection].fields.map((field) => [field.name, item[field.name] || '']),
        ));
        setError('');
        setFormOpen(true);
    }

    function closeForm() {
        setFormOpen(false);
        setEditingRecord(null);
        setFormValues({});
        setError('');
    }

    async function handleSave(event) {
        event.preventDefault();
        setSaving(true);
        setError('');

        try {
            const savedRecord = await apiRequest(
                editingRecord ? `${activeSection}/${editingRecord.id}` : activeSection,
                {
                    token: session.token,
                    method: editingRecord ? 'PUT' : 'POST',
                    body: JSON.stringify(formValues),
                },
            );

            setRecords((currentRecords) => {
                const currentItems = currentRecords[activeSection];
                const nextItems = editingRecord
                    ? currentItems.map((item) => item.id === savedRecord.id ? savedRecord : item)
                    : [...currentItems, savedRecord];

                return {
                    ...currentRecords,
                    [activeSection]: nextItems.sort((first, second) => first.name.localeCompare(second.name, 'id')),
                };
            });
            closeForm();
            setNotice(`${sections[activeSection].label} berhasil ${editingRecord ? 'diperbarui' : 'ditambahkan'}.`);
            window.setTimeout(() => setNotice(''), 3500);
        } catch (saveError) {
            setError(saveError.message);
        } finally {
            setSaving(false);
        }
    }

    async function handleDelete(item) {
        if (!window.confirm(`Hapus ${sections[activeSection].singular} "${item.name}" dari katalog?`)) {
            return;
        }

        setDeletingId(item.id);
        setError('');

        try {
            await apiRequest(`${activeSection}/${item.id}`, {
                token: session.token,
                method: 'DELETE',
            });

            setRecords((currentRecords) => ({
                ...currentRecords,
                [activeSection]: currentRecords[activeSection].filter((record) => record.id !== item.id),
            }));
            setNotice(`${sections[activeSection].label} berhasil dihapus.`);
            window.setTimeout(() => setNotice(''), 3500);
        } catch (deleteError) {
            setError(deleteError.message);
        } finally {
            setDeletingId(null);
        }
    }

    if (!session?.token) {
        return (
            <main className="login-page">
                <section className="login-card">
                    <Brand />
                    <div className="login-heading">
                        <span className="eyebrow">RUANG KERJA ADMIN</span>
                        <h1>Selamat datang kembali.</h1>
                        <p>Masuk untuk mengelola genre dan author BookSales.</p>
                    </div>
                    <form className="form-stack" onSubmit={handleLogin}>
                        <label>
                            Email
                            <input name="email" type="email" placeholder="admin@booksales.com" autoComplete="username" required />
                        </label>
                        <label>
                            Password
                            <input name="password" type="password" placeholder="Masukkan password" autoComplete="current-password" required />
                        </label>
                        {error && <p className="form-error" role="alert">{error}</p>}
                        <button className="button button-primary button-wide" type="submit" disabled={saving}>
                            {saving ? 'Memeriksa...' : 'Masuk ke dashboard'}
                            <span aria-hidden="true">→</span>
                        </button>
                    </form>
                    <p className="login-footnote">Akses khusus untuk administrator BookSales.</p>
                </section>
                <aside className="login-aside">
                    <div className="aside-orbit orbit-one" />
                    <div className="aside-orbit orbit-two" />
                    <div className="aside-copy">
                        <span className="eyebrow">CERITA DIMULAI DI SINI</span>
                        <h2>Ruang untuk cerita-cerita hebat.</h2>
                        <p>Jaga katalog tetap hidup dengan genre yang tepat dan penulis yang menginspirasi.</p>
                        <div className="book-stack" aria-hidden="true">
                            <span /><span /><span />
                        </div>
                    </div>
                    <span className="aside-caption">BOOKSALES · ADMIN WORKSPACE</span>
                </aside>
            </main>
        );
    }

    const currentSection = sections[activeSection];
    const filteredRecords = records[activeSection].filter((item) => {
        const query = search.trim().toLowerCase();
        return !query || `${item.name} ${currentSection.secondary(item)}`.toLowerCase().includes(query);
    });

    return (
        <div className="app-shell">
            <aside className="sidebar">
                <Brand />
                <div className="sidebar-label">WORKSPACE</div>
                <nav className="side-nav" aria-label="Navigasi admin">
                    {Object.entries(sections).map(([key, section]) => (
                        <button
                            className={`nav-item ${activeSection === key ? 'active' : ''}`}
                            key={key}
                            onClick={() => {
                                setActiveSection(key);
                                setSearch('');
                                setError('');
                            }}
                            type="button"
                        >
                            <span className="nav-icon">{key === 'genres' ? '◈' : '✳'}</span>
                            <span>{section.plural}</span>
                            <span className="nav-count">{records[key].length}</span>
                        </button>
                    ))}
                </nav>
                <div className="sidebar-bottom">
                    <div className="help-card">
                        <span className="help-mark">✦</span>
                        <strong>Katalog yang baik</strong>
                        <p>Dimulai dari informasi yang lengkap dan rapi.</p>
                    </div>
                    <button className="profile-button" onClick={signOut} type="button">
                        <span className="profile-avatar">{session.user?.name?.slice(0, 1) || 'A'}</span>
                        <span className="profile-details">
                            <strong>{session.user?.name || 'Administrator'}</strong>
                            <small>Administrator</small>
                        </span>
                        <span className="signout-icon" aria-label="Keluar">↗</span>
                    </button>
                </div>
            </aside>

            <main className="main-content">
                <header className="topbar">
                    <div className="breadcrumb"><span>Workspace</span><span className="breadcrumb-divider">/</span><strong>{currentSection.plural}</strong></div>
                    <div className="topbar-right">
                        <span className="live-indicator"><i /> Semua data tersimpan</span>
                        <span className="topbar-date">{new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date())}</span>
                    </div>
                </header>

                <section className="page-content">
                    <div className="page-heading">
                        <div>
                            <span className="eyebrow">KATALOG BUKU</span>
                            <h1>{currentSection.plural}</h1>
                            <p>{currentSection.description}</p>
                        </div>
                        <button className="button button-primary" onClick={openCreateForm} type="button">
                            <span className="plus-icon">+</span> Tambah {currentSection.singular}
                        </button>
                    </div>

                    <section className="summary-grid" aria-label="Ringkasan katalog">
                        <SummaryCard label="Total genre" value={records.genres.length} icon="◈" tone="lavender" />
                        <SummaryCard label="Total author" value={records.authors.length} icon="✳" tone="peach" />
                        <SummaryCard label="Koleksi katalog" value={records.genres.length + records.authors.length} icon="▤" tone="mint" />
                    </section>

                    <section className="records-panel">
                        <div className="panel-heading">
                            <div>
                                <h2>Semua {currentSection.plural.toLowerCase()}</h2>
                                <p>{records[activeSection].length} data terdaftar dalam katalog</p>
                            </div>
                            <label className="search-box">
                                <span aria-hidden="true">⌕</span>
                                <input
                                    aria-label={`Cari ${currentSection.plural.toLowerCase()}`}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder={`Cari ${currentSection.singular}...`}
                                    value={search}
                                />
                                <kbd>⌘ K</kbd>
                            </label>
                        </div>

                        <div className="records-header">
                            <span>{currentSection.singular.toUpperCase()}</span>
                            <span>DESKRIPSI</span>
                            <span>STATUS</span>
                            <span>AKSI</span>
                        </div>
                        {error && <div className="inline-error" role="alert">{error}</div>}
                        {loading ? (
                            <div className="empty-state"><span className="loading-spinner" />Memuat data katalog...</div>
                        ) : filteredRecords.length > 0 ? (
                            <div className="record-list">
                                {filteredRecords.map((item, index) => (
                                    <article className="record-row" key={item.id}>
                                        <div className="record-primary">
                                            {activeSection === 'authors' ? (
                                                <div className="record-avatar">
                                                    <span>{item.name?.slice(0, 1)}</span>
                                                    {item.photo && <img alt="" onError={(event) => { event.currentTarget.style.display = 'none'; }} src={item.photo} />}
                                                </div>
                                            ) : (
                                                <div className={`genre-avatar genre-tone-${index % 4}`}>{item.name?.slice(0, 1)?.toUpperCase()}</div>
                                            )}
                                            <div>
                                                <strong>{item.name}</strong>
                                                <small>Ditambahkan ke katalog</small>
                                            </div>
                                        </div>
                                        <p className="record-description">{currentSection.secondary(item) || 'Belum ada deskripsi.'}</p>
                                        <span className="status-pill"><i /> Aktif</span>
                                        <div className="record-actions">
                                            <button
                                                aria-label={`Edit ${item.name}`}
                                                className="record-action record-action-edit"
                                                disabled={deletingId !== null}
                                                onClick={() => openEditForm(item)}
                                                type="button"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                aria-label={`Hapus ${item.name}`}
                                                className="record-action record-action-delete"
                                                disabled={deletingId !== null}
                                                onClick={() => handleDelete(item)}
                                                type="button"
                                            >
                                                {deletingId === item.id ? 'Menghapus...' : 'Hapus'}
                                            </button>
                                        </div>
                                    </article>
                                ))}
                            </div>
                        ) : (
                            <div className="empty-state">
                                <span className="empty-icon">{search ? '⌕' : '✦'}</span>
                                <strong>{search ? 'Data tidak ditemukan' : `Belum ada ${currentSection.singular}`}</strong>
                                <p>{search ? 'Coba kata kunci yang berbeda.' : `Mulai dengan menambahkan ${currentSection.singular} pertama ke katalog.`}</p>
                                {!search && <button className="button button-secondary" onClick={openCreateForm} type="button">Tambah {currentSection.singular}</button>}
                            </div>
                        )}
                        <footer className="panel-footer">
                            <span>Menampilkan <strong>{filteredRecords.length}</strong> dari <strong>{records[activeSection].length}</strong> data</span>
                            <span className="footer-dot"><i /> Terhubung ke BookSales API</span>
                        </footer>
                    </section>
                </section>
            </main>

            {notice && <div className="toast" role="status"><span>✓</span>{notice}</div>}
            {formOpen && (
                <div className="modal-backdrop" onMouseDown={(event) => { if (event.target === event.currentTarget && !saving) closeForm(); }}>
                    <section aria-labelledby="create-title" aria-modal="true" className="modal-card" role="dialog">
                        <div className="modal-heading">
                            <div>
                                <span className="eyebrow">{editingRecord ? 'PERBARUI KATALOG' : 'KATALOG BARU'}</span>
                                <h2 id="create-title">{editingRecord ? `Edit ${currentSection.singular}` : `Tambah ${currentSection.singular}`}</h2>
                                <p>Lengkapi informasi berikut untuk {editingRecord ? 'memperbarui' : 'menambahkan'} data.</p>
                            </div>
                            <button aria-label="Tutup" className="close-button" disabled={saving} onClick={closeForm} type="button">×</button>
                        </div>
                        <form className="form-stack" onSubmit={handleSave}>
                            {currentSection.fields.map((field) => (
                                <label key={field.name}>
                                    {field.label}
                                    {field.multiline ? (
                                        <textarea
                                            onChange={(event) => setFormValues({ ...formValues, [field.name]: event.target.value })}
                                            placeholder={field.placeholder}
                                            required
                                            rows={4}
                                            value={formValues[field.name] || ''}
                                        />
                                    ) : (
                                        <input
                                            onChange={(event) => setFormValues({ ...formValues, [field.name]: event.target.value })}
                                            placeholder={field.placeholder}
                                            required
                                            type={field.name === 'photo' ? 'url' : 'text'}
                                            value={formValues[field.name] || ''}
                                        />
                                    )}
                                </label>
                            ))}
                            {error && <p className="form-error" role="alert">{error}</p>}
                            <div className="modal-actions">
                                <button className="button button-secondary" disabled={saving} onClick={closeForm} type="button">Batal</button>
                                <button className="button button-primary" disabled={saving} type="submit">
                                    {saving ? 'Menyimpan...' : editingRecord ? 'Simpan perubahan' : `Simpan ${currentSection.singular}`}
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            )}
        </div>
    );
}

function Brand() {
    return (
        <div className="brand">
            <span className="brand-mark">b<span>.</span></span>
            <span className="brand-name">booksales<small>ADMIN CONSOLE</small></span>
        </div>
    );
}

function SummaryCard({ label, value, icon, tone }) {
    return (
        <article className="summary-card">
            <div className={`summary-icon ${tone}`}>{icon}</div>
            <div><span>{label}</span><strong>{value.toLocaleString('id-ID')}</strong></div>
            <span className="summary-caption">DALAM KATALOG</span>
        </article>
    );
}

createRoot(document.getElementById('root')).render(<App />);
