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
        $content = "La liste a été créé : <article>$l[no] ; $l[user_id] ; $l[titre] ; $l[description] ; $l[expiration] ; $l[token] ; $l[token_edition]</article>\n";
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
        }

        $url_acceuil = $this->container->router->pathFor('acceuil');
        $url_listes = $this->container->router->pathFor('listeDesListes');
        $url_liste = $this->container->router->pathFor('affUneListe', ['token'=>'nosecure1']);
        $url_item = $this->container->router->pathFor('affUnItem', ['id'=>1, 'token'=>'nosecure3']);
        $url_affichageForm = $this->container->router->pathFor('affForm');

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
