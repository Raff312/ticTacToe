<?php

namespace raff312\ticTacToe\View;

use function cli\prompt;
use function cli\line;
use function cli\out;

function showGameBoard($board)
{
    $boardArr = $board->getBoardArr();
    $dim = $board->getDimension();
    for ($i = 0; $i < $dim; $i++) {
        for ($j = 0; $j < $dim; $j++) {
            $tempVar = $boardArr[$i][$j];
            out("|$tempVar");
            if ($j === ($dim - 1)) {
                out("|");
            }
        }
        line();
    }

    line("-------------------------------------------");
}

function showGamesInfoList($list)
{
    foreach ($list as $value) {
        showMessage("ID: $value->id");
        showMessage("Board size: $value->size");
        showMessage("Date: $value->date");
        showMessage("Player name: $value->name");
        showMessage("Player markup: $value->playerMarkup");
        showMessage("Winner markup: $value->winnerMarkup");
    }
}

function showGameReplayStep($stepNum, $xCoords, $oCoords)
{
    line("\nStep #: $stepNum\n'X' coord: $xCoords\n'O' coord: $oCoords");
}

function showMessage($msg)
{
    line($msg);
}

function getValue($msg)
{
    return prompt($msg);
}
