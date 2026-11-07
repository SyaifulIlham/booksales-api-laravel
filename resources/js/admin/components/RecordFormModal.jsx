import React from 'react';

export default function RecordFormModal({
    currentSection,
    editingRecord,
    error,
    formValues,
    onClose,
    onSubmit,
    saving,
    setFormValues,
}) {
    return (
        <div
            className="modal-backdrop"
            onMouseDown={(event) => {
                if (event.target === event.currentTarget && !saving) {
                    onClose();
                }
            }}
        >
            <section aria-labelledby="create-title" aria-modal="true" className="modal-card" role="dialog">
                <div className="modal-heading">
                    <div>
                        <span className="eyebrow">{editingRecord ? 'PERBARUI KATALOG' : 'KATALOG BARU'}</span>
                        <h2 id="create-title">{editingRecord ? `Edit ${currentSection.singular}` : `Tambah ${currentSection.singular}`}</h2>
                        <p>Lengkapi informasi berikut untuk {editingRecord ? 'memperbarui' : 'menambahkan'} data.</p>
                    </div>
                    <button aria-label="Tutup" className="close-button" disabled={saving} onClick={onClose} type="button">×</button>
                </div>
                <form className="form-stack" onSubmit={onSubmit}>
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
                        <button className="button button-secondary" disabled={saving} onClick={onClose} type="button">Batal</button>
                        <button className="button button-primary" disabled={saving} type="submit">
                            {saving ? 'Menyimpan...' : editingRecord ? 'Simpan perubahan' : `Simpan ${currentSection.singular}`}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    );
}
