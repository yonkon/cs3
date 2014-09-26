REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','orders_paid_count', 'Кол-во оплаченных заказов');

REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','choose_report_type', 'Выберите тип отчёта');
REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('EN','choose_report_type', 'Choose report type');

REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','admin_report_agent_total_profit', 'Общая прибыль продавца');
REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('EN','admin_report_agent_total_profit', 'Agent total profit');

REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','admin_site_profit', 'Доход по договору');
REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('EN','admin_site_profit', 'Profit by contract');

REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('RU','admin_pure_site_profit', 'Прибыль сервиса');
REPLACE INTO `cscart_language_values`(`lang_code`, `name`, `value`) VALUES ('EN','admin_pure_site_profit', 'Site pure profit');

UPDATE `cscart_language_values` SET `value` = 'Доход компании' WHERE `cscart_language_values`.`lang_code` = 'RU' AND `cscart_language_values`.`name` = 'total_profit';
UPDATE `cscart_language_values` SET `value` = 'Итого к оплате партнёру' WHERE `cscart_language_values`.`lang_code` = 'RU' AND `cscart_language_values`.`name` = 'site_profit';


REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES
('RU', 'agent_profit', 'Доход агента');