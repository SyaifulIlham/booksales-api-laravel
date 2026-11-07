import React, { useEffect, useMemo, useState } from 'react';
import { apiRequest } from '../../api';

const catalogConfig = {
    books: {
        heading: 'Temukan cerita berikutnya.',
        description: 'Jelajahi koleksi buku pilihan dari BookSales.',
        endpoint: 'books',
    },
    genres: {
        heading: 'Jelajahi berdasarkan genre.',
        description: 'Pilih suasana cerita yang ingin kamu baca hari ini.',
        endpoint: 'genres',
    },
    authors: {
        heading: 'Kenali para penulis.',
        description: 'Temukan penulis di balik cerita favoritmu.',
        endpoint: 'authors',
    },
};

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(price));
}

export default function CatalogPage({ section }) {
    const config = catalogConfig[section] || catalogConfig.books;
    const [items, setItems] = useState([]);
    const [search, setSearch] = useState('');
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        let isCurrent = true;

        apiRequest(config.endpoint)
            .then((result) => {
                if (isCurrent) {
                    setItems(result);
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
    }, [config.endpoint]);

    const filteredItems = useMemo(() => {
        const query = search.trim().toLocaleLowerCase('id');

        if (!query) {
            return items;
        }

        return items.filter((item) => {
            const searchableText = section === 'books'
                ? `${item.title} ${item.author?.name} ${item.genre?.name} ${item.description}`
                : `${item.name} ${item.description || ''} ${item.bio || ''}`;

            return searchableText.toLocaleLowerCase('id').includes(query);
        });
    }, [items, search, section]);

    return (
        <main className="shop-main">
            <section className="shop-hero">
                <span className="shop-eyebrow">KATALOG BOOKSALES</span>
                <h1>{config.heading}</h1>
                <p>{config.description}</p>
                <label className="catalog-search">
                    <span aria-hidden="true">⌕</span>
                    <input
                        aria-label="Cari katalog"
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder={section === 'books' ? 'Cari judul, author, atau genre...' : `Cari ${section === 'genres' ? 'genre' : 'author'}...`}
                        type="search"
                        value={search}
                    />
                </label>
                <div className="hero-decoration" aria-hidden="true"><span /><span /><span /></div>
            </section>

            <section className="catalog-section" aria-label={config.heading}>
                <div className="catalog-section-heading">
                    <div>
                        <span className="shop-eyebrow">{section === 'books' ? 'PILIHAN UNTUKMU' : 'JELAJAHI KATALOG'}</span>
                        <h2>{section === 'books' ? 'Koleksi buku' : section === 'genres' ? 'Semua genre' : 'Semua author'}</h2>
                    </div>
                    <span className="catalog-count">{filteredItems.length} {section === 'books' ? 'buku' : section === 'genres' ? 'genre' : 'author'}</span>
                </div>

                {error && <p className="shop-error" role="alert">{error}</p>}
                {isLoading ? (
                    <div className="catalog-state" role="status"><span className="catalog-spinner" />Memuat katalog...</div>
                ) : filteredItems.length === 0 ? (
                    <div className="catalog-state">
                        {search ? 'Tidak ada hasil yang cocok. Coba kata kunci lain.' : 'Katalog belum memiliki data.'}
                    </div>
                ) : (
                    <div className={`catalog-grid catalog-grid-${section}`}>
                        {filteredItems.map((item) => (
                            section === 'books' ? (
                                <article className="book-card" key={item.id}>
                                    <div className="book-cover">
                                        {item.cover_photo ? (
                                            <img
                                                alt={`Sampul ${item.title}`}
                                                onError={(event) => { event.currentTarget.style.display = 'none'; }}
                                                src={item.cover_photo}
                                            />
                                        ) : null}
                                        <span className="book-cover-monogram" aria-hidden="true">B.</span>
                                    </div>
                                    <div className="book-card-content">
                                        <span className="book-genre">{item.genre?.name || 'Koleksi BookSales'}</span>
                                        <h3>{item.title}</h3>
                                        <p className="book-author">{item.author?.name ? `oleh ${item.author.name}` : 'BookSales'}</p>
                                        <p className="book-description">{item.description}</p>
                                        <div className="book-card-footer">
                                            <strong>{formatPrice(item.price)}</strong>
                                            <span className={item.stock > 0 ? 'stock-available' : 'stock-empty'}>
                                                {item.stock > 0 ? `Stok ${item.stock}` : 'Habis'}
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            ) : (
                                <article className="category-card" key={item.id}>
                                    <span className="category-card-icon" aria-hidden="true">{section === 'genres' ? '◈' : '✳'}</span>
                                    <span className="book-genre">{section === 'genres' ? 'GENRE' : 'AUTHOR'}</span>
                                    <h3>{item.name}</h3>
                                    <p>{section === 'genres' ? item.description : item.bio}</p>
                                    {section === 'authors' && item.books_count !== undefined && (
                                        <span className="category-card-count">{item.books_count} buku dalam katalog</span>
                                    )}
                                </article>
                            )
                        ))}
                    </div>
                )}
            </section>
        </main>
    );
}
