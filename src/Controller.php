<?php

namespace raff312\ticTacToe\Controller;

use Exception as Exception;
use raff312\ticTacToe\Model\Board as Board;
use raff312\ticTacToe\Repositories\GamesRepository as GamesRepository;

use function raff312\ticTacToe\View\showGameBoard;
use function raff312\ticTacToe\View\showGamesInfoList;
use function raff312\ticTacToe\View\showGameReplay;
use function raff312\ticTacToe\View\showMessage;
use function raff312\ticTacToe\View\getValue;

use const raff312\ticTacToe\Model\PLAYER_X_MARKUP;

function startGame($argv)
{
    try {
        if (count($argv) <= 1 || $argv[1] === "--new" || $argv[1] === "-n") {
            startGameInternal();
        } elseif ($argv[1] === "--list" || $argv[1] === "-l") {
            listInfo();
        } elseif ($argv[1] === "--replay" || $argv[1] === "-r") {
            if (array_key_exists(2, $argv)) {
                $id = $argv[2];
                replayGame($id);
            } else {
                showMessage("There is no id");
            }
        } elseif ($argv[1] === "--help" || $argv[1] === "-h") {
            showHelpInfo();
        } else {
            showMessage("Unknown argument!");
        }
    } catch (Exception $e) {
        showMessage($e->getMessage());
    }
}

function startGameInternal()
{
    $canContinue = true;
    do {
        $gameBoard = new Board();
        initialize($gameBoard);
        gameLoop($gameBoard);
        inviteToContinue($canContinue);
    } while ($canContinue);
}

function initialize($board)
{
    try {
        $board->setDimension(getValue("Enter game board size"));
        $board->initialize();
    } catch (Exception $e) {
        showMessage($e->getMessage());
        initialize($board);
    }
}

function gameLoop($board)
{
    $stopGame = false;
    $winnerMarkup = PLAYER_X_MARKUP;
    $currentMarkup = PLAYER_X_MARKUP;
    $endGameMsg = "";

    $xCoords = "";
    $oCoords = "";

    do {
        showGameBoard($board);

        $winnerMarkup = $currentMarkup;
        if ($currentMarkup == $board->getUserMarkup()) {
            $coords = processUserTurn($board, $currentMarkup, $stopGame);
            $endGameMsg = "Player '$currentMarkup' wins the game.";
            $currentMarkup = $board->getComputerMarkup();
        } else {
            $coords = processComputerTurn($board, $currentMarkup, $stopGame);
            $endGameMsg = "Player '$currentMarkup' wins the game.";
            $currentMarkup = $board->getUserMarkup();
        }

        if ($currentMarkup === PLAYER_X_MARKUP) {
            $oCoords .= "($coords[0]; $coords[1]),";
        } else {
            $xCoords .= "($coords[0]; $coords[1]),";
        }

        if (!$board->isFreeSpaceEnough() && !$stopGame) {
            showGameBoard($board);
            $endGameMsg = "Draw!";
            $winnerMarkup = "Draw";
            $stopGame = true;
        }
    } while (!$stopGame);

    $xCoords = rtrim($xCoords, ",");
    $oCoords = rtrim($oCoords, ",");

    showGameBoard($board);
    showMessage($endGameMsg);

    $gamesRepository = new GamesRepository();
    $gamesRepository->add($board, $winnerMarkup, $xCoords, $oCoords);
}

function processUserTurn($board, $markup, &$stopGame)
{
    $answerTaked = false;
    do {
        try {
            $coords = getCoords($board);
            $board->setMarkupOnBoard($coords[0], $coords[1], $markup);
            if ($board->determineWinner($coords[0], $coords[1]) !== "") {
                $stopGame = true;
            }

            $answerTaked = true;
        } catch (Exception $e) {
            showMessage($e->getMessage());
        }
    } while (!$answerTaked);

    return $coords;
}

function getCoords($board)
{
    $markup = $board->getUserMarkup();
    $coords = getValue("Enter coords for player '$markup'");
    $coords = explode(" ", $coords);
    return $coords;
}

function processComputerTurn($board, $markup, &$stopGame)
{
    $answerTaked = false;
    do {
        $i = rand(0, $board->getDimension() - 1);
        $j = rand(0, $board->getDimension() - 1);
        try {
            $board->setMarkupOnBoard($i, $j, $markup);
            if ($board->determineWinner($i, $j) !== "") {
                $stopGame = true;
            }

            $answerTaked = true;
        } catch (Exception $e) {
        }
    } while (!$answerTaked);

    return [$i, $j];
}

function inviteToContinue(&$canContinue)
{
    $answer = "";
    do {
        $answer = strtolower(getValue("Do you want to continue? (y/n)"));
        if ($answer === "y") {
            $canContinue = true;
        } elseif ($answer === "n") {
            $canContinue = false;
        }
    } while ($answer !== "y" && $answer !== "n");
}

function listInfo()
{
    $gamesRepository = new GamesRepository();
    $infoList = $gamesRepository->getAll();
    showGamesInfoList($infoList);
}

function replayGame($id)
{
    $gamesRepository = new GamesRepository();
    $info = $gamesRepository->getById($id);
    showGameReplay($info->xCoords, $info->oCoords);
    showMessage("\nWinner: $info->winnerMarkup");
}

function showHelpInfo()
{
    showMessage("'Tic-tac-toe' with a computer on a field of arbitrary size (from 3x3 to 10x10)");
    showMessage("Coord format: i j, where i and j from 0 to (boardSize - 1)");
    showMessage("You can use following keys when start the program:");
    showMessage("--new or -n - start new game;");
    showMessage("--list or -l - show list of all games;");
    showMessage("--replay {id} or -r {id} - replay game with id;");
    showMessage("--help or -h - show short info about the game.");
}
