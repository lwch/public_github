CREATE TABLE `followers`(
    `id` int(11) NOT NULL,
    `ref` int(11) NOT NULL,
    PRIMARY KEY(`id`, `ref`)
)Engine=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
