-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

TRUNCATE TABLE `theme_sections`;
TRUNCATE TABLE `theme_positions`;
TRUNCATE TABLE `theme_types`;
TRUNCATE TABLE `vehicles_models`;

INSERT INTO `dealers` (`id`, `title`, `enabled`, `created`, `updated`) VALUES
(1,	'Default', 1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31');

INSERT INTO `theme_sections` (`id`, `route`, `title`, `template`, `enabled`, `created`, `updated`) VALUES
(1,	'/',	'Inicio',	NULL,	1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31'),
(2,	'/noticias',	'Noticias',	NULL,	1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31'),
(3,	'/vans',	'Vans',	NULL,	1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31'),
(4,	'/camiones',	'Camiones',	NULL,	1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31'),
(5,	'/autos',	'Autos',	NULL,	1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31');

INSERT INTO `theme_positions` (`id`, `title`, `enabled`, `created`, `updated`) VALUES
(1,	'Slide',	1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31'),
(2,	'Body',	1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31'),
(3,	'Sidebar',		1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31');

INSERT INTO `theme_types` (`id`, `title`, `enabled`, `created`, `updated`) VALUES
(1,	'Vans',	1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31'),
(2,	'Camiones',	1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31'),
(3,	'Autos',		1,	'2018-07-17 11:26:31',	'2018-07-17 11:26:31');

INSERT INTO `vehicles_models` (`id`, `title`, `enabled`, `created`, `updated`) VALUES
(281,	'A 45',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(282,	'A 160',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(283,	'A 190',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(284,	'A 200',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(285,	'A 250',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(286,	'B 170',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(287,	'B 180',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(288,	'B 200',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(289,	'C 63',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(290,	'C 200',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(291,	'C 220',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(292,	'C 230',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(293,	'C 240',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(294,	'C 250',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(295,	'C 270',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(296,	'C 280',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(297,	'C 300',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(298,	'C 320',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(299,	'C 350',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(300,	'CL 55',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(301,	'CL 500',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(302,	'CLA 45',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(303,	'CLA 200',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(304,	'CLA 250',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(305,	'CLC 230',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(306,	'CLC 250',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(307,	'CLC 350',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(308,	'CLK 55',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(309,	'CLK 230',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(310,	'CLK 320',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(311,	'CLK 350',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(312,	'CLK 430',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(313,	'CLK 500',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(314,	'CLS 55',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(315,	'CLS 63',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(316,	'CLS 350',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(317,	'CLS 500',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(318,	'E 55',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(319,	'E 63',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(320,	'E 250',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(321,	'E 270',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(322,	'E 280',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(323,	'E 300',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(324,	'E 320',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(325,	'E 350',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(326,	'E 400',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(327,	'E 430',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(328,	'E 500',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(329,	'GL 500',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(330,	'GLA 200',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(331,	'GLA 250',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(332,	'GLC 300',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(333,	'GLE 400',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(334,	'GLK 300',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(335,	'MERCEDES-AMG GT s',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(336,	'ML',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(337,	'S 320',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(338,	'S 400',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(339,	'S 500',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(340,	'SL 65',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(341,	'SL 500',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(342,	'SLK',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(343,	'SLS',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(344,	'SPRINTER',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(345,	'VIANO',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(346,	'VITO',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(347,	'VITO 111',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56'),
(348,	'VITO 119',	1,	'2017-01-26 14:39:56',	'2017-01-26 14:39:56');


-- 2018-04-08 16:31:31