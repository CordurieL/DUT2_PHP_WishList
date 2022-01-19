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

     public static function verifierUrl(mixed $chaine) :string|null
        {
            $res = null;
            if (is_string($chaine)) {
                $contenuChamp = filter_var($chaine, FILTER_SANITIZE_URL);
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
                $vue = new \mywishlist\vue\VueParticipant([$liste->toArray(),$liste->items->toArray(),"",$liste->messages->toArray()], $this->container) ;
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
                if ((isset($data['creanom'])&&(($nom = $this->verifierChamp($data['creanom'])) != null))&&((isset($data['creatarif'])&&$this->verifierChamp($data['creatarif'])!=null))) {
                    $item = new \mywishlist\models\Item();
                    if ((isset($data['creadescription'])&&(($description = $this->verifierChamp($data['creadescription'])) !=null))) {
                        $item->descr = $description;
                    }
                    $types = [".jpg", ".png", ".gif", ".JPG", ".PNG", ".GIF"];
                    if (in_array(substr($_FILES['image']['name'], -4), $types)) {
                        $extension = substr($_FILES['image']['name'], -3);
                        move_uploaded_file($_FILES['image']['tmp_name'], "../Ressources/img/{$item->id}.{$extension}");
                    }
                    $item->img = "{$item->id}.{$extension}";
                    $tarif = ($data['creatarif']);
                    $item->nom =$nom;
                    $item->tarif = $tarif;
                    $item->tarif_restant = $item->tarif;
                    $url = filter_var($data['creaurl'], FILTER_SANITIZE_URL);
                    $item->url = $url;
                    $item->liste_id = $liste->no;
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
            $vue = new \mywishlist\vue\VueParticipant([$liste->toArray(),$liste->items->toArray(),"",$liste->messages->toArray()], $this->container) ;
            $html = $vue->render(4) ;
        }
        $rs->getBody()->write($html);
        return $rs;
    }

    public function afficherUnItem(Request $rq, Response $rs, $args):Response
    {
        $liste =\mywishlist\models\Liste::where('token', '=', $args['token'])->first();
        $tokenEdition = "$liste[token_edition]";

        $item = \mywishlist\models\Item::find($args['id']) ;
        $tokenListe = $liste->token;
        $dateDExp = (new \DateTime("$liste[expiration]"));
        $data = $rq->getParsedBody();

        // par défaut
        $vue = new VueParticipant([$item->toArray(),$liste->toArray(), $item->participations->toArray()], $this->container);
        $html = $vue->render(3);

        // Si la liste est publique ou qu'on est l'auteur, on peut voir son item
        if (((isset($_COOKIE["TokenEdition:".$tokenEdition])) || (("$liste[valide]" == 1) && ((new \DateTime('NOW')) < $dateDExp)))) {

            //traitement nom reservation
            if (is_null($item->nomReservation)&&(isset($data['nom'])&&($this->verifierChamp($data['nom']) != null))) {
                $nom = filter_var($data['nom'], FILTER_SANITIZE_STRING);
                if ($item['estUneCagnotte'] == 0) {
                    $item->nomReservation = $nom;
                    if (isset($data['messageAuCreateur'])&&(($contenuMessage = $this->verifierChamp($data['messageAuCreateur'])) != null)) {
                        $item->messageReservation = $contenuMessage;
                    }
                    $item->tarif_restant = 0;
                    $item->update();
                } else {
                    $particip = new \mywishlist\models\Participation();
                    $particip->item_id = $item->id;
                    $particip->nomparticipation = $nom;
                    if (isset($data['messageAuCreateur'])&&(($contenuMessage = $this->verifierChamp($data['messageAuCreateur'])) != null)) {
                        $particip->messageparticipation = $contenuMessage;
                    }
                    $particip->contribution = $data['participation'];
                    $particip->save();
                    $item->tarif_restant = $item->tarif_restant - $particip->contribution;
                    if ($item->tarif_restant == 0) {
                        $item->nomReservation = "Multi-Participation";
                    }
                    $item->update();
                }
                $rs = $rs->withRedirect($this->container->router->pathFor('affUnItem', ['id'=>$args['id'], 'token'=>$args['token']]));
                setcookie(
                    "nomReservation",
                    $nom,
                    time() + (100 * 365 * 24 * 60 * 60), //expire dans 100 ans
                "/"
                );
            }

            //ajout de l'image a l'item
            if ((isset($_COOKIE["TokenEdition:".$tokenEdition]))) {
                if (($_FILES['image']['size'] != 0)) {
                    $types = [".jpg", ".png", ".gif", ".JPG", ".PNG", ".GIF"];
                    if (in_array(substr($_FILES['image']['name'], -4), $types)) {
                        $extension = substr($_FILES['image']['name'], -3);
                        move_uploaded_file($_FILES['image']['tmp_name'], "../Ressources/img/{$item->id}.{$extension}");
                    }
                    $item->img = "{$item->id}.{$extension}";
                    $item->update();
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUnItem', ['id'=>$args['id'], 'token'=>$args['token']]));
                }
            }

            //ajout de l'image a l'item via un lien
            if ((isset($_COOKIE["TokenEdition:".$tokenEdition]))) {
                if(isset($_POST['urlimage'])){
                    $url = $data['urlimage'];
                    $data = file_get_contents($url);
                    $types = [".jpg", ".png", ".gif", ".JPG", ".PNG", ".GIF"];
                    if (in_array(substr($url, -4), $types)) {
                        $extension = substr($url, -3);
                        $file = "../Ressources/img/{$item->id}.{$extension}";
                        file_put_contents($file, $data);
                        $item->img = "{$item->id}.{$extension}";
                    }
                    $item->update();
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUnItem', ['id'=>$args['id'], 'token'=>$args['token']]));
                }

            }

            //supprime l'image d'un item
            if ($data['securiteSupprimerImage'] == "supprimer") {
                 $item->img = NULL;
                 $item->update();
                 $rs = $rs->withRedirect($this->container->router->pathFor('affUnItem', ['id'=>$args['id'], 'token'=>$args['token']]));
            }


            /* Pour devenir une cagnotte */
            if ((isset($data['rendreCagnotte']))) {
                $item->estUneCagnotte = true;
                $item->save();
                $rs = $rs->withRedirect($this->container->router->pathFor('affUnItem', ['id'=>$args['id'], 'token'=>$args['token']]));
            }

            //modifier un item
            if (isset($data['nomItem'])&&($this->verifierChamp($data['nomItem']) != null)||isset($data['tarifItem'])&&($this->verifierChamp($data['tarifItem']) != null)||isset($data['descriItem'])&&($this->verifierChamp($data['descriItem']) != null) || isset($data['modifurlItem'])&&($this->verifierUrl($data['modifurlItem']) != null) ) {
                if (($nouveauNomItem = $this->verifierChamp($data['nomItem'])) != null) {
                    $item->nom = $nouveauNomItem;
                }
                if (($nouveauTarifItem = $this->verifierChamp($data['tarifItem'])) != null) {
                    $item->tarif = $nouveauTarifItem;
                    $item->tarif_restant = $item->tarif;
                }
                if (($nouveauDescriItem = $this->verifierChamp($data['descriItem'])) != null) {
                    $item->descr = $nouveauDescriItem;
                }
                if (($nouveauUrlItem = $this->verifierUrl($data['modifurlItem'])) != null) {
                    $item->url = $nouveauUrlItem;
                }
                $item->update();
                $rs = $rs->withRedirect($this->container->router->pathFor('affUnItem', ['id'=>$args['id'], 'token'=>$args['token']]));
            }

            // supprime item
            if (isset($data['securiteSupprimerItem'])&&($this->verifierChamp($data['securiteSupprimerItem']) != null)) {
                if ($data['securiteSupprimerItem'] == "Je souhaite supprimer l'item") {
                    $item->delete();
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
                }
            }
        } else {
            $vue = new VueParticipant([$liste->toArray(),$item->toArray(), "../../"], $this->container);
            $html = $vue->render(4);
        }

        $rs->getBody()->write($html);
        return $rs;
    }
}
