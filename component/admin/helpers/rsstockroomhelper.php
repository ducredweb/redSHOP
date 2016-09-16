<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;


class rsstockroomhelper
{
	protected static $instance = null;

	/**
	 * Returns the rsStockRoomHelper object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  rsStockRoomHelper  The rsStockRoomHelper object
	 *
	 * @since   1.6
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new static;
		}

		return self::$instance;
	}

	public function getStockroomDetail($stockroom_id = 0)
	{
		$list = array();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$db = JFactory::getDbo();
			$and = "";

			if ($stockroom_id != 0)
			{
				$and = "AND stockroom_id = " . (int) $stockroom_id . " ";
			}

			$query = "SELECT * FROM #__redshop_stockroom "
				. "WHERE 1=1 "
				. $and;
			$db->setQuery($query);
			$list = $db->loadObjectList();
		}

		return $list;
	}

	public function isStockExists($section_id = 0, $section = "product", $stockroom_id = 0)
	{
		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$stock = $this->getStockAmountwithReserve($section_id, $section, $stockroom_id);

			if ($stock > 0)
			{
				return true;
			}

			return false;
		}

		return true;
	}

	public function isAttributeStockExists($product_id)
	{
		$isStockExists = false;
		$producthelper = productHelper::getInstance();
		$property = $producthelper->getAttibuteProperty(0, 0, $product_id);

		for ($att_j = 0; $att_j < count($property); $att_j++)
		{
			$isSubpropertyStock = false;
			$sub_property = $producthelper->getAttibuteSubProperty(0, $property[$att_j]->property_id);

			for ($sub_j = 0; $sub_j < count($sub_property); $sub_j++)
			{
				$isSubpropertyStock = $this->isStockExists($sub_property[$sub_j]->subattribute_color_id, 'subproperty');

				if ($isSubpropertyStock)
				{
					$isStockExists = $isSubpropertyStock;

					return $isStockExists;
				}
			}

			if ($isSubpropertyStock)
			{
				return $isStockExists;
			}
			else
			{
				$isPropertystock = $this->isStockExists($property[$att_j]->property_id, "property");

				if ($isPropertystock)
				{
					$isStockExists = $isPropertystock;

					return $isStockExists;
				}
			}
		}

		return $isStockExists;
	}

	public function isPreorderStockExists($section_id = 0, $section = "product", $stockroom_id = 0)
	{
		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$stock = $this->getPreorderStockAmountwithReserve($section_id, $section, $stockroom_id);

			if ($stock > 0)
			{
				return true;
			}

			return false;
		}

		return true;
	}

	public function isAttributePreorderStockExists($product_id)
	{
		$producthelper = productHelper::getInstance();
		$property = $producthelper->getAttibuteProperty(0, 0, $product_id);

		for ($att_j = 0; $att_j < count($property); $att_j++)
		{
			$isSubpropertyStock = false;
			$sub_property = $producthelper->getAttibuteSubProperty(0, $property[$att_j]->property_id);

			for ($sub_j = 0; $sub_j < count($sub_property); $sub_j++)
			{
				$isSubpropertyStock = $this->isPreorderStockExists($sub_property[$sub_j]->subattribute_color_id, 'subproperty');

				if ($isSubpropertyStock)
				{
					$isPreorderStockExists = $isSubpropertyStock;

					return $isPreorderStockExists;
				}
			}

			if ($isSubpropertyStock)
			{
				return $isPreorderStockExists;
			}
			else
			{
				$isPropertystock = $this->isPreorderStockExists($property[$att_j]->property_id, "property");

				if ($isPropertystock)
				{
					$isPreorderStockExists = $isPropertystock;

					return $isPreorderStockExists;
				}
			}
		}

		return $isPreorderStockExists;
	}

	public function getStockroomTotalAmount($section_id = 0, $section = "product", $stockroom_id = 0)
	{
		$quantity = 1;

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$quantity = $this->getStockAmountwithReserve($section_id, $section, $stockroom_id);

			$reserve_quantity = $this->getReservedStock($section_id, $section);
			$quantity = $quantity - $reserve_quantity;

			if ($quantity < 0)
			{
				$quantity = 0;
			}
		}

		return $quantity;
	}

	public function getPreorderStockroomTotalAmount($section_id = 0, $section = "product", $stockroom_id = 0)
	{
		$quantity = 1;

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$quantity = $this->getPreorderStockAmountwithReserve($section_id, $section, $stockroom_id);

			$reserve_quantity = $this->getReservedStock($section_id, $section);
			$quantity = $quantity - $reserve_quantity;

			if ($quantity < 0)
			{
				$quantity = 0;
			}
		}

		return $quantity;
	}

	/**
	 * Get Stock Amount with Reserve
	 *
	 * @param   int     $sectionId    Section id
	 * @param   string  $section      Section
	 * @param   int     $stockroomId  Stockroom id
	 *
	 * @return int|mixed
	 */
	public function getStockAmountwithReserve($sectionId = 0, $section = 'product', $stockroomId = 0)
	{
		$quantity = 1;
		$productHelper = productHelper::getInstance();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			if ($section == 'product' && $stockroomId == 0 && $sectionId)
			{
				$sectionId = explode(',', $sectionId);
				JArrayHelper::toInteger($sectionId);
				$quantity = 0;

				foreach ($sectionId as $item)
				{
					$productData = Redshop::product((int) $item);

					if (isset($productData->sum_quanity))
					{
						$quantity += $productData->sum_quanity;
					}
				}
			}
			else
			{
				$table = 'product';
				$db = JFactory::getDbo();

				if ($section != 'product')
				{
					$table = 'product_attribute';
				}

				$query = $db->getQuery(true)
					->select('SUM(x.quantity)')
					->from($db->qn('#__redshop_' . $table . '_stockroom_xref', 'x'))
					->leftJoin($db->qn('#__redshop_stockroom', 's') . ' ON s.stockroom_id = x.stockroom_id')
					->where('x.quantity >= 0');

				if ($sectionId != 0)
				{
					$sectionId = explode(',', $sectionId);
					JArrayHelper::toInteger($sectionId);

					if ($section != 'product')
					{
						$query->where('x.section = ' . $db->quote($section))
							->where('x.section_id IN (' . implode(',', $sectionId) . ')');
					}
					else
					{
						$query->where('x.product_id IN (' . implode(',', $sectionId) . ')');
					}
				}

				if ($stockroomId != 0)
				{
					$query->where('x.stockroom_id = ' . (int) $stockroomId);
				}

				$db->setQuery($query);
				$quantity = $db->loadResult();
			}

			if ($quantity < 0)
			{
				$quantity = 0;
			}
		}

