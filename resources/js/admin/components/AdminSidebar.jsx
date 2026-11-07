import React from 'react';
import { sections } from '../sections';
import Brand from './Brand';

export default function AdminSidebar({ activeSection, onSectionChange, onSignOut, records, user }) {
    return (
        <aside className="sidebar">
            <Brand />
            <div className="sidebar-label">WORKSPACE</div>
            <nav className="side-nav" aria-label="Navigasi admin">
                {Object.entries(sections).map(([key, section]) => (
                    <button
                        className={`nav-item ${activeSection === key ? 'active' : ''}`}
                        key={key}
                        onClick={() => onSectionChange(key)}
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
                <button className="profile-button" onClick={onSignOut} type="button">
                    <span className="profile-avatar">{user?.name?.slice(0, 1) || 'A'}</span>
                    <span className="profile-details">
                        <strong>{user?.name || 'Administrator'}</strong>
                        <small>Administrator</small>
                    </span>
                    <span className="signout-icon" aria-label="Keluar">↗</span>
                </button>
            </div>
        </aside>
    );
}
