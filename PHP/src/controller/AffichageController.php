<?php
declare(strict_types=1);

namespace mywishlist\controller;

use \Slim\Container;
use mywishlist\vue\VueCreation;
use mywishlist\vue\VueParticipant;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use mywishlist\models\Item;
use mywishlist\models\Liste;
use mywishlist\models\Message;
use mywishlist\models\Participation;

class AffichageController
{
    private Container $container;

    // Constructeur
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    // methode pour vérifier que la chaine entree est conforme a nos attentes, s'adapte avec les differents filtres
    public static function verifier(mixed $chaine, int $filter) :string|null
    {
        $res = null;
        if (is_string($chaine)) {
            $contenuChamp = filter_var($chaine, $filter);
            $contenuLength = strlen((String) (preg_replace("/\s\s+/", "", $contenuChamp)));
            if ($contenuChamp != "" && $contenuChamp != null && $contenuLength >0) {
                $res = $contenuChamp;
            }
        }
        return $res;
    }

    // Pour l'accueil
    public function afficherAccueil(Request $rq, Response $rs, $args):Response
    {
        $tabListes = array(); // Donne les listes dont on a le token d'acces
        $tabListesCrees = array(); // Donne les listes dont on a le token d'edition
        foreach ($_COOKIE as $nom => $valeur) {
            if (str_starts_with($nom, 'TokenAcces')) {
                $liste = Liste::where('token', '=', $valeur)->first();
                if ($liste !== null) {
                    $tabListes[] = $liste;
                }
            }
            if (str_starts_with($nom, 'TokenEdition')) {
                $listeC = Liste::where('token_edition', '=', $valeur)->first();
                if ($listeC !== null) {
                    $tabListesCrees[] = $listeC;
                }
            }
        }
        $vue = new VueParticipant([$tabListes, $tabListesCrees], $this->container) ;
        $html = $vue->render(0) ;
        $rs->getBody()->write($html);
        return $rs;
    }

    // Pour les listes publiques et non expirees
    public function afficherListes(Request $rq, Response $rs, $args):Response
    {
        $listes = Liste::all() ;
        $vue = new VueParticipant($listes->toArray(), $this->container) ;
        $html = $vue->render(1) ;
       
        $rs->getBody()->write($html);
        return $rs;
    }

