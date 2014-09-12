REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'report_for_agent', 'Отчёт по сотруднику');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'report_for_agent', 'Report for agent');

REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'profit_source', 'Оформитель заказа');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'profit_source', 'Profit source');


REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'you', 'Вы');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'you', 'You');


REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'collegues', 'Сотрудники');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'collegues', 'Co-workers');


REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'agent_profit_from_subagent', 'Доход от продаж сотрудников');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'agent_profit_from_subagent', 'Profit from subagent');

REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'your_report', 'Отчёт о Ваших продажах');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'your_report', 'Your sales report');


REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'subagents_report', 'Отчёт по продажам сотрудников');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'subagents_report', 'Your subagents sales report');

REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'subagent', 'Сотрудник');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'subagent', 'Subagent');

REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'subagents', 'Сотрудники');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'subagents', 'Subagents');


REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'full_report', 'Полный отчёт');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'full_report', 'Full report');


REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'to_see_report', 'Смотреть отчёт');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'to_see_report', 'See report');


REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'to_see_collegues', 'Личные данные сотрудников');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'to_see_collegues', 'See personal info');


REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'show_report_for', 'Показать отчёт для');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'show_report_for', 'Show report for');


REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'all_subagents_nat', 'всех сотрудников');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'all_subagents_nat', 'all subagents');

REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('RU', 'agents_office', 'Мой офис');
REPLACE INTO `cscart_language_values` (`lang_code`, `name`, `value`) VALUES ('EN', 'agents_office', 'My office');


UPDATE `cscart_language_values` SET `value` = 'Пожалуйста, выберите услугу для указанной группы [group_name]' WHERE `cscart_language_values`.`lang_code` = 'RU' AND `cscart_language_values`.`name` = 'text_required_group_product';

UPDATE `cscart_language_values` SET `value` = 'Налоги на услугу' WHERE `cscart_language_values`.`lang_code` = 'RU' AND `cscart_language_values`.`name` = 'product_taxes';

UPDATE `cscart_language_values` SET `value` = 'Услуга была успешно удалена.' WHERE `cscart_language_values`.`lang_code` = 'RU' AND `cscart_language_values`.`name` = 'text_product_has_been_deleted';

UPDATE `cscart_language_values` SET `value` = 'Услуга(и) была(и) успешно удалена(ы)' WHERE `cscart_language_values`.`lang_code` = 'RU' AND `cscart_language_values`.`name` = 'text_products_have_been_deleted';

UPDATE `cscart_language_values` SET `value` = 'Не разрешать добавлять услуги в корзину' WHERE `cscart_language_values`.`lang_code` = 'RU' AND `cscart_language_values`.`name` = 'zpa_refuse';

UPDATE `cscart_language_values` SET `value` = 'Разрешить добавить услугу в корзину' WHERE `cscart_language_values`.`lang_code` = 'RU' AND `cscart_language_values`.`name` = 'zpa_permit';

UPDATE `cscart_language_values` SET `value` = 'Выбрать услугу' WHERE `cscart_language_values`.`lang_code` = 'RU' AND `cscart_language_values`.`name` = 'select_product';

UPDATE `cscart_language_values` SET `value` = 'Возвращаемая услуга' WHERE `cscart_language_values`.`lang_code` = 'RU' AND `cscart_language_values`.`name` = 'returnable_product';

UPDATE `cscart_language_values` SET `value` = 'Услуга(и)' WHERE `cscart_language_values`.`lang_code` = 'RU' AND `cscart_language_values`.`name` = 'product_s';

