<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package isotope_plus
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace Isotope;


use HeimrichHannot\HastePlus\Arrays;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\Subscription;
use Isotope\Model\SubscriptionArchive;
use NotificationCenter\Model\Notification;

class IsotopeSubscriptions
{
	public static function setCheckoutModuleIdSubscriptions($objOrder, $objModule)
	{
		\Session::getInstance()->set('isotopeCheckoutModuleIdSubscriptions', $objModule->id);
	}

	public static function checkForExistingSubscription(Order $objOrder, $objModule)
	{
		$strEmail = $objOrder->getBillingAddress()->email;

		if ((!$objModule->iso_addSubscriptionCheckbox || \Input::post('subscribeToProduct')) && $objModule->iso_subscriptionArchive &&
			($objSubscriptionArchive = SubscriptionArchive::findByPk($objModule->iso_subscriptionArchive)) !== null) {
			if (Subscription::findBy(array('email=?', 'pid=?', 'disable!=?'), array($strEmail, $objSubscriptionArchive->id, 1))
				!== null
			) {
				$_SESSION['ISO_ERROR'][] = sprintf(
					$GLOBALS['TL_LANG']['MSC']['iso_subscriptionAlreadyExists'],
					$strEmail, $objSubscriptionArchive->title
				);
				return false;
			}
		}

		return true;
	}

	public static function addSubscriptions(Order $objOrder, $arrTokens)
	{
		$strEmail = $objOrder->getBillingAddress()->email;
		$objAddress = $objOrder->getShippingAddress() ?: $objOrder->getBillingAddress();

		$objSession = \Session::getInstance();

		if (!($intModule = $objSession->get('isotopeCheckoutModuleIdSubscriptions')))
			return true;

		$objSession->remove('isotopeCheckoutModuleIdSubscriptions');

		if (($objModule = \ModuleModel::findByPk($intModule)) !== null && $objModule->iso_addSubscription)
		{
			if ($objModule->iso_subscriptionArchive && (!$objModule->iso_addSubscriptionCheckbox || \Input::post('subscribeToProduct')))
			{
				$objSubscription = Subscription::findOneBy(array('email=?', 'pid=?', 'activation!=?', 'disable=?'),
					array($strEmail, $objModule->iso_subscriptionArchive, '', 1));

				if (!$objSubscription)
					$objSubscription = new Subscription();

				if ($objModule->iso_addActivation)
				{
					$strToken = md5(uniqid(mt_rand(), true));

					$objSubscription->disable = true;
					$objSubscription->activation = $strToken;

					if (($objNotification = Notification::findByPk($objModule->iso_activationNotification)) !== null)
					{
						if ($objModule->iso_activationJumpTo &&
							($objPageRedirect = \PageModel::findByPk($objModule->iso_activationJumpTo)) !== null)
						{
							$arrTokens['link'] = \Environment::get('url') . '/' .
								\Controller::generateFrontendUrl($objPageRedirect->row()) . '?token=' . $strToken;
						}

						$objNotification->send($arrTokens, $GLOBALS['TL_LANGUAGE']);
					}
				}

				$arrAddressFields = \Config::get('iso_addressFields');

				if ($arrAddressFields === null)
					$arrAddressFields = serialize(array_keys(static::getIsotopeAddressFields()));

				foreach (deserialize($arrAddressFields, true) as $strName)
				{
					$objSubscription->{$strName} = $objAddress->{$strName};
				}

				$objSubscription->email = $strEmail;
				$objSubscription->pid = $objModule->iso_subscriptionArchive;
				$objSubscription->tstamp = $objSubscription->dateAdded = time();
				$objSubscription->quantity = \Input::post('quantity');
				$objSubscription->order_id = $objOrder->id;
				$objSubscription->save();
			}
		}

		return true;
	}

	public static function getIsotopeAddressFields()
	{
		\Controller::loadDataContainer('tl_iso_address');
		\System::loadLanguageFile('tl_iso_address');
		$arrOptions = array();
		$arrSkipFields = array('id', 'pid', 'tstamp', 'ptable', 'label', 'store_id', 'isDefaultBilling', 'isDefaultShipping');

		foreach ($GLOBALS['TL_DCA']['tl_iso_address']['fields'] as $strName => $arrData) {
			if (!in_array($strName, $arrSkipFields))
				$arrOptions[$strName] = $GLOBALS['TL_LANG']['tl_iso_address'][$strName][0] ?: $strName;
		}

		return $arrOptions;
	}

	public static function importIsotopeAddressFields()
	{
		$arrDca = &$GLOBALS['TL_DCA']['tl_iso_subscription'];

		\Controller::loadDataContainer('tl_iso_address');
		\System::loadLanguageFile('tl_iso_address');

		// fields
		$blnChangeMandatoryAddressFields = \Config::get('iso_changeMandatoryAddressFields');
		$arrMandatoryAddressFields = deserialize(\Config::get('iso_mandatoryAddressFields'), true);
		$arrAddressFields = \Config::get('iso_addressFields');

		if ($arrAddressFields === null)
			$arrAddressFields = serialize(array_keys(static::getIsotopeAddressFields()));

		$arrFields = array();
		foreach (deserialize($arrAddressFields, true) as $strName)
		{
			$arrFields[$strName] = $GLOBALS['TL_DCA']['tl_iso_address']['fields'][$strName];

			if ($strName == 'gender')
				$arrFields[$strName]['reference'] = &$GLOBALS['TL_LANG']['tl_iso_address']['gender'];

			if ($strName == 'email')
				$arrFields[$strName]['eval']['unique'] = true;

			if ($blnChangeMandatoryAddressFields && is_array($arrMandatoryAddressFields))
				$arrFields[$strName]['eval']['mandatory'] = in_array($strName, $arrMandatoryAddressFields);
		}

		Arrays::insertInArrayByName($arrDca['fields'], 'tstamp', $arrFields, 1);

		// palette
		$strInitialPalette = $arrDca['palettes']['default'];
		$strFeGroup = $arrDca['palettes']['default'] = '';
		$i = 0;

		foreach ($arrFields as $strName => $arrData) {
			if (!$strFeGroup || $strFeGroup != $GLOBALS['TL_DCA']['tl_iso_address']['fields'][$strName]['eval']['feGroup'])
			{
				$strFeGroup = $GLOBALS['TL_DCA']['tl_iso_address']['fields'][$strName]['eval']['feGroup'];
				$arrDca['palettes']['default'] = rtrim($arrDca['palettes']['default'], ',');
				$arrDca['palettes']['default'] .= ($i == 0 ? '' : ';') . '{' . $strFeGroup . '_legend},';
			}

			$arrDca['palettes']['default'] .= $strName . ',';

			$i++;
		}

		$arrDca['palettes']['default'] = rtrim($arrDca['palettes']['default'], ',');
		$arrDca['palettes']['default'] .= ';' . $strInitialPalette;
	}

}