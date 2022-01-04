<?php
declare(strict_types=1);
require 'vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$configuration = [
    'settings' => [
    'displayErrorDetails' => true,
   'dbconf' => '/conf/db.conf.ini' ]
   ];
   $c = new \Slim\Container($configuration);
   $app = new \Slim\App($c);

use \Illuminate\Database\Capsule\Manager as DB;

$db = new DB();
$db->addConnection(parse_ini_file('src/conf/conf.ini'));
$db->setAsGlobal();
$db->bootEloquent();


$app->get(
    '/',
    'mywishlist\controller\AffichageController:afficherAcceuil'
)->setName('acceuil');

$app->get(
    '/test',
    function ($rq, $rs, $args) {
        echo 'test';
    }
)->setName('test');

$app->get(
    '/listes',
    'mywishlist\controller\AffichageController:afficherListes'
)->setName('listeDesListes');

$app->get(
    '/liste/{token}',
    'mywishlist\controller\AffichageController:afficherUneListe'
)->setName('affUneListe');

$app->get(
    '/partageliste/{token}',
    'mywishlist\controller\AffichageController:partageUneListe'
)->setName('partUneListe');

$app->get('/newliste', 'mywishlist\controller\CreationController:afficherFormulaire')->setName('affForm');
//$app->get('/new','mywishlist\controller\AffichageController:afficherListes')->setName('affForm');

/*
$app->get(
    '/new',
    function ($rq, $rs, $args) {
        $c = new \mywishlist\controller\CreationController($this);
        return $c->afficherFormulaire($rq, $rs, $args);
    }
)->setName('affForm');
*/

$app->post('/newliste', 'mywishlist\controller\CreationController:traiterFormListe')->setName('traitForm');


$app->get(
    '/liste/{token}/item/{id}',
    function ($rq, $rs, $args) {
        $c = new \mywishlist\controller\AffichageController($this);
        return $c->afficherUnItem($rq, $rs, $args);
    }
)->setName('affUnItem');

$app->run();
