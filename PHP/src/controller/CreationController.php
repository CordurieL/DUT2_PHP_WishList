<?php

namespace mywishlist\controller;

use mywishlist\models\Item;
use mywishlist\models\Liste as Liste;
use mywishlist\vue\VueCreation as VueCreation;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CreationController
{
    private \Slim\Container $container;

    public function __construct(\Slim\Container $container)
    {
        $this->container = $container;
    }

    public function afficherFormulaire(Request $rq, Response $rs, $args):Response
    {
        $vue = new VueCreation([], $this->container);
        $html = $vue->render(1) ;
        $rs->getBody()->write($html);
        return $rs;
    }

    public function traiterFormListe(Request $rq, Response $rs, $args):Response
    {
        $data = $rq->getParsedBody();
        $titre = filter_var($data['titre'], FILTER_SANITIZE_STRING);
        $description = filter_var($data['description'], FILTER_SANITIZE_STRING);
        $expiration = filter_var($data['expiration'], FILTER_SANITIZE_STRING);
        $liste = new Liste();
        $liste->titre = $titre;
        $liste->description = $description;
        $liste->expiration = $expiration;
        $liste->token = bin2hex(random_bytes(4));
        $liste->token_edition = bin2hex(random_bytes(4));
        $liste->user_id = 0;
        $liste->save();
        $vue = new VueCreation([$liste->toArray()], $this->container) ;
        $html = $vue->render(2);
        $rs->getBody()->write($html);
        return $rs;
    }

    public function afficherReservationItem(Request $rq, Response $rs, $args):Response
    {
        $vue = new VueCreation([], $this->container);
        $html = $vue->render(3) ;
        $rs->getBody()->write($html);
        return $rs;
    }

    public function traiterReservationItem(Request $rq, Response $rs, $args):Response
    {

        $data = $rq->getParsedBody();
        $nom = filter_var($data['nom'], FILTER_SANITIZE_STRING);
        $idItem = filter_var($data['idItem'], FILTER_SANITIZE_NUMBER_INT);
        $item = \mywishlist\models\Item::find($idItem);
        if(is_null($item->nomReservation)) {
            $item->nomReservation = $nom;
            $item->update();
            $vue = new VueCreation([$item->toArray()], $this->container);
            $html = $vue->render(4);
            $rs->getBody()->write($html);
        } else {
            $vue = new VueCreation([$item->toArray()], $this->container);
            $html = $vue->render(5);
            $rs->getBody()->write($html);
        }
        return $rs;
    }
}
