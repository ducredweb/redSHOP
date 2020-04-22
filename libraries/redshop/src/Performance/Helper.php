<?php
/**
 * @package     RedShop
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Redshop\Performance;

defined('_JEXEC') or die;

/**
 * Performance Helper
 *
 * @since 3.0.1
 */
class Helper
{
    /**
     * @param         $property
     * @param         $id
     * @param         $subId
     * @param   bool  $force
     *
     * @return  mixed|null
     * @since   3.0.1
     */
    public static function load($property, $id, $subId = 0, $force = false)
    {
        if (self::isApply() || $force) {
            $redCache = \JFactory::getSession()->get('redCache', new \stdClass);

            if (isset($subId)
                && (bool)$subId
                && isset($redCache->$property[$id][$subId])
            ) {
                return $redCache->$property[$id][$subId];
            } elseif (isset($redCache->$property[$id])) {
                return $redCache->$property[$id];
            }

            return null;
        }

        return null;
    }

    protected static function isApply()
    {
        $condition = true;

        return (self::isEnable() && $condition);
    }

    protected static function isEnable()
    {
        return \Redshop::getConfig('ENABLE_PERFORMANCE_MODE');
    }

    /**
     * @param $property
     * @param $id
     * @param $data
     * @param $subId
     * @param $force
     *
     * @since 3.0.1
     */
    public static function save($property, $id, $data, $subId = 0, $force = false)
    {
        if (self::isApply() || $force) {
            $redCache = \JFactory::getSession()->get('redCache', new \stdClass);

            if (isset($subId) && (bool)$subId) {
                $redCache->$property[$id][$subId] = $data;
            } else {
                $redCache->$property[$id] = $data;
            }

            \JFactory::getSession()->set('redCache', $redCache);
        }
    }
}
