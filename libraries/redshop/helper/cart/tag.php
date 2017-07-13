<?php
/**
 * @package     RedSHOP.Library
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 *
 * @since       2.0.3
 */
defined('_JEXEC') or die;

/**
 * Class Redshop Helper for Cart - Tag replacer
 *
 * @since  2.0.7
 */
class RedshopHelperCartTag
{
	/**
	 * replace Conditional tag from Redshop tax
	 *
	 * @param   string  $template       Template
	 * @param   int     $amount         Amount
	 * @param   int     $discount       Discount
	 * @param   int     $check          Check
	 * @param   int     $quotationMode  Quotation mode
	 *
	 * @return  mixed|string
	 * @since   2.0.7
	 */
	public static function replaceTax($template = '', $amount = 0, $discount = 0, $check = 0, $quotationMode = 0)
	{
		if (strpos($template, '{if vat}') === false || strpos($template, '{vat end if}') == false)
		{
			return $template;
		}

		$cart          = RedshopHelperCartSession::getCart();
		$productHelper = productHelper::getInstance();

		if ($amount <= 0)
		{
			$templateVatSdata = explode('{if vat}', $template);
			$templateVatEdata = explode('{vat end if}', $templateVatSdata[1]);
			$template         = $templateVatSdata[0] . $templateVatEdata[1];

			return $template;
		}

		if ($quotationMode && !Redshop::getConfig()->get('SHOW_QUOTATION_PRICE'))
		{
			$template = str_replace("{tax}", "", $template);
			$template = str_replace("{order_tax}", "", $template);
		}
		else
		{
			$template = str_replace("{tax}", $productHelper->getProductFormattedPrice($amount, true), $template);
			$template = str_replace("{order_tax}", $productHelper->getProductFormattedPrice($amount, true), $template);
		}

		if (strpos($template, '{tax_after_discount}') !== false)
		{
			if (Redshop::getConfig()->get('APPLY_VAT_ON_DISCOUNT') && (float) Redshop::getConfig()->get('VAT_RATE_AFTER_DISCOUNT'))
			{
				if ($check)
				{
					$taxAfterDiscount = $discount;
				}
				else
				{
					if (!isset($cart['tax_after_discount']))
					{
						$taxAfterDiscount = RedshopHelperCart::calculateTaxAfterDiscount($amount, $discount);
					}
					else
					{
						$taxAfterDiscount = $cart['tax_after_discount'];
					}
				}

				if ($taxAfterDiscount > 0)
				{
					$template = str_replace("{tax_after_discount}", $productHelper->getProductFormattedPrice($taxAfterDiscount), $template);
				}
				else
				{
					$template = str_replace("{tax_after_discount}", $productHelper->getProductFormattedPrice($cart['tax']), $template);
				}
			}
			else
			{
				$template = str_replace("{tax_after_discount}", $productHelper->getProductFormattedPrice($cart['tax']), $template);
			}
		}

		$template = str_replace("{vat_lbl}", JText::_('COM_REDSHOP_CHECKOUT_VAT_LBL'), $template);
		$template = str_replace("{if vat}", '', $template);
		$template = str_replace("{vat end if}", '', $template);

		return $template;
	}
}