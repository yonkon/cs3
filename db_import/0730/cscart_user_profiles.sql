ALTER TABLE `cscart_user_profiles` ADD `registragion_date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ;
ALTER TABLE `cscart_user_profiles` ADD `comment` TEXT NULL ;
ALTER TABLE `cscart_user_profiles` ADD `b_email` VARCHAR(128) NULL AFTER `b_phone`;
ALTER TABLE `cscart_user_profiles` ADD `s_email` VARCHAR(128) NULL AFTER `s_phone`;