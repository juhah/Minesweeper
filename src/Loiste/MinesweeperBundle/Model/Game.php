<?php

namespace Loiste\MinesweeperBundle\Model;

define('GAME_AREA_ROWS', 10);
define('GAME_AREA_COLS', 20);
define('MINE_DENSITY', 0.3);

/**
 * This class represents a game model.
 */
class Game
{
    /**
     * A two dimensional array of game objects.
     *
     * E.x.: $gameArea[3][2] instance of GameObject
     *
     * @var array
     */
    public $gameArea;
    public $running = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Upon constructing a new game instance, setup an empty game area.
        $this->gameArea = array();

        // setup mines according to density
        $tiles = GAME_AREA_ROWS * GAME_AREA_COLS;
        $mines = round(MINE_DENSITY * $tiles);
        
        $objects = array();

        for($i = 0; $i < $tiles; $i++) {
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
     *
     */
    public function isRunning() {
        return $this->running;
    }

    /**
     *
     */
    public function setupBoard() {
        for($row = 0; $row < GAME_AREA_ROWS; $row++) {
            for($col = 0; $col < GAME_AREA_COLS; $col++) {
                $this->updateNumbers($row, $col);
            }
        }
    }

    /**
     *
     */
    public function updateNumbers($row, $col) {
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
    public function revealMines() {
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
    public function checkTile($row, $col) {
        $obj = &$this->gameArea[$row][$col];

        $obj->setDiscovered(TRUE);

        // if we hit a mine, it's game over
        if($obj->type == GameObject::TYPE_EMPTY) {
            //echo "$row:$col<br>";
            $this->discoverEmpties($row, $col);
        }
        else if($obj->type == GameObject::TYPE_MINE) {
            $obj->type = GameObject::TYPE_EXPLOSION;

            $this->revealMines();
            $this->running = false;
        }

        //$this->updateGame();
    }

    public function discoverEmpties($row, $col) {
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
                        $this->discoverEmpties($i, $j);
                    }
                }
            }
        }
    }
}