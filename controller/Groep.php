<?php
namespace Stemlijst;
use DB;

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
        $getStatement = DB::getConnection()->prepare("SELECT s.*, COUNT(songStemlijst.song) votes FROM Groep g JOIN Groep_StemLijst gsl ON gsl.groep = g.id LEFT JOIN StemLijst l ON l.id = gsl.stemlijst JOIN Song_StemLijst songStemlijst ON songStemlijst.stemlijst = gsl.groep JOIN Song s ON songStemlijst.song = s.id GROUP BY songStemlijst.song ORDER BY votes WHERE g.slug = ?");

    }

    function addList($slug, $hash){



        $latte = new \Latte\Engine;
        return $latte->renderToString(__DIR__.'/../templates/created.latte', ['name'=>$name,'slug'=>$slug]);
    }

    function getRangLijst()
    {
        $sql = "SELECT s.*, COUNT(songStemlijst.song) votes FROM Groep g JOIN Groep_StemLijst gsl ON gsl.groep = g.id LEFT JOIN StemLijst l ON l.id = gsl.stemlijst JOIN Song_StemLijst songStemlijst ON songStemlijst.stemlijst = gsl.groep JOIN Song s ON songStemlijst.song = s.id GROUP BY songStemlijst.song ORDER BY votes WHERE g.slug = ?";
    }
}