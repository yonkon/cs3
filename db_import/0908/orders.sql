ALTER TABLE `cscart_orders` ADD `order_paid_date` TIMESTAMP NULL ;

UPDATE `cscart_language_values` SET `value` = 'Ваш суммарный доход' WHERE name = 'agent_total_profit' AND lang_code = 'RU';

UPDATE `cscart_language_values` SET `value` = 'Ваш средний доход' WHERE name = 'agent_average_profit' AND lang_code = 'RU';
