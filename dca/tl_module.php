<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Palettes
 */
$arrDca['palettes']['iso_direct_checkout'] = str_replace('iso_shipping_modules', 'iso_shipping_modules,iso_addSubscription', $arrDca['palettes']['iso_direct_checkout']);
$arrDca['palettes']['iso_activation'] = '{title_legend},name,headline,type;{redirect_legend],jumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;';
$arrDca['palettes']['iso_cancellation'] = '{title_legend},name,headline,type;{config_legend},iso_cancellationArchives,nc_notification;{redirect_legend],jumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;';

/**
 * Subpalettes
 */
$arrDca['palettes']['__selector__'][] = 'iso_addSubscription';
$arrDca['subpalettes']['iso_addSubscription'] = 'iso_subscriptionArchive,iso_addActivation';

/**
 * Callbacks
 */
$arrDca['config']['onload_callback'][] = array('tl_module_isotope_subscriptions', 'modifyPalette');

/**
 * Fields
 */
$arrDca['fields']['iso_addSubscription'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_addSubscription'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class' => 'w50 long', 'submitOnChange' => true),
	'sql'                     => "char(1) NOT NULL default ''"
);

$arrDca['fields']['iso_subscriptionArchive'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_subscriptionArchive'],
	'exclude'          => true,
	'inputType'        => 'select',
	'foreignKey'       => 'tl_iso_subscription_archive.title',
	'eval'             => array('tl_class' => 'w50', 'mandatory' => true),
	'sql'              => "int(10) unsigned NOT NULL default '0'",
);

$arrDca['fields']['iso_cancellationArchives'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_cancellationArchives'],
	'exclude'          => true,
	'inputType'        => 'select',
	'foreignKey'       => 'tl_iso_subscription_archive.title',
	'eval'             => array('tl_class' => 'w50', 'mandatory' => true, 'multiple' => true, 'chosen' => true),
	'sql'              => "blob NULL",
);

$arrDca['fields']['iso_addActivation'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_addActivation'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class' => 'w50 clr', 'submitOnChange' => true),
	'sql'                     => "char(1) NOT NULL default ''"
);

$arrDca['fields']['iso_activationJumpTo'] = $arrDca['fields']['jumpTo'];
$arrDca['fields']['iso_activationJumpTo']['label'] = &$GLOBALS['TL_LANG']['tl_module']['iso_activationJumpTo'];
$arrDca['fields']['iso_activationJumpTo']['eval']['tl_class'] = 'w50';

$arrDca['fields']['iso_activationNotification'] = $arrDca['fields']['nc_notification'];
$arrDca['fields']['iso_activationNotification']['label'] = &$GLOBALS['TL_LANG']['tl_module']['iso_activationNotification'];
$arrDca['fields']['iso_activationNotification']['eval']['mandatory'] = true;

$arrDca['fields']['iso_addSubscriptionCheckbox'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_addSubscriptionCheckbox'],
	'exclude'                 => true,
	'filter'                  => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class' => 'w50'),
	'sql'                     => "char(1) NOT NULL default ''"
);



class tl_module_isotope_subscriptions {

	public function modifyPalette()
	{
		$objModule = \ModuleModel::findByPk(\Input::get('id'));
		$arrDca = &$GLOBALS['TL_DCA']['tl_module'];

		switch ($objModule->type)
		{
			case 'iso_direct_checkout':
				if (in_array('isotope_plus', \ModuleLoader::getActive()))
				{
					$arrDca['subpalettes']['iso_addSubscription'] = str_replace('iso_subscriptionArchive',
						'iso_subscriptionArchive,iso_addSubscriptionCheckbox',
						$arrDca['subpalettes']['iso_addSubscription']
					);
				}
			case 'iso_checkout':
				if ($objModule->iso_addActivation)
				{
					$arrDca['subpalettes']['iso_addSubscription'] = str_replace('iso_addActivation',
						'iso_addActivation,iso_activationNotification,iso_activationJumpTo',
						$arrDca['subpalettes']['iso_addSubscription']
					);
				}
		}
	}

}