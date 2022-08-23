<?php

namespace MGK\Auth\Models\Concerns;

trait HasAbilities
{
    /**
     * Initialize the trait.
     */
    public function initializeHasAbilities()
    {
        $this->casts = [
            ...$this->casts,
            'abilities' => 'json',
        ];
    }

    /**
     * Return the abilties that this object has.
     */
    public function getAbilities(): array
    {
        return $this->abilities ?? [];
    }

    /**
     * Has this object the given ability?
     */
    public function hasAbility(string $ability): bool
    {
        $godMode = in_array('god_mode:enabled', $this->getAbilities());
        $projectPermission = in_array(strtolower(config()->get('app.name')) . ':' . $ability, $this->getAbilities());
        $globalPermission = in_array('global:' . $ability, $this->getAbilities());

        return $godMode || $projectPermission || $globalPermission;
    }
}
