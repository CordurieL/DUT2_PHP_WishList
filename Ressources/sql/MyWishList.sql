SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `liste_id` int(11) NOT NULL,
  `nom` text NOT NULL,
  `descr` text,
  `img` text,
  `url` text,
  `tarif` decimal(5,2) NOT NULL,
  `tarif_restant` decimal(5,2) DEFAULT `tarif`,
  `nomReservation` text DEFAULT NULL,
  `messageReservation` text DEFAULT NULL,
  `estUneCagnotte` boolean DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `item` (`id`, `liste_id`, `nom`, `descr`, `img`, `url`, `tarif`) VALUES
(1,    2,    'Champagne',    'Bouteille de champagne + flutes + jeux à gratter',    'champagne.jpg',    '',    20.00),
(2,    2,    'Musique',    'Partitions de piano à 4 mains',    'musique.jpg',    '',    25.00),
(3,    2,    'Exposition',    'Visite guidée de l’exposition ‘REGARDER’ à la galerie Poirel',    'poirelregarder.jpg',    '',    14.00),
(4,    3,    'Goûter',    'Goûter au FIFNL',    'gouter.jpg',    '',    20.00),
(5,    3,    'Projection',    'Projection courts-métrages au FIFNL',    'film.jpg',    '',    10.00),
(6,    2,    'Bouquet',    'Bouquet de roses et Mots de Marion Renaud',    'rose.jpg',    '',    16.00),
(7,    2,    'Diner Stanislas',    'Diner à La Table du Bon Roi Stanislas (Apéritif /Entrée / Plat / Vin / Dessert / Café / Digestif)',    'bonroi.jpg',    '',    60.00),
(8,    3,    'Origami',    'Baguettes magiques en Origami en buvant un thé',    'origami.jpg',    '',    12.00),
(9,    3,    'Livres',    'Livre bricolage avec petits-enfants + Roman',    'bricolage.jpg',    '',    24.00),
(10,    2,    'Diner  Grand Rue ',    'Diner au Grand’Ru(e) (Apéritif / Entrée / Plat / Vin / Dessert / Café)',    'grandrue.jpg',    '',    59.00),
(11,    0,    'Visite guidée',    'Visite guidée personnalisée de Saint-Epvre jusqu’à Stanislas',    'place.jpg',    '',    11.00),
(12,    2,    'Bijoux',    'Bijoux de manteau + Sous-verre pochette de disque + Lait après-soleil',    'bijoux.jpg',    '',    29.00),
(19,    0,    'Jeu contacts',    'Jeu pour échange de contacts',    'contact.png',    '',    5.00),
(22,    0,    'Concert',    'Un concert à Nancy',    'concert.jpg',    '',    17.00);
INSERT INTO `item` (`id`, `liste_id`, `nom`, `descr`, `img`, `url`, `tarif`, `estUneCagnotte`) VALUES
(23,    1,    'Appart Hotel',    'Appart’hôtel Coeur de Ville, en plein centre-ville',    'apparthotel.jpg',    '',    56.00, 1);
INSERT INTO `item` (`id`, `liste_id`, `nom`, `descr`, `img`, `url`, `tarif`) VALUES
(24,    2,    'Hôtel d\'Haussonville',    'Hôtel d\'Haussonville, au coeur de la Vieille ville à deux pas de la place Stanislas',    'hotel_haussonville_logo.jpg',    '',    169.00),
(25,    1,    'Boite de nuit',    'Discothèque, Boîte tendance avec des soirées à thème & DJ invités',    'boitedenuit.jpg',    '',    32.00),
(26,    1,    'Planètes Laser',    'Laser game : Gilet électronique et pistolet laser comme matériel, vous voilà équipé.',    'laser.jpg',    '',    15.00),
(27,    1,    'Fort Aventure',    'Découvrez Fort Aventure à Bainville-sur-Madon, un site Accropierre unique en Lorraine ! Des Parcours Acrobatiques pour petits et grands, Jeu Mission Aventure, Crypte de Crapahute, Tyrolienne, Saut à l\'élastique inversé, Toboggan géant... et bien plus encore.',    'fort.jpg',    '',    25.00);

DROP TABLE IF EXISTS `liste`;
CREATE TABLE `liste` (
  `no` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `expiration` date DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `token_edition` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `valide` boolean not null default 0,
  PRIMARY KEY (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `liste` (`no`, `user_id`, `titre`, `description`, `expiration`, `token`, `valide`) VALUES
(1,    1,    'Pour fêter le bac !',    'Pour un week-end à Nancy qui nous fera oublier les épreuves',    '2018-06-27',    'nosecure1', '1'),
(2,    2,    'Liste de mariage d\'Alice et Bob',    'Nous souhaitons passer un week-end royal à Nancy pour notre lune de miel :)',    '2018-06-30',    'nosecure2', '1'),
(3,    3,    'C\'est l\'anniversaire de Charlie',    'Pour lui préparer une fête dont il se souviendra :)',    '2017-12-12',    'nosecure3', '1');

DROP TABLE IF EXISTS `message`;
CREATE TABLE `message` (
  `no_mess` int(11) NOT NULL AUTO_INCREMENT,
  `liste_id` int(11) NOT NULL,
  `contenu` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`no_mess`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `message` (`no_mess`, `liste_id`, `contenu`) VALUES
(1,1,'First'),
(2,1,'Bonjour je prendrai la corde'),
(3,1,'Allo ?');

DROP TABLE IF EXISTS `compte`;
CREATE TABLE `compte` (
  `no_compte` int(11) NOT NULL AUTO_INCREMENT,
  `pseudo` text NOT NULL,
  `pass` text NOT NULL,
  PRIMARY KEY(`no_compte`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `participation`;
CREATE TABLE `participation` (
  `id_participation` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `nomParticipation` text NOT NULL,
  `messageParticipation` text DEFAULT NULL,
  `contribution` decimal(5,2) NOT NULL,
  PRIMARY KEY(`id_participation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `participation` (`item_id`, `nomParticipation`, `messageParticipation`, `contribution`) VALUES
(23, 'Pierre', 'Cadeau super coolos bah ouai',1.52),
(23, 'Paul', 'Pas radin pour un sou',25.00);

