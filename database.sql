-- ============================================================
--  RHEMA SOUNDS – MySQL Database Schema
--  Import with: mysql -u root -p rhema_sounds < database.sql
--  Or paste into phpMyAdmin's SQL tab
-- ============================================================

CREATE DATABASE IF NOT EXISTS `rhema_sounds`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `rhema_sounds`;

-- ---- TRACKS ----
CREATE TABLE IF NOT EXISTS `tracks` (
  `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255)     NOT NULL,
  `artist`      VARCHAR(255)     NOT NULL,
  `album`       VARCHAR(255)     NOT NULL DEFAULT '',
  `genre`       VARCHAR(100)     NOT NULL DEFAULT 'Gospel',
  `duration`    VARCHAR(20)      NOT NULL DEFAULT '0:00',
  `filename`    VARCHAR(255)     NOT NULL,
  `color`       VARCHAR(20)      NOT NULL DEFAULT '#1a3a2a',
  `lyrics`      TEXT,
  `uploaded_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_artist` (`artist`),
  INDEX `idx_genre`  (`genre`),
  FULLTEXT INDEX `ft_search` (`title`, `artist`, `album`, `genre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- LIVE STREAMS ----
CREATE TABLE IF NOT EXISTS `live_streams` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255)  NOT NULL DEFAULT '',
  `host`       VARCHAR(255)  NOT NULL DEFAULT '',
  `viewers`    VARCHAR(50)   NOT NULL DEFAULT '0',
  `type`       ENUM('youtube','facebook','hls','radio') NOT NULL DEFAULT 'radio',
  `src`        TEXT,
  `active`     TINYINT(1)    NOT NULL DEFAULT 0,
  `sort_order` INT           NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  ADMIN-DRIVEN CONTENT TABLES (frontend hardcoded-content migration)
-- ============================================================

-- ---- VIDEOS ----
CREATE TABLE IF NOT EXISTS `videos` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255)  NOT NULL,
  `channel`     VARCHAR(255)  NOT NULL DEFAULT '',
  `duration`    VARCHAR(20)   NOT NULL DEFAULT '0:00',
  `type`        ENUM('youtube','hls','mp4') NOT NULL DEFAULT 'youtube',
  `src`         TEXT,
  `thumb`       VARCHAR(20)   NOT NULL DEFAULT '🎬',
  `description` TEXT,
  `sort_order`  INT           NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `videos` (`title`, `channel`, `duration`, `type`, `src`, `thumb`, `description`, `sort_order`) VALUES
  ('Rhema Sunday Service – Live Worship', 'Rhema Sounds Official', '1:24:00', 'youtube', 'https://www.youtube.com/embed/live_stream?channel=UCrhema', '🎬', 'Full Sunday worship service recorded live at Rhema Church.', 1),
  ('Way Maker – Official Music Video',    'Rhema All-Stars',       '7:21',    'youtube', 'https://www.youtube.com/embed/dQw4w9WgXcQ',                 '🎵', 'Official music video for Way Maker by Rhema All-Stars.', 2),
  ('Oceans – Live Worship Night',         'Rhema Worship Team',    '12:44',   'youtube', 'https://www.youtube.com/embed/dy9nwe9_xzw',                 '🌊', 'Live performance of Oceans at the annual worship night.', 3),
  ('Christmas Hallelujah Chorus',         'Rhema Mass Choir',      '8:00',    'youtube', 'https://www.youtube.com/embed/IUZEtVbJT5c',                 '🎶', 'The iconic Hallelujah Chorus performed by the mass choir.', 4),
  ('Battle Belongs – Acoustic Session',   'Rhema Warriors',        '5:30',    'youtube', 'https://www.youtube.com/embed/dQw4w9WgXcQ',                 '⚔️', 'Intimate acoustic version of Battle Belongs.', 5),
  ('Gospel Night – Full Concert',         'Rhema Sounds Choir',    '2:10:00', 'youtube', 'https://www.youtube.com/embed/M7lc1UVf-VE',                 '🙌', 'Full gospel night concert, featuring all Rhema artists.', 6);

-- ---- GENRES ----
CREATE TABLE IF NOT EXISTS `genres` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)  NOT NULL,
  `color`      VARCHAR(20)   NOT NULL DEFAULT '#1db954',
  `emoji`      VARCHAR(10)   NOT NULL DEFAULT '🎵',
  `sort_order` INT           NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_genre_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `genres` (`name`, `color`, `emoji`, `sort_order`) VALUES
  ('Gospel',       '#e65c00', '🎵', 1),
  ('Worship',      '#1db954', '🕊️', 2),
  ('Contemporary', '#509bf5', '🎸', 3),
  ('Classical',    '#e8b84b', '🎻', 4),
  ('Hymns',        '#a259ff', '📖', 5),
  ('Praise',       '#e8125c', '🙌', 6),
  ('Live',         '#e87c13', '🔴', 7),
  ('Christmas',    '#1e3264', '🎄', 8);

