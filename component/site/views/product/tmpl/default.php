<?php
/**
 * @package     RedSHOP.Frontend
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('behavior.modal');

$url    = JURI::base();
$u      = JURI::getInstance();
$Scheme = $u->getScheme();

$watched = $this->session->get('watched_product', array());

if (in_array($this->pid, $watched) == 0)
{
	array_push($watched, $this->pid);
	$this->session->set('watched_product', $watched);
}

$print = $this->input->getBool('print', false);
$user  = JFactory::getUser();

$redshopconfig   = Redconfiguration::getInstance();
$stockroomhelper = rsstockroomhelper::getInstance();
$config          = Redconfiguration::getInstance();

$template = $this->template;

if (!empty($template) && !empty($template->template_desc))
{
	$templateDesc = $template->template_desc;
}
else
{
	$templateDesc = "<div id=\"produkt\">\r\n<div class=\"produkt_spacer\"></div>\r\n<div class=\"produkt_anmeldelser_opsummering\">";
	$templateDesc .= "{product_rating_summary}</div>\r\n<div id=\"opsummering_wrapper\">\r\n<div id=\"opsummering_skubber\"></div>\r\n";
	$templateDesc .= "<div id=\"opsummering_link\">{product_rating_summary}</div>\r\n</div>\r\n<div id=\"produkt_kasse\">\r\n";
	$templateDesc .= "<div class=\"produkt_kasse_venstre\">\r\n<div class=\"produkt_kasse_billed\">{product_thumb_image}</div>\r\n";
	$templateDesc .= "<div class=\"produkt_kasse_billed_flere\">{more_images}</div>\r\n<div id=\"produkt_kasse_venstre_tekst\">";
	$templateDesc .= "{view_full_size_image_lbl}</div>\r\n</div>\r\n<div class=\"produkt_kasse_hoejre\">\r\n{attribute_template:attributes}";
	$templateDesc .= "<div class=\"produkt_kasse_hoejre_accessory\">{accessory_template:accessory}</div>\r\n";
	$templateDesc .= "<div class=\"produkt_kasse_hoejre_pris\">\r\n<div class=\"produkt_kasse_hoejre_pris_indre\" ";
	$templateDesc .= "id=\"produkt_kasse_hoejre_pris_indre\">{product_price}</div>\r\n</div>\r\n<div class=\"produkt_kasse_hoejre_laegikurv\">\r\n";
	$templateDesc .= "<div class=\"produkt_kasse_hoejre_laegikurv_indre\">{form_addtocart:add_to_cart2}</div>\r\n</div>\r\n";
	$templateDesc .= "<div class=\"produkt_kasse_hoejre_leveringstid\">\r\n<div class=\"produkt_kasse_hoejre_leveringstid_indre\">";
	$templateDesc .= "{delivery_time_lbl}: {product_delivery_time}</div>\r\n</div>\r\n<div class=\"produkt_kasse_hoejre_bookmarksendtofriend\">\r\n";
	$templateDesc .= "<div class=\"produkt_kasse_hoejre_bookmark\">{bookmark}</div>\r\n<div class=\"produkt_kasse_hoejre_sendtofriend\">";
	$templateDesc .= "{send_to_friend}</div>\r\n</div>\r\n</div>\r\n<div id=\"produkt_beskrivelse_wrapper\">\r\n<div class=\"produkt_beskrivelse\">";
	$templateDesc .= "\r\n<div id=\"produkt_beskrivelse_maal\">\r\n<div id=\"produkt_maal_wrapper\">\r\n<div id=\"produkt_maal_indhold_hojre\">\r\n";
	$templateDesc .= "<div id=\"produkt_hojde\">{product_height_lbl}: {product_height}</div>\r\n<div id=\"produkt_bredde\">";
	$templateDesc .= "x {product_width_lbl}: {product_width}</div>\r\n<div id=\"produkt_dybde\">x {product_length_lbl}: {product_length}</div>\r\n";
	$templateDesc .= "<div style=\"width: 275px; height: 10px; clear: left;\"></div>\r\n<div id=\"producent_link\">{manufacturer_link}</div>\r\n";
	$templateDesc .= "<div id=\"produkt_writereview\">{form_rating}</div>\r\n</div>\r\n</div>\r\n</div>\r\n<h2>{product_name}</h2>\r\n";
	$templateDesc .= "<div id=\"beskrivelse_lille\">{product_s_desc}</div>\r\n<div id=\"beskrivelse_stor\">{product_desc}</div>\r\n";
	$templateDesc .= "<div class=\"product_related_products\">{related_product:related_products}</div>\r\n</div>\r\n</div>\r\n";
	$templateDesc .= "<div id=\"produkt_anmeldelser\">\r\n{product_rating}</div>\r\n</div>\r\n</div>";
}

//Replace Product price when config enable discount is "No"
if (Redshop::getConfig()->getInt('DISCOUNT_ENABLE') == 0)
{
	$templateDesc = str_replace('{product_old_price}', '', $templateDesc);
}
?>

<div class="product">
    <div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
		<?php
		if (!empty($this->data))
		{
			if (!empty($this->data->pageheading))
			{
				echo $this->escape($this->data->pageheading);
			}
			else
			{
				echo $this->escape($this->pageheadingtag);
			}
		} ?>
    </div>
</div>
<div style="clear:both"></div>

<?php
// Display after title data
echo $this->data->event->afterDisplayTitle;

// Display before product data
echo $this->data->event->beforeDisplayProduct;

/*
 * Replace Discount Calculator Tag
 */
$discount_calculator = "";

if ($this->data->use_discount_calc)
{
	// Get discount calculator Template
	$templateDesc = str_replace('{discount_calculator}', $this->loadTemplate('calculator'), $templateDesc);
}
else
{
	$templateDesc = str_replace('{discount_calculator}', '', $templateDesc);
}

if (Redshop::getConfig()->getInt('COMPARE_PRODUCTS') === 0)
{
	$templateDesc = str_replace('{compare_products_button}', '', $templateDesc);
	$templateDesc = str_replace('{compare_product_div}', '', $templateDesc);
}

$templateDesc = str_replace('{component_heading}', $this->escape($this->data->product_name), $templateDesc);

if (strstr($templateDesc, '{back_link}'))
{
	$back_link     = '<a href="' . htmlentities($_SERVER['HTTP_REFERER']) . '">' . JText::_('COM_REDSHOP_BACK') . '</a>';
	$templateDesc = str_replace('{back_link}', $back_link, $templateDesc);
}

$returnToCategoryLink = strstr($templateDesc, '{returntocategory_link}');
$returnToCategoryName = strstr($templateDesc, '{returntocategory_name}');
$returnToCategoryStr  = strstr($templateDesc, '{returntocategory}');

if ($returnToCategoryLink || $returnToCategoryName || $returnToCategoryStr)
{
	$returncatlink    = '';
	$returntocategory = '';

	if ($this->data->category_id)
	{
		$returncatlink = JRoute::_(
			'index.php?option=com_redshop&view=category&layout=detail&cid=' . $this->data->category_id .
			'&Itemid=' . $this->itemId
		);

		$returntocategory = '<a href="' . $returncatlink . '">' . Redshop::getConfig()->get('DAFULT_RETURN_TO_CATEGORY_PREFIX') . " " . $this->data->category_name . '</a>';
	}

	$templateDesc = str_replace('{returntocategory_link}', $returncatlink, $templateDesc);
	$templateDesc = str_replace('{returntocategory_name}', $this->data->category_name, $templateDesc);
	$templateDesc = str_replace('{returntocategory}', $returntocategory, $templateDesc);
}

