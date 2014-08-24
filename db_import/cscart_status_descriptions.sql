UPDATE cscart_statuses SET `status` = 'D' WHERE status = 'D' AND type = 'O';
UPDATE cscart_status_descriptions SET `description` = 'Просим связаться', `status` = 'D', `email_subj` = 'был переведен в состояние \"Просим связаться\"', `email_header` = '<p>Ваш заказ был переведен в состояние \"Просим связаться\". Пожалуйста, свяжитесь с администрацией магазина.</p>' WHERE status = 'D' AND type = 'O' AND lang_code = 'RU';
UPDATE cscart_statuses SET `status` = 'C' WHERE status = 'C' AND type = 'O';
UPDATE cscart_status_descriptions SET `description` = 'Оплачено', `status` = 'C', `email_subj` = 'был выполнен', `email_header` = '<p>Ваш заказ был выполнен. Спасибо, что выбрали нас.</p>' WHERE status = 'C' AND type = 'O' AND lang_code = 'RU';
UPDATE cscart_statuses SET `status` = 'F' WHERE status = 'F' AND type = 'O';
UPDATE cscart_status_descriptions SET `description` = 'Заявка принята', `status` = 'F', `email_subj` = 'Успешно обработан.', `email_header` = '<p>Ваш заказ был успешно обработан.</p>' WHERE status = 'F' AND type = 'O' AND lang_code = 'RU';
UPDATE cscart_statuses SET `status` = 'P' WHERE status = 'P' AND type = 'O';
UPDATE cscart_status_descriptions SET `description` = 'В работе', `status` = 'P', `email_subj` = 'был обработан.', `email_header` = '<p>Ваш заказ был успешно обработан.</p>' WHERE status = 'P' AND type = 'O' AND lang_code = 'RU';

