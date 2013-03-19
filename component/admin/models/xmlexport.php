<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');

class xmlexportModelxmlexport extends JModel
{
	public $_data = null;
	public $_total = null;
	public $_pagination = null;
	public $_table_prefix = null;
	public $_context = null;

	public function __construct()
	{
		parent::__construct();

		global $mainframe;
		$this->_context = 'xmlexport_id';

		$this->_table_prefix = '#__redshop_';

		$limit = $mainframe->getUserStateFromRequest($this->_context . 'limit', 'limit', $mainframe->getCfg('list_limit'), 0);
		$limitstart = $mainframe->getUserStateFromRequest($this->_context . 'limitstart', 'limitstart', 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	public function getData()
	{
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_data;
	}

	public function getTotal()
	{
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	public function getPagination()
	{
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	public function getProduct()
	{
		$query = "SELECT * FROM " . $this->_table_prefix . "xml_export ";
		$list = $this->_data = $this->_getList($query);

		return $list;
	}

	public function _buildQuery()
	{
		$orderby = $this->_buildContentOrderBy();

		$query = "SELECT x.* FROM " . $this->_table_prefix . "xml_export AS x "
			. "WHERE 1=1 "
			. $orderby;

		return $query;
	}

	public function _buildContentOrderBy()
	{
		global $mainframe;

		$filter_order = $mainframe->getUserStateFromRequest($this->_context . 'filter_order', 'filter_order', 'xmlexport_date');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($this->_context . 'filter_order_Dir', 'filter_order_Dir', 'DESC');

		$orderby = " ORDER BY " . $filter_order . " " . $filter_order_Dir;

		return $orderby;
	}
}