if (strstr($templateDesc, '{navigation_link_right}') || strstr($templateDesc, '{navigation_link_left}'))
{
	$nextbutton = '';
	$prevbutton = '';

	// Next Navigation
	$nextproducts = $this->model->getPrevNextproduct($this->data->product_id, $this->data->category_id, 1);

	if (!empty($nextproducts))
	{
		$nextlink = JRoute::_(
			'index.php?option=com_redshop&view=product&pid=' . $nextproducts->product_id .
			'&cid=' . $this->data->category_id .
			'&Itemid=' . $this->itemId
		);

		if ((int) Redshop::getConfig()->get('DEFAULT_LINK_FIND') === 0)
		{
			$nextbutton = '<a href="' . $nextlink . '">' . $nextproducts->product_name . "" . Redshop::getConfig()->get('DAFULT_NEXT_LINK_SUFFIX') . '</a>';
		}
		elseif ((int) Redshop::getConfig()->get('DEFAULT_LINK_FIND') === 1)
		{
			$nextbutton = '<a href="' . $nextlink . '">' . Redshop::getConfig()->get('CUSTOM_NEXT_LINK_FIND') . '</a>';
		}
		elseif (file_exists(REDSHOP_FRONT_IMAGES_RELPATH . Redshop::getConfig()->get('IMAGE_PREVIOUS_LINK_FIND')))
		{
			$nextbutton = '<a href="' . $nextlink . '"><img src="' . REDSHOP_FRONT_IMAGES_ABSPATH . Redshop::getConfig()->get('IMAGE_NEXT_LINK_FIND') . '" /></a>';
		}
	}

	// Start previous logic
	$previousproducts = $this->model->getPrevNextproduct($this->data->product_id, $this->data->category_id, -1);

	if (!empty($previousproducts))
	{
		$prevlink = JRoute::_(
			'index.php?option=com_redshop&view=product&pid=' . $previousproducts->product_id .
			'&cid=' . $this->data->category_id .
			'&Itemid=' . $this->itemId
		);

		if (Redshop::getConfig()->get('DEFAULT_LINK_FIND') === 0)
		{
			$prevbutton = '<a href="' . $prevlink . '">' . Redshop::getConfig()->get('DAFULT_PREVIOUS_LINK_PREFIX') . "" . $previousproducts->product_name . '</a>';
		}
		elseif (Redshop::getConfig()->get('DEFAULT_LINK_FIND') == 1)
		{
			$prevbutton = '<a href="' . $prevlink . '">' . Redshop::getConfig()->get('CUSTOM_PREVIOUS_LINK_FIND') . '</a>';
		}
		elseif (file_exists(REDSHOP_FRONT_IMAGES_RELPATH . Redshop::getConfig()->get('IMAGE_PREVIOUS_LINK_FIND')))
		{
			$prevbutton = '<a href="' . $prevlink . '"><img src="' . REDSHOP_FRONT_IMAGES_ABSPATH . Redshop::getConfig()->get('IMAGE_PREVIOUS_LINK_FIND') . '" /></a>';
		}

		// End
	}

	$templateDesc = str_replace('{navigation_link_right}', $nextbutton, $templateDesc);
	$templateDesc = str_replace('{navigation_link_left}', $prevbutton, $templateDesc);
}

/*
 * product size variables
 */
$product_volume = "";
$product_volume .= '<span class="length_number">' . RedshopHelperProduct::redunitDecimal($this->data->product_length) . '</span>';
$product_volume .= '<span class="length_unit">' . Redshop::getConfig()->get('DEFAULT_VOLUME_UNIT') . '</span>';
$product_volume .= '<span class="separator">X</span>';
$product_volume .= '<span class="width_number">' . RedshopHelperProduct::redunitDecimal($this->data->product_width) . '</span>';
$product_volume .= '<span class="width_unit">' . Redshop::getConfig()->get('DEFAULT_VOLUME_UNIT') . '</span>';
$product_volume .= '<span class="separator">X</span>';
$product_volume .= '<span class="height_number">' . RedshopHelperProduct::redunitDecimal($this->data->product_height) . '</span>';
$product_volume .= '<span class="height_unit">' . Redshop::getConfig()->get('DEFAULT_VOLUME_UNIT') . '</span>';

$templateDesc = str_replace('{product_size}', $product_volume, $templateDesc);

if (Redshop::getConfig()->get('DEFAULT_VOLUME_UNIT'))
{
	$product_unit = '<span class="product_unit_variable">' . Redshop::getConfig()->get('DEFAULT_VOLUME_UNIT') . '</span>';
}
else
{
	$product_unit = '';
}

// Product length
if ($this->data->product_length > 0)
{
	$templateDesc = str_replace("{product_length_lbl}", JText::_('COM_REDSHOP_PRODUCT_LENGTH_LBL'), $templateDesc);

	$insertStr     = RedshopHelperProduct::redunitDecimal($this->data->product_length) . "&nbsp" . $product_unit;
	$templateDesc = str_replace('{product_length}', $insertStr, $templateDesc);
}
else
{
	$templateDesc = str_replace("{product_length_lbl}", "", $templateDesc);
	$templateDesc = str_replace('{product_length}', "", $templateDesc);
}

// Product width
if ($this->data->product_width > 0)
{
	$templateDesc = str_replace("{product_width_lbl}", JText::_('COM_REDSHOP_PRODUCT_WIDTH_LBL'), $templateDesc);

	$insertStr     = RedshopHelperProduct::redunitDecimal($this->data->product_width) . "&nbsp" . $product_unit;
	$templateDesc = str_replace('{product_width}', $insertStr, $templateDesc);
}
else
{
	$templateDesc = str_replace("{product_width_lbl}", "", $templateDesc);
	$templateDesc = str_replace('{product_width}', "", $templateDesc);
}

// Product Height
if ($this->data->product_height > 0)
{
	$templateDesc = str_replace("{product_height_lbl}", JText::_('COM_REDSHOP_PRODUCT_HEIGHT_LBL'), $templateDesc);

	$insertStr     = RedshopHelperProduct::redunitDecimal($this->data->product_height) . "&nbsp" . $product_unit;
	$templateDesc = str_replace('{product_height}', $insertStr, $templateDesc);
}
else
{
	$templateDesc = str_replace("{product_height_lbl}", "", $templateDesc);
	$templateDesc = str_replace('{product_height}', "", $templateDesc);
}

// Product Diameter
if ($this->data->product_diameter > 0)
{
	$templateDesc = str_replace("{product_diameter_lbl}", JText::_('COM_REDSHOP_PRODUCT_DIAMETER_LBL'), $templateDesc);
	$templateDesc = str_replace("{diameter}", RedshopHelperProduct::redunitDecimal($this->data->product_diameter) . "&nbsp" . $product_unit, $templateDesc);
}
else
{
	$templateDesc = str_replace("{product_diameter_lbl}", "", $templateDesc);
	$templateDesc = str_replace('{diameter}', "", $templateDesc);
}

// Product Volume
$product_volume_unit = '<span class="product_unit_variable">' . Redshop::getConfig()->get('DEFAULT_VOLUME_UNIT') . "3" . '</span>';

if ($this->data->product_volume > 0)
{
	$insertStr     = JText::_('COM_REDSHOP_PRODUCT_VOLUME_LBL') . JText::_('COM_REDSHOP_PRODUCT_VOLUME_UNIT');
	$templateDesc = str_replace("{product_volume_lbl}", $insertStr, $templateDesc);

	$insertStr     = RedshopHelperProduct::redunitDecimal($this->data->product_volume) . "&nbsp" . $product_volume_unit;
	$templateDesc = str_replace('{product_volume}', $insertStr, $templateDesc);
}
else
{
	$templateDesc = str_replace('{product_volume}', "", $templateDesc);
	$templateDesc = str_replace("{product_volume_lbl}", "", $templateDesc);
}

// Replace Product Template
if ($print)
{
	$onclick = "onclick='window.print();'";
}
else
{
	$print_url = $url . "index.php?option=com_redshop&view=product&pid=" . $this->data->product_id;
	$print_url .= "&cid=" . $this->data->category_id . "&print=1&tmpl=component&Itemid=" . $this->itemId;

	$onclick = "onclick='window.open(\"$print_url\",\"mywindow\",\"scrollbars=1\",\"location=1\")'";
}

$print_tag = "<a " . $onclick . " title='" . JText::_('COM_REDSHOP_PRINT_LBL') . "'>";
$print_tag .= "<img src='" . JSYSTEM_IMAGES_PATH . "printButton.png'
					alt='" . JText::_('COM_REDSHOP_PRINT_LBL') . "'
					title='" . JText::_('COM_REDSHOP_PRINT_LBL') . "' />";
$print_tag .= "</a>";

// Associate_tag display update
$ass_tag = '';

if (RedshopHelperUtility::isRedProductFinder())
{
	$associate_tag = RedshopHelperProduct::getassociatetag($this->data->product_id);

	for ($k = 0, $kn = count($associate_tag); $k < $kn; $k++)
	{
		if ($associate_tag[$k] != '')
		{
			$ass_tag .= $associate_tag[$k]->type_name . " : " . $associate_tag[$k]->tag_name . "<br/>";
		}
	}
}

$templateDesc = RedshopHelperTax::replaceVatInformation($templateDesc);
$templateDesc = str_replace("{associate_tag}", $ass_tag, $templateDesc);
$templateDesc = str_replace("{print}", $print_tag, $templateDesc);
$templateDesc = str_replace("{product_name}", $this->data->product_name, $templateDesc);
$templateDesc = str_replace("{product_id_lbl}", JText::_('COM_REDSHOP_PRODUCT_ID_LBL'), $templateDesc);
$templateDesc = str_replace("{product_number_lbl}", JText::_('COM_REDSHOP_PRODUCT_NUMBER_LBL'), $templateDesc);
$templateDesc = str_replace("{product_id}", $this->data->product_id, $templateDesc);

$templateDesc = str_replace("{product_s_desc}", htmlspecialchars_decode($this->data->product_s_desc), $templateDesc);
$templateDesc = str_replace("{product_desc}", htmlspecialchars_decode($this->data->product_desc), $templateDesc);
$templateDesc = str_replace("{view_full_size_image_lbl}", JText::_('COM_REDSHOP_VIEW_FULL_SIZE_IMAGE_LBL'), $templateDesc);

if (strstr($templateDesc, "{zoom_image}"))
{
	$sendlink      = $url . 'components/com_redshop/assets/images/product/' . $this->data->product_full_image;
	$send_image    = "<a  onclick=\"setZoomImagepath(this)\"
							title='" . $this->data->product_name . "'
							id='rsZoom_image" . $this->data->product_id . "'
							href='" . $sendlink . "' rel=\"lightbox[gallery]\">
			<div class='zoom_image' id='rsDiv_zoom_image'>" . JText::_('SEND_MAIL_IMAGE_LBL') . "</div></a>";
	$templateDesc = str_replace("{zoom_image}", $send_image, $templateDesc);
}

