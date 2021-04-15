<?php

namespace App\Services;

use DateTime;
use Exception;

class HelperFunction //extends AbstractController
{
    private $storeSettings;

    /**
     * @return mixed
     */
    public function __construct(StoreSettings $storeSettings)
    {
        $this->storeSettings = $storeSettings;
    }
    /**
     * @param string|null $period
     * @return string|null
     * @throws Exception
     */
    public function createDateRange($period = null)
    {
        if ($period && $period != '24 hours' && $period != '7 days' && $period != '30 days' && $period !== 'lifetime') {
            return null;
        }

        if ($period === null || $period === 'lifetime') {

        } else {
            $end = (new DateTime('NOW'));
            $start = new DateTime('NOW');

            if ($period == '24 hours') {
                $start->modify('-24 hours');
            }
            if ($period == '7 days') {
                $start->modify('-6 days');
            }
            if ($period == '30 days') {
                $start->modify('-29 days');
            }
            $start = $start->format($this->storeSettings->getDateFormat());
            $end = $end->format($this->storeSettings->getDateFormat());
        }
        return $start.' - '.$end;
    }

    /**
     * NOT IN USE !!!!!
     *
     *
     * Helper function for permutations. Returns an array with all permutations
     *
     * @param $items
     * @param array $perms
     * @return array            # Returns an array with all permutations
     */
    function pc_permute($items, $perms = [])
    {
        if (empty($items)) {
            $return = array($perms);
        } else {
            $return = array();
            for ($i = count($items) - 1; $i >= 0; --$i) {
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);
                $return = array_merge($return, $this->pc_permute($newitems, $newperms));
            }
        }
        return $return;
    }
}