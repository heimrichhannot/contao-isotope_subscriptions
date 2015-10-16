<?php

namespace Isotope\Module;
use Isotope\Model\Subscription;
use Isotope\Model\SubscriptionArchive;

/**
 * Class Activation
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package isotope_subscriptions
 * @author  Dennis Patzer <d.patzer@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */
class Activation extends Module
{

	protected $strTemplate = 'mod_iso_activation';
	protected $strFormId = 'iso_activation';
	protected $blnDoNotSubmit;

	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ACTIVATION ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}

	protected function compile()
	{
		if (!($strToken = \Input::get('token')))
			return;

		if (($objSubscription = Subscription::findByActivation($strToken)) !== null)
		{
			if (!$objSubscription->disable)
			{
				$objSubscription->activation = '';
				$objSubscription->save();
				$this->Template->warning = $GLOBALS['TL_LANG']['MSC']['iso_subscriptionAlreadyActivated'];
			}
			else
			{
				$this->Template->success = $GLOBALS['TL_LANG']['MSC']['iso_subscriptionActivatedSuccessfully'];
				$objSubscription->activation = $objSubscription->disable = '';
				$objSubscription->save();

				// redirect
				if ($this->jumpTo && ($objPageRedirect = \PageModel::findByPk($this->jumpTo)) !== null)
				{
					\Controller::redirect(\Controller::generateFrontendUrl($objPageRedirect->row()));
				}
			}

		}
		else
		{
			$this->Template->error = $GLOBALS['TL_LANG']['MSC']['iso_subscriptionTokenNotFound'];
		}
	}

}
