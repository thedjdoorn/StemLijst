<?php

require_once 'vendor/autoload.php';
require_once 'Song.php';

class Stemlijst
{

    private $songs = [];
    private $name;

    public function __construct($hash)
    {
        $this->hash = $hash;
    }

    public function save(){
        $getList = DB::getConnection()->prepare("SELECT * FROM `StemLijst` WHERE `hash` = ?");
        $getList->execute([$this->hash]);
        $result = $getList->fetch(PDO::FETCH_ASSOC);
        if($result) {
            return $result['id'];
        }
        $createList = DB::getConnection()->prepare("INSERT INTO `StemLijst` (`name`, `hash`) VALUES (?,?)");
        $createList->execute([$this->name, $this->hash]);
        $listId = DB::getConnection()->lastInsertId();


        $addSong = DB::getConnection()->prepare("INSERT INTO `Song_Stemlijst` (`song`, `stemlijst`) VALUES (?,?)");
        foreach ($this->songs as $song){
            $addSong->execute([$song->id, $listId]);
        }

        return $listId;
    }

    public function parse(){
        $url = 'https://stem-backend.npo.nl/api/form/top-2000/'.$this->hash;
        $contents =file_get_contents($url);
        $json = json_decode($contents, true);

        foreach ($json['shortlist'] as $song){
            $songObj = new Song(
                $song['_id'],
                $song['_source']['artist'],
                $song['_source']['title'],
                $song['_id'] != 0
                );
            array_push($this->songs, $songObj);
            $songObj->save();
        }
        $this->name = $json['name'];
    }
}