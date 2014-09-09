
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'working_mode', 'Work time');
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'working_mode', 'Режим работы');

INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'company_home_master', 'Master home coming');
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'company_home_master_description', 'Master home coming mode');
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'company_long_description', 'Full company description');
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'company_home_master', 'Выезд специалиста на дом');
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'company_home_master_description', 'Режим выезда специалиста');
INSERT INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'company_long_description', 'Полное описание компании');

ALTER TABLE `cscart_company_offices` ADD `fax` VARCHAR(32) NULL , ADD `working_mode` VARCHAR(64) NULL ;

ALTER TABLE `cscart_companies` ADD `company_long_description` TEXT NOT NULL , ADD `company_home_master_description` TEXT NULL ;

ALTER TABLE `cscart_companies` ADD `company_home_master` TINYINT(1) NOT NULL ;