if (strstr($templateDesc, "{product_category_list}"))
{
	$pcats    = "";
	$prodCats = RedshopHelperProduct::getProductCaterories($this->data->product_id, 1);

	foreach ($prodCats as $prodCat)
	{
		$pcats .= '<a title="' . $prodCat->name . '" href="' . $prodCat->link . '">';
		$pcats .= $prodCat->name;
		$pcats .= "</a><br />";
	}

	$templateDesc = str_replace("{product_category_list}", $pcats, $templateDesc);
}

if (strpos($templateDesc, "{manufacturer_image}") !== false)
{
	$manufacturerImage = '';
	$manufacturerMedia = RedshopEntityManufacturer::getInstance($this->data->manufacturer_id)->getMedia();

	if ($manufacturerMedia->isValid() && !empty($manufacturerMedia->get('media_name'))
		&& JFile::exists(REDSHOP_MEDIA_IMAGE_RELPATH . 'manufacturer/' . $this->data->manufacturer_id . '/' . $manufacturerMedia->get('media_name')))
	{
		$thumbHeight = Redshop::getConfig()->get('MANUFACTURER_THUMB_HEIGHT');
		$thumbWidth  = Redshop::getConfig()->get('MANUFACTURER_THUMB_WIDTH');

		if (Redshop::getConfig()->get('WATERMARK_MANUFACTURER_IMAGE') || Redshop::getConfig()->get('WATERMARK_MANUFACTURER_THUMB_IMAGE'))
		{
			$imagePath = RedshopHelperMedia::watermark(
				'manufacturer',
				$manufacturerMedia->get('media_name'),
				$thumbWidth,
				$thumbHeight,
				Redshop::getConfig()->get('WATERMARK_MANUFACTURER_IMAGE')
			);
		}
		else
		{
			$imagePath = RedshopHelperMedia::getImagePath(
				$manufacturerMedia->get('media_name'),
				'',
				'thumb',
				'manufacturer',
				$thumbWidth,
				$thumbHeight,
				Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING'),
				'manufacturer',
				$this->data->manufacturer_id
			);
		}

		$altText = $manufacturerMedia->get('media_alternate_text');

		$manufacturerImage = "<a title='" . $altText . "' class=\"modal\" href='" . REDSHOP_MEDIA_IMAGE_ABSPATH . 'manufacturer/' . $this->data->manufacturer_id . '/' . $manufacturerMedia->get('media_name') . "'   rel=\"{handler: 'image', size: {}}\">
				<img alt='" . $altText . "' title='" . $altText . "' src='" . $imagePath . "'></a>";
	}

	$templateDesc = str_replace("{manufacturer_image}", $manufacturerImage, $templateDesc);
}

$product_weight_unit = '<span class="product_unit_variable">' . Redshop::getConfig()->get('DEFAULT_WEIGHT_UNIT') . '</span>';

if ($this->data->weight > 0)
{
	$insertStr     = RedshopHelperProduct::redunitDecimal($this->data->weight) . "&nbsp;" . $product_weight_unit;
	$templateDesc = str_replace("{product_weight}", $insertStr, $templateDesc);
	$templateDesc = str_replace("{product_weight_lbl}", JText::_('COM_REDSHOP_PRODUCT_WEIGHT_LBL'), $templateDesc);
}
else
{
	$templateDesc = str_replace("{product_weight}", "", $templateDesc);
	$templateDesc = str_replace("{product_weight_lbl}", "", $templateDesc);
}

$templateDesc = RedshopHelperStockroom::replaceStockroomAmountDetail($templateDesc, $this->data->product_id);

$templateDesc = str_replace("{update_date}", $redshopconfig->convertDateFormat(strtotime($this->data->update_date)), $templateDesc);

if ($this->data->publish_date != '0000-00-00 00:00:00')
{
	$templateDesc = str_replace("{publish_date}", $redshopconfig->convertDateFormat(strtotime($this->data->publish_date)), $templateDesc);
}
else
{
	$templateDesc = str_replace("{publish_date}", "", $templateDesc);
}

/*
 * Conditional tag
 * if product on discount : Yes
 * {if product_on_sale} This product is on sale {product_on_sale end if} // OUTPUT : This product is on sale
 * NO : // OUTPUT : Display blank
 */
$templateDesc = RedshopHelperProduct::getProductOnSaleComment($this->data, $templateDesc);

/*
 * Conditional tag
 * if product on discount : Yes
 * {if product_special} This is a special product {product_special end if} // OUTPUT : This is a special product
 * NO : // OUTPUT : Display blank
 */
$templateDesc = RedshopHelperProduct::getSpecialProductComment($this->data, $templateDesc);

$manuUrl          = JRoute::_(
	'index.php?option=com_redshop&view=manufacturers&layout=detail&mid=' . $this->data->manufacturer_id .
	'&Itemid=' . $this->itemId
);
$manufacturerLink = "<a class='btn btn-primary' href='" . $manuUrl . "'>" . JText::_("COM_REDSHOP_VIEW_MANUFACTURER") . "</a>";

$manuUrl           = JRoute::_(
	'index.php?option=com_redshop&view=manufacturers&layout=products&mid=' . $this->data->manufacturer_id .
	'&Itemid=' . $this->itemId
);
$manufacturerPLink = "<a class='btn btn-primary' href='" . $manuUrl . "'>" .
	JText::_("COM_REDSHOP_VIEW_ALL_MANUFACTURER_PRODUCTS") . " " . $this->data->manufacturer_name .
	"</a>";

$templateDesc = str_replace("{manufacturer_link}", $manufacturerLink, $templateDesc);
$templateDesc = str_replace("{manufacturer_product_link}", $manufacturerPLink, $templateDesc);
$templateDesc = str_replace("{manufacturer_name}", $this->data->manufacturer_name, $templateDesc);

$supplier_name = '';

if ($this->data->supplier_id)
{
	$supplier_name = $this->model->getNameSupplierById($this->data->supplier_id);
}

$templateDesc = str_replace("{supplier_name}", $supplier_name, $templateDesc);

if (strstr($templateDesc, "{product_delivery_time}"))
{
	$product_delivery_time = RedshopHelperProduct::getProductMinDeliveryTime($this->data->product_id);

	if ($product_delivery_time != "")
	{
		$templateDesc = str_replace("{delivery_time_lbl}", JText::_('COM_REDSHOP_DELIVERY_TIME'), $templateDesc);
		$templateDesc = str_replace("{product_delivery_time}", $product_delivery_time, $templateDesc);
	}
	else
	{
		$templateDesc = str_replace("{delivery_time_lbl}", "", $templateDesc);
		$templateDesc = str_replace("{product_delivery_time}", "", $templateDesc);
	}
}

// Facebook I like Button
if (strstr($templateDesc, "{facebook_like_button}"))
{
	$uri           = JUri::getInstance();
	$facebook_link = urlencode(JFilterOutput::cleanText($uri->toString()));
	$facebook_like = '<iframe src="' . $Scheme . '://www.facebook.com/plugins/like.php?href=' . $facebook_link . '&amp;layout=standard&amp;show_faces=true&amp;width=450&amp;action=like&amp;font&amp;colorscheme=light&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:80px;" allowTransparency="true"></iframe>';
	$templateDesc = str_replace("{facebook_like_button}", $facebook_like, $templateDesc);

	$jconfig  = JFactory::getConfig();
	$sitename = $jconfig->get('sitename');

	$this->document->setMetaData("og:url", JFilterOutput::cleanText($uri->toString()));
	$this->document->setMetaData("og:type", "product");
	$this->document->setMetaData("og:site_name", $sitename);
}

// Google I like Button
if (strstr($templateDesc, "{googleplus1}"))
{
	JHTML::script('https://apis.google.com/js/plusone.js');
	$uri           = JUri::getInstance();
	$google_like   = '<g:plusone></g:plusone>';
	$templateDesc = str_replace("{googleplus1}", $google_like, $templateDesc);
}

if (strstr($templateDesc, "{bookmark}"))
{
	$bookmark      = '<script type="text/javascript">addthis_pub = "AddThis";</script>';
	$bookmark      .= '<a href="' . $Scheme . '://www.addthis.com/bookmark.php" onmouseover="return addthis_open(this, \'\', \'[URL]\', \'[TITLE]\')" onmouseout="addthis_close()" onclick="return addthis_sendto()">';
	$bookmark      .= '<img src="' . $Scheme . '://s7.addthis.com/static/btn/lg-share-en.gif" alt="Share" border="0" height="16" width="125"></a>';
	$bookmark      .= '<script type="text/javascript" src="' . $Scheme . '://s7.addthis.com/js/200/addthis_widget.js"></script>';
	$templateDesc = str_replace("{bookmark}", $bookmark, $templateDesc);
}

//  Extra field display
$extraFieldName = Redshop\Helper\ExtraFields::getSectionFieldNames(RedshopHelperExtrafields::SECTION_PRODUCT, null);
$templateDesc  = RedshopHelperProductTag::getExtraSectionTag($extraFieldName, $this->data->product_id, "1", $templateDesc);

