<?php

declare(strict_types=1);

namespace App\Actions;

/**
 * Base action.
 *
 * Actions are single-responsibility units of work (e.g. one operation per
 * class), invoked through the container and callable via `__invoke`. This keeps
 * services thin and business steps individually testable.
 */
abstract class BaseAction
{
    /**
     * Execute the action.
     *
     * @param  mixed  ...$parameters
     */
    abstract public function handle(mixed ...$parameters): mixed;

    /**
     * @param  mixed  ...$parameters
     */
    public function __invoke(mixed ...$parameters): mixed
    {
        return $this->handle(...$parameters);
    }
}
