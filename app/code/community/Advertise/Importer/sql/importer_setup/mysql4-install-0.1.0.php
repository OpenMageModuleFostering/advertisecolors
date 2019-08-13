<?php
/**
 * Setup db
 *
 * @package Advertise_Importer
 */
$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS advertise_import;
CREATE TABLE IF NOT EXISTS advertise_import (
  `id` int(11) unsigned NOT NULL auto_increment,
  `product_id` int(11) unsigned NOT NULL,
  `job` varchar(255) NOT NULL,
  `job_date` varchar(255) NOT NULL,
  `data` text NOT NULL default '',
  `attributes` text NOT NULL default '',
  `created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
	 