ALTER TABLE `cscart_company_offices` ADD `description` TEXT NOT NULL ;
ALTER TABLE `cscart_company_offices` CHANGE `office_name` `office_name` VARCHAR(128) NOT NULL;
ALTER TABLE `cscart_company_offices` CHANGE `address` `address` VARCHAR(256) NOT NULL;