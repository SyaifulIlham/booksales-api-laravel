import React from 'react';

export default function SummaryCard({ label, value, icon, tone }) {
    return (
        <article className="summary-card">
            <div className={`summary-icon ${tone}`}>{icon}</div>
            <div><span>{label}</span><strong>{value.toLocaleString('id-ID')}</strong></div>
            <span className="summary-caption">DALAM KATALOG</span>
        </article>
    );
}
