-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Май 15 2026 г., 21:24
-- Версия сервера: 8.0.30
-- Версия PHP: 8.0.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `demo`
--

-- --------------------------------------------------------

--
-- Структура таблицы `auction_bids`
--

CREATE TABLE `auction_bids` (
  `id` int UNSIGNED NOT NULL,
  `lot_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `bid_amount` int NOT NULL,
  `bid_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `auction_categories`
--

CREATE TABLE `auction_categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `auction_categories`
--

INSERT INTO `auction_categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Антиквариат и Искусство', 'antiques', '2026-04-18 10:36:56'),
(2, 'Букинистика (Книги)', 'books', '2026-04-18 10:36:56'),
(3, 'Виниловые пластинки', 'vinyl', '2026-04-18 10:36:56'),
(4, 'Военные вещи', 'military', '2026-04-18 10:36:56'),
(5, 'Монеты (Нумизматика)', 'coins', '2026-04-18 10:36:56'),
(6, 'Часы (Наручные, Каминные)', 'watches', '2026-04-18 10:36:56'),
(7, 'Игрушки и Игры', 'toys', '2026-04-18 10:36:56'),
(8, 'Ювелирные изделия', 'jewelry', '2026-04-18 10:36:56');

-- --------------------------------------------------------

--
-- Структура таблицы `auction_lots`
--

CREATE TABLE `auction_lots` (
  `id` int UNSIGNED NOT NULL,
  `category_id` int UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_price` int NOT NULL,
  `current_price` int NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `end_time` datetime NOT NULL,
  `views` int DEFAULT '0',
  `status` enum('active','sold','expired') DEFAULT 'active',
  `seller_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `auction_lots`
--

INSERT INTO `auction_lots` (`id`, `category_id`, `title`, `description`, `start_price`, `current_price`, `image`, `end_time`, `views`, `status`, `seller_id`, `created_at`) VALUES
(1, 5, 'Памятная монета \"300 лет флоту\"', 'Серебро 925 пробы, состояние UNC. Оригинал в капсуле. Редкий экземпляр.', 5000, 7400, NULL, '2026-04-18 15:36:56', 3, 'active', 1, '2026-04-18 10:36:56'),
(2, 1, 'Статуэтка \"Балерина\", ЛФЗ', 'Фарфор, ручная роспись. Без сколов и реставраций. Клеймо завода.', 8000, 12000, NULL, '2026-04-18 18:36:56', 1, 'active', 1, '2026-04-18 10:36:56'),
(3, 2, 'Набор марок Гвинеи (1960-е)', 'Полная серия в отличном состоянии. Гашеные. Редкость!', 300, 450, NULL, '2026-04-19 13:36:56', 1, 'active', 2, '2026-04-18 10:36:56'),
(4, 6, 'Антикварные карманные часы', 'Швейцария, 1920-е годы. Механизм работает отлично.', 15000, 15000, NULL, '2026-04-21 13:36:56', 0, 'active', 1, '2026-04-18 10:36:56'),
(5, 4, 'Орден Красной Звезды', 'Оригинал, послевоенный выпуск. Все детали на месте.', 3500, 3500, NULL, '2026-04-20 13:36:56', 1, 'active', 2, '2026-04-18 10:36:56'),
(6, 8, 'Кольцо с бриллиантом', 'Золото 585 пробы, бриллиант 0.25 карата.', 25000, 25000, NULL, '2026-04-23 13:36:56', 0, 'active', 1, '2026-04-18 10:36:56');

-- --------------------------------------------------------

--
-- Структура таблицы `courses`
--

CREATE TABLE `courses` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `content` text,
  `image` varchar(255) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `content`, `image`, `duration`, `created_at`) VALUES
(1, 'Современный HTML/CSS', 'Сетки, флексбоксы, семантика. Идеальная основа для любых проектов.', '<h4>Введение в HTML</h4><p>Полный курс по верстке</p>', NULL, '48 часов', '2026-04-18 10:36:56'),
(2, 'Динамика и взаимодействие', 'Подготовка к отправке данных на сервер, работа с формами и событиями.', '<h4>JavaScript</h4><p>Все о JS</p>', NULL, '32 урока', '2026-04-18 10:36:56'),
(3, 'Бэкенд-интеграция', 'Подключение базы данных, безопасность, миграция с HTML на динамику.', '<h4>PHP + MySQL</h4><p>Полный стек</p>', NULL, 'Готово к PDO', '2026-04-18 10:36:56');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `message`, `created_at`) VALUES
(1, 'admin', 'admin@demo.ru', '$2y$10$y9.SUc.HKB18WFr3Kveg/OAcpGiaEB912Wq7jWbmYhwJGFkkP9KoK', NULL, '2026-04-18 10:36:56'),
(2, 'test_user', 'test@demo.ru', '$2y$10$y9.SUc.HKB18WFr3Kveg/OAcpGiaEB912Wq7jWbmYhwJGFkkP9KoK', NULL, '2026-04-18 10:36:56');

-- --------------------------------------------------------

--
-- Структура таблицы `user_courses`
--

CREATE TABLE `user_courses` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `auction_bids`
--
ALTER TABLE `auction_bids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lot_id` (`lot_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `auction_categories`
--
ALTER TABLE `auction_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Индексы таблицы `auction_lots`
--
ALTER TABLE `auction_lots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Индексы таблицы `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `user_courses`
--
ALTER TABLE `user_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_course` (`user_id`,`course_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `auction_bids`
--
ALTER TABLE `auction_bids`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `auction_categories`
--
ALTER TABLE `auction_categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `auction_lots`
--
ALTER TABLE `auction_lots`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `user_courses`
--
ALTER TABLE `user_courses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `auction_bids`
--
ALTER TABLE `auction_bids`
  ADD CONSTRAINT `auction_bids_ibfk_1` FOREIGN KEY (`lot_id`) REFERENCES `auction_lots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `auction_bids_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `auction_lots`
--
ALTER TABLE `auction_lots`
  ADD CONSTRAINT `auction_lots_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `auction_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `auction_lots_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
