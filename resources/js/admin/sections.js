export const sections = {
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