    // Pour afficher une liste
    public function afficherUneListe(Request $rq, Response $rs, $args):Response
    {
        /* Récupération des infos générales */
        $liste =Liste::where('token', '=', $args['token'])->first();
        $tokenEdition = "$liste[token_edition]";
        $data = $rq->getParsedBody();
        /* Pour l'acces a la liste elle meme */
        if ((isset($_COOKIE["TokenEdition:".$tokenEdition])) || ("$liste[valide]" == 1)) {
            /* Empecher l'accès a la liste après expiration pour les visiteurs */
            $dateDExp = (new \DateTime("$liste[expiration]"));
            if (!(isset($_COOKIE["TokenEdition:".$tokenEdition])) && ((new \DateTime('NOW')) > $dateDExp)) {
                $vue = new VueParticipant([$liste->toArray(),$liste->items->toArray(),"",$liste->messages->toArray()], $this->container) ;
                $html = $vue->render(4) ;
            } else {
                /* Pour la validation (publique) d'une liste */
                if ((isset($data['publicationButton']))) {
                    $liste->valide = 1;
                    $liste->save();
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
                }
                /* Pour les modifications d'informations generales de la liste */
                if ((isset($data['editerTitre'])&&($this->verifier($data['editerTitre'], FILTER_SANITIZE_STRING) != null))||((isset($data['editerDescr'])&&$this->verifier($data['editerDescr'], FILTER_SANITIZE_STRING) !=null))||((isset($data['editerDateExp'])&&$this->verifier($data['editerDateExp'], FILTER_SANITIZE_STRING)!=null))) {
                    if (($nouveauTitre = $this->verifier($data['editerTitre'], FILTER_SANITIZE_STRING)) != null) {
                        $liste->titre = $nouveauTitre;
                    }
                    if (($nouvelleDescr = $this->verifier($data['editerDescr'], FILTER_SANITIZE_STRING)) != null) {
                        $liste->description = $nouvelleDescr;
                    }
                    if (($nouvelleDateExp = $this->verifier($data['editerDateExp'], FILTER_SANITIZE_STRING)) != null) {
                        $liste->expiration = $nouvelleDateExp;
                    }
                    $liste->save();
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
                }
                /* Pour creer un nouvel item dans une liste */
                if ((isset($data['creanom'])&&(($nom = $this->verifier($data['creanom'], FILTER_SANITIZE_STRING)) != null))&&((isset($data['creatarif'])&& ($this->verifier($data['creatarif'], FILTER_SANITIZE_STRING))!=null))) {
                    $item = new Item();
                    if ((isset($data['creadescription'])&&(($description = $this->verifier($data['creadescription'], FILTER_SANITIZE_STRING)) !=null))) {
                        $item->descr = $description;
                    }
                    $tariif = $_POST['creatarif'];
                    $item->nom =$nom;
                    $item->tarif = $tariif;
                    $item->tarif_restant = $item->tarif;
                    $url = filter_var($_POST['creaurl'], FILTER_SANITIZE_URL);
                    $item->url = $url;
                    $item->liste_id = $liste->no;
                    $item->save();
                    //image avec fichier
                    $types = [".jpg", ".png", ".gif", ".JPG", ".PNG", ".GIF"];
                    if (in_array(substr($_FILES['creaimage']['name'], -4), $types)) {
                        $extension = substr($_FILES['creaimage']['name'], -3);
                        move_uploaded_file($_FILES['creaimage']['tmp_name'], "../Ressources/img/{$item->id}.{$extension}");
                        $item->img = "{$item->id}.{$extension}";
                    }
                    //image avec lien
                    if (isset($_POST['creaurlimage'])) {
                        $url = $data['creaurlimage'];
                        if (in_array(substr($url, -4), $types)) {
                            $data = file_get_contents($url);
                            $types = [".jpg", ".png", ".gif", ".JPG", ".PNG", ".GIF"];
                            $extension = substr($url, -3);
                            $file = "../Ressources/img/{$item->id}.{$extension}";
                            file_put_contents($file, $data);
                            $item->img = "{$item->id}.{$extension}";
                        }
                    }
                    $item->update();
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
                }
                /* Pour les messages de liste */
                if (isset($data['contenu'])) {
                    $contenuMessage = filter_var($data['contenu'], FILTER_SANITIZE_STRING);
                    $messageLength = strlen((String) (preg_replace("/\s\s+/", "", $contenuMessage)));
                    if ($contenuMessage != "" && $contenuMessage != null && $messageLength >0) {
                        $message = new Message();
                        $message->contenu = $contenuMessage;
                        $message->liste_id = $liste->no;
                        $message->save();
                    }
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
                }
                /* Dans les autres cas */
                else {
                    $vue = new VueParticipant([$liste->toArray(),$liste->items->toArray(),$liste->messages->toArray()], $this->container) ;
                    $html = $vue->render(2) ;
                }
            }
        }
        /* Si la liste est inaccesible */
        else {
            $vue = new VueParticipant([$liste->toArray(),$liste->items->toArray(),"",$liste->messages->toArray()], $this->container) ;
            $html = $vue->render(4) ;
        }
        $rs->getBody()->write($html);
        return $rs;
    }

