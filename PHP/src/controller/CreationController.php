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

    /*
    public function traiterFormItem(Request $rq, Response $rs, $args):Response
        {
            $item =\mywishlist\models\Item::where('id', '=', $args['id'])->first();
            $data = $rq->getParsedBody();
            $nom = filter_var($data['nom'], FILTER_SANITIZE_STRING);
            $description = filter_var($data['description'], FILTER_SANITIZE_STRING);
            $tarif = filter_var($data['tarif'], FILTER_SANITIZE_NUMBER_FLOAT);
            $listeID = filter_var($data['idListe'], FILTER_SANITIZE_NUMBER_INT);
            $item->nom =$nom;
            $item->description = $description;
            $item->tarif = $tarif;
            $item->idListe = $listeID;
            $vue = new VueCreation([$liste->toArray()], $this->container) ;
            $html = $vue->render(6);
            $rs->getBody()->write($html);
            return $rs;
        }
    */

    public function afficherReservationItem(Request $rq, Response $rs, $args):Response
    {
        $vue = new VueCreation([], $this->container);
        $html = $vue->render(3) ;
        $rs->getBody()->write($html);
        return $rs;
    }
}
