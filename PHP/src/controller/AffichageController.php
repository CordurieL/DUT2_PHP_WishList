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



    public function afficherUneListe(Request $rq, Response $rs, $args):Response
    {
        $liste =\mywishlist\models\Liste::where('token', '=', $args['token'])->first();
        /* Pour les messages dans les listes */
        $data = $rq->getParsedBody();
        if (isset($data['contenu'])) {
            $contenuMessage = filter_var($data['contenu'], FILTER_SANITIZE_STRING);
            $messageLength = strlen((String) (preg_replace("/\s\s+/", "", $contenuMessage)));
            if ($contenuMessage != "" && $contenuMessage != null && $messageLength >0) {
                $message = new \mywishlist\models\Message();
                $message->contenu = $contenuMessage;
                $message->liste_id = $liste->no;
                $message->save();
            }
            //redirect
            $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
        } else {
            $vue = new \mywishlist\vue\VueParticipant([$liste->toArray(),$liste->items->toArray(),$liste->messages->toArray()], $this->container) ;
            $html = $vue->render(2) ;
        }
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
