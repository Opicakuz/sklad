* ═══════════════════════════════════════════════════════════════════════
 *   Sklad · přehled věcí, výpůjček a objednávek se správou        1.7.0
 * ═══════════════════════════════════════════════════════════════════════
 *
 *   Jeden soubor: co máte, kde to leží, čí to je, kdo si to půjčil, do kdy to má vrátit
 *   a kdo si co kdy vyzvedne. Hledání, filtry, přehled podle místa, výpůjčky a objednávky
 *   s výdejkou k tisku, import z tabulky, správa s heslem a oznámení na Discord.
 *   Sklad začíná prázdný: věci přidáš ve správě, nebo je nahraješ z tabulky.
 *
 *   JAK NA TO
 *     1. Nahraj tenhle soubor na hosting s PHP 7.4 nebo novějším, třeba jako sklad.php
 *        (nebo do vlastní složky jako index.php). Databázi ani nic dalšího nepotřebuje.
 *     2. Otevři adresu v prohlížeči. Poprvé se zeptá na heslo správce a nabídne
 *        heslo pro nahlížení (bez něj přehled uvidí každý, kdo zná adresu).
 *     3. Hotovo. Přehled je na adrese souboru, správa na  …?admin
 *     4. Ve správě přidej věci (tlačítko Přidat), nebo je nahraj z tabulky (Nastavení → Import
 *        z tabulky, vzor souboru je přímo tam). Každý sklad patří do vlastní složky: data leží
 *        vedle souboru a dva sklady v jedné složce by si je sdílely.
 *
 *   KDE JSOU DATA
 *     Vedle souboru vznikne složka  sklad-data/  (položky, fotky, denní zálohy).
 *     Databáze není potřeba. Záloha = zkopírovat tuhle složku, nebo ve správě
 *     Nastavení → Zálohy → Stáhnout zálohu (zálohy stahuje, nahrává a obnovuje jen hlavní správce).
 *
 *   ZAPOMENUTÉ HESLO
 *     Nahraj přes FTP vedle tohoto souboru prázdný soubor  reset-hesla.txt
 *     a otevři  …?admin . Zeptá se na nové heslo správce, data zůstanou.
 *     Soubor se pak sám smaže; když to hosting nedovolí, smaž ho ručně
 *     (použitý už nic nedělá).


