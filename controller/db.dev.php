<?php

// in-memory sqlite database, requires extension for Sqlite and PDO
// also kind of a hacky singleton solution, please don't really use this, for dev purposes only

class DB
{
    private static $conn;
    private static $prepared = false;

    /**
     * @return PDO connection
     */
    static function getConnection(){
        if(!self::$conn){
            self::connect();
            if(!self::$prepared){
                error_log("Setting up");
                try {
                    self::setup();
                } catch (PDOException $e){
                    error_log($e);
                }
            }
        }
        return self::$conn;
    }

    static function connect(){
        try {
            self::$conn = new PDO(
                'sqlite:../db.sqlite',
                null,
                null,
                array(PDO::ATTR_PERSISTENT => true)
            );
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            error_log("PDO Created");
        } catch (PDOException $e){
            error_log($e);
        }
    }

    static function setup()
    {
        self::$conn->exec("CREATE TABLE `Groep` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT,
 `name` varchar(255) NOT NULL,
 `slug` varchar(128) NOT NULL UNIQUE
)");

        self::$conn->exec("CREATE TABLE `StemLijst` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT,
 `name` varchar(255) NOT NULL,
 `hash` varchar(42) NOT NULL UNIQUE
)");

        self::$conn->exec("CREATE TABLE `Song` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT,
 `artist` varchar(255) NOT NULL,
 `title` varchar(255) NOT NULL,
 `npoId` int(11) UNIQUE
 
)");

        self::$conn->exec("CREATE TABLE `Groep_StemLijst` (
 `groep` int(11) NOT NULL,
 `stemlijst` int(11) NOT NULL,
 CONSTRAINT `groepUnique` UNIQUE (`groep`,`stemlijst`),
 CONSTRAINT `groep` FOREIGN KEY (`groep`) REFERENCES `Groep` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `groep_stemlijst` FOREIGN KEY (`stemlijst`) REFERENCES `StemLijst` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)");

        self::$conn->exec("CREATE TABLE `Song_StemLijst` (
 `song` int(11) NOT NULL,
 `stemlijst` int(11) NOT NULL,
 CONSTRAINT `songUnique` UNIQUE (`song`,`stemlijst`),
 CONSTRAINT `song` FOREIGN KEY (`song`) REFERENCES `Song` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `song_stemlijst` FOREIGN KEY (`stemlijst`) REFERENCES `StemLijst` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)");
        
        self::$prepared = true;
    }
}