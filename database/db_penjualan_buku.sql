-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Okt 2026 pada 07.51
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_penjualan_buku`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_beli_buku` (IN `p_order` VARCHAR(50), IN `p_customer` INT, IN `p_book` INT, IN `p_qty` INT, OUT `p_status` VARCHAR(100))   proc: BEGIN
    DECLARE v_stock INT;
    DECLARE v_price DECIMAL(10,2);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'Gagal: terjadi error, transaksi dibatalkan';
    END;

    START TRANSACTION;

    SELECT stock, price INTO v_stock, v_price
    FROM books WHERE id = p_book FOR UPDATE;

    IF v_stock IS NULL THEN
        ROLLBACK;
        SET p_status = 'Gagal: buku tidak ditemukan';
        LEAVE proc;
    ELSEIF v_stock < p_qty THEN
        ROLLBACK;
        SET p_status = 'Gagal: stok tidak cukup';
        LEAVE proc;
    END IF;

    INSERT INTO transactions (order_number, customer_id, book_id, total_amount)
    VALUES (p_order, p_customer, p_book, v_price * p_qty);

    UPDATE books SET stock = stock - p_qty WHERE id = p_book;

    COMMIT;
    SET p_status = 'Berhasil';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_buku_by_genre` (IN `p_genre` VARCHAR(255))   BEGIN
    SELECT b.id, b.title, a.name AS penulis, b.price, b.stock
    FROM books b
    JOIN genres g  ON b.genre_id  = g.id
    JOIN authors a ON b.author_id = a.id
    WHERE g.name = p_genre;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `authors`
--

CREATE TABLE `authors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `authors`
--

INSERT INTO `authors` (`id`, `name`, `foto`, `bio`) VALUES
(1, 'J.K. Rowling', 'jk_rowling.jpg', 'Penulis seri Harry Potter asal Inggris.'),
(2, 'George R.R. Martin', 'george_martin.jpg', 'Penulis novel fantasi A Song of Ice and Fire.'),
(3, 'Isaac Asimov', 'isaac_asimov.jpg', 'Penulis fiksi ilmiah dan sains populer.'),
(4, 'Tere Liye', 'tere_liye.jpg', 'Penulis novel fantasi dan drama asal Indonesia.'),
(5, 'Andrea Hirata', 'andrea_hirata.jpg', 'Penulis novel Indonesia, lahir di Belitung.'),
(6, 'Dee Lestari', 'dee_lestari.jpg', 'Penulis seri Supernova.'),
(7, 'Pramoedya Ananta Toer', 'pramoedya.jpg', 'Sastrawan Indonesia penulis Tetralogi Buru.');

-- --------------------------------------------------------

--
-- Struktur dari tabel `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `cover_foto` varchar(255) DEFAULT NULL,
  `genre_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `books`
--

INSERT INTO `books` (`id`, `title`, `description`, `price`, `stock`, `cover_foto`, `genre_id`, `author_id`) VALUES
(1, 'Harry Potter', 'Petualangan penyihir muda di Hogwarts.', 25000.00, 35, 'harry_potter.jpg', 1, 1),
(2, 'Lord of the Rings', 'Perjalanan menghancurkan cincin kekuasaan.', 75000.00, 30, 'lotr.jpg', 1, 2),
(3, '1984', 'Novel distopia tentang pengawasan totaliter.', 60000.00, 40, '1984.jpg', 1, 3),
(4, 'The Hitchhiker\'s Guide to the Galaxy', 'Petualangan komedi di luar angkasa.', 55000.00, 25, 'hitchhikers.jpg', 3, 3),
(5, 'The Hobbit', 'Petualangan Bilbo Baggins menuju Lonely Mountain.', 65000.00, 8, 'hobbit.jpg', 1, 2),
(6, 'Bumi', 'Petualangan Raib di dunia paralel.', 90000.00, 29, 'bumi.jpg', 7, 4),
(7, 'Laskar Pelangi', 'Kisah sepuluh anak di Belitung.', 75000.00, 40, 'laskar_pelangi.jpg', 1, 5),
(8, 'Supernova', 'Ksatria, Putri, dan Bintang Jatuh.', 80000.00, 25, 'supernova.jpg', 3, 6),
(9, 'Bumi Manusia', 'Kisah Minke di masa kolonial.', 95000.00, 20, 'bumi_manusia.jpg', 9, 7);

