<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Generate admin menu list
 *
 * @since  1.6.1
 */
class RedshopAdminMenu
{
	public $items = array();

	protected $data = array();

	protected $item = array();

	protected $section = null;

	protected $title = null;

	protected static $instance = null;

	/**
	 * Returns the RedshopAdminMenu object, only creating it if it doesn't already exist.
	 *
	 * @return  RedshopAdminMenu  The RedshopAdminMenu object
	 *
	 * @since   1.6.1
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new static;
		}

		return self::$instance;
	}

	public function init()
	{
		$this->data = array();

		return $this;
	}

	public function section($section)
	{
		$this->section = $section;

		return $this;
	}

	public function title($title)
	{
		$this->title = $title;

		return $this;
	}

	public function addItem($link, $title, $description = null)
	{
		$item              = new stdClass;
		$item->link        = $link;
		$item->title       = $title;
		$item->description = $description;

		if ($this->section)
		{
			$this->data[$this->section]->items[] = $item;
		}

		if ($this->title)
		{
			$this->data[$this->section]->title = $this->title;
		}

		return $this;
	}

	public function group($group)
	{
		$this->items[$group] = $this->data;
	}
}