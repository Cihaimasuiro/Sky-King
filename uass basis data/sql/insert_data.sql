USE uasbasisdata;

-- Insert data ke tabel Customer
INSERT INTO Customer (nama, email, username, password, no_pasport) VALUES
('John Doe', 'john.doe@email.com', 'johndoe', SHA2('password123', 256), 'A12345678'),
('Jane Smith', 'jane.smith@email.com', 'janesmith', SHA2('password456', 256), 'B23456789'),
('Ahmad Ibrahim', 'ahmad.ibrahim@email.com', 'ahmad', SHA2('password789', 256), 'C34567890'),
('Maria Garcia', 'maria.garcia@email.com', 'maria', SHA2('passwordabc', 256), 'D45678901'),
('Budi Santoso', 'budi.santoso@email.com', 'budi', SHA2('passworddef', 256), 'E56789012');

-- Alter table Bandara to add kode column if not exists
ALTER TABLE Bandara ADD COLUMN IF NOT EXISTS kode VARCHAR(3);

-- Insert data ke tabel Bandara dengan kode
INSERT INTO Bandara (nama, kota, negara, kode) VALUES
('Soekarno-Hatta International Airport', 'Jakarta', 'Indonesia', 'CGK'),
('Ngurah Rai International Airport', 'Denpasar', 'Indonesia', 'DPS'),
('Juanda International Airport', 'Surabaya', 'Indonesia', 'SUB'),
('Kuala Lumpur International Airport', 'Kuala Lumpur', 'Malaysia', 'KUL'),
('Changi Airport', 'Singapore', 'Singapore', 'SIN'),
('Kualanamu International Airport', 'Medan', 'Indonesia', 'KNO'),
('Sultan Hasanuddin International Airport', 'Makassar', 'Indonesia', 'UPG'),
('Husein Sastranegara International Airport', 'Bandung', 'Indonesia', 'BDO'),
('Adisucipto International Airport', 'Yogyakarta', 'Indonesia', 'JOG'),
('Sam Ratulangi International Airport', 'Manado', 'Indonesia', 'MDC');

-- Insert data ke tabel Maskapai
INSERT INTO Maskapai (nama, logo) VALUES
('Garuda Indonesia', 'images/airlines/garuda.png'),
('Lion Air', 'images/airlines/lion.png'),
('Batik Air', 'images/airlines/batik.png'),
('Citilink', 'images/airlines/citilink.png');

-- Insert data ke tabel Penerbangan dengan harga dan id_maskapai
INSERT INTO Penerbangan (nomor_penerbangan, jam_berangkat, jam_kedatangan, asal_penerbangan, tujuan_penerbangan, harga, id_maskapai) VALUES
('GA-100', '08:00:00', '09:30:00', 1, 2, 1500000, 1),
('LI-200', '10:00:00', '11:30:00', 2, 3, 1200000, 2),
('BA-300', '12:00:00', '13:30:00', 3, 4, 2000000, 3),
('GA-400', '14:00:00', '15:30:00', 4, 5, 2500000, 1),
('LI-500', '16:00:00', '17:30:00', 5, 1, 1800000, 2),
('CI-600', '18:00:00', '19:30:00', 1, 6, 1300000, 4),
('GA-700', '20:00:00', '21:30:00', 6, 7, 1600000, 1),
('BA-800', '07:00:00', '08:30:00', 7, 8, 1400000, 3),
('LI-900', '09:00:00', '10:30:00', 8, 9, 1100000, 2),
('CI-1000', '11:00:00', '12:30:00', 9, 10, 1700000, 4);

-- Insert data ke tabel Pemesanan
INSERT INTO Pemesanan (tanggal_pesan, status, id_customer, id_penerbangan) VALUES
('2024-12-01', 'Confirmed', 1, 1),
('2024-12-02', 'Pending', 2, 2),
('2024-12-03', 'Confirmed', 3, 3),
('2024-12-04', 'Cancelled', 4, 4),
('2024-12-05', 'Confirmed', 5, 5);
