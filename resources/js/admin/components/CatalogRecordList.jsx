import React from 'react';

export default function CatalogRecordList({
    activeSection,
    currentSection,
    deletingId,
    error,
    filteredRecords,
    loading,
    onCreate,
    onDelete,
    onEdit,
    onSearchChange,
    records,
    search,
}) {
    return (
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
                        onChange={(event) => onSearchChange(event.target.value)}
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
                                    onClick={() => onEdit(item)}
                                    type="button"
                                >
                                    Edit
                                </button>
                                <button
                                    aria-label={`Hapus ${item.name}`}
                                    className="record-action record-action-delete"
                                    disabled={deletingId !== null}
                                    onClick={() => onDelete(item)}
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
                    {!search && <button className="button button-secondary" onClick={onCreate} type="button">Tambah {currentSection.singular}</button>}
                </div>
            )}
            <footer className="panel-footer">
                <span>Menampilkan <strong>{filteredRecords.length}</strong> dari <strong>{records[activeSection].length}</strong> data</span>
                <span className="footer-dot"><i /> Terhubung ke BookSales API</span>
            </footer>
        </section>
    );
}
