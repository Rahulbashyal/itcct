<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class HiddenRoleScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Filters out roles where is_hidden = true
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Prevent infinite recursion during authentication checks
        if (isset($GLOBALS['is_resolving_hidden_scope'])) {
            return;
        }

        $GLOBALS['is_resolving_hidden_scope'] = true;

        try {
            // If SuperAdmin is logged in, they should see everyone
            $user = auth()->user();
            if ($user && $user->role === 'SuperAdmin') {
                return;
            }
        } catch (\Throwable $e) {
            // Silently fail if auth is not yet available
        } finally {
            unset($GLOBALS['is_resolving_hidden_scope']);
        }

        $builder->where('is_hidden', false);
    }
}
