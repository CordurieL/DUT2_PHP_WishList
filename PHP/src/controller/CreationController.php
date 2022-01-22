<?php
declare(strict_types=1);

namespace mywishlist\controller;

use \Slim\Container;
use mywishlist\models\Item;
use mywishlist\models\Liste;
use mywishlist\models\Compte;
use mywishlist\vue\VueCreation as VueCreation;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CreationController
{
    private Container $container;

    public function __construct(Container $container)
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

    public function afficherFormulaireInscription(Request $rq, Response $rs, $args):Response
    {
        $vue = new VueCreation([], $this->container);
        $html = $vue->render(8);
        $rs->getBody()->write($html);
        return $rs;
    }

    public function afficherFormulaireAuthentification(Request $rq, Response $rs, $args):Response
    {
        $vue = new VueCreation([], $this->container);
        $html = $vue->render(10);
        $rs->getBody()->write($html);
        return $rs;
    }
    // Inscription
    public function traiterFormInscription(Request $rq, Response $rs, $args):Response
    {
        if (filter_var($_POST['pass'], FILTER_SANITIZE_STRING) == filter_var($_POST['confirm_pass'], FILTER_SANITIZE_STRING)) {
            $pseudo = filter_var($_POST['pseudo'], FILTER_SANITIZE_STRING);
            $count = Compte::where('pseudo', $pseudo)->count();
            if ($count == 0) {
                $pass = password_hash(filter_var($_POST['pass'], FILTER_SANITIZE_STRING), PASSWORD_BCRYPT);
                $compte = new Compte();
                $compte->pseudo = $pseudo;
                $compte->pass = $pass;
                $compte->save();
                $vue = new VueCreation([$compte->toArray()], $this->container);
                $html = $vue->render(9);
                $rs->getBody()->write($html);
            } else {
                $vue = new VueCreation([], $this->container);
                $html = $vue->render(12);
                $rs->getBody()->write($html);
            }
        } else {
            $vue = new VueCreation([], $this->container);
            $html = $vue->render(13);
            $rs->getBody()->write($html);
        }
            
        return $rs;
    }
    
    public function traiterFormAuthentification(Request $rq, Response $rs, $args):Response
    {
        $pseudo = filter_var($_POST['pseudo'], FILTER_SANITIZE_STRING); //filtrage du pseudo
        $pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING); //filtrage et hashage du mot de passe
        $count1 = Compte::where('pseudo', $pseudo)->count();
        if ($count1 == 1) {
            $c = Compte::where('pseudo', $pseudo)->first();
            if (password_verify($pass, $c['pass'])) {
                $_SESSION['pseudo'] = $pseudo;
                $vue = new VueCreation([], $this->container);
                $html = $vue->render(11);
                $rs->getBody()->write($html);
            } else {
                $vue = new VueCreation([], $this->container);
                $html = $vue->render(14);
                $rs->getBody()->write($html);
            }
        } else {
            $vue = new VueCreation([], $this->container);
            $html = $vue->render(14);
            $rs->getBody()->write($html);;
        }
        return $rs;
    }
}
