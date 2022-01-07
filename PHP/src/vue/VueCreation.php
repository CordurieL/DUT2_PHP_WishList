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
        $content = "La liste a été créé : <article>$l[no] ; $l[user_id] ; $l[titre] ; $l[description] ; $l[expiration] ; $l[token] ; $l[token_edition]</article>";
        echo "\n";
        return $content;
    }

    public function CreationReservationItem(): string
    {
        $content = "<form method='POST' action=''>
            <input type='text' name ='nom' placeholder='nom'/><br>
	        <input type='number' name ='idItem' placeholder='numéro de l item'/><br>
	         <button type='submit'>Réserver l'item</button><br>
	        </form>";
            return $content;
    }

    public function itemReserve():string
    {
        $i = $this->tab[0];
        $content = "<article>L'item numéro $i[id] de nom $i[nom] et de prix $i[tarif] a bien été réservé</article>";
        echo "\n";
        return $content;
    }

    public function itemDejaReserve():string
    {
        $i = $this->tab[0];
        $content = "<article>Réservation Impossible ! <br>
        L'item numéro $i[id] de nom $i[nom] et de prix $i[tarif] a déjà été réservé sous le nom de $i[nomReservation] !</article>";
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
                $content = $this->CreationReservationItem();
                break;
            }
            case 4:
            {
                $content = $this->itemReserve();
                break;
            }
            case 5:
            {
                $content = $this->itemDejaReserve();
                break;
            }
        }

        $url_acceuil = $this->container->router->pathFor('acceuil');
        $url_listes = $this->container->router->pathFor('listeDesListes');
        $url_liste = $this->container->router->pathFor('affUneListe', ['token'=>'nosecure1']);
        $url_item = $this->container->router->pathFor('affUnItem', ['id'=>1, 'token'=>'nosecure3']);
        $url_affichageForm = $this->container->router->pathFor('affForm');
        $url_reserverItem = $this->container->router->pathFor('affReservation');

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