-- --------------------------------------------------------

--
-- Struktur dari tabel `genres`
--

CREATE TABLE `genres` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `genres`
--

INSERT INTO `genres` (`id`, `name`, `description`) VALUES
(1, 'Fiction', 'Cerita rekaan dan imajinatif'),
(2, 'Non-Fiction', 'Buku berbasis fakta dan pengetahuan'),
(3, 'Science Fiction', 'Cerita bertema sains dan masa depan'),
(5, 'Romance', 'Cerita bertema percintaan'),
(6, 'Mystery', 'Cerita misteri dan teka-teki'),
(7, 'Fantasy', 'Cerita fantasi dengan dunia magis'),
(9, 'History', 'Buku bertema sejarah');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `order_number`, `customer_id`, `book_id`, `total_amount`) VALUES
(1, 'ORD-001', 2, 1, 50000.00),
(2, 'ORD-002', 3, 3, 60000.00),
(3, 'ORD-003', 2, 5, 65000.00),
(9, 'ORD-101', 4, 6, 90000.00),
(10, 'ORD-102', 5, 7, 75000.00),
(11, 'ORD-103', 6, 8, 80000.00),
(12, 'ORD-104', 7, 9, 95000.00),
(14, 'ORD-201', 5, 6, 90000.00),
(15, 'ORD-301', 2, 1, 50000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Ilham', 'ipuldev@gmail.com', 'admin123', 'admin'),
(2, 'Elis', 'elis@example.com', 'elis324', 'admin'),
(3, 'Bob Smith', 'bob@example.com', 'bob901', 'customer'),
(4, 'Ali Akbar', 'Akbrali@example.com', 'likabar145', 'admin'),
(5, 'Dimas Syuhada', 'dimas@example.com', 'dimas123', 'customer'),
(6, 'Sari Assyifa', 'sari@example.com', 'sari123', 'customer'),
(7, 'Andi Siregar', 'andi@example.com', 'andi123', 'customer');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_left_genre_jumlah_buku`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_left_genre_jumlah_buku` (
`genre` varchar(255)
,`jumlah_buku` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_left_user_transaksi`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_left_user_transaksi` (
`user_id` int(11)
,`nama_user` varchar(255)
,`role` enum('customer','admin')
,`order_number` varchar(50)
,`total_amount` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_right_buku_transaksi`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_right_buku_transaksi` (
`judul_buku` varchar(255)
,`order_number` varchar(50)
,`total_amount` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_right_penulis_buku`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_right_penulis_buku` (
`penulis` varchar(255)
,`judul_buku` varchar(255)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_left_genre_jumlah_buku`
--
DROP TABLE IF EXISTS `v_left_genre_jumlah_buku`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_left_genre_jumlah_buku`  AS SELECT `g`.`name` AS `genre`, count(`b`.`id`) AS `jumlah_buku` FROM (`genres` `g` left join `books` `b` on(`b`.`genre_id` = `g`.`id`)) GROUP BY `g`.`id`, `g`.`name` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_left_user_transaksi`
--
DROP TABLE IF EXISTS `v_left_user_transaksi`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_left_user_transaksi`  AS SELECT `u`.`id` AS `user_id`, `u`.`name` AS `nama_user`, `u`.`role` AS `role`, `t`.`order_number` AS `order_number`, `t`.`total_amount` AS `total_amount` FROM (`users` `u` left join `transactions` `t` on(`t`.`customer_id` = `u`.`id`)) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_right_buku_transaksi`
--
DROP TABLE IF EXISTS `v_right_buku_transaksi`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_right_buku_transaksi`  AS SELECT `b`.`title` AS `judul_buku`, `t`.`order_number` AS `order_number`, `t`.`total_amount` AS `total_amount` FROM (`books` `b` left join `transactions` `t` on(`t`.`book_id` = `b`.`id`)) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_right_penulis_buku`
--
DROP TABLE IF EXISTS `v_right_penulis_buku`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_right_penulis_buku`  AS SELECT `a`.`name` AS `penulis`, `b`.`title` AS `judul_buku` FROM (`authors` `a` left join `books` `b` on(`b`.`author_id` = `a`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `genre_id` (`genre_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indeks untuk tabel `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `authors`
--
ALTER TABLE `authors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`),
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`);

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
