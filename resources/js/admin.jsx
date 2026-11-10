import React, { useEffect, useState } from 'react';
import AdminLogin from './admin/components/AdminLogin';
import AdminSidebar from './admin/components/AdminSidebar';
import CatalogRecordList from './admin/components/CatalogRecordList';
import DeleteRecordModal from './admin/components/DeleteRecordModal';
import RecordFormModal from './admin/components/RecordFormModal';
import SummaryCard from './admin/components/SummaryCard';
import { apiRequest } from './api';
import { sections } from './admin/sections';
import '../css/admin.css';

const SESSION_KEY = 'booksales-admin-session';

function getSavedSession() {
    try {
        const savedSession = window.localStorage.getItem(SESSION_KEY);
        const session = savedSession ? JSON.parse(savedSession) : null;

        if (session?.token && session?.user?.role === 'admin') {
            return session;
        }

        window.localStorage.removeItem(SESSION_KEY);
        return null;
    } catch {
        window.localStorage.removeItem(SESSION_KEY);
        return null;
    }
}

export default function AdminApp() {
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
    const [deletingRecord, setDeletingRecord] = useState(null);
    const [replacementId, setReplacementId] = useState('');
    const [deleteError, setDeleteError] = useState('');
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
                    ? currentItems.map((item) => item.id === savedRecord.id
                        ? { ...item, ...savedRecord, books_count: item.books_count ?? 0 }
                        : item)
                    : [...currentItems, { ...savedRecord, books_count: savedRecord.books_count ?? 0 }];

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
        if (item.books_count > 0) {
            setDeletingRecord(item);
            setReplacementId('');
            setDeleteError('');
            setError('');
            return;
        }

        if (!window.confirm(`Hapus ${sections[activeSection].singular} "${item.name}" dari katalog?`)) {
            return;
        }

        await deleteRecord(item);
    }

    async function deleteRecord(item, nextReplacementId = null) {
        setDeletingId(item.id);
        setError('');
        setDeleteError('');

        try {
            await apiRequest(`${activeSection}/${item.id}`, {
                token: session.token,
                method: 'DELETE',
                ...(nextReplacementId !== null
                    ? { body: JSON.stringify({ replacement_id: nextReplacementId }) }
                    : {}),
            });

            setRecords((currentRecords) => ({
                ...currentRecords,
                [activeSection]: currentRecords[activeSection]
                    .filter((record) => record.id !== item.id)
                    .map((record) => record.id === nextReplacementId
                        ? { ...record, books_count: record.books_count + (item.books_count || 0) }
                        : record),
            }));
            setDeletingRecord(null);
            setReplacementId('');
            setNotice(`${
                sections[activeSection].label
            } berhasil dihapus${
                nextReplacementId !== null ? ' dan buku terkait dipindahkan' : ''
            }.`);
            window.setTimeout(() => setNotice(''), 3500);
        } catch (deleteError) {
            if (nextReplacementId !== null) {
                setDeleteError(deleteError.message);
            } else {
                setError(deleteError.message);
            }
        } finally {
            setDeletingId(null);
        }
    }

    if (!session?.token) {
        return <AdminLogin error={error} onSubmit={handleLogin} saving={saving} />;
    }

    const currentSection = sections[activeSection];
    const filteredRecords = records[activeSection].filter((item) => {
        const query = search.trim().toLowerCase();
        return !query || `${item.name} ${currentSection.secondary(item)}`.toLowerCase().includes(query);
    });

    return (
        <div className="app-shell">
            <AdminSidebar
                activeSection={activeSection}
                onSectionChange={(section) => {
                    setActiveSection(section);
                    setSearch('');
                    setError('');
                }}
                onSignOut={signOut}
                records={records}
                user={session.user}
            />

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

                    <CatalogRecordList
                        activeSection={activeSection}
                        currentSection={currentSection}
                        deletingId={deletingId}
                        error={error}
                        filteredRecords={filteredRecords}
                        loading={loading}
                        onCreate={openCreateForm}
                        onDelete={handleDelete}
                        onEdit={openEditForm}
                        onSearchChange={setSearch}
                        records={records}
                        search={search}
                    />
                </section>
            </main>

            {notice && <div className="toast" role="status"><span>✓</span>{notice}</div>}
            {deletingRecord && (
                <DeleteRecordModal
                    activeSection={activeSection}
                    currentSection={currentSection}
                    deleteError={deleteError}
                    deletingId={deletingId}
                    deletingRecord={deletingRecord}
                    onClose={() => setDeletingRecord(null)}
                    onConfirm={deleteRecord}
                    records={records}
                    replacementId={replacementId}
                    setReplacementId={setReplacementId}
                />
            )}
            {formOpen && (
                <RecordFormModal
                    currentSection={currentSection}
                    editingRecord={editingRecord}
                    error={error}
                    formValues={formValues}
                    onClose={closeForm}
                    onSubmit={handleSave}
                    saving={saving}
                    setFormValues={setFormValues}
                />
            )}
        </div>
    );
}
