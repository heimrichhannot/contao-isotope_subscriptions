<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_user'];

/**
 * Extend default palette
 */
$arrDca['palettes']['extend'] = str_replace('iso_configs', 'subscriptions,subscriptionp,iso_configs', $arrDca['palettes']['extend']);
$arrDca['palettes']['custom'] = str_replace('iso_configs', 'subscriptions,subscriptionp,iso_configs', $arrDca['palettes']['custom']);


/**
 * Add fields to tl_user_group
 */
$arrDca['fields']['subscriptions'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['subscriptions'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'foreignKey'              => 'tl_subscription_archive.title',
	'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 w50h'),
	'sql'                     => "blob NULL"
);

$arrDca['fields']['subscriptionp'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['subscriptionp'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options'                 => array('create', 'delete'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 w50h'),
	'sql'                     => "blob NULL"
);