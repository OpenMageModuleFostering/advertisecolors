<?php
/**
 * Update to version 0.1.1
 *
 * @package Advertise_Importer
 */
$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS advertise_jobs;
CREATE TABLE IF NOT EXISTS advertise_jobs (
  `id` int(11) unsigned NOT NULL auto_increment,
  `job` varchar(255) NOT NULL,
  `jobid` varchar(255) NOT NULL,
  `job_date` varchar(255) NOT NULL DEFAULT '',
  `created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();