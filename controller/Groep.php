<?php
namespace Stemlijst;
use DB;
use PDO;

require_once __DIR__. '/../vendor/autoload.php';
require_once 'db.dev.php';
class GroepController
{
    function create($name)
    {
        $gen = new \Faker\Generator();
        $comp = new \Faker\Provider\nl_NL\Company($gen);
        $slug = preg_replace('/[^A-Za-z0-9?!]/', '',\Faker\Provider\nl_NL\Color::colorName().$comp->jobTitle());
        $insertStatement = DB::getConnection()->prepare("INSERT INTO `Groep`(`name`, `slug`) VALUES (?,?)");
        $insertStatement->execute(array($name, $slug));

        $latte = new \Latte\Engine;
        return $latte->renderToString(__DIR__.'/../templates/created.latte', ['name'=>$name,'slug'=>$slug]);
    }

    function get($slug)
    {

        $getGroup = DB::getConnection()->prepare("SELECT * FROM `Groep` WHERE `slug` = ?");
        $getGroup->execute([$slug]);
        $group = $getGroup->fetch(PDO::FETCH_ASSOC);
        if(!$group){
            return null;
        }
        $lists = DB::getConnection()->prepare("SELECT * FROM `Groep_Stemlijst` gsl JOIN `Stemlijst` sl ON gsl.stemlijst = sl.id WHERE gsl.groep = ?");

        $latte = new \Latte\Engine;
        return $latte->renderToString(__DIR__.'/../templates/group.latte', ['name'=>$group['name'],'slug'=>$group['slug'], 'names'=>$this->getNames($slug), 'list' => $this->getRangLijst($slug)]);
//        $getListStatement = DB::getConnection()->prepare("SELECT s.*, COUNT(songStemlijst.song) votes FROM Groep g JOIN Groep_StemLijst gsl ON gsl.groep = g.id LEFT JOIN StemLijst l ON l.id = gsl.stemlijst JOIN Song_StemLijst songStemlijst ON songStemlijst.stemlijst = gsl.groep JOIN Song s ON songStemlijst.song = s.id GROUP BY songStemlijst.song ORDER BY votes WHERE g.slug = ?");

    }

    function addList($slug, $hash){
        $latte = new \Latte\Engine;

        $getGroup = DB::getConnection()->prepare("SELECT * FROM `Groep` WHERE `slug` = ?");
        $getGroup->execute([$slug]);
        $group = $getGroup->fetch(PDO::FETCH_ASSOC);

        if(!$group){
            return null;
        }

        $lijst = new \Stemlijst($hash);
        if(!$lijst->id){
            return $latte->renderToString(__DIR__.'/../templates/group.latte', ['name'=>$group['name'],'slug'=>$group['slug'], 'names'=>$this->getNames($slug), 'error' => 'Deze lijst bestaat niet!']);
        }

        $existStatement = DB::getConnection()->prepare("SELECT * FROM `Groep_Stemlijst` gsl WHERE gsl.groep = ? AND gsl.stemlijst = ?");
        $existStatement->execute([$group['id'], $lijst->id]);
        $exists = $existStatement->fetch(PDO::FETCH_ASSOC);

        if($exists){
            return $latte->renderToString(__DIR__.'/../templates/group.latte', ['name'=>$group['name'],'slug'=>$group['slug'], 'names'=>$this->getNames($slug), 'error' => 'Deze lijst is al toegevoegd aan deze groep']);
        }

        $insertStatement = DB::getConnection()->prepare("INSERT INTO `Groep_Stemlijst` (groep, stemlijst) VALUES (?,?)");
        $insertStatement->execute([$group['id'], $lijst->id]);

        return $latte->renderToString(__DIR__.'/../templates/group.latte', ['name'=>$group['name'],'slug'=>$slug, 'names'=>$this->getNames($slug), 'success' => 'Je favorieten zijn toegevoegd!']);
    }

    function getRangLijst($slug)
    {
        $ranglijstStatement = DB::getConnection()->prepare("SELECT s.*, COUNT(s.id) votes FROM Groep g JOIN Groep_StemLijst gsl ON gsl.groep = g.id JOIN Song_StemLijst sls ON sls.stemlijst = gsl.stemlijst JOIN Song s ON s.id = sls.song WHERE g.slug = ? GROUP BY s.id ORDER BY votes DESC");
        $ranglijstStatement->execute([$slug]);
        return $ranglijst = $ranglijstStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    function getNames($slug)
    {
        $namesStatment = DB::getConnection()->prepare("SELECT s.name FROM Groep_StemLijst gs JOIN StemLijst s ON s.id = gs.stemlijst JOIN Groep g ON g.id = gs.groep WHERE g.slug = ?");
        $namesStatment->execute([$slug]);
        return $namesStatment->fetchAll(PDO::FETCH_COLUMN);
    }
}