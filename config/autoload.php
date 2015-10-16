<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Isotope',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Library
	'Isotope\IsotopeSubscriptions'      => 'system/modules/isotope_subscriptions/library/Isotope/IsotopeSubscriptions.php',
	'Isotope\Module\Activation'         => 'system/modules/isotope_subscriptions/library/Isotope/Module/Activation.php',
	'Isotope\Module\Cancellation'       => 'system/modules/isotope_subscriptions/library/Isotope/Module/Cancellation.php',
	'Isotope\Model\SubscriptionArchive' => 'system/modules/isotope_subscriptions/library/Isotope/Model/SubscriptionArchive.php',
	'Isotope\Model\Subscription'        => 'system/modules/isotope_subscriptions/library/Isotope/Model/Subscription.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_iso_activation'   => 'system/modules/isotope_subscriptions/templates',
	'mod_iso_cancellation' => 'system/modules/isotope_subscriptions/templates',
));
