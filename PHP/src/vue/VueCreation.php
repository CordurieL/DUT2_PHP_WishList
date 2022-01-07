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

    public function listeCree():string
    {
        $l = $this->tab[0];
        $tokenEdition = "$l[token_edition]";
        $content = "La liste a été créé : <article>$l[no] ; $l[user_id] ; $l[titre] ; $l[description] ; $l[expiration] ; $l[token] ; $tokenEdition</article>\n";
        setcookie(
            "TokenEdition:".$tokenEdition,
            $tokenEdition,
            time() + (100 * 365 * 24 * 60 * 60) //expire dans 100 ans
        ) ;
        echo "votre token d'édition = $tokenEdition a été créé";
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
        }

        $url_acceuil = $this->container->router->pathFor('acceuil');
        $url_listes = $this->container->router->pathFor('listeDesListes');
        $url_liste = $this->container->router->pathFor('affUneListe', ['token'=>'nosecure1']);
        $url_item = $this->container->router->pathFor('affUnItem', ['id'=>1, 'token'=>'nosecure3']);
        $url_affichageForm = $this->container->router->pathFor('affForm');
        $url_reserverItem = $this->container->router->pathFor('affReservation');
        $url_creerItem = $this->container->router->pathFor('affFormItem');

        $html = <<<END
<!DOCTYPE html>
<html>
    <body>
    <h1>My WishList</h1>
    <nav>
    <div><a href=$url_acceuil>Acceuil</a></div>
    <div><a href=$url_affichageForm>affForm</a></div>
    <div><a href=$url_listes>Listes</a></div>
    <div><a href=$url_liste>Liste</a></div>
    <div><a href=$url_item>Item</a></div>
    <div><a href=$url_reserverItem>Réserver un Item</a></div>
    <div><a href=$url_creerItem>Créer un Item</a></div>
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
