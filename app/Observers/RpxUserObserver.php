<?php

namespace App\Observers;

use App\Models\RpxUser;

class RpxUserObserver
{
    /**
     * Handle the RpxUser "created" event.
     *
     * @param \App\Models\RpxUser $rpxUser
     *
     * @return void
     */
    public function created(RpxUser $rpxUser)
    {
    }

    /**
     * Handle the RpxUser "updated" event.
     *
     * @param \App\Models\RpxUser $rpxUser
     *
     * @return void
     */
    public function updated(RpxUser $rpxUser)
    {
    }

    /**
     * Handle the RpxUser "deleted" event.
     *
     * @param \App\Models\RpxUser $rpxUser
     *
     * @return void
     */
    public function deleted(RpxUser $rpxUser)
    {
    }

    /**
     * Handle the RpxUser "restored" event.
     *
     * @param \App\Models\RpxUser $rpxUser
     *
     * @return void
     */
    public function restored(RpxUser $rpxUser)
    {
    }

    /**
     * Handle the RpxUser "force deleted" event.
     *
     * @param \App\Models\RpxUser $rpxUser
     *
     * @return void
     */
    public function forceDeleted(RpxUser $rpxUser)
    {
    }
}
