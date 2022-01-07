<?php
declare(strict_types=1);

namespace mywishlist\vue;

class VueParticipant
{
    public array $tab;
    public \Slim\Container $container;

    public function __construct(array $tab, \Slim\Container $container)
    {
        $this->tab = $tab;
        $this->container = $container;
    }

    private function htmlAcceuil() : string
    {
        $content = "Bonjour acceuil";
        return $content;
    }

    private function htmlListes() : string
    {
        $content = "\n";
        foreach ($this->tab as $l) {
            $url = $this->container->router->pathFor('affUneListe', ['token'=>$l['token']]);
            $content .= "<article>$l[no] ; $l[user_id] ; $l[titre] ; $l[description] ; $l[expiration] ; $l[token]</article>\n";
        }
        return "<section>$content</section>";
    }

    private function htmlUneListe() : string
    {
        $l = $this->tab[0];
        $content ="<article>Liste numéro $l[no] <br>Par l'utilisateur ayant l'id $l[user_id] <br><h1>$l[titre]</h1> <br>$l[description] <br>Expire le $l[expiration]</article>\n";
        $item = $this->tab[1];
        $url = $this->container->router->pathFor('affUneListe', ['token'=>$l['token']]);
        $content .= "<ul>";
        foreach ($item as $i) {
            $url = $this->container->router->pathFor('affUnItem', ['id'=>$i['id'], 'token'=>$l['token']]);
            $content .= "<div><li><a href='$url'>$i[nom]</a> : ";
            $tokenEdition = "$l[token_edition]";
            /* Le token pour savoir si on est l'éditeur */
            if (isset($_COOKIE["TokenEdition:".$tokenEdition])) {
                $content .= "C'est vous qui avez créé la liste, vous ne pouvez pas voir qui a réserver cet item<br>";
            } else {
                $content .= "Etat de la réservation visible mais à ajouter en BDD<br>";
            }
            $content .= "<img style='max-width: 200px' src='../../Ressources/img/$i[img]'></div><br>";
        }
        /* Pour les messages dans les listes */
        $content .= "</ul><hr style='border-top: 10px solid black;'>";
        $content .= "<form method='POST' action=''>
	        <textarea name ='contenu' placeholder='Message' maxlength=255 cols=50 rows=8></textarea><br>
	        <button type='submit'>Publier le message</button>
            </form><br>";
        $message = $this->tab[2];
        foreach ($message as $m) {
            $content .= "<div>$m[contenu]</div><br>";
        }
        return "<section>$content</section>";
    }
    
    private function htmlUnItem() : string
    {
        $i = $this->tab[0];
        $content = "<div>$i[id] ; $i[liste_id] ; $i[nom] ; $i[descr] ; $i[url] ; $i[tarif] <br><img style='max-width: 200px' src='../../../../Ressources/img/$i[img]'></div><br>";
        return "<section>$content</section>";
    }


    public function render($selecteur)
    {
        switch ($selecteur) {
        case 0: {
         $content = $this->htmlAcceuil();
         break;
         }
         case 1: {
         $content = $this->htmlListes();
         break;
         }
         case 2: {
         $content = $this->htmlUneListe();
         break;
         }
         case 3: {
         $content = $this->htmlUnItem();
         break;
         }
        }

        $url_acceuil = $this->container->router->pathFor('acceuil');
        $url_listes = $this->container->router->pathFor('listeDesListes');
        $url_liste = $this->container->router->pathFor('affUneListe', ['token'=>'nosecure1']);
        $url_item = $this->container->router->pathFor('affUnItem', ['id'=>1, 'token'=>'nosecure2']);
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
