CREATE TABLE `cc1_pages` (
  `id` int(12) UNSIGNED NOT NULL,
  `getName` varchar(128) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `access` enum('public','member','support','admin') NOT NULL,
  `isActive` tinyint(2) UNSIGNED NOT NULL,
  `makeTime` int(12) UNSIGNED NOT NULL,
  `updateTime` int(12) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `cc1_pages`
  ADD PRIMARY KEY (`id`);
  
ALTER TABLE `cc1_pages`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;

UPDATE `cc1_menu_admin` SET `menu_order` = '3' WHERE `adm_menu_id` = 2; 
UPDATE `cc1_menu_admin` SET `menu_order` = '4' WHERE `adm_menu_id` = 3; 
UPDATE `cc1_menu_admin` SET `menu_order` = '5' WHERE `adm_menu_id` = 4; 
UPDATE `cc1_menu_admin` SET `menu_order` = '6' WHERE `adm_menu_id` = 5; 
UPDATE `cc1_menu_admin` SET `menu_order` = '8' WHERE `adm_menu_id` = 6; 
UPDATE `cc1_menu_admin` SET `menu_order` = '7' WHERE `adm_menu_id` = 7; 
INSERT INTO `cc1_menu_admin` (`adm_menu_id`, `menu_name`, `menu_icon`, `menu_order`) VALUES (NULL, 'Seiten Manager', '[IMG_PATH]icons/news.png', '2'); 

INSERT INTO `cc1_menu_admin_sub` (`admin_sub_id`, `menu_admin_id`, `admin_sub_name`, `admin_sub_link`, `sub_name_sort`, `modul_admin_id`) VALUES (NULL, '8', 'Seiten', '[LITO_BASE_MODUL_URL]acp_pagemanager/page.php', '0', '47'); 
INSERT INTO `cc1_menu_admin_sub` (`admin_sub_id`, `menu_admin_id`, `admin_sub_name`, `admin_sub_link`, `sub_name_sort`, `modul_admin_id`) VALUES (NULL, '8', 'Seite erstellen', '[LITO_BASE_MODUL_URL]acp_pagemanager/page.php?action=new', '2', '47'); 

INSERT INTO `cc1_modul_admin` (`modul_admin_id`, `modul_name`, `modul_description`, `disable_allowed`, `activated`, `startfile`, `current_version`, `acp_modul`, `show_error_msg`, `modul_type`, `new_upd_available`, `perm_lvl`) VALUES (NULL, 'acp_pagemanager', 'ACP Seiten Manager', '1', '1', 'page.php', '0.7.1', '1', '1', '0', '0', '1000');
INSERT INTO `cc1_modul_admin` (`modul_admin_id`, `modul_name`, `modul_description`, `disable_allowed`, `activated`, `startfile`, `current_version`, `acp_modul`, `show_error_msg`, `modul_type`, `new_upd_available`, `perm_lvl`) VALUES (NULL, 'pagemanager', 'CMS-System', '1', '1', 'page.php', '0.7.1', '0', '1', '0', '0', '0'); 