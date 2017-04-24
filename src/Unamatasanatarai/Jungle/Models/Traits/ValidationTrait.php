<?php

namespace Unamatasanatarai\Jungle\Models\Traits;

use Validator;

trait ValidationTrait
{

    public static function getValidationRules($scenario = 'default', $modelId = false)
    {
        $rules = self::$rules[ $scenario ] ?? self::$rules['default'] ?? [];

        if ( ! empty($modelId)) {
            foreach ($rules as &$rule) {
                $rule = str_replace('ID', $modelId, $rule);
            }
        }

        return $rules;
    }

    public static function validate($request, $scenario = 'default', $modelId = null)
    {
        return Validator::make(
            $request,
            self::getValidationRules($scenario, $modelId)
        );
    }
}
