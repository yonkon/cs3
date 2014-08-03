CREATE TABLE IF NOT EXISTS `cscart_orders_saved` (
`id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
ALTER TABLE `cscart_orders_saved`
 ADD PRIMARY KEY (`id`);
 ALTER TABLE `cscart_orders_saved`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;