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


INSERT INTO `cc1_pages` (`id`, `getName`, `title`, `content`, `access`, `isActive`, `makeTime`, `updateTime`) VALUES
(1, 'screen', 'Screenshorts', '&lt;a href=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/main.png&quot; target=&quot;_blank&quot;&gt;&lt;/a&gt; \r\n&lt;table border=&quot;0&quot; width=&quot;100%&quot;&gt;\r\n&lt;tbody&gt;\r\n&lt;tr&gt;\r\n&lt;td&gt;Startseite&lt;/td&gt;\r\n&lt;td&gt;Baumen&amp;uuml;&lt;/td&gt;\r\n&lt;td&gt;Ausbildungscamp&lt;/td&gt;\r\n&lt;/tr&gt;\r\n&lt;tr&gt;\r\n&lt;td&gt;&lt;a href=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/main.png&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/main.png&quot; border=&quot;0&quot; alt=&quot;&quot; width=&quot;200&quot; height=&quot;160&quot; /&gt;&lt;/a&gt;&lt;/td&gt;\r\n&lt;td&gt;&lt;a href=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/bauen.png&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/bauen.png&quot; border=&quot;0&quot; alt=&quot;&quot; width=&quot;200&quot; height=&quot;160&quot; /&gt;&lt;/a&gt;&lt;/td&gt;\r\n&lt;td&gt;&lt;a href=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/ausbildung.png&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/ausbildung.png&quot; border=&quot;0&quot; alt=&quot;&quot; width=&quot;200&quot; height=&quot;160&quot; /&gt;&lt;/a&gt;&lt;/td&gt;\r\n&lt;/tr&gt;\r\n&lt;/tbody&gt;\r\n&lt;/table&gt;', 'public', 1, 1502963916, 1502964458),
(2, 'impress', 'Impressum', '{$GAME_NAME}&lt;br /&gt;{$GAME_URL}&lt;br /&gt; {$GAME_AUTHOR}&lt;br /&gt;{$ADMIN_EMAIL}&lt;br /&gt;{$SUPPORT_EMAIL}&lt;br /&gt;\r\n&lt;p&gt;&lt;strong&gt;&lt;br /&gt;&lt;span style=&quot;\\&quot;&gt;Die Litotex OpenSource Browsergame wurde entwickelt vom Litotex Team und fortgesetzt wurde es vom litotex.info Team.&lt;/span&gt;&lt;br /&gt;&lt;br /&gt;&lt;/strong&gt;F&amp;uuml;r Fragen, Anregungen lohnt sich ein Besuch des &lt;a href=&quot;https://litotex.info/&quot;&gt;Litotex Forums&lt;/a&gt;.&lt;/p&gt;\r\n&lt;p&gt;Diese Software ist nicht als fertiges Browsergame gedacht, sondern vielmehr als Vorlage f&amp;uuml;r die eigenen Ideen und W&amp;uuml;nsche.&lt;br /&gt;&lt;br /&gt;&lt;strong&gt;Besonderen Dank gilt allen Mitwirkenden:&lt;/strong&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n&lt;li&gt;&lt;strong&gt;gh1234&lt;/strong&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;strong&gt;KiraYamato&lt;/strong&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;strong&gt;ofliii&lt;/strong&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;strong&gt;SnooP&lt;/strong&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;strong&gt;Sonorc&lt;/strong&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;strong&gt;Tungdal&lt;/strong&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;Ein gro&amp;szlig;er Dank geht auch an die Foren-Community, welche uns mit Ideen, Fehlerfindung und jede menge guter Laune immer wieder dazu Motivierte noch einen Schritt weiter zu gehen.&lt;strong&gt;&lt;br /&gt;&lt;br /&gt;Und nicht zu vergessen, die Freunde/in, Eltern, oder Familienangeh&amp;ouml;rigen, die viele Stunden auf uns verzichten mussten ;)&lt;br /&gt;&lt;/strong&gt;&lt;/p&gt;', 'public', 0, 1502964991, 1502965507);

DELETE FROM `cc1_menu_admin_opt` WHERE `varname` = 'op_impressum';

UPDATE `cc1_menu_game` SET `menu_game_link` = '[LITO_BASE_MODUL_URL]pagemanager/page.php?name=screen' WHERE `menu_game_name` = 'Screenshots'; 