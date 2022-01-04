<?php
declare(strict_types=1);

namespace mywishlist\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AffichageController
{
    private \Slim\Container $container;

    public function __construct(\Slim\Container $container)
    {
        $this->container = $container;
    }

    public function afficherAcceuil(Request $rq, Response $rs, $args):Response
    {
        $listes = \mywishlist\models\Liste::all() ;
        $vue = new \mywishlist\vue\VueParticipant($listes->toArray(), $this->container) ;
        $html = $vue->render(0) ;
       
        $rs->getBody()->write($html);
        return $rs;
    }

    public function afficherListes(Request $rq, Response $rs, $args):Response
    {
        $listes = \mywishlist\models\Liste::all() ;
        $vue = new \mywishlist\vue\VueParticipant($listes->toArray(), $this->container) ;
        $html = $vue->render(1) ;
       
        $rs->getBody()->write($html);
        return $rs;
    }

    public function partageUneListe(Request $rq, Response $rs, $args):Response
    {
        //$liste = \mywishlist\models\Liste::find($args['noListe']);
        $liste = \mywishlist\models\Liste::where('token', '=', $args['token'])->first();
        $vue = new \mywishlist\vue\VueParticipant([$liste->toArray(),$liste->items->toArray()], $this->container) ;
        $html = $vue->render(2) ;
        $rs->getBody()->write($html);
        return $rs;
    }

    public function afficherUneListe(Request $rq, Response $rs, $args):Response
    {
        $liste =\mywishlist\models\Liste::where('token', '=', $args['token'])->first();
        $vue = new \mywishlist\vue\VueParticipant([$liste->toArray(),$liste->items->toArray()], $this->container) ;
        $html = $vue->render(2) ;
        $rs->getBody()->write($html);
        return $rs;
    }

    public function afficherUnItem(Request $rq, Response $rs, $args):Response
    {
        $item = \mywishlist\models\Item::find($args['id']) ;
        $liste = $item->liste;
        $tokenListe = $liste->token;
        if ($tokenListe === $args['token']) {
            $vue = new \mywishlist\vue\VueParticipant([ $item ], $this->container) ;
            $html = $vue->render(3) ;
        } else {
            $vue = new \mywishlist\vue\VueParticipant([ $item ], $this->container) ;
            $html = $vue->render(0) ; // retourne à l'accueil
        }

        $rs->getBody()->write($html);
        return $rs;
    }
}
