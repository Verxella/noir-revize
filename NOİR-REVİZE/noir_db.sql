-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 02 Haz 2026, 13:36:52
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `noir_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `favoriler`
--

CREATE TABLE `favoriler` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `ilan_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `favoriler`
--

INSERT INTO `favoriler` (`id`, `user_id`, `ilan_id`, `created_at`) VALUES
(5, 4, 10, '2026-05-06 17:39:41'),
(6, 4, 9, '2026-05-06 17:39:43'),
(7, 6, 11, '2026-05-06 17:51:58'),
(8, 6, 10, '2026-05-06 17:52:00'),
(9, 6, 8, '2026-05-06 17:52:02');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ilanlar`
--

CREATE TABLE `ilanlar` (
  `id` int(11) NOT NULL,
  `ekleyen_id` int(11) NOT NULL,
  `ilan_tipi` enum('Araba','Emlak') NOT NULL DEFAULT 'Araba',
  `pazarlama_tipi` enum('Satılık','Kiralık') NOT NULL DEFAULT 'Satılık',
  `marka_id` int(11) DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `sehir_id` int(11) NOT NULL,
  `yakit_tipi` varchar(50) DEFAULT NULL,
  `vites_tipi` varchar(50) DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `baslik` varchar(255) NOT NULL,
  `fiyat` decimal(15,2) NOT NULL,
  `metrekare` int(11) DEFAULT NULL,
  `oda_sayisi` varchar(20) DEFAULT NULL,
  `ilan_resmi` varchar(255) DEFAULT NULL,
  `tum_resimler` text DEFAULT NULL,
  `kilometre` int(11) DEFAULT 0,
  `model_yili` int(4) DEFAULT NULL,
  `yil` year(4) DEFAULT NULL,
  `tarih` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `ilanlar`
--

INSERT INTO `ilanlar` (`id`, `ekleyen_id`, `ilan_tipi`, `pazarlama_tipi`, `marka_id`, `model_id`, `sehir_id`, `yakit_tipi`, `vites_tipi`, `telefon`, `aciklama`, `baslik`, `fiyat`, `metrekare`, `oda_sayisi`, `ilan_resmi`, `tum_resimler`, `kilometre`, `model_yili`, `yil`, `tarih`, `created_at`) VALUES
(8, 6, 'Araba', 'Satılık', 3, 3, 2, 'Benzin', 'Manuel', '05468978414', 'SIFIR FABRİKA ÇIKIŞLI TEMİZ ARAÇ.', 'BAYİ ÇIKIŞLI SIFIR ARAÇ', 13000000.00, NULL, NULL, '69fb7baea4f6a_0.jpg', '[\"69fb7baea4f6a_0.jpg\",\"69fb7baea5751_1.jpg\",\"69fb7baeab10f_2.jpg\"]', 0, 2020, NULL, '2026-05-06 17:34:38', '2026-05-06 17:34:38'),
(9, 6, 'Araba', 'Satılık', 1, 1, 19, 'Benzin', 'Otomatik', '05468978414', 'Sahibinden tertemiz araç.', 'SAHİBİNDEN', 11000000.00, NULL, NULL, '69fb7bef074b4_0.jpg', '[\"69fb7bef074b4_0.jpg\",\"69fb7bef07a41_1.jpg\"]', 55000, 2022, NULL, '2026-05-06 17:35:43', '2026-05-06 17:35:43'),
(10, 6, 'Araba', 'Satılık', 2, 2, 10, 'Dizel', 'Manuel', '05468978414', '...', 'SAHİBİNDEN', 14000000.00, NULL, NULL, '69fb7cc2884e9_0.jpg', '[\"69fb7cc2884e9_0.jpg\",\"69fb7cc288b5b_1.jpg\"]', 12000, 2019, NULL, '2026-05-06 17:39:14', '2026-05-06 17:39:14'),
(11, 4, 'Araba', 'Satılık', 4, 4, 1, 'Benzin', 'Otomatik', '01235345678', '.', 'SAHİBİNDEN', 20000000.00, NULL, NULL, '69fb7f5e04218_0.jpg', '[\"69fb7f5e04218_0.jpg\"]', 15000, 2021, NULL, '2026-05-06 17:50:22', '2026-05-06 17:50:22'),
(12, 4, 'Araba', 'Satılık', 8, 8, 3, 'Hibrit', 'Otomatik', '01235345678', '..', 'SAHİBİNDEN', 450000.00, NULL, NULL, '69fb822d062c4_0.jpg', '[\"69fb822d062c4_0.jpg\"]', 12000, 2022, NULL, '2026-05-06 18:02:21', '2026-05-06 18:02:21'),
(15, 4, 'Emlak', 'Kiralık', NULL, NULL, 1, NULL, NULL, '05468978414', '.......', '1+1 EV', 12500.00, 138, '1+1', '6a1ebd4e7877b_0.png', '[\"6a1ebd4e7877b_0.png\"]', 138, 0, NULL, '2026-06-02 11:23:58', '2026-06-02 11:23:58');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `markalar`
--

CREATE TABLE `markalar` (
  `id` int(11) NOT NULL,
  `marka_adi` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `markalar`
--

INSERT INTO `markalar` (`id`, `marka_adi`) VALUES
(1, 'BMW'),
(2, 'Mercedes-Benz'),
(3, 'Audi'),
(4, 'Porsche'),
(5, 'Tesla'),
(6, 'Ferrari'),
(7, 'Lamborghini'),
(8, 'Aston Martin'),
(9, 'Bentley'),
(10, 'Maserati'),
(11, 'Rolls-Royce'),
(12, 'McLaren'),
(13, 'Land Rover'),
(14, 'Bugatti'),
(15, 'Lotus'),
(16, 'Alfa Romeo'),
(17, 'Jaguar'),
(18, 'Volvo'),
(19, 'Lexus'),
(20, 'Cadillac');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `mesajlar`
--

CREATE TABLE `mesajlar` (
  `id` int(11) NOT NULL,
  `ilan_id` int(11) NOT NULL,
  `gonderen_id` int(11) NOT NULL,
  `alici_id` int(11) NOT NULL,
  `mesaj_icerik` text NOT NULL,
  `tarih` timestamp NOT NULL DEFAULT current_timestamp(),
  `okundu_bilgisi` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `mesajlar`
--

INSERT INTO `mesajlar` (`id`, `ilan_id`, `gonderen_id`, `alici_id`, `mesaj_icerik`, `tarih`, `okundu_bilgisi`) VALUES
(1, 12, 6, 4, 'Araç hala satılık mı?', '2026-05-06 18:03:55', 1),
(2, 0, 4, 6, 'evet', '2026-05-06 18:17:44', 1),
(3, 0, 4, 6, 's', '2026-05-06 18:30:33', 1),
(4, 12, 6, 4, 'Takas düşünüyor musunuz?', '2026-05-07 09:20:05', 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `modeller`
--

CREATE TABLE `modeller` (
  `id` int(11) NOT NULL,
  `marka_id` int(11) NOT NULL,
  `model_adi` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `modeller`
--

INSERT INTO `modeller` (`id`, `marka_id`, `model_adi`) VALUES
(1, 1, 'M4 Competition'),
(2, 2, 'G63 AMG'),
(3, 3, 'RS7 Sportback'),
(4, 4, '911 Turbo S'),
(5, 5, 'Model S Plaid'),
(6, 6, 'SF90 Stradale'),
(7, 7, 'Revuelto'),
(8, 8, 'DBS Volante'),
(9, 9, 'Continental GT'),
(10, 10, 'MC20 Cielo'),
(11, 11, 'Spectre'),
(12, 12, '750S Spider'),
(13, 13, 'Range Rover SV'),
(14, 14, 'Chiron Pur Sport'),
(15, 15, 'Emira'),
(16, 16, 'Giulia Quadrifoglio'),
(17, 17, 'F-Type R'),
(18, 18, 'XC90 Recharge'),
(19, 19, 'LC 500 h'),
(20, 20, 'Escalade V-Series');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sehirler`
--

