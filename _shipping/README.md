
Litotex Browsergame Engine (http://www.litotex.info)
	VERSION: 0.7.3
	
	ENTWICKELT ab 0.7.0: litotex.info (http://www.litotex.info)  
	COPYRIGHT 2017 litotex.info (http://www.litotex.info)
	
	ENTWICKELT bis 0.7.0: FreeBG Team (http://www.freebg.de) 
	COPYRIGHT 2008 FreeBG (http://www.freebg.de)	 	      

Hinweis:				                                    
	Diese Software ist urheberechtlich gesch�tzt.	      

	F�r jegliche Fehler oder Sch�den, 		              
	die durch diese Software auftreten k�nnten,         
	�bernimmt der Autor keine Haftung.		              
                                                   
Alle Copyright - Hinweise innerhalb dieser Datei   
d�rfen NICHT entfernt und NICHT ver�ndert werden.  

Released under the GNU General Public License


ALLGEMEIN
Mit der Installation von Litotex stimmen Sie insbesondere folgenden Punkten zu: 
*) den Urheberrechtshinweis im Footer nicht zu entfernen, durch andere technische M�glichkeiten auszublenden oder unsichtbar zu machen.
*) den Urheberrechtshinweis in allen Templates, in der Form der von Litotex ausgelieferten Layoutstrukturierung anzuzeigen.

F�r die Installation und f�r den Betrieb ist es notwendig das FTP �ber PHP funktioniert.
Wir empfehlen weiterhin die Installation in einen Unterordner des Webspaces.



VORBEREITUNG


1. Laden Sie alle Dateien auf den Webspace und erstellen Sie folgende Ordner
   setup_tmp
   Setzen Sie diesen Ordner mit CHMOD Rechten (nur Linux) auf 777 (0777)


2. Legen Sie eine neue Datenbank an.
	 Die Informationen wie Username etc. werden w�hrend des Setups ben�tigt.

3. Rufen Sie nun die URL Ihrer Webseite wie folgt auf: 
	 http://ihrewebseite.tld/setup.php und folgen Sie den Anweisungen.



NACH DER INSTALLATION


1. Nach der Beendigung der Installation l�schen Sie die Datei "setup.php" 

2. Sch�tzen Sie ihr ACP Verzeichnis mittels htaccess o.�. um einen unbefugten Zutritt nicht zu erm�glichen.

3. Das Design von Litotex befindet sich im Ordner "themes\standard".
	 Dieses kann ganz nach den pers�nlichen Anspr�chen ge�ndert werden.
		 
4. F�r die automatische Punkteberechnung ist es norwendig einen Cronjob anzulegen.
	 Der Cronjob muss die Datei http://ihrewebseite.tld/cronjobs.php?key=#DEIN_UPDATE_KEY#&sid=#SERVERID#&type=points aufrufen.	 
	 
	 
INFORMATIONEN

1. Im offiziellen Supportforum, erreichbar unter http://litotex.info, erhalten Sie nat�rlich auf Wunsch
Hilfe. Desweiteren k�nnen Sie uns gerne Verbesserungsideen vorschlagen oder bei Interesse am Projekt mitwirken.


Ihr Litotex.Info-Team,
https://litotex.info
