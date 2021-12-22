<?php
declare(strict_types=1);
require 'vendor/autoload.php';
$app = new \Slim\App();

use \Illuminate\Database\Capsule\Manager as DB;

use \mywishlist\models\Liste as Liste;
use \mywishlist\models\Item as Item;

$db = new DB();
$db->addConnection(parse_ini_file('src/conf/conf.ini'));
$db->setAsGlobal();
$db->bootEloquent();

/*$req_listes = Liste::all();
$listes = $req_listes->get();
foreach ($listes as $liste) {
    echo  $liste->no . ' :' . $liste->titre;
}*/

print("La liste qui contient l'objet d'id 4");
$i = Item::where('id', '=', 4)->first() ;
$l = $i->liste()->first();
print("<br>" .$l->titre."<br>");

$l = Liste::where('no', '=', 1)->first() ;
$li = $l->items()->get() ;
print("<br>Tous les objets de la liste no 1 : $l->titre");
echo  "<br>id, liste_id, nom, descr, img, tarif";
$total = 0;
foreach ($li as $it) {
    echo  "<br>" . $it->id . ', ' . $it->liste_id. ', ' . $it->nom. ', ' . $it->descr. ', ' . $it->img. ', '  . $it->tarif;
    $total += $it->tarif;
}
echo  "<br>Tarif total de $total pesos";


$l = Liste::where('no', '=', 2)->first() ;
$li = $l->items()->get() ;
print("<br><br>Tous les objets de la liste no 2 : $l->titre");
echo  "<br>id, liste_id, nom, descr, img, tarif";
$total = 0;
foreach ($li as $it) {
    echo  "<br>" . $it->id . ', ' . $it->liste_id. ', ' . $it->nom. ', ' . $it->descr. ', ' . $it->img. ', ' . $it->tarif;
    $total += $it->tarif;
}
echo  "<br>Tarif total de $total pesos";


$l = Liste::where('no', '=', 3)->first() ;
$li = $l->items()->get() ;
print("<br><br>Tous les objets de la liste no 3 : $l->titre");
echo  "<br>id, liste_id, nom, descr, img, tarif";
$total = 0;
foreach ($li as $it) {
    echo  "<br>" . $it->id . ', ' . $it->liste_id. ', ' . $it->nom. ', ' . $it->descr. ', ' . $it->img. ', '  . $it->tarif;
    $total += $it->tarif;
}
echo  "<br>Tarif total de $total pesos";
