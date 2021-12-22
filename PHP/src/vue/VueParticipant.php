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
        $content = "Bonjour acceuil blabla";
        return $content;
    }

    private function htmlListes() : string
    {
        //echo '<pre>';
        //var_dump($this->tab);
        $content = "\n";
        foreach ($this->tab as $l) {
            $url = $this->container->router->pathFor('affUneListe', ['noListe'=>$l['no']]);
            $content .= "<article>$l[no] ; $l[user_id] ; $l[titre] ; $l[description] ; $l[expiration] ; $l[token]</article>\n";
        }
        return "<section>$content</section>";
    }

    private function htmlUneListe() : string
    {
        /*$content = "\n";
        foreach ($this->tab as $l) {
            $content .= "<article>$l[no] ; $l[user_id] ; $l[titre] ; $l[description] ; $l[expiration] ; $l[token]</article>\n";
        }
        return "<section>$content</section>";*/

        $l = $this->tab[0];
        $content ="<article>$l[no] ; $l[user_id] ; $l[titre] ; $l[description] ; $l[expiration] ; $l[token]</article>\n";
        $item = $this->tab[1];
        $content .= "<ul>";
        foreach ($item as $i) {
            $url = $this->container->router->pathFor('affUnItem', ['id'=>$i['id']]);
            $content .= "<li>$i[id] ; $i[liste_id] ; $i[nom] ; $i[descr] ; $i[img] ; $i[url] ; $i[tarif]\n";
        }
        return "<section>$content</section>";
    }
    
    private function htmlUnItem() : string
    {
        $i = $this->tab[0];
        $url = $this->container->router->pathFor('acceuil');
        $content = "$i[id] ; $i[liste_id] ; $i[nom] ; $i[descr] ; $i[img] ; $i[url] ; $i[tarif]\n";
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
        $url_affichageForm = $this->container->router->pathFor('affForm');

        $html = <<<END
<!DOCTYPE html>
<html>
    <body>
    <h1>My WishList</h1>
    <nav>
    <div><a href=$url_affichageForm>affForm</a></div>
    <div><a href=$url_acceuil>Acceuil</a></div>
    <div><a href=$url_listes>Listes</a></div>
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
