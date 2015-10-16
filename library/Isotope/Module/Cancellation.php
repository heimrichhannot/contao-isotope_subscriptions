<?php

namespace Isotope\Module;
use Isotope\Model\Subscription;
use Isotope\Model\SubscriptionArchive;

/**
 * Class Cancellation
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package isotope_subscriptions
 * @author  Dennis Patzer <d.patzer@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */
class Cancellation extends Module
{

	protected $strTemplate = 'mod_iso_cancellation';
	protected $strFormId = 'iso_cancellation';
	protected $blnDoNotSubmit;

	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: CANCELLATION ###';

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
		$this->Template->formId = $this->strFormId;
		$arrFieldDcas = array(
			'email' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_module']['email'],
				'inputType' => 'text',
				'eval'  => array('rgxp' => 'email', 'mandatory' => true)
			),
			// TODO frontend editing of archives to cancel
//			'archives' =>array(
//				'label' => &$GLOBALS['TL_LANG']['tl_module']['archives'],
//				'inputType' => 'checkbox',
//				'eval'  => array('multiple' => true)
//			),
			'submit' => array(
				'inputType' => 'submit',
				'label'     => &$GLOBALS['TL_LANG']['MSC']['cancel']
			)
		);

		$arrWidgets = array();
		foreach ($arrFieldDcas as $strName => $arrData)
		{
			if ($strClass = $GLOBALS['TL_FFL'][$arrData['inputType']])
			{
				$arrWidgets[] = new $strClass(\Widget::getAttributesFromDca($arrData, $strName));
			}
		}

		if (\Input::post('FORM_SUBMIT') == $this->strFormId)
		{
			// validate
			foreach ($arrWidgets as $objWidget)
			{
				$objWidget->validate();

				if ($objWidget->hasErrors())
				{
					$this->blnDoNotSubmit = true;
				}
			}

			if (!$this->blnDoNotSubmit)
			{
				// cancel subscription
				$strEmail = \Input::post('email');
				$arrArchives = deserialize($this->iso_cancellationArchives, true);
				$blnNoSuccess = false;

				foreach ($arrArchives as $intArchive)
				{
					if (($objSubscription = Subscription::findBy(
						array('email=?', 'pid=?'), array($strEmail, $intArchive))) === null)
					{
						if (count($arrArchives) == 1)
						{
							$this->Template->error = sprintf($GLOBALS['TL_LANG']['MSC']['iso_subscriptionDoesNotExist'],
								$strEmail, SubscriptionArchive::findByPk($intArchive)->title);
							$blnNoSuccess = true;
						}

						break;
					}

					$objSubscription->delete();
				}

				if (!$blnNoSuccess)
				{
					// success message
					if (count($arrArchives) > 1)
					{
						$this->Template->success = $GLOBALS['TL_LANG']['MSC']['iso_subscriptionsCancelledSuccessfully'];
					}
					else
					{
						$this->Template->success = sprintf($GLOBALS['TL_LANG']['MSC']['iso_subscriptionCancelledSuccessfully'],
							$strEmail, SubscriptionArchive::findByPk($arrArchives[0])->title);
					}

					// redirect
					if ($this->jumpTo && ($objPageRedirect = \PageModel::findByPk($this->jumpTo)) !== null)
					{
						\Controller::redirect(\Controller::generateFrontendUrl($objPageRedirect->row()));
					}
				}
			}
		}

		// parse (validated) widgets
		$this->Template->fields = implode('', array_map(function($objWidget) {
			return $objWidget->parse();
		}, $arrWidgets));
	}

}
