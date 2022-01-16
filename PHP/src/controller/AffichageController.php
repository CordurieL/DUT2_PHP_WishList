<?php
declare(strict_types=1);

namespace mywishlist\controller;

use mywishlist\vue\VueCreation as VueCreation;
use mywishlist\vue\VueParticipant;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AffichageController
{
    private \Slim\Container $container;

    public function __construct(\Slim\Container $container)
    {
        $this->container = $container;
    }

    public static function verifierChamp(mixed $chaine) :string|null
    {
        $res = null;
        if (is_string($chaine)) {
            $contenuChamp = filter_var($chaine, FILTER_SANITIZE_STRING);
            $contenuLength = strlen((String) (preg_replace("/\s\s+/", "", $contenuChamp)));
            if ($contenuChamp != "" && $contenuChamp != null && $contenuLength >0) {
                $res = $contenuChamp;
            }
        }
        return $res;
    }

    public function afficherAccueil(Request $rq, Response $rs, $args):Response
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
        /* Récupération des infos générales */
        $liste =\mywishlist\models\Liste::where('token', '=', $args['token'])->first();
        $tokenEdition = "$liste[token_edition]";
        $data = $rq->getParsedBody();
        /* Pour l'acces a la liste elle meme */
        if ((isset($_COOKIE["TokenEdition:".$tokenEdition])) || ("$liste[valide]" == 1)) {
            $dateDExp = (new \DateTime("$liste[expiration]"));
            /* Empecher l'accès a la liste après expiration pour les visiteurs */
            if (!(isset($_COOKIE["TokenEdition:".$tokenEdition])) && ((new \DateTime('NOW')) > $dateDExp)) {
                $vue = new \mywishlist\vue\VueParticipant([$liste->toArray(),$liste->items->toArray(),$liste->messages->toArray()], $this->container) ;
                $html = $vue->render(4) ;
            } else {
                /* Pour la validation d'une liste */
                if ((isset($data['publicationButton']))) {
                    $liste->valide = 1;
                    $liste->save();
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
                }
                /* Pour les modifications d'informations generales de la liste */
                if ((isset($data['editerTitre'])&&($this->verifierChamp($data['editerTitre']) != null))||((isset($data['editerDescr'])&&$this->verifierChamp($data['editerDescr']) !=null))||((isset($data['editerDateExp'])&&$this->verifierChamp($data['editerDateExp'])!=null))) {
                    if (($nouveauTitre = $this->verifierChamp($data['editerTitre'])) != null) {
                        $liste->titre = $nouveauTitre;
                    }
                    if (($nouvelleDescr = $this->verifierChamp($data['editerDescr'])) != null) {
                        $liste->description = $nouvelleDescr;
                    }
                    if (($nouvelleDateExp = $this->verifierChamp($data['editerDateExp'])) != null) {
                        $liste->expiration = $nouvelleDateExp;
                    }
                    $liste->save();
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
                }
                /* Pour creer un nouvel item dans une liste */
                if ((isset($data['creanom'])&&($this->verifierChamp($data['creanom']) != null))&&((isset($data['creadescription'])&&$this->verifierChamp($data['creadescription']) !=null))&&((isset($data['creatarif'])&&$this->verifierChamp($data['creatarif'])!=null))) {
                    $item = new \mywishlist\models\Item();
                    $item->save();
                    $nom = filter_var($data['creanom'], FILTER_SANITIZE_STRING);
                    $description = filter_var($data['creadescription'], FILTER_SANITIZE_STRING);
                    $types = [".jpg", ".png", ".gif", ".JPG", ".PNG", ".GIF"];
                    if (in_array(substr($_FILES['image']['name'], -4), $types)) {
                        $extension = substr($_FILES['image']['name'], -3);
                        move_uploaded_file($_FILES['image']['tmp_name'], "../Ressources/img/{$item->id}.{$extension}");
                    }
                    $item->img = "{$item->id}.{$extension}";
                    $tarif = filter_var($data['creatarif'], FILTER_SANITIZE_NUMBER_FLOAT);
                    $item->nom =$nom;
                    $item->descr = $description;
                    $item->tarif = $tarif;
                    $item->liste_id = $liste->no;
                    $vue = new VueCreation([$liste->toArray()], $this->container) ;
                    $item->save();
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
                }
                /* Pour les messages de liste */
                if (isset($data['contenu'])) {
                    $contenuMessage = filter_var($data['contenu'], FILTER_SANITIZE_STRING);
                    $messageLength = strlen((String) (preg_replace("/\s\s+/", "", $contenuMessage)));
                    if ($contenuMessage != "" && $contenuMessage != null && $messageLength >0) {
                        $message = new \mywishlist\models\Message();
                        $message->contenu = $contenuMessage;
                        $message->liste_id = $liste->no;
                        $message->save();
                    }
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
                }
                /* Dans les autres cas */
                else {
                    $vue = new \mywishlist\vue\VueParticipant([$liste->toArray(),$liste->items->toArray(),$liste->messages->toArray()], $this->container) ;
                    $html = $vue->render(2) ;
                }
            }
        }
        /* Si la liste est inaccesible */
        else {
            $vue = new \mywishlist\vue\VueParticipant([$liste->toArray(),$liste->items->toArray(),$liste->messages->toArray()], $this->container) ;
            $html = $vue->render(4) ;
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
            $vue = new \mywishlist\vue\VueParticipant([$item->toArray(),$liste->toArray()],$this->container) ;
            $html = $vue->render(3) ;
        } else {
            $vue = new \mywishlist\vue\VueParticipant([$item->toArray(),$liste->toArray()],$this->container) ;
            $html = $vue->render(0) ; // retourne à l'accueil
        }

        $data = $rq->getParsedBody();
        //$idItem = filter_var($data['idItem'], FILTER_SANITIZE_NUMBER_INT);
        //$item = \mywishlist\models\Item::find($idItem);
        //traitement nom reservation
        if (is_null($item->nomReservation)&&(isset($data['nom'])&&($this->verifierChamp($data['nom']) != null))) {
            $nom = filter_var($data['nom'], FILTER_SANITIZE_STRING);
            $item->nomReservation = $nom;
            $item->update();
            $rs = $rs->withRedirect($this->container->router->pathFor('affUnItem', ['id'=>$args['id'], 'token'=>$args['token']]));
            setcookie(
                "nomReservation",
                $nom,
                time() + (100 * 365 * 24 * 60 * 60), //expire dans 100 ans
                "/"
            );
        } else {
            $vue = new VueParticipant([$item->toArray(),$liste->toArray()], $this->container);
            $html = $vue->render(3);
        }

        //traitement message
        if (is_null($item->messageReservation)&&isset($data['messageAuCreateur'])&&($this->verifierChamp($data['messageAuCreateur']) != null)) {
            $contenuMessage = filter_var($data['messageAuCreateur'], FILTER_SANITIZE_STRING);
            $messageLength = strlen((String) (preg_replace("/\s\s+/", "", $contenuMessage)));
            if ($contenuMessage != "" && $contenuMessage != null && $messageLength >0 && $messageLength <250) {
                $item->messageReservation = $contenuMessage;
                $item->update();
                $rs = $rs->withRedirect($this->container->router->pathFor('affUnItem', ['id'=>$args['id'], 'token'=>$args['token']]));
            }
        } else {
            $vue = new VueParticipant([$item->toArray(),$liste->toArray()], $this->container);
            $html = $vue->render(3);
        }

        //ajout de l'image a l'item
        if (is_null($item->img)&&isset($data['AJimage'])&&($this->verifierChamp($data['AJimage']) != null)) {
            $types = [".jpg", ".png", ".gif", ".JPG", ".PNG", ".GIF"];
            if (in_array(substr($_FILES['image']['name'], -4), $types)) {
                $extension = substr($_FILES['image']['name'], -4);
                move_uploaded_file($_FILES['image']['tmp_name'], "../Ressources/img/{$item->id}.{$extension}");
            }
            $item->img = "{$item->id}.{$extension}";
            $item->update();
            $vue = new VueParticipant([$item->toArray(),$liste->toArray()], $this->container);
        } else {
            $vue = new VueParticipant([$item->toArray(),$liste->toArray()], $this->container);
            $html = $vue->render(3);
        }

        //modifier un item
        if  (isset($data['nomItem'])&&($this->verifierChamp($data['nomItem']) != null)||isset($data['tarifItem'])&&($this->verifierChamp($data['tarifItem']) != null)||isset($data['descriItem'])&&($this->verifierChamp($data['descriItem']) != null)) {
            if (($nouveauNomItem = $this->verifierChamp($data['nomItem'])) != null) {
                $item->nom = $nouveauNomItem;
            }
            if (($nouveauTarifItem = $this->verifierChamp($data['tarifItem'])) != null) {
                $item->tarif = $nouveauTarifItem;
            }
            if (($nouveauDescriItem = $this->verifierChamp($data['descriItem'])) != null) {
                $item->descr = $nouveauDescriItem;
            }
            $item->update();
            $rs = $rs->withRedirect($this->container->router->pathFor('affUnItem', ['id'=>$args['id'], 'token'=>$args['token']]));
        }

        if (isset($data['securiteSupprimerItem'])&&($this->verifierChamp($data['securiteSupprimerItem']) != null)){
            if($data['securiteSupprimerItem'] == "Je souhaite supprimer l'item"){
                $item->delete();
                $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
            }
        }

        $rs->getBody()->write($html);
        return $rs;
    }
}