// Product thumb image
if (strstr($templateDesc, "{product_thumb_image_3}"))
{
	$pimg_tag = '{product_thumb_image_3}';
	$ph_thumb = Redshop::getConfig()->get('PRODUCT_MAIN_IMAGE_HEIGHT_3');
	$pw_thumb = Redshop::getConfig()->get('PRODUCT_MAIN_IMAGE_3');
}
elseif (strstr($templateDesc, "{product_thumb_image_2}"))
{
	$pimg_tag = '{product_thumb_image_2}';
	$ph_thumb = Redshop::getConfig()->get('PRODUCT_MAIN_IMAGE_HEIGHT_2');
	$pw_thumb = Redshop::getConfig()->get('PRODUCT_MAIN_IMAGE_2');
}
elseif (strstr($templateDesc, "{product_thumb_image_1}"))
{
	$pimg_tag = '{product_thumb_image_1}';
	$ph_thumb = Redshop::getConfig()->get('PRODUCT_MAIN_IMAGE_HEIGHT');
	$pw_thumb = Redshop::getConfig()->get('PRODUCT_MAIN_IMAGE');
}
else
{
	$pimg_tag = '{product_thumb_image}';
	$ph_thumb = Redshop::getConfig()->get('PRODUCT_MAIN_IMAGE_HEIGHT');
	$pw_thumb = Redshop::getConfig()->get('PRODUCT_MAIN_IMAGE');
}

// More images
if (strstr($templateDesc, "{more_images_3}"))
{
	$mpimg_tag = '{more_images_3}';
	$mph_thumb = Redshop::getConfig()->get('PRODUCT_ADDITIONAL_IMAGE_HEIGHT_3');
	$mpw_thumb = Redshop::getConfig()->get('PRODUCT_ADDITIONAL_IMAGE_3');
}
elseif (strstr($templateDesc, "{more_images_2}"))
{
	$mpimg_tag = '{more_images_2}';
	$mph_thumb = Redshop::getConfig()->get('PRODUCT_ADDITIONAL_IMAGE_HEIGHT_2');
	$mpw_thumb = Redshop::getConfig()->get('PRODUCT_ADDITIONAL_IMAGE_2');
}
elseif (strstr($templateDesc, "{more_images_1}"))
{
	$mpimg_tag = '{more_images_1}';
	$mph_thumb = Redshop::getConfig()->get('PRODUCT_ADDITIONAL_IMAGE_HEIGHT');
	$mpw_thumb = Redshop::getConfig()->get('PRODUCT_ADDITIONAL_IMAGE');
}
else
{
	$mpimg_tag = '{more_images}';
	$mph_thumb = Redshop::getConfig()->get('PRODUCT_ADDITIONAL_IMAGE_HEIGHT');
	$mpw_thumb = Redshop::getConfig()->get('PRODUCT_ADDITIONAL_IMAGE');
}

// PRODUCT WRAPPER START
$wrapper         = RedshopHelperProduct::getWrapper($this->data->product_id, 0, 1);
$wrappertemplate = $this->redTemplate->getTemplate("wrapper_template");

if (strstr($templateDesc, "{wrapper_template:"))
{
	for ($w = 0, $wn = count($wrappertemplate); $w < $wn; $w++)
	{
		if (strstr($templateDesc, "{wrapper_template:" . $wrappertemplate[$w]->name . "}"))
		{
			$wrapperHtml = RedshopTagsReplacer::_(
				'wrapper',
				$wrappertemplate[$w]->template_desc,
				array(
					'data' => $this->data,
					'wrapper' => $wrapper
				)
			);

			$templateDesc = str_replace("{wrapper_template:" . $wrappertemplate[$w]->name . "}", $wrapperHtml, $templateDesc);
		}
	}
}
// PRODUCT WRAPPER END

if (strstr($templateDesc, "{child_products}"))
{
	$parentproductid = $this->data->product_id;

	if ($this->data->product_parent_id != 0)
	{
		$parentproductid = RedshopHelperProduct::getMainParentProduct($this->data->product_id);
	}

	$frmChild = "";

	if ($parentproductid != 0)
	{
		$productInfo = \Redshop\Product\Product::getProductById($parentproductid);

		// Get child products
		$childproducts = $this->model->getAllChildProductArrayList(0, $parentproductid);

		if (!empty($childproducts))
		{
			$childproducts = array_merge(array($productInfo), $childproducts);

			$display_text = (Redshop::getConfig()->get('CHILDPRODUCT_DROPDOWN') == "product_number") ? "product_number" : "product_name";

			$selected = array($this->data->product_id);

			$lists['product_child_id'] = JHtml::_(
				'select.genericlist',
				$childproducts,
				'pid',
				'class="inputbox" size="1"  onchange="document.frmChild.submit();"',
				'product_id',
				$display_text,
				$selected
			);

			$frmChild .= "<form name='frmChild' method='post' action=''>";
			$frmChild .= "<div class='product_child_product'>" . JText::_('COM_REDSHOP_CHILD_PRODUCTS') . "</div>";
			$frmChild .= "<div class='product_child_product_list'>" . $lists ['product_child_id'] . "</div>";
			$frmChild .= "<input type='hidden' name='view' value='product'>";
			$frmChild .= "<input type='hidden' name='task' value='gotochild'>";
			$frmChild .= "<input type='hidden' name='option' value='com_redshop'>";
			$frmChild .= "<input type='hidden' name='Itemid' value='" . $this->itemId . "'>";
			$frmChild .= "</form>";
		}
	}

	$templateDesc = str_replace("{child_products}", $frmChild, $templateDesc);
}

// Checking for child products
$childproduct = RedshopHelperProduct::getChildProduct($this->data->product_id);

if (!empty($childproduct))
{
	if (Redshop::getConfig()->get('PURCHASE_PARENT_WITH_CHILD') == 1)
	{
		$isChilds       = false;
		$attributes_set = array();

		if ($this->data->attribute_set_id > 0)
		{
			$attributes_set = \Redshop\Product\Attribute::getProductAttribute(0, $this->data->attribute_set_id, 0, 1);
		}

		$attributes = \Redshop\Product\Attribute::getProductAttribute($this->data->product_id);
		$attributes = array_merge($attributes, $attributes_set);
	}
	else
	{
		$isChilds   = true;
		$attributes = array();
	}
}
else
{
	$isChilds       = false;
	$attributes_set = array();

	if ($this->data->attribute_set_id > 0)
	{
		$attributes_set = \Redshop\Product\Attribute::getProductAttribute(0, $this->data->attribute_set_id, 0, 1);
	}

	$attributes = \Redshop\Product\Attribute::getProductAttribute($this->data->product_id);
	$attributes = array_merge($attributes, $attributes_set);
}

$attribute_template = \Redshop\Template\Helper::getAttribute($templateDesc);

// Check product for not for sale
$templateDesc = RedshopHelperProduct::getProductNotForSaleComment($this->data, $templateDesc, $attributes);

// Replace product in stock tags
$templateDesc = Redshop\Product\Stock::replaceInStock($this->data->product_id, $templateDesc, $attributes, $attribute_template);

// Product attribute  Start
$totalatt      = count($attributes);

$templateDesc = RedshopTagsReplacer::_(
        'attributes',
	    $templateDesc,
        array(
            'productId' => $this->data->product_id,
            'accessoryId' => 0,
            'relatedProductId' => 0,
            'attributes' => $attributes,
            'attributeTemplate' => $attribute_template,
            'isChild' => $isChilds
        )
);

// Product attribute  End

$prNumber                   = $this->data->product_number;
$preselectedresult           = array();
$moreimage_response          = '';
$property_data               = '';
$subproperty_data            = '';
$attributeproductStockStatus = null;
$selectedpropertyId          = 0;
$selectedsubpropertyId       = 0;

