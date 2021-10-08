<?php

namespace raff312\ticTacToe\Repositories;

use Exception as Exception;
use SQLite3;
use raff312\ticTacToe\Model\Board as Board;
use stdClass;

const DB_PATH = "../data/games.db";

class GamesRepository
{
    private $db;

    public function __construct()
    {
        if (!is_dir("../data")) {
            mkdir("../data");
        }

        $this->db = new SQLite3(DB_PATH);
        $this->createTable();
    }

    private function createTable()
    {
        $gamesInfoTable = "CREATE TABLE IF NOT EXISTS gamesInfo(
            id INTEGER PRIMARY KEY,
            sizeBoard INTEGER,
            gameDate DATETIME,
            playerName TEXT,
            playerMarkup TEXT,
            winnerMarkup TEXT,
            xCoords TEXT,
            oCoords TEXT
        )";
        $this->db->exec($gamesInfoTable);
    }

    public function __destruct()
    {
        $this->db->close();
    }

    public function add(Board $board, $winnerMarkup, $xCoords, $oCoords)
    {
        $size = $board->getDimension();
        date_default_timezone_set("Europe/Moscow");
        $date = date("Y-m-d H:i:s");
        $playerName = getenv("username");
        $playerMarkup = $board->getUserMarkup();

        $this->db->exec("INSERT INTO gamesInfo (
            sizeBoard, 
            gameDate, 
            playerName, 
            playerMarkup, 
            winnerMarkup, 
            xCoords, 
            oCoords
        ) VALUES (
            '$size', 
            '$date', 
            '$playerName', 
            '$playerMarkup', 
            '$winnerMarkup', 
            '$xCoords', 
            '$oCoords'
        )");
    }

    public function getAll()
    {
        $result = [];

        $query = $this->db->query("SELECT * FROM gamesInfo");
        while ($row = $query->fetchArray()) {
            $info = new stdClass();
            $info->id = $row[0];
            $info->size = $row[1];
            $info->date = $row[2];
            $info->name = $row[3];
            $info->playerMarkup = $row[4];
            $info->winnerMarkup = $row[5];
            $info->xCoords = $row[6];
            $info->oCoords = $row[7];
            array_push($result, $info);
        }

        return $result;
    }

    public function getById($id)
    {
        if (!$this->idExists($id)) {
            throw new Exception("This id doesn't exist");
        }

        $result = new stdClass();

        $query = "SELECT * FROM gamesInfo WHERE id='$id'";
        $query = $this->db->query($query);
        while ($row = $query->fetchArray()) {
            $result->id = $row[0];
            $result->size = $row[1];
            $result->date = $row[2];
            $result->name = $row[3];
            $result->playerMarkup = $row[4];
            $result->winnerMarkup = $row[5];
            $result->xCoords = $row[6];
            $result->oCoords = $row[7];
        }

        return $result;
    }

    private function idExists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM gamesInfo WHERE id='$id')";
        return $this->db->querySingle($query) == 1;
    }
}