		if ($quantity == null)
		{
			$quantity = (Redshop::getConfig()->get('USE_BLANK_AS_INFINITE')) ? 1000000000 : 0;
		}

		return $quantity;
	}

	function getPreorderStockAmountwithReserve($section_id = 0, $section = "product", $stockroom_id = 0)
	{
		$quantity = 1;

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$and = "";
			$table = "product";
			$db = JFactory::getDbo();

			if ($section != "product")
			{
				$table = "product_attribute";
			}

			if ($section_id != 0)
			{
				// Sanitize ids
				$section_id = explode(',', $section_id);
				JArrayHelper::toInteger($section_id);

				if ($section != "product")
				{
					$and = "AND x.section = " . $db->quote($section) . " AND x.section_id IN (" . implode(',', $section_id) . ") ";
				}
				else
				{
					$and = "AND x.product_id IN (" . implode(',', $section_id) . ") ";
				}
			}

			if ($stockroom_id != 0)
			{
				$and .= "AND x.stockroom_id = " . (int) $stockroom_id . " ";
			}

			$query = "SELECT SUM(x.preorder_stock) as preorder_stock, SUM(x.ordered_preorder) as ordered_preorder FROM "
				. "#__redshop_" . $table . "_stockroom_xref AS x "
				. ", #__redshop_stockroom AS s "
				. "WHERE s.stockroom_id=x.stockroom_id "
				. "AND x.quantity>=0 "
				. $and
				. "ORDER BY s.min_del_time ";

			$db->setQuery($query);
			$pre_order_stock = $db->loadObjectList();

			if ($pre_order_stock[0]->ordered_preorder == $pre_order_stock[0]->preorder_stock
				|| $pre_order_stock[0]->ordered_preorder > $pre_order_stock[0]->preorder_stock)
			{
				$quantity = 0;
			}
			else
			{
				$quantity = $pre_order_stock[0]->preorder_stock - $pre_order_stock[0]->ordered_preorder;
			}
		}

		return $quantity;
	}

	public function getStockroomAmountDetailList($section_id = 0, $section = "product", $stockroom_id = 0)
	{
		$list = array();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$and = "";
			$table = "product";
			$db = JFactory::getDbo();

			if ($section != "product")
			{
				$table = "product_attribute";
			}

			if ($section_id != 0)
			{
				if ($section != "product")
				{
					$and = "AND x.section = " . $db->quote($section) . " AND x.section_id = " . (int) $section_id . " ";
				}
				else
				{
					$and = "AND x.product_id = " . (int) $section_id . " ";
				}
			}

			if ($stockroom_id != 0)
			{
				$and .= "AND x.stockroom_id = " . (int) $stockroom_id . " ";
			}

			$query = "SELECT * FROM #__redshop_" . $table . "_stockroom_xref AS x "
				. "LEFT JOIN #__redshop_stockroom AS s ON s.stockroom_id=x.stockroom_id "
				. "WHERE 1=1 "
				. "AND x.quantity>0 "
				. $and
				. "ORDER BY s.min_del_time ";
			$db->setQuery($query);
			$list = $db->loadObjectList();
		}

		return $list;
	}

	public function getPreorderStockroomAmountDetailList($section_id = 0, $section = "product", $stockroom_id = 0)
	{
		$list = array();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$and = "";
			$table = "product";
			$db = JFactory::getDbo();

			if ($section != "product")
			{
				$table = "product_attribute";
			}

			if ($section_id != 0)
			{
				if ($section != "product")
				{
					$and = "AND x.section = " . $db->quote($section) . " AND x.section_id = " . (int) $section_id . " ";
				}
				else
				{
					$and = "AND x.product_id= " . (int) $section_id . " ";
				}
			}

			if ($stockroom_id != 0)
			{
				$and .= "AND x.stockroom_id = " . (int) $stockroom_id . " ";
			}

			$query = "SELECT * FROM #__redshop_" . $table . "_stockroom_xref AS x "
				. "LEFT JOIN #__redshop_stockroom AS s ON s.stockroom_id=x.stockroom_id "
				. "WHERE 1=1 "
				. "AND x.preorder_stock>= x.ordered_preorder "
				. $and
				. "ORDER BY s.min_del_time ";
			$db->setQuery($query);
			$list = $db->loadObjectList();
		}

		return $list;
	}

	public function updateStockroomQuantity($section_id = 0, $quantity = 0, $section = "product", $product_id = 0)
	{
		$affected_row = array();
		$stockroom_quantity = array();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$list = $this->getStockroomAmountDetailList($section_id, $section);

			for ($i = 0, $in = count($list); $i < $in; $i++)
			{
				if ($list[$i]->quantity < $quantity)
				{
					$quantity = $quantity - $list[$i]->quantity;
					$remaining_quantity = $list[$i]->quantity;
				}
				else
				{
					$remaining_quantity = $quantity;
					$quantity -= $remaining_quantity;
				}

				if ($remaining_quantity > 0)
				{
					$this->updateStockAmount($section_id, $remaining_quantity, $list[$i]->stockroom_id, $section);
					$affected_row[] = $list[$i]->stockroom_id;
					$stockroom_quantity[] = $remaining_quantity;
				}

				$stockroomDetail = $this->getStockroomAmountDetailList($section_id, $section, $list[$i]->stockroom_id);
				$remaining = $stockroomDetail[0]->quantity - $quantity;

				if ($remaining <= DEFAULT_STOCKROOM_BELOW_AMOUNT_NUMBER)
				{
					$dispatcher = JDispatcher::getInstance();
					JPluginHelper::importPlugin('redshop_alert');
					$productId = ($section == "product") ? $section_id : $product_id;
					$productData = Redshop::product((int) $productId);

					$message = JText::sprintf(
						'COM_REDSHOP_ALERT_STOCKROOM_BELOW_AMOUNT_NUMBER',
						$productData->product_id,
						$productData->product_name,
						$productData->product_number,
						$remaining,
						$stockroomDetail[0]->stockroom_name
					);

					$dispatcher->trigger('storeAlert', array($message));
					$dispatcher->trigger('sendEmail', array($message));
				}
			}

			// For preorder stock
			if ($quantity > 0)
			{
				$preorder_list = $this->getPreorderStockroomAmountDetailList($section_id, $section);
				$producthelper = productHelper::getInstance();

				if ($section == "product")
				{
					$product_data = Redshop::product((int) $section_id);
				}
				else
				{
					$product_data = Redshop::product((int) $product_id);
				}

				if ($product_data->preorder == "yes" || ($product_data->preorder == "global" && Redshop::getConfig()->get('ALLOW_PRE_ORDER'))
					|| ($product_data->preorder == "" && Redshop::getConfig()->get('ALLOW_PRE_ORDER')))
				{
					for ($i = 0, $in = count($preorder_list); $i < $in; $i++)
					{
						if ($preorder_list[$i]->preorder_stock < $quantity)
						{
							$quantity = $quantity - $preorder_list[$i]->preorder_stock;
							$remaining_quantity = $preorder_list[$i]->preorder_stock;
						}
						else
						{
							$remaining_quantity = $quantity;
							$quantity -= $remaining_quantity;
						}

						if ($remaining_quantity > 0)
						{
							$this->updatePreorderStockAmount($section_id, $remaining_quantity, $preorder_list[$i]->stockroom_id, $section);
						}
					}
				}
			}
		}

		$list = implode(",", $affected_row);
		$stockroom_quantity_list = implode(",", $stockroom_quantity);
		$result_array = array();
		$result_array['stockroom_list'] = $list;
		$result_array['stockroom_quantity_list'] = $stockroom_quantity_list;

		return $result_array;
	}

	public function updateStockAmount($section_id = 0, $quantity = 0, $stockroom_id = 0, $section = "product")
	{
		$and = "";
		$table = "product";

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$db = JFactory::getDbo();

			if ($section != "product")
			{
				$table = "product_attribute";
			}

			if ($section_id != 0)
			{
				if ($section != "product")
				{
					$and = "AND section = " . $db->quote($section) . " AND section_id = " . (int) $section_id . " ";
				}
				else
				{
					$and = "AND product_id = " . (int) $section_id . " ";
				}

				$query = 'UPDATE #__redshop_' . $table . '_stockroom_xref '
					. 'SET quantity = quantity - ' . (int) $quantity . ' '
					. 'WHERE stockroom_id = ' . (int) $stockroom_id . ' '
					. 'AND quantity > 0 '
					. $and;
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	public function updatePreorderStockAmount($section_id = 0, $quantity = 0, $stockroom_id = 0, $section = "product")
	{
		$and = "";
		$table = "product";

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$db = JFactory::getDbo();

			if ($section != "product")
			{
				$table = "product_attribute";
			}

			if ($section_id != 0 && trim($section_id) != "")
			{
				if ($section != "product")
				{
					$and = "AND section = " . $db->quote($section) . " AND section_id = " . (int) $section_id . " ";
				}
				else
				{
					$and = "AND product_id = " . (int) $section_id . " ";
				}

				$query = 'UPDATE #__redshop_' . $table . '_stockroom_xref '
					. 'SET ordered_preorder = ordered_preorder + ' . (int) $quantity . ' '
					. 'WHERE stockroom_id = ' . (int) $stockroom_id . ' '
					. $and;
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	public function manageStockAmount($section_id = 0, $quantity = 0, $stockroom_id = 0, $section = "product")
	{
		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$db = JFactory::getDbo();
			$and = "";
			$table = "product";

			if ($section != "product")
			{
				$table = "product_attribute";
			}

			if ($section_id != 0 && trim($section_id) != "")
			{
				if ($section != "product")
				{
					$and = "AND section = " . $db->quote($section) . " AND section_id = " . (int) $section_id . " ";
				}
				else
				{
					$and = "AND product_id = " . (int) $section_id . " ";
				}
			}

			$stockId = explode(",", $stockroom_id);
			$stock_Qty = explode(",", $quantity);

			for ($i = 0, $in = count($stockId); $i < $in; $i++)
			{
				if ($stockId[$i] != "" && $section_id != "" && $section_id != 0)
				{
					$query = 'UPDATE #__redshop_' . $table . '_stockroom_xref '
						. 'SET quantity = quantity + ' . (int) $stock_Qty[$i] . ' '
						. 'WHERE stockroom_id = ' . (int) $stockId[$i] . ' '
						. $and;
					$db->setQuery($query);
					$db->execute();
					$affected_row = $db->getAffectedRows();

					if ($affected_row > 0)
					{
						break;
					}
				}
			}
		}

		return true;
	}

	public function replaceStockroomAmountDetail($template_desc = "", $section_id = 0, $section = "product")
	{
		if (strpos($template_desc, '{stockroom_detail}') !== false)
		{
			$productinstock = "";

			if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
			{
				$list = $this->getStockroomAmountDetailList($section_id, $section);

				for ($i = 0, $in = count($list); $i < $in; $i++)
				{
					$productinstock .= "<div><span>" . $list[$i]->stockroom_name . "</span>:<span>" . $list[$i]->quantity . "</span></div>";
				}
			}

			$template_desc = str_replace('{stockroom_detail}', $productinstock, $template_desc);
		}

		return $template_desc;
	}

	public function getStockAmountImage($section_id = 0, $section = "product", $stock_amount = 0)
	{
		$list = array();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$db = JFactory::getDbo();

			if ($stock_amount == 0)
			{
				$stock_amount = $this->getStockAmountwithReserve($section_id, $section);
			}

			$query = "SELECT * FROM #__redshop_stockroom_amount_image as sm LEFT JOIN "
				. "#__redshop_product_stockroom_xref AS sx ON sx.stockroom_id=sm.stockroom_id LEFT JOIN "
				. "#__redshop_stockroom AS s ON sx.stockroom_id=s.stockroom_id where  sx.quantity > 0 and sx.product_id= "
				. (int) $section_id;

			$query1 = $query . " AND stock_option=2 AND stock_quantity = " . (int) $stock_amount . " ";
			$db->setQuery($query1);
			$list = $db->loadObjectList();

			if (count($list) <= 0)
			{
				$query1 = $query . " AND stock_option=1 AND stock_quantity < " . (int) $stock_amount . " ORDER BY stock_quantity DESC, s.max_del_time asc ";
				$db->setQuery($query1);
				$list = $db->loadObjectList();

				if (count($list) <= 0)
				{
					$query1 = $query . " AND stock_option=3 AND stock_quantity > " . (int) $stock_amount . " ORDER BY stock_quantity ASC , s.max_del_time asc ";
					$db->setQuery($query1);
					$list = $db->loadObjectList();
				}
			}
		}

		return $list;
	}

	public function getReservedStock($section_id, $section = "product")
	{
		if (Redshop::getConfig()->get('IS_PRODUCT_RESERVE') && Redshop::getConfig()->get('USE_STOCKROOM'))
		{
			$db = JFactory::getDbo();
			$query = "SELECT SUM(qty) FROM #__redshop_cart "
				. "WHERE product_id = " . (int) $section_id . " "
				. "AND section = " . $db->quote($section);
			$db->setQuery($query);
			$count = intval($db->loadResult());

			return $count;
		}

		return 0;
	}

	public function getCurrentUserReservedStock($section_id, $section = "product")
	{
		if (Redshop::getConfig()->get('IS_PRODUCT_RESERVE') && Redshop::getConfig()->get('USE_STOCKROOM'))
		{
			$db = JFactory::getDbo();
			$session_id = session_id();

			$query = "SELECT SUM(qty) FROM #__redshop_cart "
				. "WHERE product_id = " . (int) $section_id . " "
				. "AND session_id = " . $db->quote($session_id) . " "
				. "AND section = " . $db->quote($section);
			$db->setQuery($query);
			$count = intval($db->loadResult());

			return $count;
		}

		return 0;
	}

	public function deleteExpiredCartProduct()
	{
		if (Redshop::getConfig()->get('IS_PRODUCT_RESERVE') && Redshop::getConfig()->get('USE_STOCKROOM'))
		{
			$db = JFactory::getDbo();
			$time = time() - (Redshop::getConfig()->get('CART_TIMEOUT') * 60);

			$query = "DELETE FROM #__redshop_cart "
				. "WHERE time < " . $db->quote($time);
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	public function deleteCartAfterEmpty($section_id = 0, $section = "product", $quantity = 0)
	{
		if (Redshop::getConfig()->get('IS_PRODUCT_RESERVE') && Redshop::getConfig()->get('USE_STOCKROOM'))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->where('session_id = ' . $db->quote(session_id()));

			if ($section_id != 0)
			{
				$query->where('product_id = ' . (int) $section_id)
					->where('section = ' . $db->quote($section));
			}

			if ($quantity)
			{
				$query->select('qty')
					->from($db->qn('#__redshop_cart'));
				$db->setQuery($query);
				$qty = (int) $db->loadResult();
				$query->clear('select')
					->clear('from');

				if ($qty - (int) $quantity > 0)
				{
					$query->update($db->qn('#__redshop_cart'))
						->set('qty = ' . ($qty - (int) $quantity));
				}
				else
				{
					$query->delete($db->qn('#__redshop_cart'));
				}
			}
			else
			{
				$query->delete($db->qn('#__redshop_cart'));
			}

			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	public function addReservedStock($section_id, $quantity = 0, $section = "product")
	{
		if (Redshop::getConfig()->get('IS_PRODUCT_RESERVE') && Redshop::getConfig()->get('USE_STOCKROOM'))
		{
			$db = JFactory::getDbo();
			$session_id = session_id();
			$time = time();
			$query = $db->getQuery(true);

			$query->clear()
			->select('qty')
			->from($db->qn('#__redshop_cart'))
			->where('session_id = ' . $db->quote($session_id))
			->where('product_id = ' . (int) $section_id)
			->where('section = ' . $db->quote($section));
			$db->setQuery($query);
			$qty = $db->loadResult();

			if ($qty !== null)
			{
				$query->clear()
					->update($db->qn('#__redshop_cart'))
					->set('qty = ' . (int) $quantity)
					->set('time = ' . $db->quote($time))
					->where('session_id = ' . $db->quote($session_id))
					->where('product_id = ' . (int) $section_id)
					->where('section = ' . $db->quote($section));
			}
			else
			{
				$query->clear()
					->insert($db->qn('#__redshop_cart'))
					->columns('session_id, product_id, qty, time, section')
					->values($db->quote($session_id) . ',' . (int) $section_id . ',' . (int) $quantity . ',' . $db->quote($time) . ',' . $db->quote($section));
			}

			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	public function getStockroom($stockroom_id)
	{
		$db = JFactory::getDbo();

		// Sanitize ids
		$stockroom_id = explode(',', $stockroom_id);
		JArrayHelper::toInteger($stockroom_id);

		$query = 'SELECT * FROM #__redshop_stockroom WHERE stockroom_id in  (' . implode(',', $stockroom_id) . ') and published=1';
		$db->setQuery($query);

		return $db->loadObjectlist();
	}

	/**
	 * Function to get min delivery time
	 */
	public function getStockroom_maxdelivery($stockroom_id)
	{
		$db = JFactory::getDbo();

		// Sanitize ids
		$stockroom_id = explode(',', $stockroom_id);
		JArrayHelper::toInteger($stockroom_id);

		$query = 'SELECT max_del_time,delivery_time'
			. ' FROM #__redshop_stockroom'
			. ' WHERE stockroom_id IN  (' . implode(',', $stockroom_id) . ')'
			. ' AND published=1 order by max_del_time desc';

		$db->setQuery($query);

		return $db->loadObjectlist();
	}

	public function getdatediff($endDate, $beginDate)
	{
		$epoch_1 = mktime(0, 0, 0, date("m", $endDate), date("d", $endDate), date("Y", $endDate));
		$epoch_2 = mktime(0, 0, 0, date("m", $beginDate), date("d", $beginDate), date("Y", $beginDate));
		$dateDiff = $epoch_1 - $epoch_2;
		$fullDays = floor($dateDiff / (60 * 60 * 24));

		return $fullDays;
	}

	public function getFinalStockofProduct($product_id, $totalatt)
	{
		$producthelper = productHelper::getInstance();

		$isStockExists = $this->isStockExists($product_id);

		if ($totalatt > 0 && !$isStockExists)
		{
			$property = $producthelper->getAttibuteProperty(0, 0, $product_id);

			for ($att_j = 0; $att_j < count($property); $att_j++)
			{
				$isSubpropertyStock = false;
				$sub_property = $producthelper->getAttibuteSubProperty(0, $property[$att_j]->property_id);

				for ($sub_j = 0; $sub_j < count($sub_property); $sub_j++)
				{
					$isSubpropertyStock = $this->isStockExists($sub_property[$sub_j]->subattribute_color_id, 'subproperty');

					if ($isSubpropertyStock)
					{
						$isStockExists = $isSubpropertyStock;
						break;
					}
				}

				if ($isSubpropertyStock)
				{
					break;
				}
				else
				{
					$isPropertystock = $this->isStockExists($property[$att_j]->property_id, "property");

					if ($isPropertystock)
					{
						$isStockExists = $isPropertystock;
						break;
					}
				}
			}
		}

		return $isStockExists;
	}

	public function getFinalPreorderStockofProduct($product_id, $totalatt)
	{
		$producthelper = productHelper::getInstance();

		$isStockExists = $this->isPreorderStockExists($product_id);

		if ($totalatt > 0 && !$isStockExists)
		{
			$property = $producthelper->getAttibuteProperty(0, 0, $product_id);

			for ($att_j = 0; $att_j < count($property); $att_j++)
			{
				$isSubpropertyStock = false;
				$sub_property = $producthelper->getAttibuteSubProperty(0, $property[$att_j]->property_id);

				for ($sub_j = 0; $sub_j < count($sub_property); $sub_j++)
				{
					$isSubpropertyStock = $this->isPreorderStockExists($sub_property[$sub_j]->subattribute_color_id, 'subproperty');

					if ($isSubpropertyStock)
					{
						$isStockExists = $isSubpropertyStock;
						break;
					}
				}

				if ($isSubpropertyStock)
				{
					break;
				}
				else
				{
					$isPropertystock = $this->isPreorderStockExists($property[$att_j]->property_id, "property");

					if ($isPropertystock)
					{
						$isStockExists = $isPropertystock;
						break;
					}
				}
			}
		}

		return $isStockExists;
	}
}
