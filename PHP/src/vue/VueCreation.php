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
        $content = "<form method='POST' action=''>
	        <input type='text' name ='titre' placeholder='titre'/><br>
	        <input type='text' name ='description' placeholder='descri'/><br>
	        <div><p>Date d'expiration de la liste : </p><input type='date' name ='expiration' placeholder='expiration'/></div><br>
	        <button type='submit'>Créer la liste</button>
            </form>";
        echo "\n";
        return $content;
    }

    // Inscription
    public function creationFormulaireInscription() : string
    {
        $content = "<form action='' method='post'>
            <label for='username'>Nom d'utilisateur</label>
            <input type='text' name='username' id='username' required>
	        <label for='password'>Mot de passe</label>
			<input type='password' name='password' id='password' required>
            <label for='password_confirm'>Confirmation du mot de passe</label>
			<input type='password' name='password_confirm' id='password_confirm' required>
            <input type='submit' value='S'inscrire'>
            </form> \n";
        return $content;
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

    public function CreationFormulaireItem():string
    {
        $content = "<form method='POST' action=''>
        <input type='text' name='nom' placeholder='nom'/><br>
        <input type='text' name='description' placeholder='description'/><br>
        <input type='number' name='tarif' placeholder='tarif' step='0.01' min='0' /><br>
        <input type='number' name='idListe' placeholder='idListe'/><br>
        <button type='submit'>Créer l'item</button>
        </form>";
        echo "\n";
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
            case 3:
            {
                $content = $this->CreationFormulaireItem();
                break;
            }
            case 8:
            {
                $content = $this->creationFormulaireInscription();
                break;
            }
        }

        $url_acceuil = $this->container->router->pathFor('acceuil');
        $url_listes = $this->container->router->pathFor('listeDesListes');
        $url_liste = $this->container->router->pathFor('affUneListe', ['token'=>'nosecure1']);
        $url_item = $this->container->router->pathFor('affUnItem', ['id'=>1, 'token'=>'nosecure3']);
        $url_affichageForm = $this->container->router->pathFor('affForm');
        $url_creerItem = $this->container->router->pathFor('affFormItem');
        $url_inscription = $this->container->router->pathFor('inscription');

        $html = <<<END
<!DOCTYPE html>
<html>
    <body>
    <h1>My WishList</h1>
    <nav>
    <div><a href=$url_acceuil>Accueil</a></div>
    <div><a href=$url_affichageForm>Créer une nouvelle liste</a></div>
    <div><a href=$url_listes>Aperçu de toutes les listes (temporaire)</a></div>
    <div><a href=$url_liste>Lien vers la liste 1 (temporaire)</a></div>
    <div><a href=$url_item>Lien vers l'item 1 (temporaire)</div>
    <div><a href=$url_creerItem>Créer un Item (démo, emplacement temporaire)</a></div>
    <div><a href=$url_inscription>Inscription (démo, emplacement temporaire)</a></div>
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
