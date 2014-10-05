REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','customer_text_letter_footer', 'Спасибо за сотрудничество с нашим порталом.');

REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','became', 'на');
REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('EN','became', 'became');

REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','was', 'с');
REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('EN','was', 'was');

REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('EN','product_offices', 'Product offices');
REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','product_offices', 'Офисы, предоставляющие услугу');


REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('EN','office_name', 'Office name');
REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','office_name', 'Название офиса');


REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('EN','office_address', 'Office address');
REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','office_address', 'Адрес');

REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('EN','order_file', 'Attachment file');
REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','order_file', 'Прикреплённый файл');


REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('EN','new_order_mail_body', '<p>New order #[order_id] has been created.</p>');
REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','new_order_mail_body', '<p>Оформлен новый заказ #[order_id]</p>');

ALTER TABLE `cscart_company_offices` CHANGE `working_mode` `working_mode` VARCHAR(664) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;