import React from 'react';

export default function DeleteRecordModal({
    activeSection,
    currentSection,
    deleteError,
    deletingId,
    deletingRecord,
    onClose,
    onConfirm,
    records,
    replacementId,
    setReplacementId,
}) {
    const availableRecords = records[activeSection].filter((record) => record.id !== deletingRecord.id);

    return (
        <div className="modal-backdrop">
            <section aria-labelledby="delete-title" aria-modal="true" className="modal-card" role="dialog">
                <div className="modal-heading">
                    <div>
                        <span className="eyebrow">PEMINDAHAN BUKU</span>
                        <h2 id="delete-title">Pindahkan buku terkait?</h2>
                        <p>
                            {deletingRecord.books_count} buku memakai {currentSection.singular} "{deletingRecord.name}".
                            Pilih {currentSection.singular} pengganti sebelum menghapusnya.
                        </p>
                    </div>
                    <button
                        aria-label="Tutup"
                        className="close-button"
                        disabled={deletingId !== null}
                        onClick={onClose}
                        type="button"
                    >
                        ×
                    </button>
                </div>
                <form
                    className="form-stack"
                    onSubmit={(event) => {
                        event.preventDefault();
                        onConfirm(deletingRecord, Number(replacementId));
                    }}
                >
                    {availableRecords.length > 0 ? (
                        <label>
                            {currentSection.label} pengganti
                            <select
                                onChange={(event) => setReplacementId(event.target.value)}
                                required
                                value={replacementId}
                            >
                                <option value="">Pilih {currentSection.singular} pengganti</option>
                                {availableRecords.map((record) => (
                                    <option key={record.id} value={record.id}>{record.name}</option>
                                ))}
                            </select>
                        </label>
                    ) : (
                        <p className="form-error">
                            Tambahkan {currentSection.singular} lain terlebih dahulu untuk memindahkan buku.
                        </p>
                    )}
                    {deleteError && <p className="form-error" role="alert">{deleteError}</p>}
                    <div className="modal-actions">
                        <button
                            className="button button-secondary"
                            disabled={deletingId !== null}
                            onClick={onClose}
                            type="button"
                        >
                            Batal
                        </button>
                        <button
                            className="button button-primary"
                            disabled={deletingId !== null || !replacementId || availableRecords.length === 0}
                            type="submit"
                        >
                            {deletingId === deletingRecord.id ? 'Memindahkan dan menghapus...' : 'Pindahkan & hapus'}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    );
}
