CREATE TABLE `pushed_log`(
    `id` int(11) NOT NULL,
    `src_id` int(11) NOT NULL,
    `pushed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `full_name` varchar(150) NOT NULL,
    `language` varchar(100),
    `forks_cnt` int(11) DEFAULT 0,
    `stars_cnt` int(11) DEFAULT 0,
    `watch_cnt` int(11) DEFAULT 0,
    `rank` int(11),
    KEY `idx_id`(`id`),
    KEY `idx_src`(`src_id`, `pushed`),
    KEY `idx_fullname`(`full_name`),
    KEY `idx_language`(`language`),
    KEY `idx_forks_cnt`(`forks_cnt`),
    KEY `idx_stars_cnt`(`stars_cnt`),
    KEY `idx_watch_cnt`(`watch_cnt`),
    KEY `idx_rank`(`rank`)
)Engine=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
