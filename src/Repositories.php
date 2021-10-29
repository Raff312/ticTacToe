<?php

namespace raff312\ticTacToe\Repositories;

use RedBeanPHP\Facade as R;
use Exception as Exception;
use raff312\ticTacToe\Model\Board as Board;
use stdClass;

const DB_PATH = "../data/games.db";

class GamesRepository
{
    public function __construct()
    {
        if (!is_dir("../data")) {
            mkdir("../data");
        }

        R::setup("sqlite:" . DB_PATH);
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
        R::exec($gamesInfoTable);
    }

    public function __destruct()
    {
        R::close();
    }

    public function add(Board $board, $winnerMarkup, $xCoords, $oCoords)
    {
        $size = $board->getDimension();
        date_default_timezone_set("Europe/Moscow");
        $date = date("Y-m-d H:i:s");
        $playerName = getenv("username");
        $playerMarkup = $board->getUserMarkup();

        R::exec("INSERT INTO gamesInfo (
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

        $queryArr = R::getAll("SELECT * FROM gamesInfo");
        for ($i = 0; $i < count($queryArr); $i++) {
            $info = new stdClass();
            $info->id = $queryArr[$i]["id"];
            $info->size = $queryArr[$i]["sizeBoard"];
            $info->date = $queryArr[$i]["gameDate"];
            $info->name = $queryArr[$i]["playerName"];
            $info->playerMarkup = $queryArr[$i]["playerMarkup"];
            $info->winnerMarkup = $queryArr[$i]["winnerMarkup"];
            $info->xCoords = $queryArr[$i]["xCoords"];
            $info->oCoords = $queryArr[$i]["oCoords"];
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
        $queryArr = R::getAll($query);
        for ($i = 0; $i < count($queryArr); $i++) {
            $result->id = $queryArr[$i]["id"];
            $result->size = $queryArr[$i]["sizeBoard"];
            $result->date = $queryArr[$i]["gameDate"];
            $result->name = $queryArr[$i]["playerName"];
            $result->playerMarkup = $queryArr[$i]["playerMarkup"];
            $result->winnerMarkup = $queryArr[$i]["winnerMarkup"];
            $result->xCoords = $queryArr[$i]["xCoords"];
            $result->oCoords = $queryArr[$i]["oCoords"];
        }

        return $result;
    }

    private function idExists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM gamesInfo WHERE id='$id')";
        return count(R::getRow($query)) >= 1;
    }
}
