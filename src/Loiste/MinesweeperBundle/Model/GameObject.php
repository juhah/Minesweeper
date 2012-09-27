<?php

namespace Loiste\MinesweeperBundle\Model;

/**
 * This class represents a game object.
 */
class GameObject
{
    const TYPE_UNDISCOVERED = 0;
    const TYPE_MINE = 1;
    const TYPE_EMPTY = 2; // Discovered.
    const TYPE_NUMBER = 3; // Discovered.
    const TYPE_EXPLOSION = 4; // Damn we hit a mine!
    const TYPE_MINE_DISCOVERED = 5;

    public $type;
    public $number; 
    public $discovered; 

    /**
     * Constructor
     */
    public function __construct($type = 0, $discovered = FALSE)
    {
        $this->type = $type;
        $this->number = 0;
        $this->discovered = $discovered;
    }

    /**
     * 
     */
    public function isMine()
    {
        return $this->type === GameObject::TYPE_MINE;
    }

    /**
     * 
     */
    public function isNumber()
    {
        return $this->type === GameObject::TYPE_NUMBER;
    }

    /**
     * 
     */
    public function isEmpty()
    {
        return $this->type === GameObject::TYPE_EMPTY;
    }

    /**
     * Returns the number of mines around this cell.
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Sets the number of mines
     */
    public function setNumber($number) {
        $this->number = $number;
    }

    /**
     * Sets cell discovered
     */
    public function setDiscovered($discovered) {
        $this->discovered = $discovered;
    }
}