*   POČTY A JEDNOTKY
 *     Každá věc má počet: kolik toho máte (i s tím, co je půjčené), v kusech, gramech nebo
 *     litrech. Půjčit ani objednat nejde víc, než je v tu dobu volné. Výpůjčka drží věc do dne
 *     vrácení (bez termínu, nebo když termín minul, dokud ji člověk nevrátí), objednávka od vyzvednutí.
 *     Při vracení se píše, kolik se vrátilo: co chybí, buď ubude z počtu (snědlo se, ztratilo),
 *     nebo si to člověk ještě nechá. Vrátit jde víc výpůjček najednou.
 *
 *   CENY
 *     U věci jde zapsat výpůjční cenu (vidí ji každý, třeba „50 Kč za den“) a pořizovací cenu
 *     (vidí jen správa).
 *
 *   TYP POLOŽKY (INVENTARIZACE)
 *     Každá věc může mít ještě jeden inventarizační údaj: typ položky (spotřební materiál, nemovitý
 *     majetek, dlouhodobý majetek hmotný a nehmotný, drobný majetek do 40 000 Kč). Vidí ho jen správa;
 *     podle typu jde filtrovat a je ve sloupci „typ položky“ v tabulce ke stažení (stáhne se to, co je
 *     zrovna vyfiltrované). Typy přidává, přejmenovává, řadí a maže jen hlavní správce (Nastavení →
 *     Typy položek); u věci ho vybere každý správce i uživatel. Po přepsání souboru verzí 1.7 se
 *     založí šest výchozích typů. Typ jde nahrát i z tabulky (sloupec „typ položky“, název musí sedět).
 *
 *   TAGY, POPIS, FOTKY, HLEDÁNÍ
 *     Každá věc může mít tagy (krátká slova, třeba „tábor“), popis a fotky. Hledání prochází jen
 *     názvy, tagy a popisy věcí; místa a kategorie se vybírají ve Filtrech (tam jsou i tagy).
 *     Fotku jde přidat rovnou u věci (Přidat fotku, v telefonu i vyfotit). Nahraná fotka má
 *     v seznamu náhled, klepnutím se zvětší; přepínač Fotky nad seznamem náhledy schová.
 *     Fotky z odkazů (třeba na Disk Google) náhled v seznamu nemají, otevřou se odkazem u věci.
 *
 *   UŽIVATELÉ
 *     Hlavní správce (ten s heslem správce) může v Nastavení → Uživatelé založit přístup dalším
 *     lidem. Ti se do správy přihlašují jménem a heslem a smějí všechno kromě Discordu, hesel,
 *     uživatelů, názvu skladu, písma, typů položek a záloh (stáhnout, nahrát ani obnovit). Zapomenuté heslo
 *     uživatele nastaví znovu hlavní správce. V historii je, kdo co změnil.
 *
 *   OBJEDNÁVKY
 *     U výpůjčky jde zadat, kdy si to člověk vyzvedne (třeba příští středu v 17:00). Do té doby
 *     je to objednávka, kterou vidí všichni; v den a hodinu vyzvednutí se sama změní na výpůjčku.
 *     Když v tu chvíli ještě někdo něco nevrátil, objednávka na to počká.
 *     Víc věcí najednou: tlačítko Vybrat víc. U každé objednávky je výdejka k tisku.
 *
 *   IMPORT Z TABULKY
 *     Nastavení → Import z tabulky: CSV z Excelu nebo Google Tabulek, nebo vložené buňky.
 *     Sloupce odděluj středníkem (tak ukládá CSV český Excel); tabulátor a čárka fungují taky.
 *     Vzor souboru i s pravidly je přímo v okně importu a jde si ho stáhnout.
 *     Tabulku, kterou sklad dává ke stažení, jde upravit a nahrát zpátky (věci pozná podle kódu).
 *     Při úpravě podle kódu stačí sloupec „kód“ a to, co se mění; název a list věci zůstanou.
 *     Sloupec „tagy“ tagy věci nahradí, sloupec „přidat tagy“ je přidá k těm, které věc má.
 *
 *   JINÝ NÁZEV, HEZKÁ ADRESA
 *     Soubor se může jmenovat jakkoli a v .htaccess dostat hezkou adresu (/sklad).
 *     Všechno se drží adresy, kterou má prohlížeč: odkazy, formuláře i fotky
 *     vedou na „?něco“ téže adresy, nikdy na umístění souboru na serveru.
 *
 *   DISCORD
 *     Ve správě v Nastavení → Discord vlož adresu webhooku. Sklad pak do kanálu napíše, když si někdo
 *     něco půjčí nebo přibude nová věc, a připomíná termíny vrácení. Aby zpráva člověka označila,
 *     potřebuje Discord jeho číselné ID. To jde zapsat ručně, nebo si ho sklad najde sám podle
 *     jména (třeba @Opičák): stačí tamtéž vložit token bota, který je na vašem serveru.
 *     Jak bota založit, je popsané přímo v Nastavení. Token je tajný jako heslo.
 *     Připomínky odejdou, když sklad ve dne někdo otevře. Aby chodily i bez toho, nech hosting
 *     jednou za hodinu volat adresu  …?cron  (nic neprozradí a nic nezmění, jen pošle, co má odejít).
 *
 *   PÍSMA
 *     Písma Archivo a Zilla Slab jsou vložená přímo v souboru (jen znaky pro češtinu a pár
 *     dalších jazyků). Obě jsou pod licencí SIL Open Font License 1.1, smějí se volně šířit
 *     i vkládat; plné znění licence a autoři jsou v metadatech každého písma.
 *       Archivo: Copyright 2020 The Archivo Project Authors (github.com/Omnibus-Type/Archivo)
 *       Zilla Slab: Copyright 2017, The Mozilla Foundation (github.com/mozilla/zilla-slab)
 * ═══════════════════════════════════════════════════════════════════════
