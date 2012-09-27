<?php

namespace Loiste\MinesweeperBundle\Model;

define('GAME_AREA_ROWS', 10);
define('GAME_AREA_COLS', 20);

/**
 * This class represents a game model.
 */
class Game
{
    const DIFFICULTY_EASY = 0;
    const DIFFICULTY_MEDIUM = 1;
    const DIFFICULTY_HARD = 2;

    /**
     * A two dimensional array of game objects.
     *
     * E.x.: $gameArea[3][2] instance of GameObject
     *
     * @var array
     */
    public $gameArea;
    public $running = false;
    public $difficulty;

    /**
     * Constructor
     */
    public function __construct($difficulty = Game::DIFFICULTY_MEDIUM)
    {
        // Upon constructing a new game instance, setup an empty game area.
        $this->gameArea = array();

        $this->difficulty = $difficulty;

        // setup mines according to density
        $cells = GAME_AREA_ROWS * GAME_AREA_COLS;
        $mines = round($this->getMineDensity($this->difficulty) * $cells);
        
        $objects = array();

        for($i = 0; $i < $cells; $i++) {
            // create the needed amount of mines
            if($i < $mines) {
                $objects[] = new GameObject(GameObject::TYPE_MINE);
            }
            else {
                $objects[] = new GameObject(GameObject::TYPE_EMPTY);
            }
        }
        
        // randomize and set game area
        shuffle($objects);

        for ($row = 0; $row < GAME_AREA_ROWS; $row++) {
            $this->gameArea[$row] = array_slice($objects, $row * GAME_AREA_COLS, GAME_AREA_COLS);
        }

        // start game and update mine numbers
        $this->running = true;
        $this->setupBoard();
    }

    /**
     * Get mine density based on diffculty
     */
    private function getMineDensity($difficulty) {
        $value = 0.5; // default to medium

        if($difficulty == Game::DIFFICULTY_EASY) {
            $value = 0.2;
        }
        else if($difficulty == Game::DIFFICULTY_HARD) {
            $value = 0.7;
        }

        return $value;
    }

    /**
     *
     */
    public function isRunning() {
        return $this->running;
    }

    /**
     *
     */
    private function setupBoard() {
        for($row = 0; $row < GAME_AREA_ROWS; $row++) {
            for($col = 0; $col < GAME_AREA_COLS; $col++) {
                $this->updateNumbers($row, $col);
            }
        }
    }

    /**
     *
     */
    private function updateNumbers($row, $col) {
        $obj = &$this->gameArea[$row][$col];

        if($obj->type == GameObject::TYPE_MINE) {
            return;
        }

        $mines = 0;

        // set boundaries
        $row_start = max($row - 1, 0);
        $row_end   = min($row + 1, GAME_AREA_ROWS - 1);
        $col_start = max($col - 1, 0);
        $col_end   = min($col + 1, GAME_AREA_COLS - 1);

        for($i = $row_start; $i <= $row_end; $i++) {
            for($j = $col_start; $j <= $col_end; $j++) {
                if($this->gameArea[$i][$j]->type == GameObject::TYPE_MINE) {
                   $mines++; 
                }
            }
        }

        // set type and number according to found mines
        if($mines == 0) {
            $obj->type = GameObject::TYPE_EMPTY;
            $obj->setNumber(0);
        }
        else {
            $obj->type = GameObject::TYPE_NUMBER;
            $obj->setNumber($mines);
        }
    }

    /**
     *
     */
    private function revealMines() {
        for($row = 0; $row < GAME_AREA_ROWS; $row++) {
            for($col = 0; $col < GAME_AREA_COLS; $col++) {
                $obj = $this->gameArea[$row][$col];

                if($obj->type == GameObject::TYPE_MINE) {
                    $obj->setDiscovered(TRUE);
                }
            }
        }        
    }

    /**
     *
     */
    public function checkCell($row, $col) {
        $obj = &$this->gameArea[$row][$col];

        $obj->setDiscovered(TRUE);

        // if we hit a mine, it's game over
        if($obj->type == GameObject::TYPE_EMPTY) {
            $this->showAdjacentEmptyCells($row, $col);
        }
        else if($obj->type == GameObject::TYPE_MINE) {
            $obj->type = GameObject::TYPE_EXPLOSION;

            $this->revealMines();
            $this->running = false;
        }
    }

    public function showAdjacentEmptyCells($row, $col) {
        $obj = &$this->gameArea[$row][$col];

        if($obj->type != GameObject::TYPE_EMPTY) {
            return;
        }

        // set boundaries
        $row_start = max($row - 1, 0);
        $row_end   = min($row + 1, GAME_AREA_ROWS - 1);
        $col_start = max($col - 1, 0);
        $col_end   = min($col + 1, GAME_AREA_COLS - 1);

        for($i = $row_start; $i <= $row_end; $i++) {
            for($j = $col_start; $j <= $col_end; $j++) {
                if($i == $row && $j == $col) {
                    continue;
                }

                $tmpobj = &$this->gameArea[$i][$j];
                if($tmpobj->type != GameObject::TYPE_MINE && !$tmpobj->discovered) {
                    $tmpobj->setDiscovered(TRUE);

                    if($tmpobj->type == GameObject::TYPE_EMPTY) {
                        $this->showAdjacentEmptyCells($i, $j);
                    }
                }
            }
        }
    }
}