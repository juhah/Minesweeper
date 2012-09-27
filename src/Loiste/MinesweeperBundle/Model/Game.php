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
        
        // randomize and set game area columns to rows
        shuffle($objects);

        for ($row = 0; $row < GAME_AREA_ROWS; $row++) {
            $this->gameArea[$row] = array_slice($objects, $row * GAME_AREA_COLS, GAME_AREA_COLS);
        }

        // start game and update mine numbers
        $this->setupBoard();
        $this->running = true;
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
     * Test if game is running or not
     */
    public function isRunning() {
        return $this->running;
    }

    /**
     * Sets up numbers to tiles based on neighbouring cell mines
     */
    private function setupBoard() {
        for($row = 0; $row < GAME_AREA_ROWS; $row++) {
            for($col = 0; $col < GAME_AREA_COLS; $col++) {
                $this->updateNumbers($row, $col);
            }
        }
    }

    /**
     * Update the mine count of given cell
     */
    private function updateNumbers($row, $col) {
        $obj = &$this->gameArea[$row][$col];

        if($obj->type == GameObject::TYPE_MINE) {
            return;
        }

        $mines = 0;

        // set neighbour boundaries and count surrounding mines
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
     * Reveal all mines on the board
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
     * Check cell after user makes a move to this cell
     */
    public function checkCell($row, $col) {
        $obj = &$this->gameArea[$row][$col];

        $obj->setDiscovered(TRUE);

        // on empty cell, show neighbouring empty cells also
        if($obj->type == GameObject::TYPE_EMPTY) {
            $this->showAdjacentEmptyCells($row, $col);
        }
        else if($obj->type == GameObject::TYPE_MINE) {
            // if we hit a mine, it's game over
            $obj->type = GameObject::TYPE_EXPLOSION;

            $this->revealMines();
            $this->running = false;
        }
    }

    /**
     * Shows empty cells and also neighbouring empty cells (expanding reveal)
     */
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
                $tmpobj = &$this->gameArea[$i][$j];

                // skip cell if discovered or a mine
                if($i == $row && $j == $col || 
                    $tmpobj->type == GameObject::TYPE_MINE ||
                    $tmpobj->discovered) {
                    continue;
                }

                $tmpobj->setDiscovered(TRUE);

                // recursively check empty undiscoreved neighbours also
                if($tmpobj->type == GameObject::TYPE_EMPTY) {
                    $this->showAdjacentEmptyCells($i, $j);
                }
            }
        }
    }
}