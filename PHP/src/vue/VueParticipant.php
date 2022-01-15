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
            $dateDExp = (new \DateTime("$l[expiration]"))->format('d-m-Y');
            $url = $this->container->router->pathFor('affUneListe', ['token'=>$l['token']]);
            $content .= "<article>$l[no] ; $l[user_id] ; $l[titre] ; $l[description] ; $dateDExp ; $l[token]</article>\n";
        }
        return "<section>$content</section>";
    }

    private function htmlUneListe() : string
    {
        $l = $this->tab[0];
        $dateDExp = (new \DateTime("$l[expiration]"))->format('d-m-Y');
        $tokenEdition = "$l[token_edition]";
        $content = "";
        if (isset($_COOKIE["TokenEdition:".$tokenEdition])) {
            $content .= "
            <script type='text/javascript'>
                function copierLUrl() 
                {
                    ZoneUrl.innerHTML = window.location.href;
                    var copyTextarea = document.getElementById('ZoneUrl');
                    copyTextarea.select();
                    document.execCommand('copy');
                }
            </script>
            <div>";
            if ($l['valide'] == 1) {
                $content .= "<input type='button' value='Copier le lien à cette page' onclick='copierLUrl();' />
                <textarea id='ZoneUrl' rows='1' cols='65'></textarea>";
            } else {
                $content .= "<form method='POST' action=''>
                <button name='publicationButton' type='submit'>Rendre la liste publique</button>
                </form>";
            }
            $content .="</div>
            <br>
            <form method='POST' action=''>
            <span>Modifier la liste: </span>
	        <input type='text' name ='editerTitre' placeholder='Titre'/>
	        <input type='text' name ='editerDescr' placeholder='Description'/>
	        ".//<input type='date' name ='editerDateExp' placeholder='expiration'/>
            "<input type='text' name ='editerDateExp' placeholder='Date expiration' onfocus=(this.type='date') onblur=(this.type='text')/>
	        <button type='submit'>Modifier la liste</button>
            </form>
            <br>
            <form method='POST' action=''>
            <span>Ajouter un item à la liste: </span>
            <input class='crea' type='text' name='creanom' placeholder='nom'/>
            <input class='crea' type='text' name='creadescription' placeholder='description'/>
            <input class='crea' type='number' name='creatarif' placeholder='tarif' step='0.01' min='0' />
            <button type='submit'>Créer l'item</button>
            </form>
            <br>";
        }
        $content .="<article><h1>Liste de souhaits : $l[titre]</h1><br><b>Description :</b> <i>$l[description]</i> <br>Expire le $dateDExp<br><small>Liste numéro $l[no] <br>Par l'utilisateur ayant l'id $l[user_id]</small> </article>\n";
        $item = $this->tab[1];
        $url = $this->container->router->pathFor('affUneListe', ['token'=>$l['token']]);
        $content .= "<ul>";
        foreach ($item as $i) {
            $url = $this->container->router->pathFor('affUnItem', ['id'=>$i['id'], 'token'=>$l['token']]);
            $content .= "<div><li><a href='$url'>$i[nom]</a> : ";
            /* Le token pour savoir si on est l'éditeur */
            if (isset($_COOKIE["TokenEdition:".$tokenEdition]) && (((new \DateTime('NOW'))->format('d-m-Y')) < $dateDExp)) {
                $etatItem = "$i[nomReservation]";
                if ($etatItem == null) {
                    $etatItem = "Pas encore réservé";
                } else {
                    $etatItem = "Réservé";
                }
                $content .= "
                <script type='text/javascript'>
                function montrerReserv(obj)
                {
                    var reserv = document.getElementById('reservCachee');
                    var boutonReserv = document.getElementById('reservCacheeBouton');
                    if (reserv.style.display == 'none'){
                        reserv.style.display = '';
                        boutonReserv.value = 'Cacher';
                    }else{
                        reserv.style.display = 'none';
                        boutonReserv.value = 'Voir';
                    }
                }
                </script>
                C'est vous qui avez créé la liste, vous ne pouvez pas voir qui a réserver cet item avant le $dateDExp<br>
                <span>
                Etat de la réservation : 
                    <input id='reservCacheeBouton' type='button' value='Voir' onclick='montrerReserv(this);'>
                        <span id='reservCachee' style='display: none;'>$etatItem</span>
                </span>";
            } else {
                $content .= "Etat de la réservation visible mais à ajouter en BDD<br>";
            }
            $content .= "<br><img style='max-width: 200px' src='../../Ressources/img/$i[img]'></div><br>";
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
        $champ = "";
        if (isset($_COOKIE["nomReservation"])) {
            $champ .= $_COOKIE["nomReservation"];
        }
        $i = $this->tab[0];
        $content = "<div>$i[id] ; $i[liste_id] ; $i[nom] ; $i[descr] ; $i[url] ; $i[tarif] <br><img style='max-width: 200px' src='../../../../Ressources/img/$i[img]'></div><br>";
        if ("$i[nomReservation]"== null) {
            $content .= "<form method='POST' action=''>
        <input type='text' name='nom' value='$champ' placeholder='nom'/><br>
        <button type='submit'>Réserver l'item</button>
        </form>";
        }
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
