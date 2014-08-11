ALTER TABLE `Countries_lang` CHANGE `lang_id` `lang_id` VARCHAR(3) NOT NULL;
UPDATE `Countries_lang` SET `lang_id`='RU' WHERE `lang_id`=1;
UPDATE `Countries_lang` SET `lang_id`='EN' WHERE `lang_id`=2;