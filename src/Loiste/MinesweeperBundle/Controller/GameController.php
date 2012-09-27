<?php

namespace Loiste\MinesweeperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Loiste\MinesweeperBundle\Model\Game;

class GameController extends Controller
{
    public function beginAction() {
        return $this->render('LoisteMinesweeperBundle:Default:begin.html.twig');
    }

    public function startAction()
    {
        // set difficulty (=mine density) based on user choice, defaults to medium
        $difficulty = $this->getRequest()->get('difficulty'); 

        $difficulties = array(
            'easy' => Game::DIFFICULTY_EASY,
            'medium' => Game::DIFFICULTY_MEDIUM,
            'hard' => Game::DIFFICULTY_HARD,
        );

        $value = Game::DIFFICULTY_MEDIUM;

        if(isset($difficulty) && isset($difficulties[$difficulty])) {
            $value = $difficulties[$difficulty];
        }

        // Setup an empty game. To keep things very simple for candidates, we just store info on the session.
        $game = new Game($value);

        $session = new Session();
        $session->start();
        $session->set('game', $game);

        return $this->render('LoisteMinesweeperBundle:Default:index.html.twig', array(
            'game' => $game,
        ));
    }

    public function makeMoveAction()
    {
        $row = $this->getRequest()->get('row'); // Retrieves the row index.
        $column = $this->getRequest()->get('column'); // Retrieves the column index.

        $session = new Session();
        $session->start();
        $game = $session->get('game'); /** @var $game Game */

        // if game is running, check the tile
        if($game && $game->isRunning()) {
            $game->checkCell($row, $column);            
        }

        return $this->render('LoisteMinesweeperBundle:Default:index.html.twig', array(
            'game' => $game,
        ));
    }
}
