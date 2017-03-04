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

    // Timestamp 
    private $_time_stamp = 0;

    // Allowed Directions
    public function getAllowedDirections() {
        return $this->_allowed_direction;
    }

    public function getAllowedDirectionValue($key) {
        return $this->_allowed_direction[$key];
    }

    public function disallowDirection($direction) {
        $this->_allowed_direction[$direction] = false;
    }

    public function allowDirection($direction) {
        $this->_allowed_direction[$direction] = true;
    }

    // Preferred Directions
    public function getPreferedDirections() {
        return $this->_prefered_direction;
    }

    public function getPreferedDirectionValue($key) {
        return $this->_prefered_direction[$key];
    }

    public function incrementPreferedDirectionValue($key, $value) {
        $this->_prefered_direction[$key] += $value;
    }

    public function decrementPreferedDirectionValue($key, $value) {
        $this->_prefered_direction[$key] -= $value;
    }

    // Tick Cache
    public function getTickCache() {
        return $this->_tick_cache;
    }

    public function getTickCacheValue($key) {
        return $this->_tick_cache[$key] ?? null;
    }

    public function setTickCacheValue($key, $value) {
        $this->_tick_cache[$key] = $value;
    }


    // Other
    public function firstValidDirection() {

      // Choose direction of best target if it is > 0 and > worst target. 
      // I.e. if they are all the same skip and choose randomly later.
      // TODO IDEA: if we can't go in one direction we should give higher preference to allowable dirs. 
      $targets = array( 'left' => $this->_prefered_direction['left'], 
        'up' => $this->_prefered_direction['up'], 
        'right' => $this->_prefered_direction['right'], 
        'down' => $this->_prefered_direction['down'] );
      arsort($targets);
      reset($targets);
      $bestKey = key($targets);
      $bestValue = $targets[$bestKey];
      asort($targets);
      reset($targets);
      $worstKey = key($targets);
      $worstValue = $targets[$worstKey];
      if($bestValue > 0 && $bestValue > $worstValue){
        if($bestKey == 'left' && $this->_allowed_direction['left']){
          return 'left'; // Go left
        } 
        if($bestKey == 'up' && $this->_allowed_direction['up']){
          return 'up'; // Go up
        }
        if($bestKey == 'right' && $this->_allowed_direction['right']){
          return 'right'; // Go Right
        }
        if($bestKey == 'down' && $this->_allowed_direction['down']){
          return 'down'; // Go Down
        }
      }

      // Choose a random allowable direction. This prevents direction bias
      for($i = 0; $i < 6; $i++ ){ // Try a few times to find a random direction that is available.
        $dir = rand(0, 3);
        if($dir == 0 && $this->_allowed_direction['left']){
          return 'left';
        } 
        if($dir == 1 && $this->_allowed_direction['up']){
          return 'up';
        }
        if($dir == 2 && $this->_allowed_direction['right']){
          return 'right';
        }
        if($dir == 3 && $this->_allowed_direction['down']){
          return 'down';
        }
      }

      // Choose an allowed direction
      foreach ($this->_allowed_direction as $direction => $boolean) {
        if ($boolean) {
          return $direction;
        }
      }

      // Worst case
      //return 'down';
    }
}