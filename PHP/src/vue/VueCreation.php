<?php

namespace mywishlist\vue;

class VueCreation
{
    public array $tab;
    public \Slim\Container $container;

    public function __construct(array $tab, \Slim\Container $container)
    {
        $this->tab = $tab;
        $this->container = $container;
    }

    public function CreationformulaireListe(): string
    {
        $tommorow = (new \DateTime('tomorrow'))->format('Y-m-d');
        $content = "<form method='POST' action=''>
	        <input type='text' name ='titre' placeholder='titre' required/><br>
	        <input type='text' name ='description' placeholder='descri'/><br>
	        <div><p>Date d'expiration de la liste : </p><input type='date' name ='expiration' placeholder='expiration' value='$tommorow' min='$tommorow'/></div><br>
	        <button type='submit'>Créer la liste</button>
            </form>";
        echo "\n";
        return $content;
    }

    // Inscription
    public function creationFormulaireInscription() : string
    {
        $content = "<form method='POST' action=''>
	        <input type='text' name ='pseudo' placeholder='pseudo' required/><br>
	        <input type='password' name ='pass' placeholder='pass' required/><br>
	        <input type='password' name ='confirm_pass' placeholder='confirm_pass' required/><br>
	        <button type='submit'>Inscription</button>
            </form>";
        echo "\n";
        return $content;
    }
    
    public function creationFormulaireAuthentification() : string 
    {
        $content = "<form method='POST' action=''>
	        <input type='text' name ='pseudo' placeholder='pseudo' required/><br>
	        <input type='password' name ='pass' placeholder='pass' required/><br>
	        <button type='submit'>authentification</button>
            </form>";
        echo "\n";
        return $content;
    }
    public function compteCree():string 
    {
        return "ptn de merde oui";
    }
    public function authentifie():string 
    {
        return "connecte".$_SESSION['pseudo'];
    }
    public function listeCree():string
    {
        $l = $this->tab[0];
        $tokenEdition = "$l[token_edition]";
        $dateDExp = (new \DateTime("$l[expiration]"))->format('d-m-Y');
        $content = "La liste a été créé : <article>$l[no] ; $l[user_id] ; $l[titre] ; $l[description] ; $dateDExp ; $l[token] ; $tokenEdition</article>\n";
        setcookie(
            "TokenEdition:".$tokenEdition,
            $tokenEdition,
            time() + (100 * 365 * 24 * 60 * 60),   //expire dans 100 ans
            "/"
        );
        $url_nouvListe = $this->container->router->pathFor('affUneListe', ['token'=>"$l[token]"]);
        $content.= "<br>votre token d'édition = $tokenEdition a été créé<br><a href=$url_nouvListe>Vous rendre à votre nouvelle liste</a>";
        return $content;
    }


    public function render($selecteur)
    {
        switch ($selecteur) {
            case 1:
            {
                $content = $this->CreationformulaireListe();
                break;
            }
            case 2:
            {
               $content = $this->listeCree();
                break;
            }
            case 8:
            {
                $content = $this->creationFormulaireInscription();
                break;
            }
            case 9:
            {
                $content = $this->compteCree();
                break;
            }
            case 10:
            {
                $content = $this->creationFormulaireAuthentification();
                break;
            }
            case 11: 
            {
                $content = $this->authentifie();
            }
        }

        $url_Accueil = $this->container->router->pathFor('Accueil');
        $url_listes = $this->container->router->pathFor('listeDesListes');
        $url_liste = $this->container->router->pathFor('affUneListe', ['token'=>'nosecure1']);
        $url_item = $this->container->router->pathFor('affUnItem', ['id'=>1, 'token'=>'nosecure3']);
        $url_affichageForm = $this->container->router->pathFor('affForm');
        $url_inscription = $this->container->router->pathFor('inscription');
        $url_authentification = $this->container->router->pathFor('authentification');

        $html = <<<END
<!DOCTYPE html>
<html>
    <body>
    <h1>My WishList</h1>
    <nav>
    <div><a href=$url_Accueil>Accueil</a></div>
    <div><a href=$url_affichageForm>Créer une nouvelle liste</a></div>
    <div><a href=$url_listes>Listes publiques</a></div>
    <div><a href=$url_liste>Lien vers la liste 1 (temporaire)</a></div>
    <div><a href=$url_item>Lien vers l'item 1 (temporaire)</div>
    <div><a href=$url_inscription>Inscription (démo, emplacement temporaire)</a></div>
    <div><a href=$url_authentification>Authentification (démo, emplacement temporaire)</a></div>
    </nav>
        <br>
        <div class="content">
        $content
        </div>
    </body>
</html>
END;
        return $html;
    }
}
