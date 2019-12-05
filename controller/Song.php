<?php

class Song
{
    public $id;
    public function __construct($id, $artist, $track, $freeChoice)
    {
        $this->npoId = $id;
        $this->artist = $artist;
        $this->track = $track;
        $this->freeChoice = $freeChoice;
    }

    function save(){
        $get = DB::getConnection()->prepare("SELECT * FROM `Song` WHERE `npoId` = ?");
        $get->execute([$this->npoId]);
        $result = $get->fetch(PDO::FETCH_ASSOC);
        if($result){
            $this->id = $result['id'];
            return;
        }
        $insert = DB::getConnection()->prepare("INSERT INTO `Song`(`artist`, `title`, `npoId`) VALUES (?,?,?)");
        $insert->execute(array($this->artist, $this->track, !$this->freeChoice?null:$this->npoId ));
        $this->id = DB::getConnection()->lastInsertId();
    }
}