    // Pour afficher un item
    public function afficherUnItem(Request $rq, Response $rs, $args):Response
    {
        /* Récupération des infos générales */
        $liste =Liste::where('token', '=', $args['token'])->first();
        $tokenEdition = "$liste[token_edition]";
        $item = Item::find($args['id']) ;
        $tokenListe = $liste->token;
        $dateDExp = (new \DateTime("$liste[expiration]"));
        $data = $rq->getParsedBody();

        // par défaut
        $vue = new VueParticipant([$item->toArray(),$liste->toArray(), $item->participations->toArray()], $this->container);
        $html = $vue->render(3);

        // Si la liste est publique ou qu'on est l'auteur, on peut voir son item
        if (((isset($_COOKIE["TokenEdition:".$tokenEdition])) || (("$liste[valide]" == 1) && ((new \DateTime('NOW')) < $dateDExp)))) {

            //traitement nom reservation
            if (is_null($item->nomReservation)&&(isset($data['nom'])&&($this->verifier($data['nom'], FILTER_SANITIZE_STRING) != null))) {
                $nom = filter_var($data['nom'], FILTER_SANITIZE_STRING);
                // l'item n'est pas en cagnotte
                if ($item['estUneCagnotte'] == 0) {
                    $item->nomReservation = $nom;
                    if (isset($data['messageAuCreateur'])&&(($contenuMessage = $this->verifier($data['messageAuCreateur'], FILTER_SANITIZE_STRING)) != null)) {
                        $item->messageReservation = $contenuMessage;
                    }
                    $item->tarif_restant = 0;
                    $item->update();
                } else {
                    // l'item est en cagnotte
                    $particip = new Participation();
                    $particip->item_id = $item->id;
                    $particip->nomparticipation = $nom;
                    if (isset($data['messageAuCreateur'])&&(($contenuMessage = $this->verifier($data['messageAuCreateur'], FILTER_SANITIZE_STRING)) != null)) {
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
                // Pour se souvenir de la personne sans compte
                setcookie(
                    "nomReservation",
                    $nom,
                    time() + (100 * 365 * 24 * 60 * 60), //expire dans 100 ans
                "/"
                );
            }

            //ajout de l'image a l'item
            if ((isset($_COOKIE["TokenEdition:".$tokenEdition]))) {
                if ((isset($_FILES['image']['size']))&&($_FILES['image']['size'] != 0)) {
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
                if (isset($_POST['urlimage'])) {
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
            if (isset($_POST['supprimage'])) {
                $item->img = null;
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
            if (isset($data['nomItem'])&&($this->verifier($data['nomItem'], FILTER_SANITIZE_STRING) != null)||isset($data['tarifItem'])&&($this->verifier($data['tarifItem'], FILTER_SANITIZE_STRING) != null)||isset($data['descriItem'])&&($this->verifier($data['descriItem'], FILTER_SANITIZE_STRING) != null) || isset($data['modifurlItem'])&&($this->verifier($data['modifurlItem'], FILTER_SANITIZE_URL) != null)) {
                if (($nouveauNomItem = $this->verifier($data['nomItem'], FILTER_SANITIZE_STRING)) != null) {
                    $item->nom = $nouveauNomItem;
                }
                if (($nouveauTarifItem = $this->verifier($data['tarifItem'], FILTER_SANITIZE_STRING)) != null) {
                    $item->tarif = $nouveauTarifItem;
                    $item->tarif_restant = $item->tarif;
                }
                if (($nouveauDescriItem = $this->verifier($data['descriItem'], FILTER_SANITIZE_STRING)) != null) {
                    $item->descr = $nouveauDescriItem;
                }
                if (($nouveauUrlItem = $this->verifier($data['modifurlItem'], FILTER_SANITIZE_URL)) != null) {
                    $item->url = $nouveauUrlItem;
                }
                $item->update();
                $rs = $rs->withRedirect($this->container->router->pathFor('affUnItem', ['id'=>$args['id'], 'token'=>$args['token']]));
            }

            // supprime item
            if (isset($data['securiteSupprimerItem'])&&($this->verifier($data['securiteSupprimerItem'], FILTER_SANITIZE_STRING) != null)) {
                if ($data['securiteSupprimerItem'] == "Je souhaite supprimer l'item") {
                    $item->delete();
                    $rs = $rs->withRedirect($this->container->router->pathFor('affUneListe', ['token'=>$args['token']]));
                }
            }
        } else {
            // On ne peut pas voir l'item
            $vue = new VueParticipant([$liste->toArray(),$item->toArray(), "../../"], $this->container);
            $html = $vue->render(4);
        }

        $rs->getBody()->write($html);
        return $rs;
    }
}