if (!empty($attributes) && !empty($attribute_template))
{
	for ($a = 0, $an = count($attributes); $a < $an; $a++)
	{
		$selectedId = array();
		$property   = RedshopHelperProduct_Attribute::getAttributeProperties(0, $attributes[$a]->attribute_id);

		if ($attributes[$a]->text != "" && count($property) > 0)
		{
			for ($i = 0, $in = count($property); $i < $in; $i++)
			{
				if ($property[$i]->setdefault_selected)
				{
					$selectedId[]  = $property[$i]->property_id;
					$property_data .= $property[$i]->property_id;

					if ($i != (count($property) - 1))
					{
						$property_data .= '##';
					}
				}
			}

			if (count($selectedId) > 0)
			{
				$selectedpropertyId = $selectedId[count($selectedId) - 1];
				$subproperty        = RedshopHelperProduct_Attribute::getAttributeSubProperties(0, $selectedpropertyId);
				$selectedId         = array();

				for ($sp = 0; $sp < count($subproperty); $sp++)
				{
					if ($subproperty[$sp]->setdefault_selected)
					{
						$selectedId[]     = $subproperty[$sp]->subattribute_color_id;
						$subproperty_data .= $subproperty[$sp]->subattribute_color_id;

						if ($sp != (count($subproperty) - 1))
						{
							$subproperty_data .= '##';
						}
					}
				}

				if (count($selectedId) > 0)
				{
					$subproperty_data      = implode('##', $selectedId);
					$selectedsubpropertyId = $selectedId[count($selectedId) - 1];
				}
			}
		}
	}

	$get['product_id']       = $this->data->product_id;
	$get['main_imgwidth']    = $pw_thumb;
	$get['main_imgheight']   = $ph_thumb;
	$get['property_data']    = $property_data;
	$get['subproperty_data'] = $subproperty_data;
	$get['property_id']      = $selectedpropertyId;
	$get['subproperty_id']   = $selectedsubpropertyId;
	$pluginResults           = array();

	// Trigger plugin to get merge images.
	$this->dispatcher->trigger('onBeforeImageLoad', array($get, &$pluginResults));

	$preselectedresult = RedshopHelperProductTag::displayAdditionalImage(
		$this->data->product_id,
		0,
		0,
		$selectedpropertyId,
		$selectedsubpropertyId,
		$pw_thumb,
		$ph_thumb,
		'product'
	);

	if (isset($pluginResults['mainImageResponse']))
	{
		$preselectedresult['product_mainimg'] = $pluginResults['mainImageResponse'];
	}

	$productAvailabilityDate = strstr($templateDesc, "{product_availability_date}");
	$stockNotifyFlag         = strstr($templateDesc, "{stock_notify_flag}");
	$stockStatus             = strstr($templateDesc, "{stock_status");

	if ($productAvailabilityDate || $stockNotifyFlag || $stockStatus)
	{
		$attributeproductStockStatus = RedshopHelperProduct::getproductStockStatus(
			$this->data->product_id,
			$totalatt,
			$selectedpropertyId,
			$selectedsubpropertyId
		);
	}

	$moreimage_response  = $preselectedresult['response'];
	$aHrefImageResponse  = $preselectedresult['aHrefImageResponse'];
	$aTitleImageResponse = $preselectedresult['aTitleImageResponse'];

	$mainImageResponse = $preselectedresult['product_mainimg'];

	$attributeImg = $preselectedresult['attrbimg'];

	if (!is_null($preselectedresult['pr_number']) && !empty($preselectedresult['pr_number']))
	{
		$prNumber = $preselectedresult['pr_number'];
	}
}
else
{
	$productAvailabilityDate = strstr($templateDesc, "{product_availability_date}");
	$stockNotifyFlag         = strstr($templateDesc, "{stock_notify_flag}");
	$stockStatus             = strstr($templateDesc, "{stock_status");

	if ($productAvailabilityDate || $stockNotifyFlag || $stockStatus)
	{
		$attributeproductStockStatus = RedshopHelperProduct::getproductStockStatus($this->data->product_id, $totalatt);
	}
}

$templateDesc = \Redshop\Helper\Stockroom::replaceProductStockData(
	$this->data->product_id,
	$selectedpropertyId,
	$selectedsubpropertyId,
	$templateDesc,
	$attributeproductStockStatus
);

$product_number_output = '<span id="product_number_variable' . $this->data->product_id . '">' . $prNumber . '</span>';
$templateDesc         = str_replace("{product_number}", $product_number_output, $templateDesc);

// Product accessory Start
$accessory      = RedshopHelperAccessory::getProductAccessories(0, $this->data->product_id);
$totalAccessory = count($accessory);

$templateDesc = RedshopHelperProductAccessory::replaceAccessoryData($this->data->product_id, 0, $accessory, $templateDesc, $isChilds);

// Product accessory End

if (strstr($templateDesc, $mpimg_tag))
{
	if ($moreimage_response != "")
	{
		$more_images = $moreimage_response;
	}
	else
	{
		$media_image = RedshopHelperMedia::getAdditionMediaImage($this->data->product_id, "product");
		$more_images = '';

		for ($m = 0, $mn = count($media_image); $m < $mn; $m++)
		{
			$filename1 = REDSHOP_FRONT_IMAGES_RELPATH . "product/" . $media_image[$m]->media_name;

			if ($media_image[$m]->media_name != $media_image[$m]->product_full_image && file_exists($filename1))
			{
				$alttext = RedshopHelperMedia::getAlternativeText('product', $media_image[$m]->section_id, '', $media_image[$m]->media_id);

				if (!$alttext)
				{
					$alttext = $media_image [$m]->media_name;
				}

				if ($media_image [$m]->media_name)
				{
					$thumb = $media_image [$m]->media_name;

					if (Redshop::getConfig()->get('WATERMARK_PRODUCT_ADDITIONAL_IMAGE'))
					{
						$pimg      = RedshopHelperMedia::watermark('product', $thumb, $mpw_thumb, $mph_thumb, Redshop::getConfig()->get('WATERMARK_PRODUCT_ADDITIONAL_IMAGE'), "1");
						$linkimage = RedshopHelperMedia::watermark('product', $thumb, '', '', Redshop::getConfig()->get('WATERMARK_PRODUCT_ADDITIONAL_IMAGE'), "0");

						$hoverimg_path = RedshopHelperMedia::watermark(
							'product',
							$thumb,
							Redshop::getConfig()->get('ADDITIONAL_HOVER_IMAGE_WIDTH'),
							Redshop::getConfig()->get('ADDITIONAL_HOVER_IMAGE_HEIGHT'),
							Redshop::getConfig()->get('WATERMARK_PRODUCT_ADDITIONAL_IMAGE'),
							'2'
						);
					}
					else
					{
						$pimg      = RedshopHelperMedia::getImagePath(
							$thumb,
							'',
							'thumb',
							'product',
							$mpw_thumb,
							$mph_thumb,
							Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING')
						);
						$linkimage = REDSHOP_FRONT_IMAGES_ABSPATH . "product/" . $thumb;

						$hoverimg_path = RedshopHelperMedia::getImagePath(
							$thumb,
							'',
							'thumb',
							'product',
							Redshop::getConfig()->get('ADDITIONAL_HOVER_IMAGE_WIDTH'),
							Redshop::getConfig()->get('ADDITIONAL_HOVER_IMAGE_HEIGHT'),
							Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING')
						);
					}

					if (Redshop::getConfig()->get('PRODUCT_ADDIMG_IS_LIGHTBOX'))
					{
						$more_images_div_start = "<div class='additional_image'><a href='" . $linkimage . "' title='" . $alttext . "' rel=\"myallimg\">";
						$more_images_div_end   = "</a></div>";
						$more_images           .= $more_images_div_start;
						$more_images           .= "<img src='" . $pimg . "' alt='" . $alttext . "' title='" . $alttext . "'>";
						$more_images_hrefend   = "";
					}
					else
					{
						if (Redshop::getConfig()->get('WATERMARK_PRODUCT_ADDITIONAL_IMAGE'))
						{
							$img_path = RedshopHelperMedia::watermark('product', $thumb, $pw_thumb, $ph_thumb, Redshop::getConfig()->get('WATERMARK_PRODUCT_ADDITIONAL_IMAGE'), '0');
						}
						else
						{
							$img_path = RedshopHelperMedia::getImagePath(
								$thumb,
								'',
								'thumb',
								'product',
								$pw_thumb,
								$ph_thumb,
								Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING')
							);
						}

						$hovermore_images = RedshopHelperMedia::watermark('product', $thumb, '', '', Redshop::getConfig()->get('WATERMARK_PRODUCT_ADDITIONAL_IMAGE'), '0');

						$filename_org = REDSHOP_FRONT_IMAGES_RELPATH . "product/" . $media_image[$m]->product_full_image;

						if (file_exists($filename_org))
						{
							$thumb_original = $media_image[$m]->product_full_image;
						}
						else
						{
							$thumb_original = Redshop::getConfig()->get('PRODUCT_DEFAULT_IMAGE');
						}

						if (Redshop::getConfig()->get('WATERMARK_PRODUCT_THUMB_IMAGE'))
						{
							$img_path_org = RedshopHelperMedia::watermark('product', $thumb_original, $pw_thumb, $ph_thumb, Redshop::getConfig()->get('WATERMARK_PRODUCT_THUMB_IMAGE'), '0');
						}
						else
						{
							$img_path_org = RedshopHelperMedia::getImagePath(
								$thumb_original,
								'',
								'thumb',
								'product',
								$pw_thumb,
								$ph_thumb,
								Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING')
							);
						}

						$hovermore_org = RedshopHelperMedia::getImagePath(
							$thumb_original,
							'',
							'thumb',
							'product',
							$pw_thumb,
							$ph_thumb,
							Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING')
						);
						$oimg_path     = RedshopHelperMedia::getImagePath(
							$thumb,
							'',
							'thumb',
							'product',
							$mpw_thumb,
							$mph_thumb,
							Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING')
						);

						$more_images_div_start = "<div class='additional_image'
						 								onmouseover='display_image(\"" . $img_path . "\"," . $this->data->product_id . ",\"" . $hovermore_images . "\");'
						 								onmouseout='display_image_out(\"" . $img_path_org . "\"," . $this->data->product_id . ",\"" . $img_path_org . "\");'>";
						$more_images_div_end   = "</div>";
						$more_images           .= $more_images_div_start;
						$more_images           .= '<a href="javascript:void(0)" >' . "<img src='" . $pimg . "' title='" . $alttext . "' style='cursor: auto;'>";
						$more_images_hrefend   = "</a>";
					}

					if (Redshop::getConfig()->get('ADDITIONAL_HOVER_IMAGE_ENABLE'))
					{
						$more_images .= "<img src='" . $hoverimg_path . "' alt='" . $alttext . "' title='" . $alttext . "' class='redImagepreview'>";
					}

					$more_images .= $more_images_hrefend;
					$more_images .= $more_images_div_end;
				}
			}
		}
	}

	$insertStr     = "<div class='redhoverImagebox' id='additional_images" . $this->data->product_id . "'>" . $more_images . "</div><div class=\"clr\"></div>";
	$templateDesc = str_replace($mpimg_tag, $insertStr, $templateDesc);
}

