<?php

namespace Unamatasanatarai\Jungle\Models\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Positionable
 *
 * @package App\Helpers\Traits
 */
trait PositionTrait
{

    /**
     * Events
     *
     * @return void
     */
    protected static function bootPositionTrait()
    {
        static::creating(
            function ($model) {
                $model->{static::$positionable['column']} = $model->getMaxPosition();
            }
        );
    }

    protected function positionComposeConditions()
    {
        $conditions = [];

        if ( ! empty(static::$positionable['conditions'])) {
            foreach (static::$positionable['conditions'] as $column) {
                $conditions[ $column ] = $this->$column;
            }
        }

        return $conditions;
    }

    private function setPositionConditions()
    {
        $conditions = $this->positionComposeConditions();

        $search = self::select();

        foreach ($conditions as $condition => $value) {
            $search->where($condition, $value);
        }

        return $search;
    }

    public function getMaxPosition()
    {
        $search = $this->setPositionConditions();

        $search->orderBy(static::$positionable['column'], 'desc');

        $result = $search->first();
        if ( ! $result) {
            return 0;
        }

        return (int) $result->{static::$positionable['column']} + 1;
    }

    public function moveUp()
    {
        $current = $this->{static::$positionable['column']};
        $prev    = $this->prev();

        if ( ! empty($prev)) {
            $this->{static::$positionable['column']} = $prev->{static::$positionable['column']};
            $prev->{static::$positionable['column']} = $current;
            $this->save();
            $prev->save();
        }

        return $this;
    }

    public function moveDown()
    {
        $current = $this->{static::$positionable['column']};
        $next    = $this->next();

        if ( ! empty($next)) {
            $this->{static::$positionable['column']} = $next->{static::$positionable['column']};
            $next->{static::$positionable['column']} = $current;
            $this->save();
            $next->save();
        }

        return $this;
    }

    /**
     * Get next element.
     * If there is no next element, return $this->getMaxPosition()
     *
     * @return Model [description]
     */
    public function next()
    {
        $search = $this->setPositionConditions();

        $search->orderBy(static::$positionable['column'], 'asc');
        $search->where(static::$positionable['column'], '>', $this->{static::$positionable['column']});

        return $search->first();
    }

    /**
     * Get next element's position.
     * If there is no next element, return $this->getMaxPosition()
     *
     * @return integer [description]
     */
    public function getNextPosition()
    {
        $next = $this->next();

        if (empty($next)) {
            return $this->getMaxPosition();
        }

        return $next->{static::$positionable['column']};
    }

    /**
     * Get Previous element.
     * If there is no previous element, get current `pos - 1`
     * if current.pos == 0, return 0
     *
     * @return Model [description]
     */
    public function prev()
    {
        $search = $this->setPositionConditions();

        $search->orderBy(static::$positionable['column'], 'desc');
        $search->where(static::$positionable['column'], '<', $this->{static::$positionable['column']});

        return $search->first();
    }

    /**
     * Get Previous element's position.
     * If there is no previous element, get current `pos - 1`
     * if current.pos == 0, return 0
     *
     * @return integer [description]
     */
    public function getPrevPosition()
    {
        $prev = $this->prev();

        if (empty($prev)) {
            return $this->getMaxPosition();
        }

        return $prev->{static::$positionable['column']};
    }

    /**
     * @return mixed
     */
    public function ordered()
    {
        $search = $this->setPositionConditions();

        $search->orderBy(static::$positionable['column'], 'asc');

        return $search->get();
    }

    /**
     * [reorderPosition description]
     *
     * @param array $conditionsValues
     *
     * @return int [description]
     */
    public static function reorderPosition(Array $conditionsValues)
    {
        $all  = self::getAllOrdered($conditionsValues);
        $prev = null;

        foreach ($all as $item) {
            if (isset($prev)) {
                $item->{static::$positionable['column']} = ++$prev;
            } else {
                $prev                                    = 0;
                $item->{static::$positionable['column']} = $prev;
            }
            $item->save();
        }
    }

    public static function getAllOrdered(Array $conditionsValues)
    {
        $conditions = [];

        if ( ! empty(static::$positionable['conditions'])) {
            foreach (static::$positionable['conditions'] as $column) {
                $conditions[ $column ] = $conditionsValues[ $column ];
            }
        }

        $search = self::select();

        foreach ($conditions as $condition => $value) {
            $search->where($condition, $value);
        }

        $search->orderBy(static::$positionable['column'], 'asc');

        return $search->get();
    }
}
