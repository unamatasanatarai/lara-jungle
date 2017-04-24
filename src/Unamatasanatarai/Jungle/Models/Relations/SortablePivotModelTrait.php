<?php

namespace Unamatasanatarai\Jungle\Models\Relations;

trait SortablePivotModelTrait
{

    public function sortableBelongsToMany(
        $related,
        $orderColumn = null,
        $table = null,
        $foreignKey = null,
        $otherKey = null,
        $relation = null
    ) {
        // If no relationship name was passed, we will pull backtraces to get the
        // name of the calling function. We will use that function name as the
        // title of this relation since that is a great convention to apply.
        if (is_null($relation)) {
            $relation = $this->guessBelongsToManyRelation();
        }

        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey
            ? : $this->getForeignKey();

        $otherKey = $otherKey
            ? : $instance->getForeignKey();

        // If no table name was provided, we can guess it by concatenating the two
        // models using underscores in alphabetical order. The two model names
        // are transformed to snake case from their default CamelCase also.
        if (is_null($table)) {
            $table = $this->joiningTable($related);
        }

        return new SortableBelongsToMany(
            $instance->newQuery(), $this, $table, $foreignKey, $otherKey, $relation, $orderColumn
        );
    }
}