// More images end

// More videos (youtube)
if (strstr($templateDesc, "{more_videos}"))
{
	$media_youtube = RedshopHelperProduct::getVideosProduct($this->data->product_id, $attributes, $attribute_template, 'youtube');
	$media_videos = RedshopHelperProduct::getVideosProduct($this->data->product_id, $attributes, $attribute_template, 'video');

	$insertStr = '';

	if (count($media_youtube) > 0)
	{
		for ($m = 0, $mn = count($media_youtube); $m < $mn; $m++)
		{
			$insertStr .= "<div id='additional_vids_" . $media_youtube[$m]->media_id . "'><a class='modal' title='" . $media_youtube[$m]->media_alternate_text . "' href='http://www.youtube.com/embed/" . $media_youtube[$m]->media_name . "' rel='{handler: \"iframe\", size: {x: 800, y: 500}}'><img src='https://img.youtube.com/vi/" . $media_youtube[$m]->media_name . "/default.jpg' height='80px' width='80px'/></a></div>";
		}
	}

	if (count($media_videos) > 0)
	{
		for ($m = 0, $mn = count($media_videos); $m < $mn; $m++)
		{
			$videoPath = REDSHOP_FRONT_VIDEO_ABSPATH . $media_videos[$m]->media_section . '/' . $media_videos[$m]->media_name;
			$insertStr .= '<video width="400" controls autoplay><source src="'. $videoPath .'" type="'. $media_videos[$m]->media_mimetype .'"></video>';
		}
	}

	$templateDesc = str_replace("{more_videos}", $insertStr, $templateDesc);
}
// More videos (youtube) end

// More documents
if (strstr($templateDesc, "{more_documents}"))
{
	$media_documents = RedshopHelperMedia::getAdditionMediaImage($this->data->product_id, "product", "document");
	$more_doc        = '';

	for ($m = 0, $mn = count($media_documents); $m < $mn; $m++)
	{
		$alttext = RedshopHelperMedia::getAlternativeText("product", $media_documents[$m]->section_id, "", $media_documents[$m]->media_id, "document");

		if (!$alttext)
		{
			$alttext = $media_documents[$m]->media_name;
		}

		if (JFile::exists(REDSHOP_FRONT_DOCUMENT_RELPATH . "product/" . $media_documents[$m]->media_name))
		{
			$downlink = JURI::root() . 'index.php?tmpl=component&option=com_redshop&view=product&pid=' . $this->data->product_id .
				'&task=downloadDocument&fname=' . $media_documents[$m]->media_name .
				'&Itemid=' . $this->itemId;
			$more_doc .= "<div><a href='" . $downlink . "' title='" . $alttext . "'>";
			$more_doc .= $alttext;
			$more_doc .= "</a></div>";
		}
	}

	$insertStr     = "<span id='additional_docs" . $this->data->product_id . "'>" . $more_doc . "</span>";
	$templateDesc = str_replace("{more_documents}", $insertStr, $templateDesc);
}

// More documents end

$hidden_thumb_image = "<input type='hidden' name='prd_main_imgwidth' id='prd_main_imgwidth' value='" . $pw_thumb . "'>
						<input type='hidden' name='prd_main_imgheight' id='prd_main_imgheight' value='" . $ph_thumb . "'>";
$link               = JRoute::_('index.php?option=com_redshop&view=product&pid=' . $this->data->product_id);

// Product image
$thum_image = "<div style='height: " . $ph_thumb . "px' class='productImageWrap' id='productImageWrapID_" . $this->data->product_id . "'>" .
	Redshop\Product\Image\Image::getImage($this->data->product_id, $link, $pw_thumb, $ph_thumb, Redshop::getConfig()->get('PRODUCT_DETAIL_IS_LIGHTBOX'), 0, 0, $preselectedresult) .
	"</div>";

$templateDesc = str_replace($pimg_tag, $thum_image . $hidden_thumb_image, $templateDesc);
// Product image end

$templateDesc = RedshopHelperProduct::getJcommentEditor($this->data, $templateDesc);

// ProductFinderDatepicker Extra Field Start

$fieldArray    = RedshopHelperExtrafields::getSectionFieldList(17, 0, 0);
$templateDesc = RedshopHelperProduct::getProductFinderDatepickerValue($templateDesc, $this->data->product_id, $fieldArray);

// ProductFinderDatepicker Extra Field End

// Product User Field Start
$count_no_user_field = 0;
$returnArr           = \Redshop\Product\Product::getProductUserfieldFromTemplate($templateDesc);
$template_userfield  = $returnArr[0];
$userfieldArr        = $returnArr[1];

if (strstr($templateDesc, "{if product_userfield}") && strstr($templateDesc, "{product_userfield end if}") && $template_userfield != "")
{
	$ufield = "";
	$cart   = $this->session->get('cart');

	if (isset($cart['idx']))
	{
		$idx = (int) ($cart['idx']);
	}

	$idx     = 0;
	$cart_id = '';

	for ($j = 0; $j < $idx; $j++)
	{
		if ($cart[$j]['product_id'] == $this->data->product_id)
		{
			$cart_id = $j;
		}
	}

	for ($ui = 0; $ui < count($userfieldArr); $ui++)
	{
		if (!$idx)
		{
			$cart_id = "";
		}

		$productUserFields = Redshop\Fields\SiteHelper::listAllUserFields($userfieldArr[$ui], 12, '', $cart_id, 0, $this->data->product_id);

		$ufield .= $productUserFields[1];

		if ($productUserFields[1] != "")
		{
			$count_no_user_field++;
		}

		$templateDesc = str_replace('{' . $userfieldArr[$ui] . '_lbl}', $productUserFields[0], $templateDesc);
		$templateDesc = str_replace('{' . $userfieldArr[$ui] . '}', $productUserFields[1], $templateDesc);
	}

	$productUserFieldsForm = "<form method='post' action='' id='user_fields_form' name='user_fields_form'>";

	if ($ufield != "")
	{
		$templateDesc = str_replace("{if product_userfield}", $productUserFieldsForm, $templateDesc);
		$templateDesc = str_replace("{product_userfield end if}", "</form>", $templateDesc);
	}
	else
	{
		$templateDesc = str_replace("{if product_userfield}", "", $templateDesc);
		$templateDesc = str_replace("{product_userfield end if}", "", $templateDesc);
	}
}

// Product User Field End

// Category front-back image tag...
if (strstr($templateDesc, "{category_product_img}"))
{
	$mainsrcPath = RedshopHelperMedia::getImagePath(
		$this->data->category_full_image,
		'',
		'thumb',
		'category',
		$pw_thumb,
		$ph_thumb,
		Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING')
	);
	$backsrcPath = RedshopHelperMedia::getImagePath(
		$this->data->category_back_full_image,
		'',
		'thumb',
		'category',
		$pw_thumb,
		$ph_thumb,
		Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING')
	);

	$ahrefpath     = REDSHOP_FRONT_IMAGES_ABSPATH . "category/" . $this->data->category_full_image;
	$ahrefbackpath = REDSHOP_FRONT_IMAGES_ABSPATH . "product/" . $this->data->category_back_full_image;

	$product_front_image_link = "<a href='#' onClick='javascript:changeproductImage(" . $this->data->product_id . ",\"" . $mainsrcPath . "\",\"" .
		$ahrefpath . "\");'>" .
		JText::_('COM_REDSHOP_FRONT_IMAGE') .
		"</a>";
	$product_back_image_link  = "<a href='#' onClick='javascript:changeproductImage(" . $this->data->product_id . ",\"" . $backsrcPath . "\",\"" .
		$ahrefbackpath . "\");'>" .
		JText::_('COM_REDSHOP_BACK_IMAGE') .
		"</a>";

	$templateDesc = str_replace("{category_front_img_link}", $product_front_image_link, $templateDesc);
	$templateDesc = str_replace("{category_back_img_link}", $product_back_image_link, $templateDesc);

	// Display category front image
	$thum_catimage = RedshopHelperProduct::getProductCategoryImage(
		$this->data->product_id,
		$this->data->category_full_image,
		'',
		$pw_thumb, $ph_thumb,
		Redshop::getConfig()->get('PRODUCT_DETAIL_IS_LIGHTBOX')
	);
	$templateDesc = str_replace("{category_product_img}", $thum_catimage, $templateDesc);

	// Category front-back image tag end
}
else
{
	$templateDesc = str_replace("{category_front_img_link}", "", $templateDesc);
	$templateDesc = str_replace("{category_back_img_link}", "", $templateDesc);
	$templateDesc = str_replace("{category_product_img}", "", $templateDesc);
}

