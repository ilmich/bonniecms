<h3>Installazione</h3>
<p>Per installare BonnieCMS e' sufficiente estrarre l'archivio della distribuzione in una cartella qualsiasi del vostro webserver.</p>
<p>Una volta estratto l'archivio bisogna editare il file
	<code>data/config.php</code>
presente nella cartella principale e adattarlo alle proprie esigenze.
</p>
<p>
Ecco un esempio di configurazione realistica:
<code>&lt;?php

	$site['WEB_ROOT'] = "http://localhost/phpapps/bonniecms";
	$site['SITE_NAME'] = "BonnieCMS SampleSite";
	$site['SITE_SLOGAN'] = "...keep it simple...";
	$site['DEFAULT_PAGE'] = "home";
	$site['TEMPLATE'] = "winterplain";	
	$site['LANG']="en";
	
	return $site;
	
</code>
</p>
<p>
Una volta installato dovreste esser in grado di vedere la pagina di esempio del cms.
</p>
<h3>Creazione di una pagina di esempio</h3>
<p>
Le pagine in BonnieCMS sono semplici file html posizionati nella cartella specifica:
	<code>data/pages/</code>
Aggiungere una nuova pagina consiste in due passi:
<ul>
	<li>Creare la pagina con i suoi metadati</li>
	<li>Creare una voce di menu</li>
</ul>
<p>Per il primo passo andiamo a creare i seguenti file:
	<code>data/pages/welcome.php
data/pages/welcome.metadata.php</code>
Il primo file contiene la pagina html vera e propria, mentre il secondo definisce delle configurazioni specifiche per la pagina che stiamo creando.
</p>
<p>Editiamo quindi i precenti file inserendo rispettivamente il seguente contenuto:<br>

<code>&lt;p&gt;Hello world&lt;/p&gt;</code>
<code>&lt;?php
$page['title'] = "HelloWorld";</code>
</p>
<p>Dopo aver eseguito questo task, e' gia' possibile fare un piccolo test facendo puntare il browser
al seguente indirizzo:
<code>http://www.yourhost.com/index.php?page=welcome</code>
</p>
<p>Ora che la nostra pagina di prova e' funzionante, e' necessario definire un link che punti ad essa.
Al momento i menu in BonnieCMS sono configurati all'interno del file:
<code>data/menus.php</code>
</p>
<p>Editate il file presente nella distribuzione e aggiungete la riga evidenziata in grassetto:
<code>&lt;?php

	$menu_list = array(
		"top_menu" => array(
			"home" => array(makeLink("home"),"Home"),	
			"getstarted" => array(makeLink("getstarted"),"Get Started")
			<b>,"welcome" => array(makeLink("welcome"),"Welcome")</b>
			)
	);</code>
</p>
<p>Avete appena creato la vostra prima pagina funzionante!!<br>
</p>
<h3>Conclusioni</h3>
BonnieCMS al momento non e' adatto a chi non ha un minimo di dimestichezza con l'html e basi del linguaggio php.
Questo in futuro e' destinato a cambiare in quanto e' previsto lo sviluppo di un'interfaccia di amministrazione che rendera' tutto
piu' semplice da gestire anche a chi e' completamente digiuno di una qualunque competenza tecnica.