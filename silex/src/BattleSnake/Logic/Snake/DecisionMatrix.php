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

    // Prefered Directions (weighting)
    private $_prefered_direction = [
      'left'  => 0,
      'right' => 0,
      'up'    => 0,
      'down'  => 0
    ];

    // Cache Board state
    private $_tick_cache = [];

    // Allowed Directions
    public function getAllowedDirections() {
        return $this->_allowed_direction;
    }

    public function getAllowedDirectionValue(string $key) {
        return $this->_allowed_direction[$key];
    }

    public function disallowDirection(string $direction) {
        $this->_allowed_direction[$direction] = false;
    }

    public function allowDirection(string $direction) {
        $this->_allowed_direction[$direction] = true;
    }

    // Preferred Directions
    public function getPreferedDirections() {
        return $this->_prefered_direction;
    }

    public function getPreferedDirectionValue(string $key) {
        return $this->_prefered_direction[$key];
    }

    public function incrementPreferedDirectionValue(string $key, int $value) {
        $this->_prefered_direction[$key] += $value;
    }

    public function decrementPreferedDirectionValue(string $key, int $value) {
        $this->_prefered_direction[$key] -= $value;
    }

    // Tick Cache
    public function getTickCache() {
        return $this->_tick_cache;
    }

    public function getTickCacheValue(string $key) {
        return $this->_tick_cache[$key];
    }

    public function setTickCacheValue(string $key, string $value) {
        $this->_tick_cache[$key] = $value;
    }


    // Other
    public function firstValidDirection() {
        foreach ($this->_allowed_direction as $direction => $boolean) {
            if ($boolean) {
                return $direction;
            }
        }
    }
}