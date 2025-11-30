<?php

require_once 'vendor/autoload.php';
require_once 'Song.php';

class Stemlijst
{

    public $id;
    private $songs = [];
    private $name;

    public function __construct($hash)
    {
        $this->hash = $hash;
        $getList = DB::getConnection()->prepare("SELECT * FROM `StemLijst` WHERE `hash` = ?");
        $getList->execute([$this->hash]);
        $result = $getList->fetch(PDO::FETCH_ASSOC);
        if($result) {
            $this->id = $result['id'];
        } else {
            if ($this->parse()){
                $this->id = $this->save();
            }
        }
    }

    private function save(){
        $createList = DB::getConnection()->prepare("INSERT INTO `StemLijst` (`name`, `hash`) VALUES (?,?)");
        $createList->execute([$this->name, $this->hash]);
        $listId = DB::getConnection()->lastInsertId();


        $addSong = DB::getConnection()->prepare("INSERT INTO `Song_Stemlijst` (`song`, `stemlijst`) VALUES (?,?)");
        foreach ($this->songs as $song){
            $addSong->execute([$song->id, $listId]);
        }

        return $listId;
    }

    private function parse(){
        $url = 'https://npo.nl/luister/stem/npo-radio-2-top-2000/inzending/'.$this->hash;
        $ch = curl_init($url);
        $VALID_UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0";
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $VALID_UA);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);
        if(curl_errno($ch)) {
            $error_message = curl_error($ch);
            error_log('curl error '.$error_message);
            return false;
        }
        curl_close($ch);
        $pattern = '/tracks\\\":(\[.*?\])\}/';
        if(preg_match($pattern, $res, $matches)){
            $jsonGroup = $matches[1];
            $jsonGroup = str_replace('\"','"', $jsonGroup);
            $parsed_json = json_decode($jsonGroup, true);
            if(isset($parsed_json)) {
                foreach ($parsed_json as $song_object) {
                    $songObj = new Song(
                        $song_object['id'],
                        $song_object['artist'],
                        $song_object['title'],
                        isset($song_object['freeChoice']) && $song_object['freeChoice']                    );
                    $songObj->save();
                    array_push($this->songs, $songObj);
                }
            }
            $namePattern = '/userName\\\":\\\"(.*?)\\\"/';
            if (preg_match($namePattern, $res, $matches)) {
                $this->name = $matches[1];
            }
            return true;
        } else {
            error_log("No matches, probably not a real list");
            return false;
        }
    }

    private function parse_legacy(){
        $url = 'https://stem-backend.npo.nl/api/form/top-2000/'.$this->hash;
        $contents =file_get_contents($url);
        error_log($contents);
        if($contents == "Niet gevonden"){
            error_log("shit bestaat niet");
            return false;
        } else {
            $json = json_decode($contents, true);

            foreach ($json['shortlist'] as $song) {
                $songObj = new Song(
                    $song['_id'],
                    $song['_source']['artist'],
                    $song['_source']['title'],
                    $song['_id'] != 0
                );
                $songObj->save();
                array_push($this->songs, $songObj);
            }
            $this->name = $json['name'];
            return true;
        }
    }
}