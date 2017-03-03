<?php

/*
  * DirectionMatrix - Contains information about where to move
*/

namespace BattleSnake\Logic\Snake;

class DecisionMatrix
{
    // Allowed Directions
    private $_allowed_direction = [
      'left'  => true,
      'right' => true,
      'up'    => true,
      'down'  => true
    ];

    private $_prefered_direction = [
      'left'  => 0,
      'right' => 0,
      'up'    => 0,
      'down'  => 0
    ];

    public function getAllowedDirections() {
        return $this->_allowed_direction;
    }

    public function getPreferedDirections() {
        return $this->_prefered_direction;
    }

    public function disallowDirection($direction) {
        $this->_allowed_direction[$direction] = false;
    }

    public function firstValidDirection() {
        foreach ($this->_allowed_direction as $direction => $boolean) {
            if ($boolean) {
                return $direction;
            }
        }
    }
}