if (strstr($templateDesc, "{front_img_link}") || strstr($templateDesc, "{back_img_link}"))
{
	// Front-back image tag...
	if ($this->data->product_thumb_image)
	{
		$mainsrcPath = REDSHOP_FRONT_IMAGES_ABSPATH . "product/" . $this->data->product_thumb_image;
	}
	else
	{
		$mainsrcPath = RedshopHelperMedia::getImagePath(
			$this->data->product_full_image,
			'',
			'thumb',
			'product',
			$pw_thumb,
			$ph_thumb,
			Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING')
		);
	}

	if ($this->data->product_back_thumb_image)
	{
		$backsrcPath = REDSHOP_FRONT_IMAGES_ABSPATH . "product/" . $this->data->product_back_thumb_image;
	}
	else
	{
		$backsrcPath = RedshopHelperMedia::getImagePath(
			$this->data->product_back_full_image,
			'',
			'thumb',
			'product',
			$pw_thumb,
			$ph_thumb,
			Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING')
		);
	}

	$ahrefpath     = REDSHOP_FRONT_IMAGES_ABSPATH . "product/" . $this->data->product_full_image;
	$ahrefbackpath = REDSHOP_FRONT_IMAGES_ABSPATH . "product/" . $this->data->product_back_full_image;

	$product_front_image_link = "<a href='#' onClick='javascript:changeproductImage(" . $this->data->product_id . ",\"" . $mainsrcPath . "\",\"" .
		$ahrefpath . "\");'>" .
		JText::_('COM_REDSHOP_FRONT_IMAGE') .
		"</a>";
	$product_back_image_link  = "<a href='#' onClick='javascript:changeproductImage(" . $this->data->product_id . ",\"" . $backsrcPath . "\",\"" .
		$ahrefbackpath . "\");'>" .
		JText::_('COM_REDSHOP_BACK_IMAGE') .
		"</a>";

	$templateDesc = str_replace("{front_img_link}", $product_front_image_link, $templateDesc);
	$templateDesc = str_replace("{back_img_link}", $product_back_image_link, $templateDesc);
}
else
{
	$templateDesc = str_replace("{front_img_link}", "", $templateDesc);
	$templateDesc = str_replace("{back_img_link}", "", $templateDesc);
}

// Front-back image tag end

// Product preview image.
if (strstr($templateDesc, "{product_preview_img}"))
{
	if (JFile::exists(REDSHOP_FRONT_IMAGES_RELPATH . 'product/' . $this->data->product_preview_image))
	{
		$previewsrcPath = RedshopHelperMedia::getImagePath(
			$this->data->product_preview_image,
			'',
			'thumb',
			'product',
			Redshop::getConfig()->get('PRODUCT_PREVIEW_IMAGE_WIDTH'),
			Redshop::getConfig()->get('PRODUCT_PREVIEW_IMAGE_HEIGHT'),
			Redshop::getConfig()->get('USE_IMAGE_SIZE_SWAPPING')
		);

		$previewImg    = "<img src='" . $previewsrcPath . "' class='rs_previewImg' />";
		$templateDesc = str_replace("{product_preview_img}", $previewImg, $templateDesc);
	}
	else
	{
		$templateDesc = str_replace("{product_preview_img}", "", $templateDesc);
	}
}

// Cart
$templateDesc = Redshop\Cart\Render::replace(
	$this->data->product_id,
	$this->data->category_id,
	0,
	0,
	$templateDesc,
	$isChilds,
	$userfieldArr,
	$totalatt,
	$totalAccessory,
	$count_no_user_field
);

$templateDesc = str_replace("{ajaxwishlist_icon}", '', $templateDesc);

// Replace wishlistbutton
$templateDesc = RedshopHelperWishlist::replaceWishlistTag($this->data->product_id, $templateDesc);

// Replace compare product button
$templateDesc = Redshop\Product\Compare::replaceCompareProductsButton($this->data->product_id, $this->data->category_id, $templateDesc);

// Ajax detail box template
$ajaxdetail_templatedata = \Redshop\Template\Helper::getAjaxDetailBox($this->data);

if (null !== $ajaxdetail_templatedata)
{
	$templateDesc = str_replace("{ajaxdetail_template:" . $ajaxdetail_templatedata->name . "}", "", $templateDesc);
}

// Checking if user logged in then only enabling review button
$reviewform = "";

if (($user->id && Redshop::getConfig()->get('RATING_REVIEW_LOGIN_REQUIRED')) || !Redshop::getConfig()->get('RATING_REVIEW_LOGIN_REQUIRED'))
{
	// Write Review link with the products
	if (strstr($templateDesc, "{form_rating_without_lightbox}") && !JFactory::getApplication()->input->getInt('rate', 0))
	{
		$form = RedshopModelForm::getInstance(
			'Product_Rating',
			'RedshopModel',
			array(
				'context' => 'com_redshop.edit.product_rating.' . $this->data->product_id
			)
		)->/** @scrutinizer ignore-call */ getForm();

		$ratingForm = RedshopLayoutHelper::render(
			'product.product_rating',
			array(
				'form'       => $form,
				'modal'      => 0,
				'product_id' => $this->data->product_id,
				'returnUrl'  => base64_encode(Juri::getInstance()->toString())
			)
		);

		$templateDesc = str_replace("{form_rating_without_lightbox}", $ratingForm, $templateDesc);
	}

	if (strstr($templateDesc, "{form_rating}"))
	{
		$reviewlink    = "";
		$reviewform    = "";
		$reviewlink    = JURI::root() . 'index.php?option=com_redshop&view=product_rating&tmpl=component&product_id=' . $this->data->product_id .
			'&category_id=' . $this->data->category_id .
			'&Itemid=' . $this->itemId;
		$reviewform    = '<a class="redbox btn btn-primary" rel="{handler:\'iframe\',size:{x:500,y:500}}" href="' . $reviewlink . '">' .
			JText::_('COM_REDSHOP_WRITE_REVIEW') .
			'</a>';
		$templateDesc = str_replace("{form_rating}", $reviewform, $templateDesc);
	}
}
else
{
	$reviewform = JText::_('COM_REDSHOP_YOU_NEED_TO_LOGIN_TO_POST_A_REVIEW');

	if (strstr($templateDesc, "{form_rating_without_lightbox}"))
	{
		$templateDesc = str_replace("{form_rating_without_lightbox}", $reviewform, $templateDesc);
	}

	if (strstr($templateDesc, "{form_rating}"))
	{
		$templateDesc = str_replace("{form_rating}", $reviewform, $templateDesc);
	}
}

$templateDesc = str_replace("{form_rating}", $reviewform, $templateDesc);

// Product Review/Rating
if (strstr($templateDesc, "{product_rating_summary}"))
{
	$final_avgreview_data = Redshop\Product\Rating::getRating($this->data->product_id);

	if ($final_avgreview_data != "")
	{
		$templateDesc = str_replace("{product_rating_summary}", $final_avgreview_data, $templateDesc);
	}
	else
	{
		$templateDesc = str_replace("{product_rating_summary}", '', $templateDesc);
	}
}

if (strstr($templateDesc, "{product_rating}"))
{
	if ((int) Redshop::getConfig()->get('FAVOURED_REVIEWS') !== 0)
	{
		$mainblock = Redshop::getConfig()->get('FAVOURED_REVIEWS');
	}
	else
	{
		$mainblock = 5;
	}

	$main_template = $this->redTemplate->getTemplate("review");

	if (count($main_template) > 0 && $main_template[0]->template_desc)
	{
		$main_template = $main_template[0]->template_desc;
	}
	else
	{
		$main_template = RedshopHelperTemplate::getDefaultTemplateContent('review');
	}

	$main_template = RedshopTagsReplacer::_(
			'review',
			$main_template,
			array(
				'productId' => $this->data->product_id,
				'mainBlock'    => $mainblock
			)
	);

	$templateDesc = str_replace("{product_rating}", $main_template, $templateDesc);
}

// Send to friend
$rlink            = JURI::root() . 'index.php?option=com_redshop&view=send_friend&pid=' . $this->data->product_id . '&tmpl=component&Itemid=' . $this->itemId;
$send_friend_link = '<a class="redcolorproductimg" href="' . $rlink . '" >' . JText::_('COM_REDSHOP_SEND_FRIEND') . '</a>';
$templateDesc    = str_replace("{send_to_friend}", $send_friend_link, $templateDesc);

// Ask question about this product
if (strstr($templateDesc, "{ask_question_about_product}"))
{
	$asklink           = JURI::root() . 'index.php?option=com_redshop&view=ask_question&pid=' . $this->data->product_id .
		'&tmpl=component&Itemid=' . $this->itemId;
	$ask_question_link = '<a class="redbox btn btn-primary" rel="{handler:\'iframe\',size:{x:500,y:500}}" href="' . $asklink . '" >' .
		JText::_('COM_REDSHOP_ASK_QUESTION_ABOUT_PRODUCT') .
		'</a>';
	$templateDesc     = str_replace("{ask_question_about_product}", $ask_question_link, $templateDesc);
}

