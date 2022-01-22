<?php
declare(strict_types=1);
namespace mywishlist;

session_start();
require 'vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Illuminate\Database\Capsule\Manager as DB;
use \Slim\Container;
use \Slim\App;

$configuration = [
    'settings' => [
    'displayErrorDetails' => true,
    'dbconf' => '/conf/db.conf.ini' ]
    ];
    $c = new Container($configuration);
    $app = new App($c);

$db = new DB();
$db->addConnection(parse_ini_file('src/conf/conf.ini'));
$db->setAsGlobal();
$db->bootEloquent();

/* Affiche l'accueil */
$app->get(
    '/',
    'mywishlist\controller\AffichageController:afficherAccueil'
)->setName('Accueil');

/* Mene a toutes les listes publiques et non expirees */
$app->get(
    '/listes',
    'mywishlist\controller\AffichageController:afficherListes'
)->setName('listeDesListes');

/* Mene a une liste */
$app->get(
    '/liste/{token}',
    'mywishlist\controller\AffichageController:afficherUneListe'
)->setName('affUneListe');

/* Pour tous les post dans une liste */
$app->post('/liste/{token}', 'mywishlist\controller\AffichageController:afficherUneListe')->setName('traitFormMessListe');

/* Mene a la creation d'une nouvelle liste */
$app->get('/newliste', 'mywishlist\controller\CreationController:afficherFormulaire')->setName('affForm');

/* page de confirmation de la creation d'une liste */
$app->post('/newliste', 'mywishlist\controller\CreationController:traiterFormListe')->setName('traitForm');

//inscription
$app->get('/inscription', 'mywishlist\controller\CreationController:afficherFormulaireInscription')->setName('inscription');
$app->post('/inscription', 'mywishlist\controller\CreationController:traiterFormInscription')->setName('compteCree');

//authentification
$app->get('/authentification', 'mywishlist\controller\CreationController:afficherFormulaireAuthentification')->setName('authentification');
$app->post('/authentification', 'mywishlist\controller\CreationController:traiterFormAuthentification')->setName('authentifie');

/* Mene a un item d'une liste */
$app->get(
    '/liste/{token}/item/{id}',
    function ($rq, $rs, $args) {
        $c = new \mywishlist\controller\AffichageController($this);
        return $c->afficherUnItem($rq, $rs, $args);
    }
)->setName('affUnItem');

/* pour tous les post d'un item */
$app->post('/liste/{token}/item/{id}', 'mywishlist\controller\AffichageController:afficherUnItem')->setName('traitReservation');

$app->run();
