<?php

namespace Manusiakemos\Wirecrud;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Manusiakemos\Wirecrud\Skeleton\SkeletonClass
 */
class WirecrudFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wirecrud';
    }
}