-- ---- FEATURED BANNERS (homepage hero slides) ----
CREATE TABLE IF NOT EXISTS `featured_banners` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `title`         VARCHAR(255)  NOT NULL,
  `subtitle`      VARCHAR(255)  NOT NULL DEFAULT '',
  `gradient`      VARCHAR(255)  NOT NULL DEFAULT 'linear-gradient(135deg,#1a3a2a,#0d7a3a)',
  `emoji`         VARCHAR(10)   NOT NULL DEFAULT '🎵',
  `action_type`   ENUM('track','playlist','page','url') NOT NULL DEFAULT 'page',
  `action_target` VARCHAR(255)  NOT NULL DEFAULT 'home',
  `sort_order`    INT           NOT NULL DEFAULT 0,
  `active`        TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `idx_active_sort` (`active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `featured_banners` (`title`, `subtitle`, `gradient`, `emoji`, `action_type`, `action_target`, `sort_order`, `active`) VALUES
  ('Sunday Service LIVE', 'Stream now',          'linear-gradient(135deg,#3a0000,#800000)', '🔴', 'page',     'live', 1, 1),
  ('Way Maker',           'Rhema All-Stars',     'linear-gradient(135deg,#1a3a2a,#0d7a3a)', '✨', 'track',    '6',    2, 1),
  ('Worship Anthems',     'Playlist · 4 songs',  'linear-gradient(135deg,#001a3a,#005080)', '🕊️', 'playlist', 'pl1',  3, 1),
  ('Oceans',              'Rhema Worship Team',  'linear-gradient(135deg,#001a4a,#0040a0)', '🌊', 'track',    '4',    4, 1),
  ('Gospel Fire',         'Playlist · 4 songs',  'linear-gradient(135deg,#3a1a00,#a03000)', '🔥', 'playlist', 'pl2',  5, 1);

-- ---- PLAYLISTS ----
CREATE TABLE IF NOT EXISTS `playlists` (
  `id`          VARCHAR(64)   NOT NULL,
  `name`        VARCHAR(255)  NOT NULL,
  `description` VARCHAR(500)  NOT NULL DEFAULT '',
  `emoji`       VARCHAR(10)   NOT NULL DEFAULT '🎵',
  `is_liked`    TINYINT(1)    NOT NULL DEFAULT 0,
  `sort_order`  INT           NOT NULL DEFAULT 0,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `playlist_tracks` (
  `playlist_id` VARCHAR(64)   NOT NULL,
  `track_id`    INT UNSIGNED  NOT NULL,
  `sort_order`  INT           NOT NULL DEFAULT 0,
  PRIMARY KEY (`playlist_id`, `track_id`),
  INDEX `idx_pt_track` (`track_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `playlists` (`id`, `name`, `description`, `emoji`, `is_liked`, `sort_order`) VALUES
  ('pl1', 'Worship Anthems', 'The best worship songs', '🕊️', 0, 1),
  ('pl2', 'Gospel Fire',     'High energy gospel',     '🔥', 0, 2),
  ('pl3', 'Liked Songs',     'All your liked songs',   '💚', 1, 3);

-- ---- BROADCAST SCHEDULE ----
CREATE TABLE IF NOT EXISTS `broadcast_schedule` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `day`        VARCHAR(20)   NOT NULL,
  `time_range` VARCHAR(50)   NOT NULL DEFAULT '',
  `show_name`  VARCHAR(255)  NOT NULL DEFAULT '',
  `host`       VARCHAR(255)  NOT NULL DEFAULT '',
  `sort_order` INT           NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_sched_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `broadcast_schedule` (`day`, `time_range`, `show_name`, `host`, `sort_order`) VALUES
  ('Sunday',    '9:00 AM – 1:00 PM',   'Morning Worship Service', 'Full Choir',         1),
  ('Monday',    '6:00 PM – 7:00 PM',   'Evening Devotional',      'Praise Team',        2),
  ('Wednesday', '7:00 PM – 9:00 PM',   'Midweek Gospel Show',     'DJ Grace',           3),
  ('Friday',    '8:00 PM – 10:00 PM',  'Praise Night',            'Rhema Youth',        4),
  ('Saturday',  '10:00 AM – 12:00 PM', 'Gospel Requests',         'Pastor Emmanuel',    5);

-- ---- SITE SETTINGS (key/value) ----
CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key`   VARCHAR(100)  NOT NULL,
  `setting_value` TEXT,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
  ('site_name',          'Rhema Sounds'),
  ('site_tagline',       'Your daily dose of Rhema Sounds'),
  ('site_title',         'Rhema Sounds – Stream Music & Video'),
  ('logo_emoji',         '🎵'),
  ('default_profile_name','Rhema Listener'),
  ('share_base_url',     ''),
  ('auto_approve_tracks','1');

-- ============================================================
--  USER ACCOUNTS (public sign-up / sign-in, separate from admin)
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(255)  NOT NULL,
  `email`         VARCHAR(255)  NOT NULL,
  `password_hash` VARCHAR(255)  NULL,
  `google_id`     VARCHAR(64)   NULL,
  `avatar`        VARCHAR(255)  NOT NULL DEFAULT '',
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_email` (`email`),
  UNIQUE KEY `uniq_user_google_id` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- LIKED TRACKS (per-user) ----
CREATE TABLE IF NOT EXISTS `user_likes` (
  `user_id`    INT UNSIGNED  NOT NULL,
  `track_id`   INT UNSIGNED  NOT NULL,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `track_id`),
  INDEX `idx_user_likes_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- USER-CREATED PLAYLISTS ----
CREATE TABLE IF NOT EXISTS `user_playlists` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED  NOT NULL,
  `name`        VARCHAR(255)  NOT NULL,
  `description` VARCHAR(500)  NOT NULL DEFAULT '',
  `emoji`       VARCHAR(10)   NOT NULL DEFAULT '🎵',
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user_playlists_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_playlist_tracks` (
  `playlist_id` INT UNSIGNED  NOT NULL,
  `track_id`    INT UNSIGNED  NOT NULL,
  `sort_order`  INT           NOT NULL DEFAULT 0,
  PRIMARY KEY (`playlist_id`, `track_id`),
  INDEX `idx_upt_track` (`track_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
