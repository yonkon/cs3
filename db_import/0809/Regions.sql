ALTER TABLE `Cities_lang` CHANGE `lang_id` `lang_id` VARCHAR(3) NOT NULL;
ALTER TABLE `Regions_lang` CHANGE `lang_id` `lang_id` VARCHAR(3) NOT NULL;
UPDATE `Regions_lang` SET `lang_id`='RU' WHERE `lang_id`=1;
UPDATE `Cities_lang` SET `lang_id`='RU' WHERE `lang_id`=1;
UPDATE `Regions_lang` SET `lang_id`='EN' WHERE `lang_id`=2;
UPDATE `Cities_lang` SET `lang_id`='EN' WHERE `lang_id`=2;
