USE db_clahstra;

-- Level Data
INSERT INTO level (level_id, level_name) VALUES
(1, 'admin'),
(2, 'customer');

-- User Data (bcrypt hash example)
INSERT INTO user (id_user, username, password, level_id) VALUES
(1, 'admin', '$2y$10$sOmeSaLt.j/8LYqB89/81Oa98uLZJIe09.Xy0/A2d.oJDqQSFQ/S', 1);

-- Bandara Data
INSERT INTO bandara (id_bandara, nama, kota, negara, kode, timezone) VALUES
(1, 'Soekarno-Hatta International Airport', 'Jakarta', 'Indonesia', 'CGK', '+07:00'),
(2, 'Ngurah Rai International Airport', 'Denpasar', 'Indonesia', 'DPS', '+08:00');

-- Customer Data
INSERT INTO customer (id_customer, first_name, last_name, email, username, password, no_pasport, address, phone_number, nationality, date_of_birth) VALUES
(1, 'John', 'Doe', 'john.doe@email.com', 'johndoe', '$2y$10$aNotherHash.j/8LYqB89/81Oa98uLZJIe09.Xy0/A2d.oJDqQSFQ/S', 'A12345678', '123 Main St', '123-456-7890', 'American', '1990-01-01'),
(2, 'Jane', 'Smith', 'jane.smith@email.com', 'janesmith', '$2y$10$yetAnotherHash.j/8LYqB89/81Oa98uLZJIe09.Xy0/A2d.oJDqQSFQ/S', 'B23456789', '456 Oak Ave', '987-654-3210', 'British', '1995-05-05');



-- Maskapai Data
INSERT INTO maskapai (id_maskapai, nama, logo) VALUES
(1, 'Garuda Indonesia', 'images/airlines/garuda.png'),
(2, 'Lion Air', 'images/airlines/lion.png');

-- Penerbangan Data
INSERT INTO penerbangan (id_penerbangan, nomor_penerbangan, jam_berangkat, jam_kedatangan, asal_penerbangan, tujuan_penerbangan, harga, id_maskapai, status, seats_available) VALUES
(1, 'GA-100', '08:00:00', '09:30:00', 1, 2, 1500000, 1, 'On Time', 150),
(2, 'LI-200', '10:00:00', '11:30:00', 2, 1, 1200000, 2, 'Delayed', 100);

-- Pemesanan Data
INSERT INTO pemesanan (id_pemesanan, tanggal_pesan, status, id_customer, id_penerbangan) VALUES
(1, '2024-12-01', 'Confirmed', 1, 1),
(2, '2024-12-02', 'Pending', 2, 2);

-- Pembayaran Data
INSERT INTO pembayaran (id_pembayaran, id_pemesanan, jumlah, tanggal_pembayaran, jenis_pembayaran, status, transaction_id) VALUES
(1, 1, 1500000, '2024-12-01', 'Kartu Kredit', 'Completed', 'TXN12345'),
(2, 2, 1200000, '2024-12-02', 'Transfer Bank', 'Pending', 'TXN67890');