CREATE TABLE `sehirler` (
  `id` int(11) NOT NULL,
  `sehir_adi` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `sehirler`
--

INSERT INTO `sehirler` (`id`, `sehir_adi`) VALUES
(1, 'İstanbul'),
(2, 'Ankara'),
(3, 'İzmir'),
(4, 'Antalya'),
(5, 'Bursa'),
(6, 'Muğla'),
(7, 'Adana'),
(8, 'Gaziantep'),
(9, 'Kocaeli'),
(10, 'Mersin'),
(11, 'Denizli'),
(12, 'Sakarya'),
(13, 'Samsun'),
(14, 'Eskişehir'),
(15, 'Aydın'),
(16, 'Trabzon'),
(17, 'Tekirdağ'),
(18, 'Kayseri'),
(19, 'Konya'),
(20, 'Diyarbakır');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `ad_soyad` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `sifre` varchar(255) NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `profil_resmi` varchar(255) DEFAULT 'default.png',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `ad_soyad`, `email`, `telefon`, `sifre`, `remember_token`, `profil_resmi`, `status`, `created_at`) VALUES
(1, 'Efe Yılmaz', 'efe@noir.com', '05051234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'default.png', 1, '2026-05-05 16:56:58'),
(4, 'efe', 'efe@gmail.com', '05468978414', '$2y$10$sr7nLHRAZ6NK1TWRPs0XV.JZSckVgHCzSPgR0.t4w6ADs04r4e5Y.', NULL, 'default-avatar.png', 1, '2026-05-06 16:37:44'),
(6, 'E', 'a@gmail.com', '01235345678', '$2y$10$0jk.VLIqXCbSJI97VcYo0uzoBh1a77yFsAQfWbkdcnSydMg2zzT0W', NULL, 'default-avatar.png', 1, '2026-05-06 16:44:43');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `favoriler`
--
ALTER TABLE `favoriler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_ilan_unique` (`user_id`,`ilan_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_ilan_id` (`ilan_id`);

--
-- Tablo için indeksler `ilanlar`
--
ALTER TABLE `ilanlar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ekleyen_id` (`ekleyen_id`),
  ADD KEY `marka_id` (`marka_id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `sehir_id` (`sehir_id`);

--
-- Tablo için indeksler `markalar`
--
ALTER TABLE `markalar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `mesajlar`
--
ALTER TABLE `mesajlar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `modeller`
--
ALTER TABLE `modeller`
  ADD PRIMARY KEY (`id`),
  ADD KEY `marka_id` (`marka_id`);

--
-- Tablo için indeksler `sehirler`
--
ALTER TABLE `sehirler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `telefon` (`telefon`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `favoriler`
--
ALTER TABLE `favoriler`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Tablo için AUTO_INCREMENT değeri `ilanlar`
--
ALTER TABLE `ilanlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Tablo için AUTO_INCREMENT değeri `markalar`
--
ALTER TABLE `markalar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Tablo için AUTO_INCREMENT değeri `mesajlar`
--
ALTER TABLE `mesajlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `modeller`
--
ALTER TABLE `modeller`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Tablo için AUTO_INCREMENT değeri `sehirler`
--
ALTER TABLE `sehirler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `favoriler`
--
ALTER TABLE `favoriler`
  ADD CONSTRAINT `fk_favori_ilan` FOREIGN KEY (`ilan_id`) REFERENCES `ilanlar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_favori_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `ilanlar`
--
ALTER TABLE `ilanlar`
  ADD CONSTRAINT `ilanlar_ibfk_1` FOREIGN KEY (`ekleyen_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ilanlar_ibfk_2` FOREIGN KEY (`marka_id`) REFERENCES `markalar` (`id`),
  ADD CONSTRAINT `ilanlar_ibfk_3` FOREIGN KEY (`model_id`) REFERENCES `modeller` (`id`),
  ADD CONSTRAINT `ilanlar_ibfk_4` FOREIGN KEY (`sehir_id`) REFERENCES `sehirler` (`id`);

--
-- Tablo kısıtlamaları `modeller`
--
ALTER TABLE `modeller`
  ADD CONSTRAINT `modeller_ibfk_1` FOREIGN KEY (`marka_id`) REFERENCES `markalar` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