// Product subscription type
if (strstr($templateDesc, "{subscription}") || strstr($templateDesc, "{product_subscription}"))
{
	if ($this->data->product_type == 'subscription')
	{
		$subscription      = RedshopHelperProduct::getSubscription($this->data->product_id);
		$subscription_data = "<table>";
		$subscription_data .= "<tr><th>" . JText::_('COM_REDSHOP_SUBSCRIPTION_PERIOD') . "</th><th>" . JText::_('COM_REDSHOP_SUBSCRIPTION_PRICE') . "</th>";
		$subscription_data .= "<th>" . JText::_('COM_REDSHOP_SUBSCRIBE') . "</th></tr>";

		for ($sub = 0; $sub < count($subscription); $sub++)
		{
			$subscription_data .= "<tr>";
			$subscription_data .= "<td>" . $subscription [$sub]->subscription_period . " " . $subscription [$sub]->period_type . "</td>";
			$subscription_data .= "<td>" . RedshopHelperProductPrice::formattedPrice($subscription [$sub]->subscription_price) . "</td>";
			$subscription_data .= "<td>";
			$subscription_data .= "<input type='hidden'
			 								id='hdn_subscribe_" . $subscription [$sub]->subscription_id . "'
			 								value='" . $subscription [$sub]->subscription_price . "' />";
			$subscription_data .= "<input type='radio'
			 								name='rdoSubscription'
			 								value='" . $subscription [$sub]->subscription_id . "'
			 								onClick=\"changeSubscriptionPrice(" . $subscription [$sub]->subscription_id . ",this.value, " . $this->data->product_id .
				")\" /></td>";
			$subscription_data .= "</tr>";
		}

		$subscription_data .= "</table>";
		$templateDesc     = str_replace("{subscription}", $subscription_data, $templateDesc);
		$templateDesc     = str_replace("{product_subscription}", $subscription_data, $templateDesc);
	}
	else
	{
		$templateDesc = str_replace("{subscription}", "", $templateDesc);
		$templateDesc = str_replace("{product_subscription}", "", $templateDesc);
	}
}

// Product subscription type ene here

// PRODUCT QUESTION START
if (strstr($templateDesc, "{question_loop_start}") && strstr($templateDesc, "{question_loop_end}"))
{
	$qstart         = $templateDesc;
	$qmiddle        = "";
	$qend           = "";
	$question_start = explode("{question_loop_start}", $templateDesc);

	if (count($question_start) > 0)
	{
		$qstart       = $question_start [0];
		$question_end = explode("{question_loop_end}", $question_start [1]);

		if (count($question_end) > 1)
		{
			$qmiddle = $question_end [0];
			$qend    = $question_end [1];
		}
	}

	$product_question = RedshopHelperProduct::getQuestionAnswer(0, $this->data->product_id, 0, 1);
	$questionloop     = "";

	if ($qmiddle != "")
	{
		for ($q = 0, $qn = count($product_question); $q < $qn; $q++)
		{
			$qloop = str_replace("{question}", $product_question [$q]->question, $qmiddle);
			$qloop = str_replace("{question_date}", $config->convertDateFormat($product_question [$q]->question_date), $qloop);
			$qloop = str_replace("{question_owner}", $product_question [$q]->user_name, $qloop);

			$astart       = $qloop;
			$amiddle      = "";
			$aend         = "";
			$answer_start = explode("{answer_loop_start}", $qloop);

			if (count($answer_start) > 0)
			{
				$astart     = $answer_start [0];
				$answer_end = explode("{answer_loop_end}", $answer_start [1]);

				if (count($answer_end) > 0)
				{
					$amiddle = $answer_end [0];
					$aend    = $answer_end [1];
				}
			}

			$product_answer = RedshopHelperProduct::getQuestionAnswer($product_question [$q]->id, 0, 1, 1);
			$answerloop     = "";

			for ($a = 0, $an = count($product_answer); $a < $an; $a++)
			{
				$aloop = str_replace("{answer}", $product_answer [$a]->question, $amiddle);
				$aloop = str_replace("{answer_date}", $config->convertDateFormat($product_answer [$a]->question_date), $aloop);
				$aloop = str_replace("{answer_owner}", $product_answer [$a]->user_name, $aloop);

				$answerloop .= $aloop;
			}

			$questionloop .= $astart . $answerloop . $aend;
		}
	}

	$templateDesc = $qstart . $questionloop . $qend;
}

// PRODUCT QUESTION END

$my_tags = '';

if (Redshop::getConfig()->get('MY_TAGS') != 0 && $user->id && strstr($templateDesc, "{my_tags_button}"))
{
	// Product Tags - New Feature Like Magento Store
	$my_tags .= "<div id='tags_main'><div id='tags_title'>" . JText::_('COM_REDSHOP_PRODUCT_TAGS') . "</div>";
	$my_tags .= "<div id='tags_lable'>" . JText::_('COM_REDSHOP_ADD_YOUR_TAGS') . "</div>";
	$my_tags .= "<div id='tags_form'><form method='post' action='' id='form_tags' name='form_tags'>
				<table id='tags_table'><tr>
						<td><span>" . JText::_('COM_REDSHOP_TAG_NAME') . "</span></td>
						<td><input type='text'	name='tags_name' id='tags_name' value='' size='52' /></td>
						<td><input type='submit' name='tags_submit' id='tags_submit' value='" . JText::_('COM_REDSHOP_ADD_TAGS') . "' /></td>
					</tr>
					<tr><td colspan='3'>" . JText::_('COM_REDSHOP_TIP_TAGS') . "</td></tr>
					<tr><td colspan='3'>
							<input type='hidden' name='tags_id' id='tags_id' value='0' />
							<input type='hidden' name='product_id' id='product_id' value='" . $this->data->product_id . "' />
							<input type='hidden' name='users_id' id='users_id' value='" . $user->id . "' />
							<input type='hidden' name='view' id='view' value='product' />
							<input type='hidden' name='task' id='task' value='addProductTags' />
							<input type='hidden' name='published' id='published' value='1' /></td></tr>
				</table></form>";
	$my_tags .= "</div>";
	$my_tags .= "</div>";

	// End Product Tags
}

$templateDesc = str_replace("{my_tags_button}", $my_tags, $templateDesc);

$templateDesc = str_replace("{with_vat}", "", $templateDesc);
$templateDesc = str_replace("{without_vat}", "", $templateDesc);

$templateDesc = str_replace("{attribute_price_with_vat}", "", $templateDesc);
$templateDesc = str_replace("{attribute_price_without_vat}", "", $templateDesc);

// Replace Minimum quantity per order
$minOrderProductQuantity = '';

if ((int) $this->data->min_order_product_quantity > 0)
{
	$minOrderProductQuantity = $this->data->min_order_product_quantity;
}

$templateDesc = str_replace(
	'{min_order_product_quantity}',
	$minOrderProductQuantity,
	$templateDesc
);

$templateDesc = $this->redTemplate->parseredSHOPplugin($templateDesc);

$templateDesc = $this->textHelper->replace_texts($templateDesc);

$templateDesc = RedshopHelperProduct::getRelatedTemplateView($templateDesc, $this->data->product_id);

// Replacing ask_question_about_product_without_lightbox must be after parseredSHOPplugin for not replace in cloak plugin form emails
if (strstr($templateDesc, '{ask_question_about_product_without_lightbox}'))
{
	$questionForm = RedshopTagsReplacer::_(
		'askquestion',
		'',
		array(
			'form' => RedshopModelForm::getInstance('Ask_Question', 'RedshopModel')->getForm(),
			'ask' => 1
		)
	);

	$templateDesc = str_replace('{ask_question_about_product_without_lightbox}', $questionForm, $templateDesc);
}

// Replacing form_rating_without_link must be after parseredSHOPplugin for not replace in cloak plugin form emails
if (strstr($templateDesc, '{form_rating_without_link}'))
{
	$form          = RedshopModelForm::getInstance(
		'Product_Rating',
		'RedshopModel',
		array(
			'context' => 'com_redshop.edit.product_rating.' . $this->data->product_id
		)
	)
		->getForm();
	$displayData   = array(
		'form'       => $form,
		'modal'      => 0,
		'product_id' => $this->data->product_id
	);
	$templateDesc = str_replace('{form_rating_without_link}', RedshopLayoutHelper::render('product.product_rating', $displayData), $templateDesc);
}

/**
 * Trigger event onAfterDisplayProduct will display content after product display.
 * Will we change only $templateDesc inside a plugin, that's why only $templateDesc should be
 * passed by reference.
 */
$this->dispatcher->trigger('onAfterDisplayProduct', array(&$templateDesc, $this->params, $this->data));

echo eval("?>" . $templateDesc . "<?php ");

?>

<script type="text/javascript">

    function setsendImagepath(elm) {
        var path = document.getElementById('<?php echo "main_image" . $this->pid;?>').src;
        var filenamepath = path.replace(/\\/g, '/').replace(/.*\//, '');
        var imageName = filenamepath.split('&');
        elm.href = elm + '&imageName=' + imageName[0];
    }

</script>
