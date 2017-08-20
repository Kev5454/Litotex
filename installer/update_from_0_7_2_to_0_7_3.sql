
INSERT INTO `cc1_pages` (`id`, `getName`, `title`, `content`, `access`, `isActive`, `makeTime`, `updateTime`) VALUES
(1, 'screen', 'Screenshorts', '&lt;a href=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/screenshot/main.png&quot; target=&quot;_blank&quot;&gt;&lt;/a&gt; \r\n&lt;table border=&quot;0&quot; width=&quot;100%&quot;&gt;\r\n&lt;tbody&gt;\r\n&lt;tr&gt;\r\n&lt;td&gt;Startseite&lt;/td&gt;\r\n&lt;td&gt;Baumen&amp;uuml;&lt;/td&gt;\r\n&lt;td&gt;Ausbildungscamp&lt;/td&gt;\r\n&lt;/tr&gt;\r\n&lt;tr&gt;\r\n&lt;td&gt;&lt;a href=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/main.png&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/main.png&quot; border=&quot;0&quot; alt=&quot;&quot; width=&quot;200&quot; height=&quot;160&quot; /&gt;&lt;/a&gt;&lt;/td&gt;\r\n&lt;td&gt;&lt;a href=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/bauen.png&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/bauen.png&quot; border=&quot;0&quot; alt=&quot;&quot; width=&quot;200&quot; height=&quot;160&quot; /&gt;&lt;/a&gt;&lt;/td&gt;\r\n&lt;td&gt;&lt;a href=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/ausbildung.png&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;{$LITO_GLOBAL_IMAGE_URL}standard/pagemanager/ausbildung.png&quot; border=&quot;0&quot; alt=&quot;&quot; width=&quot;200&quot; height=&quot;160&quot; /&gt;&lt;/a&gt;&lt;/td&gt;\r\n&lt;/tr&gt;\r\n&lt;/tbody&gt;\r\n&lt;/table&gt;', 'public', 1, 1502963916, 1502964458),
(2, 'impress', 'Impressum', '{$GAME_NAME}&lt;br /&gt;{$GAME_URL}&lt;br /&gt; {$GAME_AUTHOR}&lt;br /&gt;{$ADMIN_EMAIL}&lt;br /&gt;{$SUPPORT_EMAIL}&lt;br /&gt;\r\n&lt;p&gt;&lt;strong&gt;&lt;br /&gt;&lt;span style=&quot;\\&quot;&gt;Die Litotex OpenSource Browsergame wurde entwickelt vom Litotex Team und fortgesetzt wurde es vom litotex.info Team.&lt;/span&gt;&lt;br /&gt;&lt;br /&gt;&lt;/strong&gt;F&amp;uuml;r Fragen, Anregungen lohnt sich ein Besuch des &lt;a href=&quot;https://litotex.info/&quot;&gt;Litotex Forums&lt;/a&gt;.&lt;/p&gt;\r\n&lt;p&gt;Diese Software ist nicht als fertiges Browsergame gedacht, sondern vielmehr als Vorlage f&amp;uuml;r die eigenen Ideen und W&amp;uuml;nsche.&lt;br /&gt;&lt;br /&gt;&lt;strong&gt;Besonderen Dank gilt allen Mitwirkenden:&lt;/strong&gt;&lt;/p&gt;\r\n&lt;ul&gt;\r\n&lt;li&gt;&lt;strong&gt;gh1234&lt;/strong&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;strong&gt;KiraYamato&lt;/strong&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;strong&gt;ofliii&lt;/strong&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;strong&gt;SnooP&lt;/strong&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;strong&gt;Sonorc&lt;/strong&gt;&lt;/li&gt;\r\n&lt;li&gt;&lt;strong&gt;Tungdal&lt;/strong&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;p&gt;Ein gro&amp;szlig;er Dank geht auch an die Foren-Community, welche uns mit Ideen, Fehlerfindung und jede menge guter Laune immer wieder dazu Motivierte noch einen Schritt weiter zu gehen.&lt;strong&gt;&lt;br /&gt;&lt;br /&gt;Und nicht zu vergessen, die Freunde/in, Eltern, oder Familienangeh&amp;ouml;rigen, die viele Stunden auf uns verzichten mussten ;)&lt;br /&gt;&lt;/strong&gt;&lt;/p&gt;', 'public', 0, 1502964991, 1502965507);

DELETE FROM `cc1_menu_admin_opt` WHERE `varname` = 'op_impressum'