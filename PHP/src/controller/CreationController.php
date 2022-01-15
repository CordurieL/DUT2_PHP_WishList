<?php

namespace mywishlist\controller;

use mywishlist\models\Item;
use mywishlist\models\Liste as Liste;
use mywishlist\models\Compte;
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

    // Inscription
    public function traiterFormInscription(Request $rq, Response $rs, $args):Response
    {
        $data = $rq->getParsedBody();
        $pseudo = filter_var($data['pseudo'], FILTER_SANITIZE_STRING);
        $pass = password_hash(filter_var($data['pass'], FILTER_SANITIZE_STRING), PASSWORD_BCRYPT);
        $compte = new Compte();
        $compte->pseudo = $pseudo;
        $compte->pass = $pass;
        $compte->save();
        $vue = new VueCreation([$compte->toArray()], $this->container);
        $html = $vue->render(8);
        $rs->getBody()->write($html);
        return $rs;
    }
    
    // à compléter
    public function registerForm()
    {
    }
}
