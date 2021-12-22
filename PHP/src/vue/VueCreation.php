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
	        <input type='text' name ='titre' placeholder='titre'/>
	        <input type='text' name ='description' placeholder='descri'/>
	        <input type='date' name ='expiration' placeholder='expiration'/>
	        <button type='submit'>Créer la liste</button>
            </form>";
        echo "\n";
        return $content;
    }

    public function listeCree():string{
        $l = $this->tab[0];
        $content = "La liste a été créé : <article>$l[no] ; $l[titre]</article>";
        return $content;
}


    public function render($selecteur) {
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



        $html = <<<END
<!DOCTYPE html>
<html>
    <body>
    <nav>
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