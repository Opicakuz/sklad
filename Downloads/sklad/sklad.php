<?php
/*
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
 *
 *   AKTUALIZACE
 *     Přepiš soubor novou verzí pod stejným názvem. Složky sklad-data se to nedotkne:
 *     věci, výpůjčky, fotky i nastavení zůstanou.
 *     Která verze na webu běží, je napsané pod přihlašovacím formulářem a v patičce.
 *     Tímhle souborem jde přepsat i sklad v jiné edici (s jiným vzhledem): data zůstanou,
 *     změní se jen vzhled.
 *
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
 */

const SK_VERSION = '1.7.0';
const SK_EDICE = 'verejna';            // titio | verejna | hb: vzhled, písma, značka a soupis na začátku (kód je stejný)
const SK_PRODUKT = 'Sklad';        // jak se aplikace jmenuje (patička, titulky)
const SK_PRODUKT2 = 'skladu';      // … a ve druhém pádě („není záloha …“)
const SK_SLUG = 'sklad';              // začátek názvu stažené zálohy
const SK_NAZEV = 'Sklad';            // výchozí název skladu
const SK_DATA = 'sklad-data';          // název složky s daty (vedle tohoto souboru)
const SK_COOKIE = 'skladt';
const SK_GUARD = "<?php exit; ?>\n";   // první řádek datových souborů: kdyby je server spustil, nic neukážou

// Chyby patří do protokolu serveru, ne do stránky. Hosting může ini_set zakázat (PHP 8 pak takovou funkci vůbec
// nezná), proto se u všeho, co není nezbytné, nejdřív ptáme, jestli to existuje.
error_reporting(E_ALL);
if (function_exists('ini_set')) {
    @ini_set('display_errors', '0');
}
if (function_exists('ini_get') && in_array(strtolower((string) @ini_get('display_errors')), array('1', 'on', 'true', 'yes', 'stdout'), true)) {
    // vypisování chyb vypnout nejde: ať se do stránek a odpovědí nepletou aspoň poznámky a upozornění na zastaralé věci
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE & ~E_USER_NOTICE);
}
@date_default_timezone_set('Europe/Prague');

/** Chyba, kterou má člověk vidět (text je pro něj, stav pro prohlížeč). */
class SkError extends Exception
{
    public $status;

    public function __construct($message, $status = 400)
    {
        parent::__construct((string) $message);
        $this->status = (int) $status;
    }
}

/* ───────────────────────── základ: texty, soubory, adresa ───────────────────────── */

function sk_dir()
{
    return __DIR__ . '/' . SK_DATA;
}

/** Bezpečný výpis do HTML. */
function sk_e($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sk_json($v)
{
    $j = json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    return $j === false ? 'null' : $j;
}

/** JSON, který se dá vložit do stránky (značky a uvozovky jsou zakódované, takže z <script> neuteče). */
function sk_json_html($v)
{
    $j = json_encode($v, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PARTIAL_OUTPUT_ON_ERROR);
    return $j === false ? 'null' : $j;
}

/**
 * Text od člověka: platné UTF-8, bez řídicích znaků, oříznutý na $max znaků.
 * Jednořádkový text přijde o konce řádků a zdvojené mezery, víceřádkový si řádky nechá.
 */
function sk_text($v, $max, $multiline = false)
{
    if (is_int($v) || is_float($v)) {
        $v = (string) $v;
    }
    if (!is_string($v) || $v === '') {
        return '';
    }
    if (!preg_match('//u', $v)) {
        // neplatné UTF-8: nech jen to, co se dá přečíst
        $v = function_exists('mb_convert_encoding') ? (string) @mb_convert_encoding($v, 'UTF-8', 'UTF-8') : (string) preg_replace('/[^\x20-\x7e\n]/', '', $v);
        if (!preg_match('//u', $v)) {
            return '';
        }
    }
    $v = str_replace(array("\r\n", "\r"), "\n", $v);
    $v = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]|\xE2\x80[\xAA-\xAE]|\xE2\x81[\xA6-\xA9]/', '', $v);
    if ($multiline) {
        $v = (string) preg_replace('/[ \t]+/u', ' ', $v);
        $v = (string) preg_replace('/ ?\n ?/', "\n", $v);
        $v = (string) preg_replace('/\n{3,}/', "\n\n", $v);
    } else {
        $v = (string) preg_replace('/\s+/u', ' ', $v);
    }
    $v = trim($v);
    if (preg_match('/^.{0,' . (int) $max . '}/us', $v, $m)) {
        $v = rtrim($m[0]);
    }
    return $v;
}

/** Text pro porovnávání jmen: malá písmena bez háčků a čárek. */
function sk_fold($s)
{
    static $map = null;
    if ($map === null) {
        $low = array('á' => 'a', 'ä' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e', 'ë' => 'e', 'í' => 'i', 'ĺ' => 'l', 'ľ' => 'l', 'ň' => 'n',
            'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'ř' => 'r', 'ŕ' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u', 'ů' => 'u', 'ü' => 'u', 'ý' => 'y', 'ž' => 'z');
        $up = array('Á' => 'a', 'Ä' => 'a', 'Č' => 'c', 'Ď' => 'd', 'É' => 'e', 'Ě' => 'e', 'Ë' => 'e', 'Í' => 'i', 'Ĺ' => 'l', 'Ľ' => 'l', 'Ň' => 'n',
            'Ó' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'Ř' => 'r', 'Ŕ' => 'r', 'Š' => 's', 'Ť' => 't', 'Ú' => 'u', 'Ů' => 'u', 'Ü' => 'u', 'Ý' => 'y', 'Ž' => 'z');
        $map = $low + $up;
    }
    return trim(strtolower(strtr((string) $s, $map)));
}

/** Tvar slova podle počtu: 1 den, 2 dny, 5 dní. */
function sk_plural($n, $one, $few, $many)
{
    return $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);
}

/** Zkoušky: jen když server běží s proměnnou SKLAD_TEST=1, jde podvrhnout čas a adresa Discordu. */
function sk_test_on()
{
    static $on = null;
    if ($on === null) {
        $on = function_exists('getenv') && (string) getenv('SKLAD_TEST') === '1';
    }
    return $on;
}

/** Kolik je hodin. */
function sk_now()
{
    if (sk_test_on() && isset($_SERVER['HTTP_X_SKLAD_NOW'])) {
        $t = strtotime((string) $_SERVER['HTTP_X_SKLAD_NOW']);
        if ($t !== false) {
            return $t;
        }
    }
    return time();
}

/** Datum ve tvaru RRRR-MM-DD, nebo prázdný text. */
function sk_date($v)
{
    if (!is_string($v) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})\z/', $v, $m) || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
        return '';
    }
    return $v;
}

/** O kolik dní je $to později než $from (obě RRRR-MM-DD). Počítá se od poledne, takže změna času nevadí. */
function sk_days($from, $to)
{
    return (int) round((strtotime($to . ' 12:00:00') - strtotime($from . ' 12:00:00')) / 86400);
}

/** Datum pro lidi: 17. 10. 2026. */
function sk_date_cs($ymd)
{
    return preg_match('/^(\d{4})-(\d{2})-(\d{2})\z/', (string) $ymd, $m) ? ((int) $m[3]) . '. ' . ((int) $m[2]) . '. ' . $m[1] : '';
}

/* ───── počty: celá čísla v kusech („ks“), v gramech („g“), nebo v mililitrech („ml“, lidem se ukazují litry) ───── */

/** Kladné celé číslo (kusy, gramy, mililitry), s $zero i nula; jinak null. */
function sk_amount($v, $zero = false)
{
    if (is_string($v) && preg_match('/^\s*\d{1,9}\s*\z/', $v)) {
        $v = (int) trim($v);
    } elseif (is_float($v) && $v >= 0 && $v < 1e9 && floor($v) === $v) {
        $v = (int) $v;
    }
    return is_int($v) && ($v > 0 || ($zero && $v === 0)) && $v <= 999999999 ? $v : null;
}

/** Jednotka, jak se ukládá: „g“, „ml“, jinak kusy („ks“). */
function sk_unit_ok($u)
{
    return $u === 'g' || $u === 'ml' ? $u : 'ks';
}

/** „v kusech“, „v gramech“, „v mililitrech“ (do vět o tom, jak počet napsat). */
function sk_unit_in($u)
{
    return $u === 'g' ? 'v gramech' : ($u === 'ml' ? 'v mililitrech' : 'v kusech');
}

/**
 * Počet ze zápisu v tabulce: „4“, „4x“, „4 ks“, „6 balení“, „1x krabice“ jsou kusy, „500 g“ a „2,5 kg“ gramy,
 * „1 l“, „0,5 l“, „2 dl“ a „330 ml“ mililitry. Vrací [číslo, 'ks', 'g' nebo 'ml'], nebo null, když to jasný počet
 * není („99+“, „velké balení“, nic).
 */
function sk_qty_guess($v)
{
    if (is_int($v)) {
        return $v >= 0 ? array($v, 'ks') : null;
    }
    if (!is_string($v)) {
        return null;
    }
    $t = sk_fold(str_replace("\xC2\xA0", ' ', $v));                     // malá písmena bez háčků
    $t = (string) preg_replace('/(?<=\d) +(?=\d{3}(?!\d))/', '', $t);  // „2 700“ = 2700
    if (preg_match('/^(\d{1,9}) *(?:x|×|ks|kus|kusy|kusu)?\z/u', $t, $m)) {
        return array((int) $m[1], 'ks');
    }
    if (preg_match('/^(\d{1,9}) *(?:x|×)? *(?:baleni|krabice|krabic|krabicka|krabicky|sacek|sacky|sacku|pytel|pytle|pytlu)\z/u', $t, $m)) {
        return array((int) $m[1], 'ks');
    }
    $mul = array('g' => array(1, 'g'), 'kg' => array(1000, 'g'), 'ml' => array(1, 'ml'), 'dl' => array(100, 'ml'),
        'l' => array(1000, 'ml'), 'litr' => array(1000, 'ml'), 'litry' => array(1000, 'ml'), 'litru' => array(1000, 'ml'));
    if (preg_match('/^(\d{1,7}(?:[.,]\d{1,3})?) *(g|kg|ml|dl|l|litr|litry|litru)\z/', $t, $m)) {
        $n = (float) str_replace(',', '.', $m[1]) * $mul[$m[2]][0];
        $g = (int) round($n);
        return abs($n - $g) < 0.001 ? array($g, $mul[$m[2]][1]) : null;
    }
    return null;
}

/** Počet česky: „5 ks“, „500 g“, „2,5 kg“, „330 ml“, „1,5 l“. */
function sk_qty_cs($n, $u)
{
    $n = (int) $n;
    if ($u === 'g' || $u === 'ml') {
        if ($n >= 1000) {
            return rtrim(rtrim(number_format($n / 1000, 3, ',', ' '), '0'), ',') . ($u === 'g' ? ' kg' : ' l');
        }
        return $n . ' ' . $u;
    }
    return number_format($n, 0, ',', ' ') . ' ks';
}

/** Kolik z kolika: „5 z 99 ks“, „500 g z 6 kg“; když je to všechno (nebo celek neznáme), jen „5 ks“. */
function sk_part_cs($part, $all, $u)
{
    if ($all === null || $all === $part) {
        return sk_qty_cs($part, $u);
    }
    return ($u === 'ks' ? number_format($part, 0, ',', ' ') : sk_qty_cs($part, $u)) . ' z ' . sk_qty_cs($all, $u);
}

/* ───── ceny: v haléřích, lidem v korunách ───── */

/** Cena v haléřích z čísla nebo textu („1 200“, „12,50 Kč“, „80,-“); null, když to cena není. Nula = bez ceny. */
function sk_price($v)
{
    if (is_int($v)) {
        return $v >= 0 && $v <= 999999999 ? $v : null;       // z dat a z prohlížeče chodí už v haléřích (nejvýš 9 999 999,99 Kč)
    }
    if (!is_string($v)) {
        return null;
    }
    $t = sk_fold(str_replace("\xC2\xA0", ' ', $v));
    $t = trim((string) preg_replace('/\s*(?:kc|czk)\.?\s*$|,-\s*$/', '', $t));
    $t = (string) preg_replace('/(?<=\d) +(?=\d{3}(?!\d))/', '', $t);
    if ($t === '' || !preg_match('/^(\d{1,7})(?:[.,](\d{1,2}))?\z/', $t, $m)) {
        return null;
    }
    return (int) $m[1] * 100 + (isset($m[2]) ? (int) str_pad($m[2], 2, '0') : 0);
}

/** Cena česky: „1 200 Kč“, „12,50 Kč“. */
function sk_price_cs($h)
{
    $h = (int) $h;
    return number_format(intdiv($h, 100), 0, ',', ' ') . ($h % 100 ? ',' . str_pad((string) ($h % 100), 2, '0', STR_PAD_LEFT) : '') . ' Kč';
}

/** Webová adresa (http, https), jinak prázdný text. */
function sk_url($v)
{
    $v = sk_text($v, 800);
    return preg_match('~^https?://[^\s<>"\'`\\\\]{3,790}\z~i', $v) ? $v : '';
}

/**
 * Přečte datový soubor. null = soubor není, false = je, ale nejde přečíst (poškozený),
 * jinak pole. Rozdíl je důležitý: přes poškozený soubor se nikdy nezapisuje.
 */
function sk_file_read($path)
{
    if (!@is_file($path)) {
        return null;
    }
    $raw = @file_get_contents($path);
    if ($raw === false) {
        return false;
    }
    $p = strpos($raw, "\n");
    $d = json_decode($p === false ? $raw : substr($raw, $p + 1), true);
    return is_array($d) ? $d : false;
}

/** Zapíše datový soubor najednou (nejdřív vedle, pak přejmenuje), aby nikdy nezůstal napůl. */
function sk_file_write($path, $value)
{
    $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    $tmp = $path . '.' . bin2hex(random_bytes(5)) . '.tmp.php';
    if (@file_put_contents($tmp, SK_GUARD . $json, LOCK_EX) === false) {
        @unlink($tmp);
        return false;
    }
    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

/** Soubory ve složce, jejichž název sedí na výraz (plné cesty, podle abecedy). */
function sk_ls($dir, $regex)
{
    $names = function_exists('scandir') ? @scandir($dir) : false;
    $out = array();
    foreach ((is_array($names) ? $names : array()) as $n) {
        if (preg_match($regex, $n)) {
            $out[] = $dir . '/' . $n;
        }
    }
    sort($out);
    return $out;
}

/** Založí složku s daty a zavře ji před webem. Vrací, jestli se do ní dá psát. */
function sk_dir_ensure()
{
    $dir = sk_dir();
    foreach (array($dir, $dir . '/foto', $dir . '/zalohy') as $d) {
        if (!@is_dir($d) && !@mkdir($d, 0755, true) && !@is_dir($d)) {
            return false;
        }
        if (!@is_file($d . '/index.html')) {
            @file_put_contents($d . '/index.html', '');
        }
    }
    if (!@is_file($dir . '/.htaccess')) {
        @file_put_contents($dir . '/.htaccess', "# Do téhle složky nemá web co lézt: data a fotky vydává jen aplikace.\n"
            . "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n"
            . "<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Deny from all\n</IfModule>\n");
    }
    return @is_writable($dir);
}

/** Část přilepená za kódem (styly, skript, soupis). */
function sk_asset($name)
{
    static $parts = null;
    if ($parts === null) {
        $parts = array();
        $tail = (string) @file_get_contents(__FILE__, false, null, __COMPILER_HALT_OFFSET__);
        $chunks = preg_split('/^#SK:([a-z]+)\r?\n/m', $tail, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 1; is_array($chunks) && $i + 1 < count($chunks); $i += 2) {
            $parts[$chunks[$i]] = $chunks[$i + 1];
        }
    }
    return isset($parts[$name]) ? $parts[$name] : '';
}

/* ───── kde aplikace je ─────
 * Adresa, na které je aplikace vidět v prohlížeči, a umístění souboru na serveru se nemusí shodovat:
 * hosting s doménami v podsložkách (Wedos) hlásí soubor jako /domains/web.cz/sklad.php, správce si navíc
 * může v .htaccess udělat hezkou adresu /sklad. Proto se tu nikde nepočítá s umístěním souboru:
 * odkazy a formuláře vedou samy na sebe („?admin“), přesměrování a cookie se drží adresy z prohlížeče.
 */

/** Adresa aplikace, jak ji poslal prohlížeč (cesta bez dotazu): „/sklad“, „/sklad.php“, „/sklad/“. */
function sk_self()
{
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $p = (string) preg_replace('~[?#].*$~s', '', $uri);
    // jen obyčejná cesta: žádné „//“ na začátku (to by byl jiný web), tečkové složky, zakódovaná lomítka
    if ($p !== '' && $p[0] === '/' && strlen($p) <= 300
        && preg_match('~^/(?:[A-Za-z0-9._\~%@+-]+/)*[A-Za-z0-9._\~%@+-]*\z~', $p)
        && !preg_match('~/(?:\.|%2e){1,2}(?:/|\z)~i', $p)
        && !preg_match('/%(?:2f|5c|00|0a|0d)/i', $p)) {
        return $p;
    }
    $s = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '/';
    return preg_match('~^/(?:[A-Za-z0-9._\~@+-]+/)*[A-Za-z0-9._\~@+-]*\z~', $s) ? $s : '/';
}

/** Cesta, ke které si prohlížeč přiřadí přihlášení. */
function sk_cookie_path()
{
    $p = sk_self();
    if (substr($p, -10) === '/index.php') {
        $p = substr($p, 0, -9);
    }
    $p = rtrim($p, '/');            // „/sklad“ platí pro /sklad i /sklad/ (a nic dalšího)
    return $p === '' ? '/' : $p;
}

function sk_https()
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        return true;
    }
    return isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443;
}

function sk_method()
{
    return isset($_SERVER['REQUEST_METHOD']) ? strtoupper((string) $_SERVER['REQUEST_METHOD']) : 'GET';
}

/** Požadavek poslaný z cizího webu (prohlížeč to u formulářů a skriptů hlásí sám). */
function sk_cross_site()
{
    if (isset($_SERVER['HTTP_SEC_FETCH_SITE'])) {
        $s = strtolower((string) $_SERVER['HTTP_SEC_FETCH_SITE']);
        return $s !== 'same-origin' && $s !== 'none';
    }
    if (!empty($_SERVER['HTTP_ORIGIN']) && !empty($_SERVER['HTTP_HOST'])) {
        $o = parse_url((string) $_SERVER['HTTP_ORIGIN']);
        $host = isset($o['host']) ? $o['host'] . (isset($o['port']) ? ':' . $o['port'] : '') : '';
        return strcasecmp($host, (string) $_SERVER['HTTP_HOST']) !== 0;
    }
    return false;
}

function sk_redirect($query = '')
{
    header('Location: ' . sk_self() . ($query !== '' ? '?' . $query : ''), true, 303);
    exit;
}

function sk_nonce()
{
    static $n = null;
    if ($n === null) {
        $n = rtrim(strtr(base64_encode(random_bytes(15)), '+/', '-_'), '=');
    }
    return $n;
}

/** Hlavičky společné všem odpovědím. */
function sk_headers($html = false)
{
    header('X-Content-Type-Options: nosniff');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store, private');
    if ($html) {
        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: DENY');
        $n = sk_nonce();
        header("Content-Security-Policy: default-src 'none'; script-src 'nonce-$n'; style-src 'nonce-$n'; "
            . "img-src 'self' data: blob:; font-src 'self'; connect-src 'self'; form-action 'self'; "
            . "base-uri 'none'; frame-ancestors 'none'");
    }
}

/** Zabalí textovou odpověď, když to server neudělá sám. */
function sk_gzip()
{
    $already = function_exists('ini_get') ? @ini_get('zlib.output_compression') : '1';       // když se nedá zjistit, nebalíme
    if (!headers_sent() && function_exists('ob_gzhandler') && !$already && ob_get_level() === 0) {
        @ob_start('ob_gzhandler');
    }
}

/* ───────────────────────── hesla a přihlášení ─────────────────────────
 * Hlavní správce (heslo správce) smí všechno. Další uživatele zakládá on: každý má jméno a vlastní heslo a ve správě
 * smí všechno kromě Discordu, hesel, uživatelů, názvu skladu, písma a záloh. Volitelné heslo pro nahlížení pustí jen ke čtení; bez něj je přehled
 * otevřený každému, kdo zná adresu. Přihlášení je podepsaná cookie, na serveru se neukládá nic.
 * Změna hesla odhlásí všechny, kdo se přihlásili tím starým (u uživatele i nové heslo od hlavního správce).
 * Role: 'a' hlavní správce, 'u' uživatel, 'v' čtenář.
 */

function sk_auth($fresh = false)
{
    static $a = null;
    if ($a === null || $fresh) {
        $r = sk_file_read(sk_dir() . '/auth.php');
        $a = is_array($r) ? $r : array();
    }
    return $a;
}

function sk_auth_save($a)
{
    $ok = sk_file_write(sk_dir() . '/auth.php', $a);
    sk_auth(true);
    return $ok;
}

function sk_installed()
{
    $a = sk_auth();
    return isset($a['admin'], $a['secret']) && is_string($a['admin']) && $a['admin'] !== '' && is_string($a['secret']) && $a['secret'] !== '';
}

/** Je nastavené heslo pro nahlížení? */
function sk_view_locked()
{
    $a = sk_auth();
    return isset($a['view']) && is_string($a['view']) && $a['view'] !== '';
}

function sk_b64($s)
{
    return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
}

function sk_b64d($s)
{
    $r = base64_decode(strtr((string) $s, '-_', '+/'), true);
    return $r === false ? '' : $r;
}

/** Uživatelé (bez hlavního správce): [{id, n: jméno, h: otisk hesla, t: založen, s: naposledy přihlášen}]. */
function sk_users()
{
    $a = sk_auth();
    $out = array();
    foreach ((isset($a['users']) && is_array($a['users']) ? $a['users'] : array()) as $u) {
        if (is_array($u) && isset($u['id'], $u['n'], $u['h']) && is_string($u['id']) && is_string($u['n']) && is_string($u['h']) && $u['h'] !== '') {
            $out[] = $u;
        }
    }
    return $out;
}

function sk_user($id)
{
    foreach (sk_users() as $u) {
        if ($u['id'] === $id) {
            return $u;
        }
    }
    return null;
}

/** Uživatel podle jména (velikost písmen a háčky nevadí), nebo null. */
function sk_user_by_name($name)
{
    $key = sk_name_key($name);
    if ($key === '') {
        return null;
    }
    foreach (sk_users() as $u) {
        if (sk_name_key($u['n']) === $key) {
            return $u;
        }
    }
    return null;
}

/** Otisk hesla dané role (u uživatele jeho hesla): přihlášení platí, jen dokud se heslo nezmění. */
function sk_role_key($role, $uid = '')
{
    $a = sk_auth();
    if ($role === 'u') {
        $u = sk_user($uid);
        $h = $u !== null ? $u['h'] : '';
        if ($h === '') {
            return '';
        }
    } else {
        $h = $role === 'a' ? (isset($a['admin']) ? $a['admin'] : '') : (isset($a['view']) ? $a['view'] : '');
    }
    return substr(hash('sha256', 'k|' . $h), 0, 16);
}

function sk_sign($payload)
{
    $a = sk_auth();
    return sk_b64(hash_hmac('sha256', $payload, isset($a['secret']) ? (string) $a['secret'] : '', true));
}

/** Přihlásí prohlížeč jako hlavního správce ('a'), uživatele ('u', $uid) nebo čtenáře ('v'). */
function sk_login($role, $uid = '')
{
    $ttl = $role === 'v' ? 180 * 86400 : 30 * 86400;
    $p = array('r' => $role, 'e' => time() + $ttl, 'n' => bin2hex(random_bytes(9)), 'k' => sk_role_key($role, $uid));
    if ($role === 'u') {
        $p['i'] = $uid;
    }
    $p = sk_b64(json_encode($p));
    $tok = $p . '.' . sk_sign($p);
    sk_cookie($tok, time() + $ttl);
    $_COOKIE[SK_COOKIE] = $tok;
    sk_session(true);
}

function sk_logout()
{
    sk_cookie('', time() - 86400 * 365);
    unset($_COOKIE[SK_COOKIE]);
    sk_session(true);
}

function sk_cookie($value, $expires)
{
    if (headers_sent()) {
        return;
    }
    setcookie(SK_COOKIE, $value, array(
        'expires' => $expires,
        'path' => sk_cookie_path(),
        'secure' => sk_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ));
}

/** Kdo je přihlášený: r = 'a' (hlavní správce), 'u' (uživatel, i = jeho id), 'v' (čtenář) nebo '' (nikdo). */
function sk_session($fresh = false)
{
    static $s = null;
    if ($s !== null && !$fresh) {
        return $s;
    }
    $s = array('r' => '', 'n' => '', 'i' => '');
    $c = isset($_COOKIE[SK_COOKIE]) ? $_COOKIE[SK_COOKIE] : '';
    if (!is_string($c) || $c === '' || strlen($c) > 600 || !sk_installed()) {
        return $s;
    }
    $parts = explode('.', $c);
    if (count($parts) !== 2 || !hash_equals(sk_sign($parts[0]), $parts[1])) {
        return $s;
    }
    $p = json_decode(sk_b64d($parts[0]), true);
    if (!is_array($p) || !isset($p['r'], $p['e'], $p['n'], $p['k']) || (int) $p['e'] < time()) {
        return $s;
    }
    $r = $p['r'];
    $uid = $r === 'u' && isset($p['i']) && is_string($p['i']) ? $p['i'] : '';
    if (!in_array($r, array('a', 'u', 'v'), true) || ($r === 'u' && $uid === '') || !is_string($p['k'])) {
        return $s;
    }
    $key = sk_role_key($r, $uid);
    if ($key === '' || !hash_equals($key, $p['k'])) {
        return $s;              // heslo se mezitím změnilo, nebo uživatel už není
    }
    if ($r === 'v' && !sk_view_locked()) {
        return $s;
    }
    $s = array('r' => $r, 'n' => (string) $p['n'], 'i' => $uid);
    return $s;
}

/** Smí spravovat (hlavní správce, nebo uživatel)? */
function sk_is_admin()
{
    $r = sk_session()['r'];
    return $r === 'a' || $r === 'u';
}

/** Je to hlavní správce? Jen on smí Discord, hesla a uživatele. */
function sk_is_super()
{
    return sk_session()['r'] === 'a';
}

/** Jméno toho, kdo právě mění data (do historie); prázdné, když to dělá sklad sám. */
function sk_actor()
{
    $s = sk_session();
    if ($s['r'] === 'u') {
        $u = sk_user($s['i']);
        return $u !== null ? $u['n'] : '';
    }
    if ($s['r'] === 'a') {
        $a = sk_auth();
        $n = isset($a['an']) && is_string($a['an']) ? $a['an'] : '';
        return $n !== '' ? $n : 'hlavní správce';
    }
    return '';
}

function sk_can_view()
{
    if (!sk_view_locked()) {
        return true;
    }
    $s = sk_session();
    return $s['r'] !== '';
}

/** Kód, který musí přijít s každou změnou (chrání přihlášeného před požadavky z cizích stránek). */
function sk_csrf()
{
    $s = sk_session();
    return $s['r'] === '' ? '' : sk_sign('csrf|' . $s['n']);
}

/* ───── zdržování hádačů hesel ───── */

function sk_client()
{
    return substr(hash('sha256', 'ip|' . (isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '')), 0, 16);
}

/** Kolik sekund ještě musí tenhle prohlížeč počkat (0 = může zkoušet). */
function sk_throttle_wait()
{
    $a = sk_auth();
    $f = isset($a['fails']) && is_array($a['fails']) ? $a['fails'] : array();
    $me = sk_client();
    $now = time();
    $mine = array();
    $all = 0;
    foreach ($f as $ip => $times) {
        foreach ((array) $times as $t) {
            if ((int) $t > $now - 900) {
                $all++;
                if ($ip === $me) {
                    $mine[] = (int) $t;
                }
            }
        }
    }
    // 8 špatných pokusů za čtvrt hodiny z jedné adresy, 80 odkudkoli
    if (count($mine) >= 8) {
        return max(1, min($mine) + 900 - $now);
    }
    return $all >= 80 ? 60 : 0;
}

function sk_throttle_fail()
{
    sk_locked(function () {
        $a = sk_auth(true);
        $now = time();
        $f = array();
        foreach ((isset($a['fails']) && is_array($a['fails']) ? $a['fails'] : array()) as $ip => $times) {
            $keep = array();
            foreach ((array) $times as $t) {
                if ((int) $t > $now - 900) {
                    $keep[] = (int) $t;
                }
            }
            if ($keep) {
                $f[$ip] = array_slice($keep, -10);
            }
        }
        $me = sk_client();
        $f[$me] = isset($f[$me]) ? $f[$me] : array();
        $f[$me][] = $now;
        $a['fails'] = array_slice($f, -200, null, true);
        sk_auth_save($a);
    });
}

function sk_throttle_clear()
{
    $a = sk_auth();
    $me = sk_client();
    if (isset($a['fails'][$me])) {
        sk_locked(function () use ($me) {
            $a = sk_auth(true);
            unset($a['fails'][$me]);
            sk_auth_save($a);
        });
    }
}

/**
 * Zkusí heslo. Vrací 'a', 'v' nebo ''. $adminOnly = nepřijmout heslo pro nahlížení.
 * Při moc pokusech vyhodí chybu s tím, jak dlouho počkat.
 */
function sk_try_password($pass, $adminOnly)
{
    list($role) = sk_try_login('', $pass, $adminOnly);
    return $role;
}

/**
 * Přihlášení jménem a heslem. Bez jména je to heslo hlavního správce (nebo pro nahlížení, když ho $adminOnly nezakazuje),
 * se jménem heslo toho uživatele; hlavní správce se může přihlásit i svým jménem. Vrací [role, id uživatele].
 */
function sk_try_login($name, $pass, $adminOnly)
{
    $wait = sk_throttle_wait();
    if ($wait > 0) {
        throw new SkError('Moc pokusů za sebou. Zkus to znovu za ' . max(1, (int) ceil($wait / 60)) . ' min.', 429);
    }
    $a = sk_auth();
    $pass = is_string($pass) ? $pass : '';
    $name = is_string($name) ? trim($name) : '';
    $role = '';
    $uid = '';
    if ($pass !== '' && strlen($pass) <= 300 && strlen($name) <= 200) {
        $u = $name !== '' ? sk_user_by_name($name) : null;
        $an = isset($a['an']) && is_string($a['an']) ? $a['an'] : '';
        if ($u !== null) {
            if (password_verify($pass, $u['h'])) {
                $role = 'u';
                $uid = $u['id'];
            }
        } elseif ($name === '' || ($an !== '' && sk_name_key($an) === sk_name_key($name)) || sk_name_key($name) === sk_name_key('hlavní správce')) {
            if (password_verify($pass, (string) $a['admin'])) {
                $role = 'a';
            } elseif ($name === '' && !$adminOnly && sk_view_locked() && password_verify($pass, (string) $a['view'])) {
                $role = 'v';
            }
        }
    }
    if ($role === '') {
        sk_throttle_fail();
    } elseif ($role !== 'v') {
        // počítadlo nuluje jen heslo do správy: kdo zná heslo pro nahlížení, by si jím jinak mazal své špatné pokusy
        sk_throttle_clear();
        if ($role === 'u') {
            sk_user_seen($uid);
        }
    }
    return array($role, $uid);
}

/** Poznamená, kdy se uživatel naposledy přihlásil (hlavní správce to vidí v Nastavení). */
function sk_user_seen($uid)
{
    sk_locked(function () use ($uid) {
        $a = sk_auth(true);
        foreach ((isset($a['users']) && is_array($a['users']) ? $a['users'] : array()) as $k => $u) {
            if (is_array($u) && isset($u['id']) && $u['id'] === $uid) {
                $a['users'][$k]['s'] = time();
                sk_auth_save($a);
                return;
            }
        }
    });
}

/* ───── zapomenuté heslo: soubor reset-hesla.txt vedle aplikace ───── */

function sk_reset_file()
{
    return __DIR__ . '/reset-hesla.txt';
}

/** Otisk souboru, aby jeden nahraný soubor poslal heslo změnit jen jednou (i když ho hosting nedovolí smazat). */
function sk_reset_mark()
{
    $f = sk_reset_file();
    if (!@is_file($f)) {
        return '';
    }
    clearstatcache(true, $f);
    return 'r' . @filemtime($f) . '-' . @filesize($f) . '-' . @fileinode($f);
}

function sk_reset_pending()
{
    $m = sk_reset_mark();
    if ($m === '') {
        return false;
    }
    $a = sk_auth();
    return !isset($a['reset']) || $a['reset'] !== $m;
}

/**
 * Zámek pro všechno, co zapisuje (data i hesla): dva lidé, kteří uloží ve stejnou chvíli, jdou po sobě.
 * Kde hosting zamykání souborů neumí, pracuje se bez něj; zápis je i tak vždycky celý (viz sk_file_write).
 */
function sk_locked($fn)
{
    static $depth = 0;
    static $h = null;
    if ($depth === 0) {
        $h = @fopen(sk_dir() . '/.lock', 'c');
        if ($h && !@flock($h, LOCK_EX)) {
            @fclose($h);
            $h = null;
        }
    }
    $depth++;
    try {
        return $fn();
    } finally {
        $depth--;
        if ($depth === 0 && $h) {
            @flock($h, LOCK_UN);
            @fclose($h);
            $h = null;
        }
    }
}

/* ───────────────────────── data: listy, věci, výpůjčky, objednávky, přání ─────────────────────────
 * Všechno je v jednom souboru sklad-data/data.php:
 *   sections  listy (jako listy v tabulce): název, kdy se naposledy měnil, která pole má a jak se jmenují
 *   items     věci; pořadí v poli je pořadí v soupisu
 *   orders    objednávky: kdo si co a kdy vyzvedne (v den vyzvednutí se z nich samy stanou výpůjčky)
 *   wishes    seznam přání a nákupů (od verze 1.3 vypnutý, dá se zapnout v Nastavení)
 *   marks     písmena ze sloupce „označení“ a co znamenají
 *   places    barvy míst („červená police“ je červená jako v tabulce)
 * Počet věci je celé číslo „q“ s jednotkou „u“: kusy (ks), nebo u potravin gramy (g). Je to všechno, co oddíl má,
 * i to, co je zrovna půjčené. Když v soupisu počet jako číslo není (třeba „99+“), je jeho text v „qt“ a věc nejde
 * půjčit, dokud ho správce nepřepíše na číslo.
 * Výpůjčky jsou u věci v seznamu „l“: kdo (who), kolik (q), odkdy (since), do kdy (due), podle které objednávky (no).
 * Jednu věc si tak může půjčit víc lidí najednou; volné je to, co z počtu zbývá.
 * Prázdné hodnoty se neukládají, prohlížeč si je doplní.
 */

/** Verze tvaru dat. 2 = verze 1.3 (objednávky, bez starých listů ze soupisu), 3 = verze 1.4 (počty, výpůjčky po kusech), 5 = verze 1.7 (typy položek). */
const SK_DATA_V = 5;

/** Pole, která může list mít (vedle názvu věci), a jejich výchozí popisky. */
function sk_field_defaults()
{
    return array(
        'q' => 'počet',
        'ok' => 'Ve skladu?',
        'who' => 'Kdo to má půjčené',
        'p1' => 'Kde najdu?',
        'p2' => 'Kde najdu?',
        'mk' => 'označení',
        'own' => 'Čí',
        'cat' => 'kategorie',
        'kw' => 'popis',
        'ph' => 'foto',
        'note' => 'poznámka',
    );
}

function sk_colors()
{
    return array('smaragd', 'limetka', 'modra', 'piskova', 'zluta', 'oranzova', 'cervena', 'seda', 'cerna');
}

function sk_wish_labels()
{
    return array(
        'who' => 'Kdo to chce', 'n' => 'Co to je', 'q' => 'Kolik toho chceme', 'where' => 'Kde to pak bude',
        'why' => 'K čemu nám to bude', 'alt' => 'Nedá se to sehnat jinak', 'by' => 'Kdo sežene',
        'ok1' => 'Odsouhlaseno', 'ok2' => 'Koupeno', 'ok3' => 'Faktura doručena', 'note' => 'Poznámky',
    );
}

function sk_id_ok($v)
{
    return is_string($v) && preg_match('/^[a-z0-9]{3,24}\z/', $v) === 1;
}

/** Nové id, které v daném seznamu ještě není. */
function sk_new_id($list)
{
    $used = array();
    foreach ($list as $x) {
        if (isset($x['id'])) {
            $used[$x['id']] = true;
        }
    }
    do {
        $id = 'x' . substr(bin2hex(random_bytes(6)), 0, 9);
    } while (isset($used[$id]));
    return $id;
}

/**
 * Typy položek (inventarizace) při prvním založení. Věc si pamatuje jen id typu, takže přejmenování
 * typu se projeví u všech věcí. Id zůstávají čitelná, ať se dají najít v záloze.
 */
function sk_types_default()
{
    return array(
        array('id' => 'spotreba', 'n' => 'spotřební materiál'),
        array('id' => 'nemovity', 'n' => 'nemovitý majetek'),
        array('id' => 'dhm', 'n' => 'dlouhodobý majetek hmotný'),
        array('id' => 'dnm', 'n' => 'dlouhodobý majetek nehmotný'),
        array('id' => 'ddhm', 'n' => 'drobný hmotný majetek do 40 000 Kč'),
        array('id' => 'ddnm', 'n' => 'drobný nehmotný majetek do 40 000 Kč'),
    );
}

/** Seznam typů položek v pořádku: bez prázdných názvů a bez dvou stejných (velikost písmen a háčky nerozhodují), nejvýš 60. */
function sk_types($v)
{
    $out = array();
    $names = array();
    foreach ((is_array($v) ? $v : array()) as $x) {
        if (!is_array($x)) {
            continue;
        }
        $n = sk_text(isset($x['n']) ? $x['n'] : '', 80);
        $key = sk_fold($n);
        if ($n === '' || isset($names[$key])) {
            continue;
        }
        $names[$key] = true;
        $id = sk_id_ok(isset($x['id']) ? $x['id'] : '') ? $x['id'] : '';
        foreach ($out as $o) {
            if ($o['id'] === $id) {
                $id = '';       // stejné id podruhé: je to nový typ
                break;
            }
        }
        if ($id === '') {
            $id = sk_new_id($out);
        }
        $out[] = array('id' => $id, 'n' => $n);
        if (count($out) >= 60) {
            break;
        }
    }
    return $out;
}

/** Id typů položek, které sklad zná. */
function sk_type_ids($d)
{
    $ids = array();
    foreach ((isset($d['types']) && is_array($d['types']) ? $d['types'] : array()) as $t) {
        $ids[] = $t['id'];
    }
    return $ids;
}

/** Seznam krátkých textů (kategorie, označení) bez prázdných a opakovaných. */
function sk_list($v, $maxItems, $maxLen)
{
    if (is_string($v)) {
        $v = explode(',', $v);
    }
    $out = array();
    foreach ((is_array($v) ? $v : array()) as $x) {
        $t = sk_text($x, $maxLen);
        if ($t !== '' && !in_array($t, $out, true)) {
            $out[] = $t;
        }
        if (count($out) >= $maxItems) {
            break;
        }
    }
    return $out;
}

/**
 * Tagy věci: krátká slova k hledání a filtrování („tábor“, „elektro“). Ze seznamu nebo z textu odděleného čárkami;
 * bez mřížky na začátku, každý jen jednou (velikost písmen a háčky nerozhodují, platí první zápis), nejvýš 20.
 */
function sk_tags($v)
{
    if (is_string($v)) {
        $v = explode(',', $v);
    }
    $out = array();
    $seen = array();
    foreach ((is_array($v) ? $v : array()) as $x) {
        if (!is_string($x)) {
            continue;
        }
        $t = sk_text(ltrim(trim($x), '#'), 40);
        $key = sk_fold($t);
        if ($t === '' || isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $out[] = $t;
        if (count($out) >= 20) {
            break;
        }
    }
    return $out;
}

/** Fotky věci: odkaz (u) nebo nahraný soubor (f, případně rozměry). */
function sk_photos($v)
{
    $out = array();
    foreach ((is_array($v) ? $v : array()) as $p) {
        if (!is_array($p)) {
            continue;
        }
        if (isset($p['f']) && is_string($p['f']) && preg_match('/^[a-f0-9]{20}\.(?:jpg|png|webp)\z/', $p['f'])) {
            $o = array('f' => $p['f']);
            foreach (array('w', 'h') as $k) {
                if (isset($p[$k]) && is_int($p[$k]) && $p[$k] > 0 && $p[$k] < 20000) {
                    $o[$k] = $p[$k];
                }
            }
            if (!empty($p['th'])) {
                $o['th'] = 1;
            }
            $out[] = $o;
        } elseif (isset($p['u']) && sk_url($p['u']) !== '') {
            $out[] = array('u' => sk_url($p['u']));
        }
        if (count($out) >= 12) {
            break;
        }
    }
    return $out;
}

/**
 * Věc v plném tvaru, nebo null, když nemá název nebo list. Výpůjčky („l“) bere ze vstupu; kde na tom záleží, přepíše je volající.
 * $typeIds = id typů položek, které sklad zná (neznámý typ se zahodí); bez nich se bere každé id v pořádku.
 */
function sk_item($in, $sectionIds, $typeIds = null)
{
    if (!is_array($in)) {
        return null;
    }
    $s = isset($in['s']) && is_string($in['s']) && in_array($in['s'], $sectionIds, true) ? $in['s'] : '';
    $n = sk_text(isset($in['n']) ? $in['n'] : '', 200);
    if ($s === '' || $n === '') {
        return null;
    }
    $g = function ($k) use ($in) {
        return isset($in[$k]) ? $in[$k] : '';
    };
    $q = sk_amount(isset($in['q']) ? $in['q'] : null, true);
    return array(
        'id' => sk_id_ok($g('id')) ? $in['id'] : '',
        's' => $s,
        'n' => $n,
        'q' => $q,                                                      // kolik ho oddíl má (i to, co je půjčené)
        'u' => $g('u') === 'g' || $g('u') === 'ml' ? $g('u') : '',      // „g“ = v gramech, „ml“ = v mililitrech (ukazují se litry), jinak kusy
        'qt' => $q === null ? sk_text($g('qt'), 120) : '',              // počet ze soupisu, který číslem není (k přepsání)
        'ok' => isset($in['ok']) && is_bool($in['ok']) ? $in['ok'] : null,
        'l' => sk_loans($g('l')),                                       // výpůjčky
        'p1' => sk_text($g('p1'), 300),
        'p2' => sk_text($g('p2'), 300),
        'mk' => sk_list($g('mk'), 8, 16),
        'own' => sk_text($g('own'), 120),
        'cat' => sk_list($g('cat'), 12, 60),
        'tg' => sk_tags(isset($in['tg']) ? $in['tg'] : array()),        // tagy (hledá se v nich a jde podle nich filtrovat)
        'kw' => sk_text($g('kw'), 600, true),                          // popis (hledá se v něm)
        'ph' => sk_photos($g('ph')),
        'note' => sk_text($g('note'), 3000, true),
        'rc' => sk_price(isset($in['rc']) ? $in['rc'] : null),         // výpůjční cena v haléřích (vidí ji všichni)
        'rn' => sk_text($g('rn'), 40),                                  // za co: „za den“, „za výpůjčku“
        'pc' => sk_price(isset($in['pc']) ? $in['pc'] : null),         // pořizovací cena v haléřích (jen pro správu)
        'ty' => isset($in['ty']) && is_string($in['ty']) && ($typeIds === null ? sk_id_ok($in['ty']) : in_array($in['ty'], $typeIds, true)) ? $in['ty'] : '',   // typ položky: id z „types“ (inventarizace, jen pro správu)
        't' => isset($in['t']) && is_int($in['t']) && $in['t'] > 0 ? $in['t'] : 0,
    );
}

function sk_wish($in)
{
    if (!is_array($in)) {
        return null;
    }
    $g = function ($k) use ($in) {
        return isset($in[$k]) ? $in[$k] : '';
    };
    $n = sk_text($g('n'), 300, true);
    if ($n === '') {
        return null;
    }
    return array(
        'id' => sk_id_ok($g('id')) ? $in['id'] : '',
        'who' => sk_text($g('who'), 120),
        'n' => $n,
        'q' => sk_text($g('q'), 200),
        'where' => sk_text($g('where'), 200),
        'why' => sk_text($g('why'), 1000, true),
        'alt' => sk_text($g('alt'), 300),
        'by' => sk_text($g('by'), 120),
        'ok1' => !empty($in['ok1']),
        'ok2' => !empty($in['ok2']),
        'ok3' => !empty($in['ok3']),
        'note' => sk_text($g('note'), 3000, true),
        't' => isset($in['t']) && is_int($in['t']) && $in['t'] > 0 ? $in['t'] : 0,
    );
}

/** Výpůjčka: kdo, kolik, odkdy, do kdy, podle které objednávky. Kolik je null jen u výpůjček převedených ze starší verze, kde to nebylo zapsané. */
function sk_loan($in)
{
    if (!is_array($in)) {
        return null;
    }
    $who = sk_text(isset($in['who']) ? $in['who'] : '', 200);
    if ($who === '') {
        return null;
    }
    $no = isset($in['no']) && is_int($in['no']) && $in['no'] > 0 ? $in['no'] : 0;
    return sk_pack(array(
        'id' => sk_id_ok(isset($in['id']) ? $in['id'] : '') ? $in['id'] : '',
        'who' => $who,
        'q' => sk_amount(isset($in['q']) ? $in['q'] : null),
        'since' => sk_date(isset($in['since']) ? $in['since'] : ''),
        'due' => sk_date(isset($in['due']) ? $in['due'] : ''),
        'no' => $no,
    ));
}

function sk_loans($v)
{
    $out = array();
    foreach ((is_array($v) ? $v : array()) as $x) {
        $l = sk_loan($x);
        if ($l !== null) {
            $out[] = $l;
        }
        if (count($out) >= 500) {
            break;
        }
    }
    return $out;
}

/**
 * Věc ze starší verze (do 1.3) v dnešním tvaru: počet z textu na číslo s jednotkou (co číslo není, zůstane
 * k přepsání v „qt“), výpůjčka z polí who/since/due/lq do seznamu „l“. Na dnešní tvar nesahá.
 * Věc, která byla půjčená, měla často odškrtnuté „Ve skladu?“; to teď říká výpůjčka sama, takže se zaškrtne zpátky.
 */
function sk_item_legacy($in)
{
    if (!is_array($in)) {
        return $in;
    }
    if (isset($in['q']) && is_string($in['q']) && sk_amount($in['q'], true) === null) {
        $g = sk_qty_guess($in['q']);
        if ($g !== null) {
            $in['q'] = $g[0];
            $in['u'] = $g[1];
        } else {
            $in['qt'] = $in['q'];
            unset($in['q']);
        }
    }
    if (!isset($in['l']) && isset($in['who']) && is_string($in['who']) && trim($in['who']) !== '') {
        $unit = sk_unit_ok(isset($in['u']) ? $in['u'] : '');
        $lq = isset($in['lq']) && is_string($in['lq']) ? trim($in['lq']) : '';
        $q = null;
        if ($lq !== '') {
            $g = sk_qty_guess($lq);
            if ($g !== null && $g[1] === $unit && $g[0] > 0) {
                $q = $g[0];
            }
        } else {
            $all = sk_amount(isset($in['q']) ? $in['q'] : null);
            $q = $all !== null ? $all : null;      // nezapsané „kolik“ znamenalo všechno
        }
        $in['l'] = array(array(
            'id' => isset($in['id']) ? $in['id'] : '',     // stejné id jako věc: Discord si pamatuje, které připomínky už odešly
            'who' => $in['who'],
            'q' => $q,
            'since' => isset($in['since']) ? $in['since'] : '',
            'due' => isset($in['due']) ? $in['due'] : '',
        ));
        if (isset($in['ok']) && $in['ok'] === false) {
            $in['ok'] = true;
        }
    }
    unset($in['who'], $in['since'], $in['due'], $in['lq']);
    return $in;
}

/** Každá výpůjčka dostane id, které nemá žádná jiná. */
function sk_loan_ids(&$items)
{
    $used = array();
    foreach ($items as $k => $it) {
        if (empty($it['l'])) {
            continue;
        }
        foreach ($it['l'] as $j => $l) {
            $id = isset($l['id']) ? $l['id'] : '';
            if (!sk_id_ok($id) || isset($used[$id])) {
                do {
                    $id = 'x' . substr(bin2hex(random_bytes(6)), 0, 9);
                } while (isset($used[$id]));
                $items[$k]['l'][$j]['id'] = $id;
            }
            $used[$id] = true;
        }
    }
}

/** Nové id výpůjčky (žádná jiná výpůjčka ho nemá). */
function sk_loan_new_id($d)
{
    $used = array();
    foreach ($d['items'] as $it) {
        foreach ((isset($it['l']) ? $it['l'] : array()) as $l) {
            $used[$l['id']] = true;
        }
    }
    do {
        $id = 'x' . substr(bin2hex(random_bytes(6)), 0, 9);
    } while (isset($used[$id]));
    return $id;
}

/**
 * Řádky objednávek ze starší verze: kolik byl text, teď je to číslo v jednotce věci.
 * Prázdné „kolik“ znamenalo všechno. Co se převést nedá, zůstane bez počtu a objednávka na něj počká.
 */
function sk_orders_legacy(&$orders, $items)
{
    $by = array();
    foreach ($items as $it) {
        $by[$it['id']] = $it;
    }
    foreach ($orders as $k => $o) {
        if (!is_array($o) || !isset($o['items']) || !is_array($o['items'])) {
            continue;
        }
        foreach ($o['items'] as $j => $l) {
            if (!is_array($l) || !isset($l['i']) || !isset($by[$l['i']]) || (isset($l['q']) && is_int($l['q']))) {
                continue;
            }
            $it = $by[$l['i']];
            $unit = sk_unit_ok(isset($it['u']) ? $it['u'] : '');
            $txt = isset($l['q']) && is_string($l['q']) ? trim($l['q']) : '';
            $q = null;
            if ($txt === '') {
                $q = isset($it['q']) && is_int($it['q']) && $it['q'] > 0 ? $it['q'] : null;
            } else {
                $g = sk_qty_guess($txt);
                $q = $g !== null && $g[1] === $unit && $g[0] > 0 ? $g[0] : null;
            }
            if ($q !== null) {
                $orders[$k]['items'][$j]['q'] = $q;
            } else {
                unset($orders[$k]['items'][$j]['q']);
            }
        }
    }
}

/** Čas „HH:MM“, nebo prázdný text. */
function sk_time($v)
{
    return is_string($v) && preg_match('/^([01]\d|2[0-3]):[0-5]\d\z/', $v) ? $v : '';
}

/**
 * Objednávka v plném tvaru, nebo null, když chybí člověk, den vyzvednutí nebo věci.
 * $itemIds = [id => true] věcí, které ve skladu jsou; řádky s jinými věcmi se zahodí.
 * Řádek: i = id věci, q = kolik (celé číslo v jednotce věci), w = čeká, až se uvolní (někdo ještě nevrátil).
 */
function sk_order($in, $itemIds)
{
    if (!is_array($in)) {
        return null;
    }
    $who = sk_text(isset($in['who']) ? $in['who'] : '', 200);
    $from = sk_date(isset($in['from']) ? $in['from'] : '');
    $lines = array();
    $seen = array();
    foreach ((isset($in['items']) && is_array($in['items']) ? $in['items'] : array()) as $l) {
        $id = is_array($l) && isset($l['i']) && is_string($l['i']) ? $l['i'] : '';
        if (!isset($itemIds[$id]) || isset($seen[$id])) {
            continue;
        }
        $seen[$id] = true;
        $x = array('i' => $id);
        $q = sk_amount(isset($l['q']) ? $l['q'] : null);
        if ($q !== null) {
            $x['q'] = $q;
        }
        if (!empty($l['w'])) {
            $x['w'] = 1;            // čeká, až se věc uvolní
        }
        $lines[] = $x;
        if (count($lines) >= 300) {
            break;
        }
    }
    if ($who === '' || $from === '' || !$lines) {
        return null;
    }
    $num = function ($k) use ($in) {
        return isset($in[$k]) && is_int($in[$k]) && $in[$k] > 0 ? $in[$k] : 0;
    };
    return array(
        'id' => sk_id_ok(isset($in['id']) ? $in['id'] : '') ? $in['id'] : '',
        'no' => $num('no'),                                         // pořadové číslo („objednávka č. 7“)
        'who' => $who,
        'from' => $from,                                            // den vyzvednutí
        'at' => sk_time(isset($in['at']) ? $in['at'] : ''),         // čas vyzvednutí (nepovinný)
        'due' => sk_date(isset($in['due']) ? $in['due'] : ''),      // vrátit do (nepovinné)
        'note' => sk_text(isset($in['note']) ? $in['note'] : '', 500),
        'items' => $lines,
        'made' => $num('made'),
        't' => $num('t'),
    );
}

/** Vyhodí prázdné hodnoty, ať je soubor malý a čitelný. */
function sk_pack($rec)
{
    foreach ($rec as $k => $v) {
        if ($k === 'id' || $k === 's' || $k === 'n') {
            continue;
        }
        if ($v === '' || $v === null || $v === array() || $v === false || $v === 0) {
            // „ok: false“ u věci je údaj (není ve skladu), ten zůstává; stejně tak počet 0 (nic nezbylo)
            if (($k === 'ok' && $v === false || $k === 'q' && $v === 0) && array_key_exists('s', $rec)) {
                continue;
            }
            unset($rec[$k]);
        }
    }
    return $rec;
}

function sk_section($in)
{
    if (!is_array($in)) {
        return null;
    }
    $name = sk_text(isset($in['name']) ? $in['name'] : '', 60);
    if ($name === '') {
        return null;
    }
    $def = sk_field_defaults();
    $f = array();
    $src = isset($in['f']) && is_array($in['f']) ? $in['f'] : array();
    foreach ($def as $k => $label) {
        // počet má každý list (bez něj nejde půjčovat), popis a fotky taky (každá věc je může mít)
        if (array_key_exists($k, $src) || $k === 'q' || $k === 'kw' || $k === 'ph') {
            $l = sk_text(isset($src[$k]) ? $src[$k] : '', 80);
            $f[$k] = $l !== '' ? $l : $label;
        }
    }
    $upd = isset($in['upd']) && is_string($in['upd']) && preg_match('/^\d{4}-\d{2}-\d{2}\z/', $in['upd']) ? $in['upd'] : '';
    $nl = sk_text(isset($in['nl']) ? $in['nl'] : '', 60);
    return array(
        'id' => sk_id_ok(isset($in['id']) ? $in['id'] : '') ? $in['id'] : '',
        'name' => $name,
        'upd' => $upd,
        'note' => sk_text(isset($in['note']) ? $in['note'] : '', 2000, true),
        'nl' => $nl !== '' ? $nl : 'věc',
        'f' => $f,
    );
}

function sk_marks($v)
{
    $out = array();
    $seen = array();
    $colors = sk_colors();
    foreach ((is_array($v) ? $v : array()) as $m) {
        if (!is_array($m)) {
            continue;
        }
        $k = sk_text(isset($m['k']) ? $m['k'] : '', 16);
        if ($k === '' || isset($seen[$k])) {
            continue;
        }
        $seen[$k] = true;
        $c = isset($m['c']) && in_array($m['c'], $colors, true) ? $m['c'] : 'seda';
        $out[] = array('k' => $k, 'l' => sk_text(isset($m['l']) ? $m['l'] : '', 120), 'c' => $c);
        if (count($out) >= 24) {
            break;
        }
    }
    return $out;
}

function sk_places($v)
{
    $out = array();
    $colors = sk_colors();
    foreach ((is_array($v) ? $v : array()) as $m) {
        if (!is_array($m)) {
            continue;
        }
        $t = sk_text(isset($m['t']) ? $m['t'] : '', 80);
        if ($t === '') {
            continue;
        }
        $out[] = array('t' => $t, 'c' => isset($m['c']) && in_array($m['c'], $colors, true) ? $m['c'] : 'seda');
        if (count($out) >= 40) {
            break;
        }
    }
    return $out;
}

function sk_site($v)
{
    $v = is_array($v) ? $v : array();
    $title = sk_text(isset($v['title']) ? $v['title'] : '', 60);
    return array(
        'title' => $title !== '' ? $title : SK_NAZEV,
        'fonts' => !isset($v['fonts']) || !empty($v['fonts']),
    );
}

function sk_wish_conf($v)
{
    $v = is_array($v) ? $v : array();
    $labels = array();
    $src = isset($v['labels']) && is_array($v['labels']) ? $v['labels'] : array();
    foreach (sk_wish_labels() as $k => $def) {
        $l = sk_text(isset($src[$k]) ? $src[$k] : '', 80);
        $labels[$k] = $l !== '' ? $l : $def;
    }
    $name = sk_text(isset($v['name']) ? $v['name'] : '', 60);
    return array(
        'on' => !empty($v['on']),               // ukazuje se seznam přání? (od verze 1.3 standardně ne)
        'name' => $name !== '' ? $name : 'Přání',
        'upd' => isset($v['upd']) && is_string($v['upd']) && preg_match('/^\d{4}-\d{2}-\d{2}\z/', $v['upd']) ? $v['upd'] : '',
        'note' => sk_text(isset($v['note']) ? $v['note'] : '', 2000, true),
        'labels' => $labels,
        'hint' => sk_text(isset($v['hint']) ? $v['hint'] : '', 600, true),
    );
}

/**
 * Celá data v pořádku: použije se na soupis vložený v souboru a na nahranou zálohu.
 * Co nedává smysl, zahodí; když nezbude žádný list, vyhodí chybu.
 */
function sk_data_clean($in)
{
    if (!is_array($in)) {
        throw new SkError('Tohle není záloha ' . SK_PRODUKT2 . '.');
    }
    $sections = array();
    $ids = array();
    foreach ((isset($in['sections']) && is_array($in['sections']) ? $in['sections'] : array()) as $s) {
        $sec = sk_section($s);
        if ($sec === null) {
            continue;
        }
        if ($sec['id'] === '' || isset($ids[$sec['id']])) {
            $sec['id'] = sk_new_id($sections);
        }
        $ids[$sec['id']] = true;
        $sections[] = $sec;
        if (count($sections) >= 40) {
            break;
        }
    }
    if (!$sections) {
        throw new SkError('V záloze není žádný list s věcmi.');
    }
    $secIds = array_keys($ids);
    // záloha ze starší verze typy nemá: dostane výchozí; záloha, kde je seznam prázdný (hlavní správce je všechny smazal), prázdný zůstane
    $types = array_key_exists('types', $in) && is_array($in['types']) ? sk_types($in['types']) : sk_types_default();
    $typeIds = sk_type_ids(array('types' => $types));
    $items = array();
    $seen = array();
    foreach ((isset($in['items']) && is_array($in['items']) ? $in['items'] : array()) as $i) {
        $it = sk_item(sk_item_legacy($i), $secIds, $typeIds);
        if ($it === null) {
            continue;
        }
        if ($it['id'] === '' || isset($seen[$it['id']])) {
            $it['id'] = sk_new_id($items);
        }
        $seen[$it['id']] = true;
        $items[] = sk_pack($it);
        if (count($items) >= 20000) {
            break;
        }
    }
    sk_loan_ids($items);
    $itemIds = array();
    foreach ($items as $it) {
        $itemIds[$it['id']] = true;
    }
    $orders = array();
    $seen = array();
    $nextNo = 1;
    $srcOrders = isset($in['orders']) && is_array($in['orders']) ? $in['orders'] : array();
    sk_orders_legacy($srcOrders, $items);
    foreach ($srcOrders as $o) {
        $or = sk_order($o, $itemIds);
        if ($or === null) {
            continue;
        }
        if ($or['id'] === '' || isset($seen[$or['id']])) {
            $or['id'] = sk_new_id($orders);
        }
        $seen[$or['id']] = true;
        $orders[] = $or;
        $nextNo = max($nextNo, $or['no'] + 1);
        if (count($orders) >= 2000) {
            break;
        }
    }
    foreach ($orders as $k => $or) {
        if ($or['no'] === 0) {
            $orders[$k]['no'] = $nextNo++;
        }
        $orders[$k] = sk_pack($orders[$k]);
    }
    $wishes = array();
    $seen = array();
    foreach ((isset($in['wishes']) && is_array($in['wishes']) ? $in['wishes'] : array()) as $w) {
        $wi = sk_wish($w);
        if ($wi === null) {
            continue;
        }
        if ($wi['id'] === '' || isset($seen[$wi['id']])) {
            $wi['id'] = sk_new_id($wishes);
        }
        $seen[$wi['id']] = true;
        $wishes[] = sk_pack($wi);
        if (count($wishes) >= 5000) {
            break;
        }
    }
    $wish = isset($in['wish']) && is_array($in['wish']) ? $in['wish'] : array();
    if (!array_key_exists('on', $wish)) {
        $wish['on'] = (bool) $wishes;       // záloha ze starší verze: přání tehdy byla vidět vždycky
    }
    return array(
        'v' => SK_DATA_V,
        'rev' => isset($in['rev']) && is_int($in['rev']) ? $in['rev'] : 0,
        'site' => sk_site(isset($in['site']) ? $in['site'] : null),
        'marks' => sk_marks(isset($in['marks']) ? $in['marks'] : null),
        'places' => sk_places(isset($in['places']) ? $in['places'] : null),
        'types' => $types,
        'sections' => $sections,
        'wish' => sk_wish_conf($wish),
        'items' => $items,
        'orders' => $orders,
        'ono' => max($nextNo, isset($in['ono']) && is_int($in['ono']) ? $in['ono'] : 1),
        'wishes' => $wishes,
        'log' => array(),
    );
}

/** Soupis vložený na konci souboru. */
function sk_seed()
{
    $d = json_decode(sk_asset('seed'), true);
    if (!is_array($d)) {
        throw new SkError('Soubor aplikace je poškozený (chybí vložený soupis). Nahraj ho znovu, v binárním režimu.', 500);
    }
    return sk_data_clean($d);
}

/**
 * Aktuální data. Když soubor s daty ještě není, vrátí soupis ze souboru aplikace.
 * Cestou udělá, co je potřeba: převede data ze starší verze a vydá objednávky, kterým nastal čas vyzvednutí.
 */
function sk_data()
{
    static $busy = false;
    $d = sk_data_read();
    if ($d === null) {
        return sk_seed();
    }
    if (!$busy && (sk_upgrade_needed($d) || sk_orders_due($d, sk_now()))) {
        $busy = true;
        try {
            list(, $d) = sk_mutate(function (&$d) {
                sk_auto(function () use (&$d) {
                    sk_upgrade($d);
                    sk_orders_tick($d, sk_now());
                });
            });
        } catch (SkError $e) {
            // zapsat se to nepodařilo (třeba práva složky): přehled aspoň ukáže, jak to má být
            sk_auto(function () use (&$d) {
                sk_upgrade($d);
                sk_orders_tick($d, sk_now());
            });
        } finally {
            $busy = false;
        }
    }
    return $d;
}

/** Data ze souboru v ustáleném tvaru, null když soubor ještě není. */
function sk_data_read()
{
    $d = sk_file_read(sk_dir() . '/data.php');
    if ($d === false) {
        throw new SkError('Soubor ' . SK_DATA . '/data.php je poškozený a nejde přečíst. Nic se do něj nezapsalo. '
            . 'Obnov ho z denní zálohy ve složce ' . SK_DATA . '/zalohy (zkopíruj poslední zálohu přes data.php).', 500);
    }
    if ($d === null) {
        return null;
    }
    foreach (array('sections', 'items', 'orders', 'wishes', 'marks', 'places', 'types', 'log') as $k) {
        if (!isset($d[$k]) || !is_array($d[$k])) {
            $d[$k] = array();
        }
    }
    $d['site'] = sk_site(isset($d['site']) ? $d['site'] : null);
    $d['wish'] = sk_wish_conf(isset($d['wish']) ? $d['wish'] : null);
    $d['rev'] = isset($d['rev']) ? (int) $d['rev'] : 0;
    $d['ono'] = isset($d['ono']) ? max(1, (int) $d['ono']) : 1;
    return $d;
}

function sk_upgrade_needed($d)
{
    return (isset($d['v']) ? (int) $d['v'] : 1) < SK_DATA_V;
}

/**
 * Převede data ze starší verze. Stav před převodem si odloží mezi zálohy.
 * 1.3: ze soupisu Titia zmizí listy, které byly v jeho tabulce jako „(staré) máme“ (u nás „Kde a čí“)
 * a „(staré) chceme“ (seznam přání). Seznam přání se vypne; zapnout jde v Nastavení.
 * 1.4: počty jsou čísla s jednotkou (co číslo nebylo, čeká na správce), výpůjčky jsou u věci v seznamu.
 * 1.7: typy položek (inventarizace): založí se šest výchozích, věci je zatím nemají.
 */
function sk_upgrade(&$d)
{
    $v = isset($d['v']) ? (int) $d['v'] : 1;
    if ($v >= SK_DATA_V) {
        return;
    }
    sk_snapshot('pred-aktualizaci');
    if ($v < 2) {
        $gone = array();
        $pos = sk_find($d['sections'], 'kdeaci');
        if ($pos >= 0 && count($d['sections']) > 1) {
            $keep = array();
            $n = 0;
            foreach ($d['items'] as $it) {
                if (isset($it['s']) && $it['s'] === 'kdeaci') {
                    $n++;
                } else {
                    $keep[] = $it;
                }
            }
            $d['items'] = $keep;
            $gone[] = '„' . $d['sections'][$pos]['name'] . '“ (' . $n . ' ' . sk_plural($n, 'věc', 'věci', 'věcí') . ')';
            array_splice($d['sections'], $pos, 1);
        }
        $nw = count($d['wishes']);
        if ($nw) {
            $gone[] = '„' . $d['wish']['name'] . '“ (' . $nw . ' přání)';
        }
        $d['wishes'] = array();
        $d['wish']['on'] = false;
        $d['orders'] = array();
        $d['ono'] = 1;
        if ($gone) {
            sk_log($d, 'aktualizace 1.3: odstraněny staré listy ' . implode(' a ', $gone) . '; stav před tím je v zálohách', '');
        }
    }
    if ($v < 3) {
        $secIds = array();
        foreach ($d['sections'] as $k => $s) {
            $secIds[] = $s['id'];
            $sec = sk_section($s);              // počet má od teď každý list (a pole jsou v obvyklém pořadí)
            if ($sec !== null && $sec['id'] === $s['id']) {
                $d['sections'][$k] = $sec;
            }
        }
        $num = 0;
        $text = 0;
        $loans = 0;
        $out = array();
        foreach ($d['items'] as $it) {
            $new = sk_item(sk_item_legacy($it), $secIds);
            if ($new === null) {
                continue;
            }
            if ($new['q'] !== null) {
                $num++;
            } else {
                $text++;
            }
            $loans += count($new['l']);
            $out[] = sk_pack($new);
        }
        $d['items'] = $out;
        sk_loan_ids($d['items']);
        sk_orders_legacy($d['orders'], $d['items']);
        foreach ($d['orders'] as $k => $o) {
            unset($d['orders'][$k]['keep']);
        }
        sk_log($d, 'aktualizace 1.4: počty jsou čísla s jednotkou (číslem ' . $num . ', k přepsání ' . $text . ')'
            . ($loans ? ', výpůjčky převedeny (' . $loans . ')' : '') . '; stav před tím je v zálohách', '');
    }
    if ($v < 4) {
        // popis a fotky má od teď každý list (data věcí se nemění)
        foreach ($d['sections'] as $k => $s) {
            $sec = sk_section($s);
            if ($sec !== null && $sec['id'] === $s['id']) {
                $d['sections'][$k] = $sec;
            }
        }
    }
    if ($v < 5) {
        if (empty($d['types'])) {
            $d['types'] = sk_types_default();
        }
        sk_log($d, 'aktualizace 1.7: přibyl typ položky (inventarizace), založeno ' . count($d['types']) . ' výchozích typů', '');
    }
    $d['v'] = SK_DATA_V;
}

/** Data pro prohlížeč: bez historie změn, a kdo nespravuje, nedostane ani pořizovací ceny a typy položek. */
function sk_data_public($d)
{
    unset($d['log']);
    if (!sk_is_admin()) {
        unset($d['types']);
        foreach ($d['items'] as $k => $it) {
            unset($d['items'][$k]['pc'], $d['items'][$k]['ty']);
        }
    }
    return $d;
}

/** Zapíše do historie změn (nejnovější nahoře, drží se posledních 400), i s tím, kdo to udělal. */
/**
 * Co dělá sklad sám (aktualizace dat, vydání objednávky v čas vyzvednutí), se do historie zapisuje bez jména:
 * neudělal to ten, kdo zrovna otevřel stránku. sk_auto($fn) zavolá $fn v tomhle režimu; sk_auto() řekne, jestli v něm jsme.
 */
function sk_auto($fn = null)
{
    static $on = false;
    if ($fn === null) {
        return $on;
    }
    $was = $on;
    $on = true;
    try {
        return $fn();
    } finally {
        $on = $was;
    }
}

function sk_log(&$d, $what, $name)
{
    $e = array('t' => time(), 'a' => $what, 'x' => sk_text($name, 160));
    $who = sk_auto() ? '' : sk_actor();
    if ($who !== '') {
        $e['u'] = sk_text($who, 60);
    }
    array_unshift($d['log'], $e);
    if (count($d['log']) > 400) {
        $d['log'] = array_slice($d['log'], 0, 400);
    }
}

function sk_find($list, $id)
{
    foreach ($list as $i => $x) {
        if (isset($x['id']) && $x['id'] === $id) {
            return $i;
        }
    }
    return -1;
}

/** List se právě změnil: zapíše se k němu dnešní datum (v tabulce „aktualizováno“). */
function sk_touch(&$d, $sectionId)
{
    $i = sk_find($d['sections'], $sectionId);
    if ($i >= 0) {
        $d['sections'][$i]['upd'] = date('Y-m-d');
    }
}

/**
 * Jedna změna dat: zamkne, načte, zavolá $fn(&$data), uloží. Vrací [výsledek $fn, nová data].
 * Před první změnou dne si odloží kopii do zalohy/.
 */
function sk_mutate($fn)
{
    return sk_locked(function () use ($fn) {
        $d = sk_data();
        $res = $fn($d);
        if (sk_orders_due($d, sk_now())) {
            sk_auto(function () use (&$d) {
                sk_orders_tick($d, sk_now());   // změna mohla uvolnit věc, na kterou čeká objednávka
            });
        }
        $d['rev'] = (int) $d['rev'] + 1;
        $d['v'] = SK_DATA_V;
        sk_snapshot('');
        if (!sk_file_write(sk_dir() . '/data.php', $d)) {
            throw new SkError('Změnu se nepodařilo uložit. Zkontroluj, jestli má složka ' . SK_DATA . ' práva k zápisu.', 500);
        }
        return array($res, $d);
    });
}

/**
 * Kopie dat do zalohy/. Bez přípony jedna za den (stav před první změnou toho dne),
 * s příponou vždy (před obnovou nebo nahráním zálohy).
 */
function sk_snapshot($suffix)
{
    $dir = sk_dir();
    $src = $dir . '/data.php';
    if (!@is_file($src)) {
        return;
    }
    $name = 'data-' . date('Y-m-d') . ($suffix !== '' ? '-' . date('His') . '-' . $suffix : '') . '.php';
    $dst = $dir . '/zalohy/' . $name;
    if (@is_file($dst)) {
        return;
    }
    if (!@is_dir($dir . '/zalohy')) {
        @mkdir($dir . '/zalohy', 0755, true);
    }
    @copy($src, $dst);
    // drž posledních 40 kopií
    $all = sk_ls($dir . '/zalohy', '/^data-.+\.php\z/');
    if (count($all) > 40) {
        foreach (array_slice($all, 0, count($all) - 40) as $old) {
            @unlink($old);
        }
    }
}

function sk_snapshots()
{
    $out = array();
    foreach (array_reverse(sk_ls(sk_dir() . '/zalohy', '/^data-.+\.php\z/')) as $f) {
        $out[] = array('name' => basename($f, '.php'), 'size' => (int) @filesize($f), 't' => (int) @filemtime($f));
    }
    return $out;
}

/**
 * Smaže nahrané fotky, na které už žádná věc neodkazuje. Hodinu po nahrání je nechává být (rozepsaný formulář)
 * a nechává i ty, které ještě zná některá z kopií v zalohy/: obnova ze zálohy pak své fotky najde.
 */
function sk_photo_gc($d)
{
    $files = sk_ls(sk_dir() . '/foto', '/^[a-f0-9]{20}(?:-t)?\.(?:jpg|png|webp)\z/');
    if (!$files) {
        return;
    }
    $used = array();
    foreach ($d['items'] as $it) {
        foreach ((isset($it['ph']) && is_array($it['ph']) ? $it['ph'] : array()) as $p) {
            if (isset($p['f'])) {
                $used[$p['f']] = true;
            }
        }
    }
    $now = time();
    $orphans = array();
    foreach ($files as $f) {
        $main = (string) preg_replace('/-t(\.[a-z]+)\z/', '$1', basename($f));
        if (!isset($used[$main]) && (int) @filemtime($f) < $now - 3600) {
            $orphans[$f] = $main;
        }
    }
    if (!$orphans) {
        return;
    }
    foreach (sk_ls(sk_dir() . '/zalohy', '/^data-.+\.php\z/') as $snap) {
        if (preg_match_all('/"f":"([a-f0-9]{20}\.(?:jpg|png|webp))"/', (string) @file_get_contents($snap), $m)) {
            foreach ($m[1] as $name) {
                $used[$name] = true;
            }
        }
    }
    foreach ($orphans as $f => $main) {
        if (!isset($used[$main])) {
            @unlink($f);
        }
    }
}

/* ───────────────────────── výpůjčky a objednávky ─────────────────────────
 * Počet věci („q“) je všechno, co oddíl má, i to, co je zrovna venku. Půjčit nebo objednat jde jen to, co je v tu dobu
 * volné. Od počtu se odečítá:
 *   výpůjčka    od dneška do dne vrácení; bez data vrácení, nebo když už minul, pořád (věc ještě není zpátky)
 *   objednávka  od dne vyzvednutí (nejdřív od dneška) do dne vrácení; bez data vrácení pořád
 * Dny se počítají celé: co má být zpátky v pátek, jde objednat až od soboty.
 * Věc bez počtu (v soupisu byl jen text, třeba „99+“) a věc, která chybí („Ve skladu?“ nezaškrtnuté), půjčit nejde.
 * Objednávka říká, kdo si co kdy vyzvedne, a je vidět všem. Když nastane den (a případně hodina) vyzvednutí, stanou se
 * z jejích řádků výpůjčky a objednávka zmizí. Řádek, na který zrovna nezbývá (někdo to ještě nevrátil), počká.
 */

/** Věci podle id: [id => pozice v $d['items']]. */
function sk_item_index($d)
{
    $by = array();
    foreach ($d['items'] as $k => $it) {
        if (isset($it['id'])) {
            $by[$it['id']] = $k;
        }
    }
    return $by;
}

/** Jednotka věci: „g“ (gramy), „ml“ (mililitry), jinak „ks“. */
function sk_unit($it)
{
    return sk_unit_ok(isset($it['u']) ? $it['u'] : '');
}

/** Kolik věci oddíl má (i s tím, co je půjčené); null, když počet není zapsaný číslem. */
function sk_count($it)
{
    return isset($it['q']) && is_int($it['q']) ? $it['q'] : null;
}

/** Chybí věc? („Ve skladu?“ je odškrtnuté.) */
function sk_missing($it)
{
    return isset($it['ok']) && $it['ok'] === false;
}

/** Kolik z věci jde půjčit, když je všechno doma: počet, u chybějící věci nic, bez počtu null. */
function sk_capacity($it)
{
    $q = sk_count($it);
    return $q !== null && sk_missing($it) ? 0 : $q;
}

/** Kolik drží výpůjčka nebo řádek objednávky. Když se neví kolik (data ze starší verze), drží všechno (INF). */
function sk_hold_q($x)
{
    return isset($x['q']) && is_int($x['q']) ? $x['q'] : INF;
}

/** Kolik je z věci teď půjčené (INF, když u některé výpůjčky není zapsané kolik). */
function sk_lent($it)
{
    $sum = 0;
    foreach ((isset($it['l']) ? $it['l'] : array()) as $l) {
        $sum += sk_hold_q($l);
    }
    return $sum;
}

/** Součet výpůjček, u kterých je zapsané kolik (pod tohle počet věci klesnout nesmí). */
function sk_lent_known($it)
{
    $sum = 0;
    foreach ((isset($it['l']) ? $it['l'] : array()) as $l) {
        if (isset($l['q']) && is_int($l['q'])) {
            $sum += $l['q'];
        }
    }
    return $sum;
}

/** Kolik je teď volné; null, když věc nemá počet. */
function sk_free($it)
{
    $cap = sk_capacity($it);
    if ($cap === null) {
        return null;
    }
    $lent = sk_lent($it);
    return $lent >= $cap ? 0 : (int) ($cap - $lent);
}

/** Výpůjčka podle id: [pozice věci, pozice výpůjčky], nebo null. */
function sk_loan_find($d, $id)
{
    if (!is_string($id) || $id === '') {
        return null;
    }
    foreach ($d['items'] as $k => $it) {
        foreach ((isset($it['l']) ? $it['l'] : array()) as $j => $l) {
            if ($l['id'] === $id) {
                return array($k, $j);
            }
        }
    }
    return null;
}

/** Kolik toho je, česky: „5 ks“, „500 g“, „2,5 kg“; když se neví, „neví se kolik“. */
function sk_hold_cs($q, $it)
{
    return $q === INF ? 'nezapsáno kolik' : sk_qty_cs($q, sk_unit($it));
}

/**
 * Co z věci kdy drží výpůjčky a objednávky: [[od, do (null = bez konce), kolik, popis pro člověka]].
 * $skipOrder, $skipLoan: objednávka nebo výpůjčka, která se zrovna upravuje (sama sobě nepřekáží).
 */
function sk_holds($d, $it, $today, $skipOrder = '', $skipLoan = '')
{
    $out = array();
    foreach ((isset($it['l']) ? $it['l'] : array()) as $l) {
        if ($l['id'] === $skipLoan) {
            continue;
        }
        $q = sk_hold_q($l);
        $due = isset($l['due']) ? (string) $l['due'] : '';
        if ($due === '') {
            $when = 'bez data vrácení';
        } elseif (strcmp($due, $today) < 0) {
            $when = 'termín vrácení ' . sk_date_cs($due) . ' už minul';
        } else {
            $when = 'vrátit do ' . sk_date_cs($due);
        }
        $out[] = array($today, $due !== '' && strcmp($due, $today) >= 0 ? $due : null, $q, $l['who'] . ' (' . sk_hold_cs($q, $it) . ', ' . $when . ')');
    }
    foreach ($d['orders'] as $o) {
        if ($o['id'] === $skipOrder) {
            continue;
        }
        foreach ($o['items'] as $ln) {
            if ($ln['i'] !== $it['id']) {
                continue;
            }
            $q = sk_hold_q($ln);
            $from = strcmp($o['from'], $today) > 0 ? $o['from'] : $today;
            $due = isset($o['due']) ? (string) $o['due'] : '';
            $to = $due === '' ? null : (strcmp($due, $from) >= 0 ? $due : $from);
            $when = strcmp($o['from'], $today) <= 0 ? 'čeká na vydání' : 'od ' . sk_date_cs($o['from']) . ($due !== '' ? ' do ' . sk_date_cs($due) : ', bez data vrácení');
            $out[] = array($from, $to, $q, 'objednávka' . (!empty($o['no']) ? ' č. ' . $o['no'] : '') . ' – ' . $o['who'] . ' (' . sk_hold_cs($q, $it) . ', ' . $when . ')');
        }
    }
    return $out;
}

/** Nejvíc, kolik věc drží najednou v některém dni od $a do $b (null = bez konce). Vrací [kolik, co ji tehdy drží]. */
function sk_peak($holds, $a, $b)
{
    $days = array($a);
    foreach ($holds as $h) {
        if (strcmp($h[0], $a) > 0 && ($b === null || strcmp($h[0], $b) <= 0)) {
            $days[] = $h[0];
        }
    }
    $best = 0;
    $who = array();
    foreach (array_unique($days) as $t) {
        $sum = 0;
        $at = array();
        foreach ($holds as $h) {
            if (strcmp($h[0], $t) <= 0 && ($h[1] === null || strcmp($h[1], $t) >= 0)) {
                $sum += $h[2];
                $at[] = $h[3];
            }
        }
        if ($sum > $best) {
            $best = $sum;
            $who = $at;
        }
    }
    return array($best, $who);
}

/**
 * Vejdou se věci do toho, co je v tu dobu volné? $want = [[pozice věci, kolik]], čas od $a do $b (null = bez konce).
 * Když ne, vyhodí chybu, ve které je u každé věci, kolik je volné a kdo ji v tu dobu má.
 */
function sk_fit($d, $want, $a, $b, $today, $skipOrder = '', $skipLoan = '')
{
    $bad = array();
    foreach ($want as $w) {
        list($k, $q) = $w;
        $it = $d['items'][$k];
        $name = '„' . $it['n'] . '“';
        if (sk_count($it) === null) {
            $bad[] = $name . ' nemá zapsaný počet' . (isset($it['qt']) && $it['qt'] !== '' ? ' (v soupisu je „' . $it['qt'] . '“)' : '') . '. Doplň ho u věci, pak půjde půjčit.';
            continue;
        }
        if (sk_missing($it)) {
            $bad[] = $name . ' chybí: u věci není zaškrtnuté „Ve skladu?“. Až se najde, zaškrtni ho.';
            continue;
        }
        $cap = sk_capacity($it);
        list($peak, $who) = sk_peak(sk_holds($d, $it, $today, $skipOrder, $skipLoan), $a, $b);
        if ($peak + $q <= $cap) {
            continue;
        }
        $ask = sk_qty_cs($q, sk_unit($it));
        if ($peak >= $cap) {
            $msg = $name . ': v tu dobu není volné nic (chceš ' . $ask . ', celkem je ' . sk_qty_cs($cap, sk_unit($it)) . ').';
        } else {
            $msg = $name . ': v tu dobu je volno jen ' . sk_qty_cs($cap - $peak, sk_unit($it)) . ' z ' . sk_qty_cs($cap, sk_unit($it)) . ' (chceš ' . $ask . ').';
        }
        if ($who) {
            $msg .= ' Mají to: ' . implode(', ', $who) . '.';
        }
        $bad[] = $msg;
    }
    if ($bad) {
        throw new SkError(implode("\n", $bad), 409);
    }
}

/** Nová výpůjčka (pozice věci $k). Vrací ji. */
function sk_loan_add(&$d, $k, $who, $q, $since, $due, $no)
{
    $l = sk_loan(array('id' => sk_loan_new_id($d), 'who' => $who, 'q' => $q, 'since' => $since, 'due' => $due, 'no' => $no));
    if (!isset($d['items'][$k]['l'])) {
        $d['items'][$k]['l'] = array();
    }
    $d['items'][$k]['l'][] = $l;
    return $l;
}

/** Nastal čas objednávku vydat? (den vyzvednutí, a když je zadaný čas, tak i ten) */
function sk_order_ready($o, $now)
{
    $from = isset($o['from']) ? (string) $o['from'] : '';
    $today = date('Y-m-d', $now);
    if ($from === '' || $from > $today) {
        return false;
    }
    if ($from < $today) {
        return true;
    }
    $at = isset($o['at']) ? (string) $o['at'] : '';
    return $at === '' || $at <= date('H:i', $now);
}

/**
 * Je u objednávky, které nastal čas, co dělat? Vydat řádek, na který už zbývá, uklidit řádek se smazanou věcí,
 * nebo poprvé poznačit, že řádek čeká. Řádky, které už čekají a dál čekat musí, se nepočítají.
 */
function sk_order_movable($d, $o, $by)
{
    foreach ((isset($o['items']) && is_array($o['items']) ? $o['items'] : array()) as $l) {
        $id = isset($l['i']) ? $l['i'] : '';
        if (!isset($by[$id])) {
            return true;
        }
        $free = sk_free($d['items'][$by[$id]]);
        if (isset($l['q']) && $free !== null && $free >= $l['q']) {
            return true;
        }
        if (empty($l['w'])) {
            return true;
        }
    }
    return false;
}

/** Čeká nějaká objednávka na vydání? Levná kontrola při každém načtení dat. */
function sk_orders_due($d, $now)
{
    $by = null;
    foreach ($d['orders'] as $o) {
        if (!is_array($o) || !sk_order_ready($o, $now)) {
            continue;
        }
        if ($by === null) {
            $by = sk_item_index($d);
        }
        if (sk_order_movable($d, $o, $by)) {
            return true;
        }
    }
    return false;
}

/** Vydá všechny objednávky, kterým nastal čas. Výpůjčky dostanou jako den půjčení den vyzvednutí. */
function sk_orders_tick(&$d, $now)
{
    $i = 0;
    while ($i < count($d['orders'])) {
        $o = $d['orders'][$i];
        if (is_array($o) && sk_order_ready($o, $now)) {
            $n = count($d['orders']);
            sk_order_issue($d, $i, (string) $o['from']);
            if (count($d['orders']) < $n) {
                continue;       // objednávka je celá vydaná a zmizela: na jejím místě už je další
            }
        }
        $i++;
    }
}

/**
 * Vydá objednávku na pozici $pos: z řádků, na které teď zbývá dost, udělá výpůjčky ode dne $since
 * (řádek, který musel čekat, dostane jako den půjčení dnešek). Ostatní řádky počkají.
 * Vrací [vydané [věc, výpůjčka], řádky, které dál čekají]. Když nic nečeká, objednávka zmizí.
 */
function sk_order_issue(&$d, $pos, $since)
{
    $o = $d['orders'][$pos];
    $by = sk_item_index($d);
    $today = date('Y-m-d', sk_now());
    $due = isset($o['due']) ? (string) $o['due'] : '';
    $no = isset($o['no']) ? (int) $o['no'] : 0;
    $done = array();
    $left = array();
    foreach ((isset($o['items']) && is_array($o['items']) ? $o['items'] : array()) as $l) {
        $id = isset($l['i']) ? $l['i'] : '';
        if (!isset($by[$id])) {
            continue;       // věc mezitím zmizela ze skladu
        }
        $k = $by[$id];
        $it = $d['items'][$k];
        $free = sk_free($it);
        if (!isset($l['q']) || $free === null || $free < $l['q']) {
            $l['w'] = 1;
            $left[] = $l;   // zatím na to nezbývá: počká, až to někdo vrátí (nebo až správce doplní počet)
            continue;
        }
        $loan = sk_loan_add($d, $k, $o['who'], $l['q'], !empty($l['w']) ? $today : $since, $due, $no);
        sk_touch($d, $it['s']);
        sk_log($d, 'půjčeno ' . sk_qty_cs($l['q'], sk_unit($it)) . ($no ? ' podle objednávky č. ' . $no : '')
            . ' (' . $o['who'] . ($due !== '' ? ', vrátit do ' . sk_date_cs($due) : '') . ')', $it['n']);
        $done[] = array($d['items'][$k], $loan);
    }
    if ($left) {
        $d['orders'][$pos]['items'] = $left;
    } else {
        array_splice($d['orders'], $pos, 1);
    }
    return array($done, $left);
}

/** Uklidí objednávky po smazaných věcech: zmizí řádky s věcmi, které už nejsou, a prázdné objednávky. */
function sk_orders_prune(&$d)
{
    $by = sk_item_index($d);
    $keep = array();
    foreach ($d['orders'] as $o) {
        $lines = array();
        foreach ((isset($o['items']) && is_array($o['items']) ? $o['items'] : array()) as $l) {
            if (isset($l['i']) && isset($by[$l['i']])) {
                $lines[] = $l;
            }
        }
        if ($lines) {
            $o['items'] = $lines;
            $keep[] = $o;
        } else {
            sk_log($d, 'zrušena objednávka č. ' . (isset($o['no']) ? $o['no'] : '?') . ' (' . (isset($o['who']) ? $o['who'] : '') . '): její věci už ve skladu nejsou', '');
        }
    }
    $d['orders'] = $keep;
}

/** Čas, který řádek objednávky drží: [od, do (null = bez konce)]. */
function sk_order_span($o, $today)
{
    $a = strcmp($o['from'], $today) > 0 ? $o['from'] : $today;
    $b = isset($o['due']) && $o['due'] !== '' ? $o['due'] : null;
    return array($a, $b !== null && strcmp($b, $a) < 0 ? $a : $b);
}

/**
 * Chce řádek upravené objednávky víc, než měl dřív? Když ne (stejně nebo méně kusů, ne déle), nemusí se
 * znovu ověřovat: objednávka, která čeká, až někdo něco vrátí, jde pořád upravit (třeba poznámka nebo čas).
 */
function sk_order_line_more($old, $line, $span, $today)
{
    list($a0, $b0) = sk_order_span($old, $today);
    list($a, $b) = $span;
    foreach ($old['items'] as $l) {
        if ($l['i'] !== $line['i']) {
            continue;
        }
        if (!isset($l['q']) || $line['q'] > $l['q']) {
            return true;
        }
        if (strcmp($a, $a0) < 0) {
            return true;
        }
        return $b0 !== null && ($b === null || strcmp($b, $b0) > 0);
    }
    return true;
}

/**
 * Uloží objednávku z prohlížeče (novou, nebo upravenou). Ověří, že se v tu dobu vejde do toho, co je volné.
 * Když už nastal čas vyzvednutí, rovnou ji vydá. $events dostane, co se má oznámit na Discord.
 * Vrací [id, no, vydáno, čeká, zůstala objednávka].
 */
function sk_order_save(&$d, $in, $now, &$events)
{
    $by = sk_item_index($d);
    $o = sk_order(isset($in['order']) ? $in['order'] : null, $by);
    if ($o === null) {
        throw new SkError('Objednávka potřebuje, kdo si věci bere, kdy si je vyzvedne, a aspoň jednu věc ze skladu.');
    }
    $noQ = array();
    foreach ($o['items'] as $l) {
        if (!isset($l['q'])) {
            $noQ[] = '„' . $d['items'][$by[$l['i']]]['n'] . '“';
        }
    }
    if ($noQ) {
        throw new SkError('Napiš, kolik si toho bere: ' . implode(', ', $noQ) . '.');
    }
    if ($o['due'] !== '' && $o['due'] < $o['from']) {
        throw new SkError('Vrátit se to má až po vyzvednutí. Zkontroluj data.');
    }
    $today = date('Y-m-d', $now);
    $pos = $o['id'] !== '' ? sk_find($d['orders'], $o['id']) : -1;
    if ($o['id'] !== '' && $pos < 0) {
        throw new SkError('Tahle objednávka už není. Mezitím ji někdo vydal nebo zrušil.', 404);
    }
    $old = $pos >= 0 ? $d['orders'][$pos] : null;
    // „čeká, až se to uvolní“ ví jen server: z prohlížeče se nebere, při úpravě se převezme z uložené objednávky
    $waits = array();
    if ($old !== null) {
        foreach ($old['items'] as $l) {
            if (!empty($l['w'])) {
                $waits[$l['i']] = true;
            }
        }
    }
    foreach ($o['items'] as $k => $l) {
        unset($o['items'][$k]['w']);
        if (isset($waits[$l['i']])) {
            $o['items'][$k]['w'] = 1;
        }
    }
    $isNew = $old === null;
    if ($isNew && $o['from'] < $today) {
        throw new SkError('Den vyzvednutí je v minulosti. Zkontroluj datum.');
    }
    if (!$isNew) {
        $oldT = isset($old['t']) ? (int) $old['t'] : 0;
        if (isset($in['t']) && (int) $in['t'] !== $oldT) {
            throw new SkError('Tuhle objednávku mezitím upravil někdo jiný. Zavři okno, podívej se na aktuální stav a uprav ji znovu.', 409);
        }
    }
    // vejde se to?
    $span = sk_order_span($o, $today);
    $want = array();
    foreach ($o['items'] as $l) {
        if ($isNew || sk_order_line_more($old, $l, $span, $today)) {
            $want[] = array($by[$l['i']], $l['q']);
        }
    }
    sk_fit($d, $want, $span[0], $span[1], $today, $o['id']);

    if ($isNew) {
        $o['id'] = sk_new_id($d['orders']);
        $o['no'] = 0;               // číslo dostane, až když opravdu zůstane jako objednávka (ne výpůjčka hned)
        $o['made'] = time();
        $o['t'] = time();
        $d['orders'][] = sk_pack($o);
        $pos = count($d['orders']) - 1;
    } else {
        $o['no'] = isset($old['no']) ? (int) $old['no'] : 0;
        $o['made'] = isset($old['made']) ? (int) $old['made'] : time();
        $o['t'] = max(time(), $oldT + 1);
        $d['orders'][$pos] = sk_pack($o);
        sk_log($d, 'upravena objednávka č. ' . $o['no'] . ' (' . $o['who'] . ', vyzvednutí ' . sk_date_cs($o['from']) . ($o['at'] !== '' ? ' v ' . $o['at'] : '') . ')', sk_order_names($d, $o));
    }
    $done = array();
    $left = array();
    $ready = sk_order_ready($o, $now);
    if ($ready) {
        list($done, $left) = sk_order_issue($d, $pos, $today);
    }
    if ($isNew && (!$ready || $left)) {
        $o['no'] = (int) $d['ono'];
        $d['ono'] = $o['no'] + 1;
        $d['orders'][sk_find($d['orders'], $o['id'])]['no'] = $o['no'];
        if ($ready) {
            $o['items'] = $left;
            sk_log($d, 'objednávka č. ' . $o['no'] . ' (' . $o['who'] . '): čeká, až se věci uvolní', sk_order_names($d, $o));
        } else {
            sk_log($d, 'objednávka č. ' . $o['no'] . ' (' . $o['who'] . ', vyzvedne ' . sk_date_cs($o['from']) . ($o['at'] !== '' ? ' v ' . $o['at'] : '') . ')', sk_order_names($d, $o));
            $events[] = array('order', $o, sk_order_lines($d, $o));
        }
    }
    sk_loan_events($d, $done, $o['who'], $o['due'], $events);
    return array(
        'id' => $o['id'],
        'no' => $o['no'],
        'issued' => count($done),
        'waiting' => count($left),
        'open' => !$ready || (bool) $left,
    );
}

/** Oznámení o nových výpůjčkách: jedna věc zvlášť, víc věcí jedním seznamem. $done = [[věc, výpůjčka]]. */
function sk_loan_events($d, $done, $who, $due, &$events)
{
    if (!$done) {
        return;
    }
    if (count($done) === 1) {
        $names = sk_section_names($d);
        $events[] = array('loan', $done[0][0], array('loan' => $done[0][1], 'list' => $names[$done[0][0]['s']]));
        return;
    }
    $lines = array();
    foreach ($done as $x) {
        $lines[] = array($x[0], $x[1]['q']);
    }
    $events[] = array('loans', array('who' => $who, 'due' => $due), $lines);
}

/**
 * Vydá objednávku hned (tlačítko u objednávky). Když měla vyzvednutí až později, posune se na dnešek: to se musí
 * vejít i do dní před původním vyzvednutím. Co teď vydat nejde (někdo to ještě nevrátil), dál čeká.
 */
function sk_order_issue_now(&$d, $id, $now, &$events)
{
    $pos = sk_find($d['orders'], is_string($id) ? $id : '');
    if ($pos < 0) {
        throw new SkError('Tahle objednávka už není. Mezitím ji někdo vydal nebo zrušil.', 404);
    }
    $o = $d['orders'][$pos];
    $today = date('Y-m-d', $now);
    if (strcmp($o['from'], $today) > 0) {
        $by = sk_item_index($d);
        $want = array();
        foreach ($o['items'] as $l) {
            if (isset($by[$l['i']]) && isset($l['q'])) {
                $want[] = array($by[$l['i']], $l['q']);
            }
        }
        $moved = $o;
        $moved['from'] = $today;
        $span = sk_order_span($moved, $today);
        sk_fit($d, $want, $span[0], $span[1], $today, $o['id']);
    }
    $d['orders'][$pos]['from'] = $today;
    unset($d['orders'][$pos]['at']);
    list($done, $left) = sk_order_issue($d, $pos, $today);
    sk_loan_events($d, $done, $o['who'], isset($o['due']) ? $o['due'] : '', $events);
    return array('issued' => count($done), 'waiting' => count($left));
}

/** Názvy věcí v objednávce (do historie). */
function sk_order_names($d, $o)
{
    $by = sk_item_index($d);
    $names = array();
    foreach ($o['items'] as $l) {
        if (isset($by[$l['i']])) {
            $names[] = $d['items'][$by[$l['i']]]['n'];
        }
    }
    return implode(', ', $names);
}

/** Řádky objednávky jako [věc, kolik] (pro zprávy). */
function sk_order_lines($d, $o)
{
    $by = sk_item_index($d);
    $out = array();
    foreach ($o['items'] as $l) {
        if (isset($by[$l['i']])) {
            $out[] = array($d['items'][$by[$l['i']]], isset($l['q']) ? $l['q'] : null);
        }
    }
    return $out;
}

/** [id listu => název] */
function sk_section_names($d)
{
    $out = array();
    foreach ($d['sections'] as $s) {
        $out[$s['id']] = $s['name'];
    }
    return $out;
}

/**
 * Vrácení jedné výpůjčky. $back = kolik se vrátilo (null = všechno). Co se nevrátilo:
 *   $keep  člověk si to ještě nechává: výpůjčka zůstane s tím, co zbývá;
 *   jinak  spotřebovalo se nebo ztratilo: o tolik ubude z počtu věci.
 * U výpůjčky, u které se neví kolik (ze starší verze), se vrací všechno a počet se nemění.
 * Vrací [věc (už po změně), výpůjčka (jak byla), vráceno, ubylo, zbývá u člověka].
 */
function sk_loan_back(&$d, $id, $back, $keep)
{
    $at = sk_loan_find($d, $id);
    if ($at === null) {
        throw new SkError('Tahle výpůjčka už není. Mezitím ji někdo vrátil nebo smazal.', 404);
    }
    list($k, $j) = $at;
    $it = $d['items'][$k];
    $l = $it['l'][$j];
    $u = sk_unit($it);
    $lost = 0;
    $rest = 0;
    if (!isset($l['q'])) {
        $back = null;
    } else {
        if ($back === null) {
            $back = $l['q'];
        }
        if (!is_int($back) || $back < 0 || $back > $l['q']) {
            throw new SkError('Vrátit jde 0 až ' . sk_qty_cs($l['q'], $u) . ' (tolik si ' . $l['who'] . ' vzal).');
        }
        if ($back === 0 && $keep) {
            throw new SkError('Nevrátil nic a všechno si nechává: výpůjčka zůstává, jak je.');
        }
        if ($back < $l['q']) {
            if ($keep) {
                $rest = $l['q'] - $back;
            } else {
                $lost = $l['q'] - $back;
            }
        }
    }
    if ($rest > 0) {
        $d['items'][$k]['l'][$j]['q'] = $rest;
    } else {
        array_splice($d['items'][$k]['l'], $j, 1);
        if (!$d['items'][$k]['l']) {
            unset($d['items'][$k]['l']);
        }
    }
    $count = sk_count($it);
    if ($lost > 0 && $count !== null) {
        $d['items'][$k]['q'] = max(0, $count - $lost);
        $d['items'][$k]['t'] = max(time(), (isset($it['t']) ? (int) $it['t'] : 0) + 1);       // změnil se počet: otevřený formulář věci by ho přepsal
    }
    sk_touch($d, $it['s']);
    if ($back === null) {
        $what = 'vráceno (' . $l['who'] . ')';
    } else {
        $what = 'vráceno ' . sk_part_cs($back, $l['q'], $u) . ' (' . $l['who'] . ')';
        if ($rest > 0) {
            $what .= ', ' . sk_qty_cs($rest, $u) . ' si ještě nechává';
        } elseif ($lost > 0) {
            $what .= ', ubylo ' . sk_qty_cs($lost, $u) . ($count !== null ? ' (počet ' . sk_qty_cs($count, $u) . ' → ' . sk_qty_cs(max(0, $count - $lost), $u) . ')' : '');
        }
    }
    sk_log($d, $what, $it['n']);
    return array($d['items'][$k], $l, $back, $lost, $rest);
}

/**
 * Vrátí víc výpůjček najednou. $list = [{id, back, keep}] (back = kolik se vrátilo, chybí-li, tak všechno;
 * keep = zbytek si člověk nechává, jinak ubude), nebo jen [id, …] = každá celá. Výpůjčky, které už mezitím
 * někdo vrátil, se přeskočí. Vrací [[věc, {loan, back, lost, rest}]].
 */
function sk_loans_back(&$d, $list)
{
    $done = array();
    $seen = array();
    foreach ((is_array($list) ? $list : array()) as $x) {
        if (is_string($x)) {
            $x = array('id' => $x);
        }
        if (!is_array($x) || !isset($x['id']) || !is_string($x['id']) || isset($seen[$x['id']]) || sk_loan_find($d, $x['id']) === null) {
            continue;
        }
        $seen[$x['id']] = true;
        $back = null;
        if (array_key_exists('back', $x) && $x['back'] !== null) {
            $back = sk_amount($x['back'], true);
            if ($back === null) {
                throw new SkError('Napiš, kolik toho vrátil (celé číslo).');
            }
        }
        if ($back === 0 && !empty($x['keep'])) {
            continue;       // nevrátil nic a všechno si nechává: výpůjčka zůstane, jak je
        }
        list($it, $l, $b, $lost, $rest) = sk_loan_back($d, $x['id'], $back, !empty($x['keep']));
        $done[] = array($it, array('loan' => $l, 'back' => $b, 'lost' => $lost, 'rest' => $rest));
    }
    return $done;
}

/**
 * Úprava výpůjčky (kdo, kolik, do kdy). Víc kusů nebo pozdější vrácení se musí vejít do toho, co je v tu dobu volné.
 * Méně kusů je oprava zápisu (nevzal si tolik), ne vrácení: počet věci se nemění.
 */
function sk_loan_edit(&$d, $in, $now)
{
    $src = isset($in['loan']) && is_array($in['loan']) ? $in['loan'] : array();
    $at = sk_loan_find($d, isset($src['id']) ? $src['id'] : '');
    if ($at === null) {
        throw new SkError('Tahle výpůjčka už není. Mezitím ji někdo vrátil nebo smazal.', 404);
    }
    list($k, $j) = $at;
    $it = $d['items'][$k];
    $old = $it['l'][$j];
    $who = sk_text(isset($src['who']) ? $src['who'] : '', 200);
    if ($who === '') {
        throw new SkError('Napiš, kdo to má.');
    }
    $q = sk_amount(isset($src['q']) ? $src['q'] : null);
    if ($q === null) {
        throw new SkError('Napiš, kolik si toho vzal (celé číslo ' . sk_unit_in(sk_unit($it)) . ').');
    }
    $due = sk_date(isset($src['due']) ? $src['due'] : '');      // i minulé datum: oprava zápisu („měl to vrátit už v pondělí“)
    $today = date('Y-m-d', $now);
    $oldDue = isset($old['due']) ? (string) $old['due'] : '';
    $more = !isset($old['q']) ? false : $q > $old['q'];
    // pozdější vrácení: drží věc déle (bez data vrácení = pořád; minulé datum už teď drží pořád).
    // Minulé datum je ale oprava zápisu, ne nové půjčení: věc je po termínu jako každá jiná nevrácená
    // a objednávka, která s ní počítala, počká (stejně jako kdyby termín minul sám).
    $oldEnd = $oldDue !== '' && strcmp($oldDue, $today) >= 0 ? $oldDue : null;
    $newEnd = $due !== '' && strcmp($due, $today) >= 0 ? $due : null;
    $past = $due !== '' && strcmp($due, $today) < 0;
    $longer = !$past && $oldEnd !== null && ($newEnd === null || strcmp($newEnd, $oldEnd) > 0);
    if ($more || $longer) {
        sk_fit($d, array(array($k, $q)), $today, $newEnd, $today, '', $old['id']);
    }
    $new = $old;
    $new['who'] = $who;
    $new['q'] = $q;
    $new['due'] = $due;
    $d['items'][$k]['l'][$j] = sk_loan($new);
    $u = sk_unit($it);
    $parts = array();
    if ($who !== $old['who']) {
        $parts[] = 'má to ' . $who . ' (dřív ' . $old['who'] . ')';
    }
    if (!isset($old['q']) || $q !== $old['q']) {
        $parts[] = 'kolik: ' . sk_qty_cs($q, $u) . (isset($old['q']) ? ' (dřív ' . sk_qty_cs($old['q'], $u) . ')' : '');
    }
    if ($due !== $oldDue) {
        $parts[] = $due !== '' ? 'vrátit do ' . sk_date_cs($due) : 'bez termínu vrácení';
    }
    if ($parts) {
        sk_touch($d, $it['s']);
        sk_log($d, 'upravena výpůjčka: ' . implode(', ', $parts), $it['n']);
    }
    return $old['id'];
}

/* ───────────────────────── import z tabulky ─────────────────────────
 * Soubor CSV čte a sloupce přiřazuje prohlížeč (ukáže náhled, dá vybrat, co je co). Sem přijdou už hotové řádky
 * s poli věcí. Server je zkontroluje stejně jako každou jinou změnu, stav před importem si odloží mezi zálohy
 * a celé to zapíše najednou: buď všechno, nebo nic.
 */

/**
 * Pole věci, která jde importovat. „u“ je jednotka počtu (ks, g, kg, l, ml) a patří k poli „q“; rc, rn a pc jsou ceny
 * a tg tagy (nejsou to pole listu, má je každá věc). Výpůjčky se neimportují.
 */
function sk_import_fields()
{
    return array('q', 'u', 'ok', 'p1', 'p2', 'mk', 'own', 'cat', 'tg', 'kw', 'ph', 'note', 'rc', 'rn', 'pc', 'ty');
}

/**
 * Počet z tabulky: [číslo nebo null, „g“ nebo „“ (kusy), text, když to číslo není].
 * Jednotka ze sloupce (ks, g, kg) platí pro holé číslo; „2,5 kg“ nebo „4x“ v buňce počtu se pozná i bez ní.
 */
function sk_import_qty($q, $u)
{
    if (is_int($q) || is_float($q)) {
        $q = str_replace('.', ',', (string) $q);
    }
    $t = is_string($q) ? trim(str_replace("\xC2\xA0", ' ', $q)) : '';
    if ($t === '') {
        return array(null, '', '');
    }
    $units = array(
        'ks' => 'ks', 'kus' => 'ks', 'kusy' => 'ks', 'kusu' => 'ks', 'x' => 'ks',
        'g' => 'g', 'gram' => 'g', 'gramy' => 'g', 'gramu' => 'g',
        'kg' => 'kg', 'kilo' => 'kg', 'kila' => 'kg', 'kilogram' => 'kg', 'kilogramy' => 'kg', 'kilogramu' => 'kg',
        'l' => 'l', 'litr' => 'l', 'litry' => 'l', 'litru' => 'l', 'ml' => 'ml', 'mililitr' => 'ml', 'mililitry' => 'ml', 'mililitru' => 'ml', 'dl' => 'dl',
    );
    $uk = is_string($u) ? sk_fold(trim($u)) : '';
    if (isset($units[$uk]) && preg_match('/^\d[\d .,]*\z/', $t)) {
        $t .= ' ' . $units[$uk];
    }
    $g = sk_qty_guess($t);
    if ($g === null) {
        return array(null, '', sk_text($q, 120));
    }
    return array($g[0], $g[1] === 'ks' ? '' : $g[1], '');
}

/**
 * Hromadný import věcí. $in:
 *   mode    add = přidat jako nové | update = upravit existující (podle kódu, jinak podle názvu v listu), ostatní přidat
 *           | replace = věci v listech, kterých se import týká, nahradit těmi ze souboru
 *   def     list pro řádky bez listu (nové věci a hledání podle názvu); věc nalezená podle kódu ve svém listu zůstane
 *   lists   nové listy: [{key, name, nl}]; řádky na ně ukazují svým „key“
 *   labels  {pole: popisek ze záhlaví}: pro pole, která list ještě nemá a import je naplní
 *   rows    [{s?: id listu nebo key nového, id?: kód věci, n?: název, tga?: tagy k přidání, …pole}]
 *           při úpravě se mění jen pole, která v řádku jsou; u věci nalezené podle kódu stačí kód (název a list zůstanou);
 *           tga přidá tagy k těm, které věc má (tg je nahradí); ty = id typu položky (neznámé se zahodí a typ věci zůstane)
 * Vrací souhrn: added, updated, removed, skipped, lists (názvy nových listů), fields (přidaná pole).
 */
function sk_import(&$d, $in)
{
    $mode = isset($in['mode']) && in_array($in['mode'], array('add', 'update', 'replace'), true) ? $in['mode'] : 'add';
    $defSec = isset($in['def']) && is_string($in['def']) ? $in['def'] : '';
    $rows = isset($in['rows']) && is_array($in['rows']) ? array_values($in['rows']) : array();
    if (!$rows) {
        throw new SkError('V souboru nejsou žádné řádky s věcmi.');
    }
    if (count($rows) > 5000) {
        throw new SkError('Najednou jde nahrát nejvýš 5000 řádků. Rozděl soubor na víc částí.');
    }
    $def = sk_field_defaults();
    $labels = array();
    foreach ((isset($in['labels']) && is_array($in['labels']) ? $in['labels'] : array()) as $k => $v) {
        if (isset($def[$k])) {
            $l = sk_text($v, 80);
            if ($l !== '') {
                $labels[$k] = $l;
            }
        }
    }

    // nové listy (list téhož jména, který už je, se použije)
    $keys = array();
    $made = array();
    foreach ((isset($in['lists']) && is_array($in['lists']) ? $in['lists'] : array()) as $l) {
        if (!is_array($l) || !isset($l['key']) || !is_string($l['key']) || $l['key'] === '') {
            continue;
        }
        $name = sk_text(isset($l['name']) ? $l['name'] : '', 60);
        foreach ($d['sections'] as $s) {
            if ($name !== '' && sk_fold($s['name']) === sk_fold($name)) {
                $keys[$l['key']] = $s['id'];
                continue 2;
            }
        }
        $sec = sk_section(array('name' => $name, 'nl' => isset($l['nl']) ? $l['nl'] : '', 'f' => isset($labels['q']) ? array('q' => $labels['q']) : array()));
        if ($sec === null) {
            throw new SkError('Nový list ze souboru potřebuje název.');
        }
        if (count($d['sections']) >= 40) {
            throw new SkError('Víc než 40 listů se sem nevejde.');
        }
        $sec['id'] = sk_new_id($d['sections']);
        $d['sections'][] = $sec;
        $keys[$l['key']] = $sec['id'];
        $made[] = $sec['name'];
    }
    $secIds = array();
    foreach ($d['sections'] as $s) {
        $secIds[] = $s['id'];
    }
    $typeIds = sk_type_ids($d);

    // řádky
    $fields = sk_import_fields();
    $parsed = array();
    $skipped = 0;
    $byId0 = $mode === 'update' ? sk_item_index($d) : array();
    foreach ($rows as $r) {
        if (!is_array($r)) {
            $skipped++;
            continue;
        }
        $code = isset($r['id']) && is_string($r['id']) ? $r['id'] : '';      // kód věci (k úpravě té pravé)
        $old = $code !== '' && isset($byId0[$code]) ? $d['items'][$byId0[$code]] : null;
        $s = isset($r['s']) && is_string($r['s']) ? $r['s'] : '';
        if (isset($keys[$s])) {
            $s = $keys[$s];
        }
        if ($s === '') {
            $s = $old !== null ? $old['s'] : $defSec;       // bez listu: věc nalezená podle kódu zůstane, kde je
        }
        $r['s'] = $s;
        $byCode = false;                                    // řádek jen s kódem: upraví tu věc, nic nového z něj nevznikne
        if ($old !== null && sk_text(isset($r['n']) ? $r['n'] : '', 200) === '') {
            $r['n'] = $old['n'];
            $byCode = true;
        }
        $plus = array_key_exists('tga', $r) ? sk_tags($r['tga']) : array();     // tagy k přidání: ty, co věc má, zůstanou
        unset($r['id'], $r['t'], $r['l'], $r['qt'], $r['tga']);
        if (array_key_exists('q', $r)) {
            list($qn, $qu, $qt) = sk_import_qty($r['q'], isset($r['u']) ? $r['u'] : '');
            $r['q'] = $qn;
            $r['u'] = $qu;
            $r['qt'] = $qt;
        } else {
            unset($r['u']);
        }
        $it = sk_item($r, $secIds, $typeIds);
        if ($it === null) {
            $skipped++;
            continue;
        }
        $has = array();
        foreach ($fields as $k) {
            if (array_key_exists($k, $r)) {
                $has[$k] = true;
            }
        }
        foreach (array('rc', 'pc') as $k) {
            if (isset($has[$k]) && is_string($r[$k]) && trim($r[$k]) !== '' && $it[$k] === null) {
                unset($has[$k]);        // nesmysl místo ceny cenu, kterou věc má, nesmaže (jako text místo počtu)
            }
        }
        if (isset($has['ty']) && is_string($r['ty']) && trim($r['ty']) !== '' && $it['ty'] === '') {
            unset($has['ty']);          // typ, který sklad nezná, typ věci nesmaže
        }
        $parsed[] = array($it, $has, $code, $plus, $byCode);
    }
    if (!$parsed) {
        throw new SkError('Žádný řádek nemá název věci a list, kam patří. Nic se nenahrálo.');
    }

    // nahradit: věci v dotčených listech pryč (jen když žádná z nich není půjčená ani objednaná)
    $removed = 0;
    if ($mode === 'replace') {
        $targets = array();
        foreach ($parsed as $p) {
            $targets[$p[0]['s']] = true;
        }
        $busy = array();
        foreach ($d['items'] as $it) {
            if (isset($targets[$it['s']]) && sk_item_busy($d, $it)) {
                $busy[] = '„' . $it['n'] . '“';
            }
        }
        if ($busy) {
            $n = count($busy);
            throw new SkError('Nahradit nejde: ' . implode(', ', array_slice($busy, 0, 6)) . ($n > 6 ? ' a další (' . $n . ')' : '')
                . ($n === 1 ? ' je půjčené nebo objednané' : ' jsou půjčené nebo objednané') . ' a s nahrazením by výpůjčky a objednávky zmizely. '
                . 'Vyber „Upravit existující“, nebo nejdřív zapiš vrácení a zruš objednávky.', 409);
        }
        $keep = array();
        foreach ($d['items'] as $it) {
            if (isset($targets[$it['s']])) {
                $removed++;
            } else {
                $keep[] = $it;
            }
        }
        $d['items'] = $keep;
    }

    // párování při úpravě: podle kódu, jinak podle názvu v listu (stejné názvy se párují popořadě)
    $byId = sk_item_index($d);
    $byName = array();
    foreach ($d['items'] as $k => $it) {
        $byName[$it['s'] . '|' . sk_fold($it['n'])][] = $k;
    }
    $taken = array();
    $added = 0;
    $updated = 0;
    $used = array();
    $kept = 0;          // řádky, kde počet číslem nebyl a věc ho číslem už měla: zůstal ten její
    foreach ($parsed as $p) {
        list($it, $has, $code, $plus, $byCode) = $p;
        $k = -1;
        if ($mode === 'update') {
            if ($code !== '' && isset($byId[$code]) && !isset($taken[$byId[$code]])) {
                $k = $byId[$code];
            } elseif ($byCode) {
                $skipped++;                                 // tentýž kód podruhé a bez názvu: věc už je upravená
                continue;
            } else {
                $key = $it['s'] . '|' . sk_fold($it['n']);
                while (!empty($byName[$key]) && isset($taken[$byName[$key][0]])) {
                    array_shift($byName[$key]);
                }
                if (!empty($byName[$key])) {
                    $k = array_shift($byName[$key]);
                }
            }
        }
        if ($k >= 0) {
            $old = $d['items'][$k];
            $new = sk_item($old, $secIds);          // výpůjčky zůstávají
            $new['s'] = $it['s'];
            $new['n'] = $it['n'];
            foreach ($has as $f => $x) {
                if ($f === 'u') {
                    continue;                       // patří k počtu
                }
                if ($f === 'q') {
                    if ($it['q'] !== null) {
                        sk_count_check($d, $old, $it['q'], sk_unit($it));
                        $new['q'] = $it['q'];
                        $new['u'] = $it['u'];
                        $new['qt'] = '';
                    } elseif ($new['q'] !== null) {
                        $kept++;                    // číslo se textem nepřepisuje
                    } elseif ($it['qt'] !== '') {
                        $new['qt'] = $it['qt'];
                    }
                    continue;
                }
                if ($f === 'ph') {
                    // nahrané fotky zůstávají, odkazy se vezmou ze souboru
                    $ph = array();
                    foreach ($new['ph'] as $ph0) {
                        if (isset($ph0['f'])) {
                            $ph[] = $ph0;
                        }
                    }
                    $new['ph'] = sk_photos(array_merge($ph, $it['ph']));
                } else {
                    $new[$f] = $it[$f];
                }
            }
            if ($plus) {
                $new['tg'] = sk_tags(array_merge($new['tg'], $plus));
            }
            $new['id'] = $old['id'];
            $new['t'] = max(time(), (isset($old['t']) ? (int) $old['t'] : 0) + 1);
            $d['items'][$k] = sk_pack($new);
            $taken[$k] = true;
            $updated++;
            if ($old['s'] !== $new['s']) {
                sk_touch($d, $old['s']);
            }
        } else {
            if ($plus) {
                $it['tg'] = sk_tags(array_merge($it['tg'], $plus));
            }
            $it['l'] = array();
            $it['id'] = sk_new_id($d['items']);
            $it['t'] = time();
            $d['items'][] = sk_pack($it);
            $byId[$it['id']] = count($d['items']) - 1;
            $added++;
        }
        foreach ($has as $f => $x) {
            $v = $it[$f];
            if (!in_array($f, array('u', 'rc', 'rn', 'pc', 'tg', 'ty'), true) && $v !== '' && $v !== null && $v !== array()) {
                $used[$it['s']][$f] = true;
            }
        }
        sk_touch($d, $it['s']);
    }
    if (count($d['items']) > 20000) {
        throw new SkError('Ve skladu by bylo přes 20 000 věcí. Tolik se sem nevejde.');
    }

    // listy dostanou pole, která import naplnil a která ještě neměly (jinak by data nebyla vidět)
    $newFields = array();
    foreach ($d['sections'] as $si => $s) {
        if (empty($used[$s['id']])) {
            continue;
        }
        foreach ($used[$s['id']] as $f => $x) {
            if (!isset($s['f'][$f])) {
                $d['sections'][$si]['f'][$f] = isset($labels[$f]) ? $labels[$f] : $def[$f];
                $newFields[] = $s['name'] . ': ' . $d['sections'][$si]['f'][$f];
            }
        }
    }
    sk_orders_prune($d);
    $nocount = 0;
    foreach ($d['items'] as $it) {
        if (sk_count($it) === null) {
            $nocount++;
        }
    }

    $parts = array();
    if ($added) {
        $parts[] = 'přidáno ' . $added;
    }
    if ($updated) {
        $parts[] = 'upraveno ' . $updated;
    }
    if ($removed) {
        $parts[] = 'nahrazeno (smazáno) ' . $removed;
    }
    if ($skipped) {
        $parts[] = 'přeskočeno ' . $skipped;
    }
    if ($made) {
        $parts[] = 'nové listy: ' . implode(', ', $made);
    }
    sk_log($d, 'nahráno z tabulky (' . implode(', ', $parts) . ')', '');
    return array('added' => $added, 'updated' => $updated, 'removed' => $removed, 'skipped' => $skipped, 'lists' => $made, 'fields' => $newFields,
        'kept' => $kept, 'nocount' => $nocount);
}

/* ───────────────────────── rozhraní pro prohlížeč ─────────────────────────
 * Adresa  …?api=název . Čtení smí každý, kdo vidí přehled; měnit smí jen přihlášený správce nebo uživatel,
 * a to jen metodou POST s kódem v hlavičce X-Sklad-Csrf. Discord, hesla, uživatele, název, písmo, typy položek a zálohy smí jen hlavní správce.
 * Každá změna vrátí celá čerstvá data, takže prohlížeč vidí i to, co mezitím změnil někdo jiný.
 */

function sk_api($name)
{
    sk_headers();
    header('Content-Type: application/json; charset=utf-8');
    sk_gzip();
    try {
        $out = sk_api_run((string) $name);
        echo sk_json(array('ok' => true) + $out);
    } catch (SkError $e) {
        http_response_code($e->status);
        echo sk_json(array('ok' => false, 'error' => $e->getMessage()));
    } catch (Throwable $e) {
        http_response_code(500);
        echo sk_json(array('ok' => false, 'error' => 'Na serveru se něco pokazilo. Zkus to znovu.'));
    }
}

function sk_api_input()
{
    $raw = (string) @file_get_contents('php://input');
    if ($raw === '') {
        return array();
    }
    if (strlen($raw) > 6000000) {
        throw new SkError('Tolik dat najednou nepoberu.', 413);
    }
    $in = json_decode($raw, true);
    if (!is_array($in)) {
        throw new SkError('Požadavku nerozumím.');
    }
    return $in;
}

/** Změny jen metodou POST, z téže stránky a s kódem, který zná jen přihlášený prohlížeč. */
function sk_api_csrf()
{
    if (sk_method() !== 'POST') {
        throw new SkError('Tohle jde jen odeslat, ne otevřít.', 405);
    }
    $sent = isset($_SERVER['HTTP_X_SKLAD_CSRF']) ? (string) $_SERVER['HTTP_X_SKLAD_CSRF'] : '';
    $mine = sk_csrf();
    if ($sent === '' || $mine === '' || !hash_equals($mine, $sent) || sk_cross_site()) {
        throw new SkError('Stránka je moc dlouho otevřená. Načti ji znovu a zkus to ještě jednou.', 403);
    }
}

/** Vpustí jen přihlášeného správce nebo uživatele. */
function sk_api_admin()
{
    if (!sk_is_admin()) {
        throw new SkError('Přihlášení vypršelo. Přihlas se znovu.', 401);
    }
    sk_api_csrf();
}

/** Co smí jen hlavní správce: Discord (webhook, bot, lidé), hesla, uživatelé, typy položek a zálohy. Název a písmo hlídá settings.save. */
function sk_api_super_only($name)
{
    return in_array($name, array('hook.save', 'hook.check', 'hook.test', 'access.save', 'user.save', 'user.pass', 'user.del', 'super.save',
        'types.save', 'backup.list', 'backup.restore', 'import'), true);
}

function sk_api_run($name)
{
    if (!sk_installed()) {
        throw new SkError('Sklad ještě není založený.', 409);
    }

    /* ── čtení ── */
    if ($name === 'data') {
        if (!sk_can_view()) {
            throw new SkError('Nejdřív zadej heslo.', 401);
        }
        return array('data' => sk_data_public(sk_data()), 'tick' => sk_hook_tick_due());
    }

    // připomínky na Discord: smí zavolat kdokoli, pošle se jen to, co má zrovna odejít, a nic se nevrací
    if ($name === 'tick') {
        return array('ran' => sk_hook_tick() >= 0);
    }

    if ($name === 'logout') {
        sk_api_csrf();
        sk_logout();
        return array();
    }

    /* ── všechno ostatní smí jen správce (a uživatelé, co nepatří hlavnímu správci) ── */
    sk_api_admin();
    if (sk_api_super_only($name) && !sk_is_super()) {
        throw new SkError('Tohle smí jen hlavní správce.', 403);
    }

    switch ($name) {
        /* ── věci ── */
        case 'item.save':
            $in = sk_api_input();
            $events = array();      // co se má oznámit na Discord: [druh, věc, název listu]
            list($item, $d) = sk_mutate(function (&$d) use ($in, &$events) {
                $ids = array();
                $names = array();
                foreach ($d['sections'] as $s) {
                    $ids[] = $s['id'];
                    $names[$s['id']] = $s['name'];
                }
                $src = isset($in['item']) && is_array($in['item']) ? $in['item'] : null;
                if ($src !== null && isset($src['q']) && is_string($src['q']) && sk_amount($src['q'], true) === null) {
                    $g = sk_qty_guess($src['q']);         // „3 ks“, „2,5 kg“ jde napsat i takhle
                    if ($g !== null) {
                        $src['q'] = $g[0];
                        $src['u'] = $g[1];
                    }
                }
                $it = sk_item($src, $ids, sk_type_ids($d));
                if ($it === null) {
                    throw new SkError('Věc potřebuje název a list, do kterého patří.');
                }
                if ($it['q'] === null) {
                    throw new SkError('Napiš počet: kolik toho máte, i s tím, co je půjčené. Celé číslo, u potravin v gramech nebo kusech.');
                }
                $it['qt'] = '';
                $pos = $it['id'] !== '' ? sk_find($d['items'], $it['id']) : -1;
                $old = $pos >= 0 ? $d['items'][$pos] : null;
                if ($old !== null && !array_key_exists('ty', $src)) {
                    $it['ty'] = isset($old['ty']) ? $old['ty'] : '';        // stránka typ neposlala (stará, otevřená od včera): zůstane, jaký byl
                }
                // výpůjčky se mění jen vlastními tlačítky (Vrátit, Upravit výpůjčku), ne formulářem věci
                $it['l'] = $old !== null && isset($old['l']) ? $old['l'] : array();
                if ($old !== null) {
                    $oldT = isset($old['t']) ? (int) $old['t'] : 0;
                    if (isset($in['t']) && (int) $in['t'] !== $oldT) {
                        throw new SkError('Tuhle věc mezitím upravil někdo jiný. Zavři okno, podívej se na aktuální stav a uprav ji znovu.', 409);
                    }
                    sk_count_check($d, $old, $it['q'], sk_unit($it));
                    $it['t'] = max(time(), $oldT + 1);      // každé uložení dostane jiné číslo, i dvě v téže vteřině
                    $d['items'][$pos] = sk_pack($it);
                    if ($old['s'] !== $it['s']) {
                        sk_touch($d, $old['s']);
                    }
                    sk_log($d, sk_item_change($old, $it, $d['types']), $it['n']);
                } else {
                    $it['id'] = sk_new_id($d['items']);
                    $it['t'] = time();
                    $d['items'][] = sk_pack($it);
                    sk_log($d, 'přidáno', $it['n']);
                    $events[] = array('item', $it, $names[$it['s']]);
                }
                sk_touch($d, $it['s']);
                return $it['id'];
            });
            sk_photo_gc($d);
            $out = array('id' => $item, 'data' => sk_data_public($d));
            $err = sk_hook_events($events);         // až po uložení a mimo zámek dat: sklad na Discord nečeká
            if ($err !== '') {
                $out['discordError'] = $err;
            }
            return sk_api_notes($out);

        case 'item.del':
            $in = sk_api_input();
            list(, $d) = sk_mutate(function (&$d) use ($in) {
                $pos = sk_find($d['items'], isset($in['id']) ? $in['id'] : '');
                if ($pos < 0) {
                    throw new SkError('Tahle věc už v seznamu není.', 404);
                }
                $old = $d['items'][$pos];
                sk_lent_guard(array($old));
                array_splice($d['items'], $pos, 1);
                sk_touch($d, $old['s']);
                sk_log($d, 'smazáno', $old['n']);
                sk_orders_prune($d);
            });
            sk_photo_gc($d);
            return array('data' => sk_data_public($d));

        /* ── objednávky a hromadné vracení ── */
        case 'order.save':
            $in = sk_api_input();
            $events = array();
            $now = sk_now();
            list($res, $d) = sk_mutate(function (&$d) use ($in, $now, &$events) {
                return sk_order_save($d, $in, $now, $events);
            });
            $out = $res + array('data' => sk_data_public($d));
            $err = sk_hook_events($events);
            if ($err !== '') {
                $out['discordError'] = $err;
            }
            return sk_api_notes($out);

        case 'order.del':
            $in = sk_api_input();
            list(, $d) = sk_mutate(function (&$d) use ($in) {
                $pos = sk_find($d['orders'], isset($in['id']) ? $in['id'] : '');
                if ($pos < 0) {
                    throw new SkError('Tahle objednávka už není. Mezitím ji někdo vydal nebo zrušil.', 404);
                }
                $o = $d['orders'][$pos];
                array_splice($d['orders'], $pos, 1);
                sk_log($d, 'zrušena objednávka č. ' . $o['no'] . ' (' . $o['who'] . ', vyzvednutí ' . sk_date_cs($o['from']) . ')', sk_order_names($d, $o));
            });
            return array('data' => sk_data_public($d));

        case 'order.issue':
            // vydat hned: co čeká, až to někdo vrátí, jde k tomuhle člověku hned po vrácení, ne až v den z objednávky
            $in = sk_api_input();
            $now = sk_now();
            $events = array();
            list($res, $d) = sk_mutate(function (&$d) use ($in, $now, &$events) {
                return sk_order_issue_now($d, isset($in['id']) ? $in['id'] : '', $now, $events);
            });
            $out = $res + array('data' => sk_data_public($d));
            $err = sk_hook_events($events);
            if ($err !== '') {
                $out['discordError'] = $err;
            }
            return sk_api_notes($out);

        /* ── výpůjčky ── */
        case 'loan.save':
            $in = sk_api_input();
            $now = sk_now();
            list($id, $d) = sk_mutate(function (&$d) use ($in, $now) {
                return sk_loan_edit($d, $in, $now);
            });
            return array('id' => $id, 'data' => sk_data_public($d));

        case 'loan.back':
            $in = sk_api_input();
            list($res, $d) = sk_mutate(function (&$d) use ($in) {
                $back = array_key_exists('back', $in) && $in['back'] !== null ? sk_amount($in['back'], true) : null;
                if (array_key_exists('back', $in) && $in['back'] !== null && $back === null) {
                    throw new SkError('Napiš, kolik toho vrátil (celé číslo).');
                }
                return sk_loan_back($d, isset($in['id']) ? $in['id'] : '', $back, !empty($in['keep']));
            });
            list($it, $l, $back, $lost, $rest) = $res;
            $out = array('back' => $back, 'lost' => $lost, 'rest' => $rest, 'data' => sk_data_public($d));
            $err = sk_hook_events(array(array('back', $it, array('loan' => $l, 'back' => $back, 'lost' => $lost, 'rest' => $rest))));
            if ($err !== '') {
                $out['discordError'] = $err;
            }
            return $out;

        case 'loans.back':
            // víc výpůjček najednou: items = [{id, back, keep}] (kolik se u každé vrátilo), nebo ids = [id, …] (celé)
            $in = sk_api_input();
            list($done, $d) = sk_mutate(function (&$d) use ($in) {
                return sk_loans_back($d, isset($in['items']) ? $in['items'] : (isset($in['ids']) ? $in['ids'] : null));
            });
            $out = array('back' => count($done), 'data' => sk_data_public($d));
            if ($done) {
                $err = sk_hook_events(array(count($done) === 1 ? array('back', $done[0][0], $done[0][1]) : array('backs', null, $done)));
                if ($err !== '') {
                    $out['discordError'] = $err;
                }
            }
            return $out;

        /* ── počty a věci, které chyběly ── */
        case 'items.counts':
            $in = sk_api_input();
            list($n, $d) = sk_mutate(function (&$d) use ($in) {
                return sk_items_counts($d, isset($in['counts']) ? $in['counts'] : null);
            });
            return array('saved' => $n, 'data' => sk_data_public($d));

        case 'items.found':
            $in = sk_api_input();
            list($n, $d) = sk_mutate(function (&$d) use ($in) {
                $want = array();
                foreach ((isset($in['ids']) && is_array($in['ids']) ? $in['ids'] : array()) as $id) {
                    if (is_string($id)) {
                        $want[$id] = true;
                    }
                }
                $n = 0;
                foreach ($d['items'] as $k => $it) {
                    if (!isset($want[$it['id']]) || !sk_missing($it)) {
                        continue;
                    }
                    $d['items'][$k]['ok'] = true;
                    $d['items'][$k]['t'] = max(time(), (isset($it['t']) ? (int) $it['t'] : 0) + 1);
                    sk_touch($d, $it['s']);
                    sk_log($d, 'je zase ve skladu', $it['n']);
                    $n++;
                }
                return $n;
            });
            return array('found' => $n, 'data' => sk_data_public($d));

        /* ── import z tabulky ── */
        case 'items.import':
            $in = sk_api_input();
            list($res, $d) = sk_mutate(function (&$d) use ($in) {
                sk_snapshot('pred-importem');
                return sk_import($d, $in);
            });
            sk_photo_gc($d);
            return array('result' => $res, 'data' => sk_data_public($d));

        /* ── přání ── */
        case 'wish.save':
            $in = sk_api_input();
            list($id, $d) = sk_mutate(function (&$d) use ($in) {
                $w = sk_wish(isset($in['wish']) ? $in['wish'] : null);
                if ($w === null) {
                    throw new SkError('Napiš aspoň, co to je.');
                }
                $pos = $w['id'] !== '' ? sk_find($d['wishes'], $w['id']) : -1;
                if ($pos >= 0) {
                    $oldT = isset($d['wishes'][$pos]['t']) ? (int) $d['wishes'][$pos]['t'] : 0;
                    if (isset($in['t']) && (int) $in['t'] !== $oldT) {
                        throw new SkError('Tohle přání mezitím upravil někdo jiný. Zavři okno, podívej se na aktuální stav a uprav ho znovu.', 409);
                    }
                    $w['t'] = max(time(), $oldT + 1);
                    $d['wishes'][$pos] = sk_pack($w);
                    sk_log($d, 'upraveno přání', $w['n']);
                } else {
                    $w['id'] = sk_new_id($d['wishes']);
                    $w['t'] = time();
                    $d['wishes'][] = sk_pack($w);
                    sk_log($d, 'přidáno přání', $w['n']);
                }
                $d['wish']['upd'] = date('Y-m-d');
                return $w['id'];
            });
            return array('id' => $id, 'data' => sk_data_public($d));

        case 'wish.del':
            $in = sk_api_input();
            list(, $d) = sk_mutate(function (&$d) use ($in) {
                $pos = sk_find($d['wishes'], isset($in['id']) ? $in['id'] : '');
                if ($pos < 0) {
                    throw new SkError('Tohle přání už v seznamu není.', 404);
                }
                $old = $d['wishes'][$pos];
                array_splice($d['wishes'], $pos, 1);
                $d['wish']['upd'] = date('Y-m-d');
                sk_log($d, 'smazáno přání', $old['n']);
            });
            return array('data' => sk_data_public($d));

        /* ── listy ── */
        case 'section.save':
            $in = sk_api_input();
            list($id, $d) = sk_mutate(function (&$d) use ($in) {
                $src = isset($in['section']) && is_array($in['section']) ? $in['section'] : array();
                unset($src['upd']);
                $sec = sk_section($src);
                if ($sec === null) {
                    throw new SkError('List potřebuje název.');
                }
                $pos = $sec['id'] !== '' ? sk_find($d['sections'], $sec['id']) : -1;
                if ($pos >= 0) {
                    $sec['upd'] = isset($d['sections'][$pos]['upd']) ? $d['sections'][$pos]['upd'] : '';
                    $d['sections'][$pos] = $sec;
                    sk_log($d, 'upraven list', $sec['name']);
                } else {
                    if (count($d['sections']) >= 40) {
                        throw new SkError('Víc než 40 listů se sem nevejde.');
                    }
                    $sec['id'] = sk_new_id($d['sections']);
                    $d['sections'][] = $sec;
                    sk_log($d, 'přidán list', $sec['name']);
                }
                return $sec['id'];
            });
            return array('id' => $id, 'data' => sk_data_public($d));

        case 'section.del':
            $in = sk_api_input();
            list(, $d) = sk_mutate(function (&$d) use ($in) {
                $pos = sk_find($d['sections'], isset($in['id']) ? $in['id'] : '');
                if ($pos < 0) {
                    throw new SkError('Tenhle list už neexistuje.', 404);
                }
                if (count($d['sections']) < 2) {
                    throw new SkError('Poslední list smazat nejde. Aspoň jeden tu musí zůstat.');
                }
                $sec = $d['sections'][$pos];
                $to = isset($in['to']) && is_string($in['to']) ? $in['to'] : '';
                $n = 0;
                foreach ($d['items'] as $it) {
                    if ($it['s'] === $sec['id']) {
                        $n++;
                    }
                }
                if ($n > 0) {
                    if ($to !== '' && $to !== $sec['id'] && sk_find($d['sections'], $to) >= 0) {
                        foreach ($d['items'] as $k => $it) {
                            if ($it['s'] === $sec['id']) {
                                $d['items'][$k]['s'] = $to;
                            }
                        }
                        sk_touch($d, $to);
                    } elseif (!empty($in['purge'])) {
                        $keep = array();
                        $gone = array();
                        foreach ($d['items'] as $it) {
                            if ($it['s'] !== $sec['id']) {
                                $keep[] = $it;
                            } else {
                                $gone[] = $it;
                            }
                        }
                        sk_lent_guard($gone);
                        $d['items'] = $keep;
                    } else {
                        throw new SkError('Na listu ještě jsou věci. Vyber, kam je přesunout, nebo potvrď, že se mají smazat s ním.');
                    }
                }
                array_splice($d['sections'], $pos, 1);
                sk_log($d, 'smazán list', $sec['name'] . ($n > 0 ? ' (' . $n . ' věcí ' . ($to !== '' ? 'přesunuto' : 'smazáno') . ')' : ''));
                sk_orders_prune($d);
            });
            sk_photo_gc($d);
            return array('data' => sk_data_public($d));

        case 'section.order':
            $in = sk_api_input();
            list(, $d) = sk_mutate(function (&$d) use ($in) {
                $want = isset($in['ids']) && is_array($in['ids']) ? $in['ids'] : array();
                $by = array();
                foreach ($d['sections'] as $s) {
                    $by[$s['id']] = $s;
                }
                $out = array();
                foreach ($want as $id) {
                    if (is_string($id) && isset($by[$id])) {
                        $out[] = $by[$id];
                        unset($by[$id]);
                    }
                }
                $d['sections'] = array_merge($out, array_values($by));
            });
            return array('data' => sk_data_public($d));

        /* ── nastavení ── */
        case 'settings.save':
            $in = sk_api_input();
            list(, $d) = sk_mutate(function (&$d) use ($in) {
                if (isset($in['site'])) {
                    $site = sk_site($in['site']);
                    if ($site !== sk_site($d['site']) && !sk_is_super()) {
                        throw new SkError('Název a písmo mění jen hlavní správce.', 403);
                    }
                    $d['site'] = $site;
                }
                if (isset($in['marks'])) {
                    $d['marks'] = sk_marks($in['marks']);
                }
                if (isset($in['places'])) {
                    $d['places'] = sk_places($in['places']);
                }
                if (isset($in['wish']) && is_array($in['wish'])) {
                    $w = $in['wish'];
                    $w['upd'] = $d['wish']['upd'];
                    $d['wish'] = sk_wish_conf($w);
                }
                sk_log($d, 'změněno nastavení', '');
            });
            return array('data' => sk_data_public($d));

        case 'types.save':
            // celý seznam najednou, v pořadí, v jakém se má ukazovat; typ, který v seznamu chybí, zmizí i z věcí
            $in = sk_api_input();
            $src = isset($in['types']) && is_array($in['types']) ? $in['types'] : null;
            if ($src === null) {
                throw new SkError('Chybí seznam typů položek.');
            }
            $types = sk_types($src);
            $filled = 0;
            foreach ($src as $x) {
                if (is_array($x) && sk_text(isset($x['n']) ? $x['n'] : '', 80) !== '') {
                    $filled++;
                }
            }
            if ($filled > 60) {
                throw new SkError('Víc než 60 typů položek se sem nevejde.');
            }
            if ($filled > count($types)) {
                throw new SkError('Dva typy položek se nesmějí jmenovat stejně (velikost písmen a háčky nerozhodují).');
            }
            list($lost, $d) = sk_mutate(function (&$d) use ($types) {
                $keep = array();
                foreach ($types as $t) {
                    $keep[$t['id']] = true;
                }
                $n = 0;
                foreach ($d['items'] as $it) {
                    if (isset($it['ty']) && $it['ty'] !== '' && !isset($keep[$it['ty']])) {
                        $n++;
                    }
                }
                if ($n) {
                    sk_snapshot('pred-typy');
                    foreach ($d['items'] as $k => $it) {
                        if (isset($it['ty']) && $it['ty'] !== '' && !isset($keep[$it['ty']])) {
                            unset($d['items'][$k]['ty']);
                        }
                    }
                }
                $d['types'] = $types;
                sk_log($d, 'změněny typy položek (' . count($types) . ')' . ($n ? ', ' . $n . ' ' . sk_plural($n, 'věc', 'věci', 'věcí') . ' zůstalo bez typu' : ''), '');
                return $n;
            });
            return array('lost' => $lost, 'data' => sk_data_public($d));

        case 'access.save':
            $in = sk_api_input();
            $cur = isset($in['current']) && is_string($in['current']) ? $in['current'] : '';
            if (sk_try_password($cur, true) !== 'a') {
                throw new SkError('Současné heslo správce nesedí.', 403);
            }
            $newAdmin = isset($in['admin']) && is_string($in['admin']) ? $in['admin'] : '';
            $setView = array_key_exists('view', $in);
            $newView = $setView && is_string($in['view']) ? $in['view'] : '';
            if ($newAdmin !== '') {
                sk_password_check($newAdmin, 8, 'Heslo správce');
            }
            if ($setView && $newView !== '') {
                sk_password_check($newView, 4, 'Heslo pro nahlížení');
                if ($newView === ($newAdmin !== '' ? $newAdmin : $cur)) {
                    throw new SkError('Heslo pro nahlížení musí být jiné než heslo správce.');
                }
            }
            sk_locked(function () use ($newAdmin, $setView, $newView) {
                $a = sk_auth(true);
                if ($newAdmin !== '') {
                    $a['admin'] = password_hash($newAdmin, PASSWORD_DEFAULT);
                }
                if ($setView) {
                    $a['view'] = $newView !== '' ? password_hash($newView, PASSWORD_DEFAULT) : '';
                }
                if (!sk_auth_save($a)) {
                    throw new SkError('Heslo se nepodařilo uložit. Zkontroluj práva složky ' . SK_DATA . '.', 500);
                }
            });
            sk_login('a');
            return array('viewLocked' => sk_view_locked(), 'csrf' => sk_csrf());

        /* ── fotky ── */
        case 'photo.up':
            return array('photo' => sk_photo_upload());

        case 'item.photo':
            // přidá k věci nahranou fotku (tlačítko „Přidat fotku“ u věci): bez celého formuláře, a tak i u věci bez počtu
            $in = sk_api_input();
            $ph = sk_photos(array(isset($in['photo']) ? $in['photo'] : null));
            if (!$ph || !isset($ph[0]['f']) || !@is_file(sk_dir() . '/foto/' . $ph[0]['f'])) {
                throw new SkError('Fotka se nenahrála. Zkus to znovu.');
            }
            list($t, $d) = sk_mutate(function (&$d) use ($in, $ph) {
                $pos = sk_find($d['items'], isset($in['id']) && is_string($in['id']) ? $in['id'] : '');
                if ($pos < 0) {
                    throw new SkError('Tahle věc už ve skladu není.', 404);
                }
                $it = $d['items'][$pos];
                $list = isset($it['ph']) && is_array($it['ph']) ? $it['ph'] : array();
                if (count($list) >= 12) {
                    throw new SkError('Víc než 12 fotek se k jedné věci nevejde.');
                }
                $list[] = $ph[0];
                $d['items'][$pos]['ph'] = sk_photos($list);
                $d['items'][$pos]['t'] = max(time(), (isset($it['t']) ? (int) $it['t'] : 0) + 1);      // otevřený formulář věci by fotku přepsal
                sk_touch($d, $it['s']);
                sk_log($d, 'přidána fotka', $it['n']);
                return $d['items'][$pos]['t'];
            });
            return array('t' => $t, 'data' => sk_data_public($d));

        /* ── zálohy ── */
        case 'backup.list':
            return array('list' => sk_snapshots());

        case 'backup.restore':
            $in = sk_api_input();
            $name = isset($in['name']) && is_string($in['name']) ? $in['name'] : '';
            if (!preg_match('/^data-[0-9a-z-]{10,60}\z/', $name)) {
                throw new SkError('Takovou zálohu neznám.', 404);
            }
            $snap = sk_file_read(sk_dir() . '/zalohy/' . $name . '.php');
            if (!is_array($snap)) {
                throw new SkError('Zálohu se nepodařilo přečíst.', 404);
            }
            return array('data' => sk_data_public(sk_replace_data($snap, 'obnoveno ze zálohy', $name)));

        case 'import':
            $in = sk_api_input();
            if (!isset($in['data']) || !is_array($in['data'])) {
                throw new SkError('V souboru není záloha ' . SK_PRODUKT2 . '.');
            }
            return array('data' => sk_data_public(sk_replace_data($in['data'], 'nahrána záloha ze souboru', '')));

        case 'log':
            $d = sk_data();
            return array('log' => array_slice($d['log'], 0, 400));

        /* ── Discord ── */
        case 'hook.save':
            return sk_hook_save(sk_api_input());

        case 'hook.check':
            return array('hook' => sk_hook_check());

        case 'hook.test':
            return sk_api_notes(array('hook' => sk_hook_test(sk_api_input())));

        case 'loan.remind':
            $in = sk_api_input();
            return sk_api_notes(sk_hook_remind_now(isset($in['id']) ? $in['id'] : '') + array('hook' => sk_hook_public(sk_hook())));

        /* ── uživatelé (zakládá a spravuje je hlavní správce) ── */
        case 'user.save':
            // nový uživatel (jméno a heslo), nebo nové jméno pro stávajícího
            $in = sk_api_input();
            $id = isset($in['id']) && is_string($in['id']) ? $in['id'] : '';
            $uname = sk_user_name_check(isset($in['n']) ? $in['n'] : '', $id);
            $hash = '';
            if ($id === '') {
                $pass = isset($in['pass']) && is_string($in['pass']) ? $in['pass'] : '';
                sk_password_check($pass, 8, 'Heslo');
                $hash = password_hash($pass, PASSWORD_DEFAULT);
            }
            list($uid, $what) = sk_users_update(function (&$users) use ($id, $uname, $hash) {
                if ($id === '') {
                    if (count($users) >= 50) {
                        throw new SkError('Víc než 50 uživatelů se sem nevejde.');
                    }
                    $nid = sk_new_id($users);
                    $users[] = array('id' => $nid, 'n' => $uname, 'h' => $hash, 't' => time());
                    return array($nid, 'přidán uživatel');
                }
                foreach ($users as $k => $u) {
                    if ($u['id'] === $id) {
                        $users[$k]['n'] = $uname;
                        return array($id, 'přejmenován uživatel (dřív ' . $u['n'] . ')');
                    }
                }
                throw new SkError('Takový uživatel už není.', 404);
            });
            sk_mutate(function (&$d) use ($what, $uname) {
                sk_log($d, $what, $uname);
            });
            return array('id' => $uid, 'users' => sk_users_public());

        case 'user.pass':
            // nové heslo uživateli: kde je přihlášený, tam se tím odhlásí
            $in = sk_api_input();
            $id = isset($in['id']) && is_string($in['id']) ? $in['id'] : '';
            $pass = isset($in['pass']) && is_string($in['pass']) ? $in['pass'] : '';
            sk_password_check($pass, 8, 'Heslo');
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $uname = sk_users_update(function (&$users) use ($id, $hash) {
                foreach ($users as $k => $u) {
                    if ($u['id'] === $id) {
                        $users[$k]['h'] = $hash;
                        return $u['n'];
                    }
                }
                throw new SkError('Takový uživatel už není.', 404);
            });
            sk_mutate(function (&$d) use ($uname) {
                sk_log($d, 'nové heslo pro uživatele', $uname);
            });
            return array('users' => sk_users_public());

        case 'user.del':
            $in = sk_api_input();
            $id = isset($in['id']) && is_string($in['id']) ? $in['id'] : '';
            $uname = sk_users_update(function (&$users) use ($id) {
                foreach ($users as $k => $u) {
                    if ($u['id'] === $id) {
                        array_splice($users, $k, 1);
                        return $u['n'];
                    }
                }
                throw new SkError('Takový uživatel už není.', 404);
            });
            sk_mutate(function (&$d) use ($uname) {
                sk_log($d, 'smazán uživatel', $uname);
            });
            return array('users' => sk_users_public());

        case 'super.save':
            // jméno hlavního správce (ukazuje se v historii; jde se jím i přihlásit)
            $in = sk_api_input();
            $an = sk_text(isset($in['n']) ? $in['n'] : '', 60);
            if ($an !== '') {
                $an = sk_user_name_check($an, '', true);
            }
            sk_locked(function () use ($an) {
                $a = sk_auth(true);
                $a['an'] = $an;
                if (!sk_auth_save($a)) {
                    throw new SkError('Jméno se nepodařilo uložit. Zkontroluj práva složky ' . SK_DATA . '.', 500);
                }
            });
            return array('me' => sk_actor());

        case 'me.pass':
            // uživatel si mění svoje heslo (hlavní správce to má v části Hesla)
            $in = sk_api_input();
            $sess = sk_session();
            if ($sess['r'] !== 'u') {
                throw new SkError('Heslo hlavního správce se mění v Nastavení, v části Hesla.');
            }
            $u = sk_user($sess['i']);
            $cur = isset($in['current']) && is_string($in['current']) ? $in['current'] : '';
            $pass = isset($in['pass']) && is_string($in['pass']) ? $in['pass'] : '';
            $wait = sk_throttle_wait();
            if ($wait > 0) {
                throw new SkError('Moc pokusů za sebou. Zkus to znovu za ' . max(1, (int) ceil($wait / 60)) . ' min.', 429);
            }
            if ($u === null || !password_verify($cur, $u['h'])) {
                sk_throttle_fail();
                throw new SkError('Současné heslo nesedí.', 403);
            }
            sk_password_check($pass, 8, 'Nové heslo');
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            sk_users_update(function (&$users) use ($u, $hash) {
                foreach ($users as $k => $x) {
                    if ($x['id'] === $u['id']) {
                        $users[$k]['h'] = $hash;
                    }
                }
            });
            sk_login('u', $u['id']);         // nové heslo = nové přihlášení (ostatní prohlížeče s tím starým se odhlásí)
            return array('csrf' => sk_csrf());
    }
    throw new SkError('Tohle neumím.', 404);
}

/** Přidá k odpovědi, koho se cestou (ne)podařilo najít na Discordu, a s tím i čerstvý seznam lidí. */
function sk_api_notes($out)
{
    list($notes, $bad) = sk_hook_notes();
    if ($notes) {
        $out['discordNote'] = $notes;
        $out['hook'] = sk_hook_public(sk_hook());
        if ($bad) {
            $out['discordBad'] = true;
        }
    }
    return $out;
}

/** Uživatelé pro prohlížeč hlavního správce: bez otisků hesel. */
function sk_users_public()
{
    $out = array();
    foreach (sk_users() as $u) {
        $out[] = array('id' => $u['id'], 'n' => $u['n'], 't' => isset($u['t']) ? (int) $u['t'] : 0, 's' => isset($u['s']) ? (int) $u['s'] : 0);
    }
    return $out;
}

/**
 * Jméno uživatele: neprázdné a jiné než u ostatních uživatelů i hlavního správce (velikost písmen a háčky nevadí,
 * přihlašuje se jím). $exceptId = uživatel, který se přejmenovává; $forSuper = je to jméno hlavního správce.
 */
function sk_user_name_check($name, $exceptId, $forSuper = false)
{
    $n = sk_text($name, 60);
    $key = sk_name_key($n);
    if ($key === '') {
        throw new SkError($forSuper ? 'Jméno musí mít aspoň jedno písmeno nebo číslici.' : 'Uživatel potřebuje jméno (aspoň jedno písmeno nebo číslici).');
    }
    foreach (sk_users() as $u) {
        if ($u['id'] !== $exceptId && sk_name_key($u['n']) === $key) {
            throw new SkError('Uživatel „' . $u['n'] . '“ už je. Vyber jiné jméno.');
        }
    }
    if (!$forSuper) {
        $a = sk_auth();
        $an = isset($a['an']) && is_string($a['an']) ? $a['an'] : '';
        if (($an !== '' && sk_name_key($an) === $key) || $key === sk_name_key('hlavní správce')) {
            throw new SkError('Tak se jmenuje hlavní správce. Vyber jiné jméno.');
        }
    }
    return $n;
}

/** Změní seznam uživatelů pod zámkem: $fn(&$users) vrací výsledek. */
function sk_users_update($fn)
{
    return sk_locked(function () use ($fn) {
        $a = sk_auth(true);
        $users = isset($a['users']) && is_array($a['users']) ? array_values($a['users']) : array();
        $res = $fn($users);
        $a['users'] = array_values($users);
        if (!sk_auth_save($a)) {
            throw new SkError('Uživatele se nepodařilo uložit. Zkontroluj práva složky ' . SK_DATA . '.', 500);
        }
        return $res;
    });
}

/** Co se na věci změnilo, pro historii. */
function sk_item_change($old, $new, $types = array())
{
    $u = sk_unit($new);
    $was = sk_count($old);
    if ($was === null) {
        $text = 'zapsán počet ' . sk_qty_cs($new['q'], $u) . (isset($old['qt']) && $old['qt'] !== '' ? ' (v soupisu bylo „' . $old['qt'] . '“)' : '');
    } elseif ($was !== $new['q'] || sk_unit($old) !== $u) {
        $text = 'upraveno (počet ' . sk_qty_cs($was, sk_unit($old)) . ' → ' . sk_qty_cs($new['q'], $u) . ')';
    } else {
        $text = 'upraveno';
    }
    // typ položky se zapisuje jen tam, kde ho nová věc vůbec nese (hromadné doplnění počtů ho nemá)
    if (array_key_exists('ty', $new)) {
        $t0 = isset($old['ty']) ? $old['ty'] : '';
        $t1 = $new['ty'];
        if ($t0 !== $t1) {
            $nm = array();
            foreach ($types as $t) {
                $nm[$t['id']] = $t['n'];
            }
            $text .= ', typ položky: ' . ($t0 !== '' && isset($nm[$t0]) ? '„' . $nm[$t0] . '“' : 'nezadán') . ' → ' . ($t1 !== '' && isset($nm[$t1]) ? '„' . $nm[$t1] . '“' : 'nezadán');
        }
    }
    return $text;
}

/**
 * Smazat jde jen věc, kterou nikdo nemá půjčenou: výpůjčka by jinak zmizela i s tím, že se má něco vrátit.
 * Vyhodí chybu se jmény, když je některá z věcí půjčená.
 */
function sk_lent_guard($items)
{
    $out = array();
    foreach ($items as $it) {
        if (empty($it['l'])) {
            continue;
        }
        $who = array();
        foreach ($it['l'] as $l) {
            $who[] = $l['who'];
        }
        $out[] = '„' . $it['n'] . '“ (' . implode(', ', array_unique($who)) . ')';
    }
    if ($out) {
        $n = count($out);
        throw new SkError(($n === 1 ? 'Věc je půjčená: ' : 'Některé věci jsou půjčené: ') . implode(', ', array_slice($out, 0, 8)) . ($n > 8 ? ' a další' : '')
            . '. Nejdřív zapiš vrácení (co se nevrátí, zapiš jako vrácené 0), pak ' . ($n === 1 ? 'ji' : 'je') . ' jde smazat.', 409);
    }
}

/** Je věc půjčená nebo objednaná? Pak jí nejde změnit jednotku (počty u výpůjček a objednávek jsou v té staré). */
function sk_item_busy($d, $it)
{
    if (!empty($it['l'])) {
        return true;
    }
    foreach ($d['orders'] as $o) {
        foreach ($o['items'] as $l) {
            if ($l['i'] === $it['id']) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Smí mít věc nový počet $q v jednotce $u? Nesmí klesnout pod to, co je půjčené, a jednotka se nemění,
 * dokud je věc půjčená nebo objednaná. Když ne, vyhodí chybu.
 */
function sk_count_check($d, $it, $q, $u)
{
    if (sk_unit($it) !== $u && sk_item_busy($d, $it)) {
        throw new SkError('„' . $it['n'] . '“: jednotku nejde změnit, dokud je věc půjčená nebo objednaná (počty u výpůjček a objednávek jsou '
            . sk_unit_in(sk_unit($it)) . ').');
    }
    $lent = sk_lent_known($it);
    if ($q !== sk_count($it) && $q < $lent) {
        throw new SkError('„' . $it['n'] . '“: půjčeno je ' . sk_qty_cs($lent, $u) . ', tak počet nemůže být menší. Až se to vrátí, '
            . 'zapiš vrácení u výpůjčky (co se nevrátí, z počtu ubude samo).');
    }
}

/**
 * Doplní počty víc věcem najednou (okno „Doplnit počty“). $list = [{id, q, u}], prázdné q se přeskočí.
 * Buď se uloží všechny, nebo žádný. Vrací, kolik věcí dostalo nový počet.
 */
function sk_items_counts(&$d, $list)
{
    $by = sk_item_index($d);
    $n = 0;
    $names = array();
    $bad = array();
    foreach ((is_array($list) ? $list : array()) as $c) {
        if (!is_array($c) || !isset($c['id']) || !is_string($c['id']) || !isset($by[$c['id']])) {
            continue;
        }
        $q = sk_amount(isset($c['q']) ? $c['q'] : null, true);
        if ($q === null) {
            continue;
        }
        $k = $by[$c['id']];
        $it = $d['items'][$k];
        $u = sk_unit_ok(isset($c['u']) ? $c['u'] : '');
        try {
            sk_count_check($d, $it, $q, $u);
        } catch (SkError $e) {
            $bad[] = $e->getMessage();
            continue;
        }
        if (sk_count($it) === $q && sk_unit($it) === $u) {
            continue;
        }
        $what = sk_item_change($it, array('q' => $q, 'u' => $u));
        $it['q'] = $q;
        if ($u !== 'ks') {
            $it['u'] = $u;
        } else {
            unset($it['u']);
        }
        unset($it['qt']);
        $it['t'] = max(time(), (isset($it['t']) ? (int) $it['t'] : 0) + 1);
        $d['items'][$k] = $it;
        sk_touch($d, $it['s']);
        $names[] = array($what, $it['n']);
        $n++;
    }
    if ($bad) {
        throw new SkError(implode("\n", $bad));
    }
    if ($n > 5) {
        $all = array();
        foreach ($names as $x) {
            $all[] = $x[1];
        }
        sk_log($d, 'doplněny počty (' . $n . ' ' . sk_plural($n, 'věc', 'věci', 'věcí') . ')', implode(', ', $all));
    } else {
        foreach ($names as $x) {
            sk_log($d, $x[0], $x[1]);
        }
    }
    return $n;
}

function sk_password_check($p, $min, $what)
{
    $len = function_exists('mb_strlen') ? mb_strlen($p, 'UTF-8') : strlen($p);
    if ($len < $min) {
        throw new SkError($what . ' musí mít aspoň ' . $min . ' znaků.');
    }
    if (strlen($p) > 200) {
        throw new SkError($what . ' je moc dlouhé.');
    }
}

/** Vymění celá data (obnova, nahrání zálohy; smí jen hlavní správce). Stav před výměnou si odloží do zalohy/. */
function sk_replace_data($src, $what, $name)
{
    $clean = sk_data_clean($src);
    return sk_locked(function () use ($clean, $src, $what, $name) {
        $cur = sk_data();
        sk_snapshot('pred-obnovou');
        $clean['rev'] = (int) $cur['rev'] + 1;
        $clean['log'] = $cur['log'];
        sk_log($clean, $what, $name);
        if (!sk_file_write(sk_dir() . '/data.php', $clean)) {
            throw new SkError('Data se nepodařilo uložit. Zkontroluj práva složky ' . SK_DATA . '.', 500);
        }
        return $clean;
    });
}

/**
 * Přijme fotku zmenšenou už v prohlížeči (pole „f“) a její náhled („t“).
 * Bere jen skutečné obrázky JPEG, PNG a WebP a ukládá je pod vlastním náhodným názvem.
 */
function sk_photo_upload()
{
    if (!isset($_FILES['f']) || !is_array($_FILES['f']) || !isset($_FILES['f']['tmp_name']) || is_array($_FILES['f']['tmp_name'])) {
        throw new SkError('Fotka nedorazila. Možná je moc velká pro tenhle hosting.');
    }
    $main = sk_photo_check($_FILES['f'], 8000000);
    $base = bin2hex(random_bytes(10));
    $dir = sk_dir() . '/foto';
    if (!@is_dir($dir) && !@mkdir($dir, 0755, true)) {
        throw new SkError('Nejde založit složka na fotky. Zkontroluj práva složky ' . SK_DATA . '.', 500);
    }
    $name = $base . '.' . $main['ext'];
    if (!@move_uploaded_file($main['tmp'], $dir . '/' . $name)) {
        throw new SkError('Fotku se nepodařilo uložit. Zkontroluj práva složky ' . SK_DATA . '/foto.', 500);
    }
    if (function_exists('chmod')) {
        @chmod($dir . '/' . $name, 0644);
    }
    $out = array('f' => $name, 'w' => $main['w'], 'h' => $main['h']);
    if (isset($_FILES['t']) && is_array($_FILES['t']) && isset($_FILES['t']['tmp_name']) && !is_array($_FILES['t']['tmp_name'])) {
        try {
            $th = sk_photo_check($_FILES['t'], 400000);
            if ($th['ext'] === $main['ext'] && @move_uploaded_file($th['tmp'], $dir . '/' . $base . '-t.' . $th['ext'])) {
                if (function_exists('chmod')) {
                    @chmod($dir . '/' . $base . '-t.' . $th['ext'], 0644);
                }
                $out['th'] = 1;
            }
        } catch (SkError $e) {
            // bez náhledu se obejdeme, ukáže se velká fotka
        }
    }
    return $out;
}

function sk_photo_check($file, $maxBytes)
{
    $err = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        throw new SkError('Fotka je pro tenhle hosting moc velká.', 413);
    }
    if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
        throw new SkError('Fotka se nenahrála celá. Zkus to znovu.');
    }
    $size = (int) @filesize($file['tmp_name']);
    if ($size < 100 || $size > $maxBytes) {
        throw new SkError('Fotka je moc velká.', 413);
    }
    $info = @getimagesize($file['tmp_name']);
    $types = array(IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png');
    if (defined('IMAGETYPE_WEBP')) {
        $types[IMAGETYPE_WEBP] = 'webp';
    }
    if (!is_array($info) || !isset($types[$info[2]]) || $info[0] < 1 || $info[1] < 1 || $info[0] > 8000 || $info[1] > 8000) {
        throw new SkError('Tohle není fotka, kterou umím uložit (JPEG, PNG nebo WebP).');
    }
    return array('tmp' => $file['tmp_name'], 'ext' => $types[$info[2]], 'w' => (int) $info[0], 'h' => (int) $info[1]);
}

/* ───── soubory vydávané aplikací ───── */

/** Nahraná fotka ( …?foto=název , s  &t=1  náhled). Vidí ji jen ten, kdo vidí přehled. */
function sk_serve_photo($name, $thumb)
{
    sk_headers();
    if (!sk_installed() || !sk_can_view()) {
        http_response_code(403);
        return;
    }
    if (!is_string($name) || !preg_match('/^([a-f0-9]{20})\.(jpg|png|webp)\z/', $name, $m)) {
        http_response_code(404);
        return;
    }
    $dir = sk_dir() . '/foto/';
    $path = $dir . $name;
    if ($thumb && @is_file($dir . $m[1] . '-t.' . $m[2])) {
        $path = $dir . $m[1] . '-t.' . $m[2];
    }
    if (!@is_file($path)) {
        http_response_code(404);
        return;
    }
    $types = array('jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp');
    header('Content-Type: ' . $types[$m[2]]);
    header('Content-Length: ' . (int) @filesize($path));
    header('Cache-Control: private, max-age=31536000, immutable');
    @readfile($path);
}

/** Písma oddílu, když na serveru leží (viz hlavička souboru). */
function sk_fonts()
{
    static $f = null;
    if ($f !== null) {
        return $f;
    }
    $f = array('dir' => '', 'delm' => array(), 'titio' => false);
    $up = dirname(__DIR__);
    $cands = array(
        sk_dir() . '/pisma',
        __DIR__ . '/rozcestnik-data/pisma',
        $up . '/rozcestnik-data/pisma',
        __DIR__ . '/rozcestnik/rozcestnik-data/pisma',
        $up . '/rozcestnik/rozcestnik-data/pisma',
    );
    foreach ($cands as $dir) {
        if (@is_file($dir . '/delm-400.woff2')) {
            $f['dir'] = $dir;
            $f['delm']['400'] = 'delm-400';
            if (@is_file($dir . '/delm-700.woff2')) {
                $f['delm']['700'] = 'delm-700';
            }
            $f['titio'] = @is_file($dir . '/titio.woff2');
            break;
        }
    }
    return $f;
}

function sk_serve_font($name)
{
    sk_headers();
    foreach (sk_own_fonts() as $p) {                // veřejná verze: písmo vložené v souboru
        if ($p['name'] === $name) {
            $bin = (string) base64_decode($p['data']);
            header('Content-Type: font/woff2');
            header('Content-Length: ' . strlen($bin));
            header('Cache-Control: public, max-age=31536000, immutable');
            echo $bin;
            return;
        }
    }
    // písma ze serveru: Delm pro Titio a HB, Titio jen pro Titio; veřejná verze má jen svá vložená
    $f = sk_fonts();
    $known = sk_own_fonts() !== array() ? array() : array_values($f['delm']);
    if ($f['titio'] && SK_EDICE === 'titio') {
        $known[] = 'titio';
    }
    if (!is_string($name) || !in_array($name, $known, true)) {
        http_response_code(404);
        return;
    }
    $path = $f['dir'] . '/' . $name . '.woff2';
    header('Content-Type: font/woff2');
    header('Content-Length: ' . (int) @filesize($path));
    header('Cache-Control: public, max-age=31536000, immutable');
    @readfile($path);
}

/** Záloha ke stažení ( …?export=json ), jen pro hlavního správce. */
function sk_export()
{
    sk_headers();
    if (!sk_is_super()) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Zálohu smí stáhnout jen hlavní správce.';
        return;
    }
    $d = sk_data();
    unset($d['log']);
    $d = array('app' => SK_PRODUKT, 'verze' => SK_VERSION, 'stazeno' => date('c')) + $d;   // „app“ je jen popisek, import ho nečte
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . SK_SLUG . '-zaloha-' . date('Y-m-d') . '.json"');
    echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

/* ───────────────────────── Discord: oznámení a připomínky ─────────────────────────
 * Nastavení leží zvlášť v sklad-data/hook.php. Adresa webhooku je tajná (kdo ji zná, může do kanálu psát)
 * a token bota taky. Proto se neposílají s daty přehledu, nejsou v zálohách ke stažení a do prohlížeče správce
 * z nich jde jen číslo webhooku a jméno bota.
 *
 * Co se posílá:
 *   - někdo si něco půjčil, přibyla nová věc, věc se vrátila (hned při uložení ve správě),
 *   - připomínky termínu vrácení: den předem, v den vrácení a po termínu, jedna zpráva na člověka.
 *
 * Označit člověka tak, aby mu Discord poslal upozornění, jde přes webhook jen číselným ID (<@123…>).
 * Text „@přezdívka“ by ve zprávě zůstal obyčejným textem. ID se dá k lidem zapsat ručně, nebo si ho sklad
 * dohledá sám podle jména: k tomu potřebuje token bota, který je na stejném serveru (bot smí hledat mezi
 * členy, webhook ne). Nalezené ID si pamatuje, takže se Discordu ptá jen poprvé.
 * Každá zpráva navíc Discordu výslovně říká, koho smí označit; „@everyone“ v názvu věci tak nikoho nevzbudí.
 *
 * Připomínky se pošlou, když sklad mezi 8. a 21. hodinou někdo otevře (prohlížeč pak potichu zavolá …?api=tick),
 * nebo když hosting pravidelně volá adresu …?cron.
 */

function sk_hook_file()
{
    return sk_dir() . '/hook.php';
}

/** Adresa webhooku Discordu v ustáleném tvaru, nebo prázdný text. Jinam než na Discord se odsud nevolá. */
function sk_hook_url($v)
{
    $v = trim((string) $v);
    if (preg_match('~^https://(?:(?:canary|ptb)\.)?discord(?:app)?\.com/api/(?:v\d{1,2}/)?webhooks/(\d{15,21})/([A-Za-z0-9_-]{30,200})/?(?:\?thread_id=(\d{15,21}))?\z~', $v, $m)) {
        return 'https://discord.com/api/webhooks/' . $m[1] . '/' . $m[2] . (isset($m[3]) && $m[3] !== '' ? '?thread_id=' . $m[3] : '');
    }
    if (sk_test_on()) {
        $base = (string) getenv('SKLAD_TEST_HOOK');
        if ($base !== '' && strpos($v, $base) === 0 && preg_match('~^https?://[A-Za-z0-9.:-]+/[A-Za-z0-9_/-]+(?:\?thread_id=\d+)?\z~', $v)) {
            return $v;
        }
    }
    return '';
}

/** Číselné ID uživatele Discordu („123…“, případně vložené jako „<@123…>“), nebo prázdný text. */
function sk_snowflake($v)
{
    return preg_match('/^<?@?!?(\d{15,21})>?\z/', trim((string) $v), $m) ? $m[1] : '';
}

/** Koho označit u oznámení: „123…“ (člověk) nebo „&123…“ (role). */
function sk_ping($v)
{
    return preg_match('/^<?@?(&?)!?(\d{15,21})>?\z/', trim((string) $v), $m) ? $m[1] . $m[2] : '';
}

/** Seznam lidí a jejich Discord ID: n = jméno tak, jak se píše u výpůjček, d = ID, u a h = jak se jmenují na Discordu. */
function sk_people($v)
{
    $out = array();
    $seen = array();
    foreach ((is_array($v) ? $v : array()) as $p) {
        if (!is_array($p)) {
            continue;
        }
        $n = sk_text(isset($p['n']) ? $p['n'] : '', 80);
        $id = sk_snowflake(isset($p['d']) ? $p['d'] : '');
        $key = sk_name_key($n);
        if ($key === '' || $id === '' || isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $x = array('n' => $n, 'd' => $id);
        $u = ltrim(sk_text(isset($p['u']) ? $p['u'] : '', 40), '@');        // uživatelské jméno na Discordu (když ho známe od bota)
        if ($u !== '') {
            $x['u'] = $u;
        }
        $h = sk_text(isset($p['h']) ? $p['h'] : '', 80);                    // jak je člověk vidět na serveru
        if ($h !== '') {
            $x['h'] = $h;
        }
        $out[] = $x;
        if (count($out) >= 300) {
            break;
        }
    }
    return $out;
}

function sk_hook_clean($c)
{
    $c = is_array($c) ? $c : array();
    $on = isset($c['on']) && is_array($c['on']) ? $c['on'] : array();
    $rem = isset($c['rem']) && is_array($c['rem']) ? $c['rem'] : array();
    $sent = array();
    foreach ((isset($c['sent']) && is_array($c['sent']) ? $c['sent'] : array()) as $id => $s) {
        if (sk_id_ok((string) $id) && is_array($s) && isset($s['who'], $s['due']) && is_string($s['who']) && sk_date($s['due']) !== '') {
            $x = array('who' => $s['who'], 'due' => $s['due']);
            if (!empty($s['b'])) {
                $x['b'] = 1;
            }
            if (!empty($s['d'])) {
                $x['d'] = 1;
            }
            if (isset($s['o']) && sk_date($s['o']) !== '') {
                $x['o'] = $s['o'];
            }
            $sent[(string) $id] = $x;
        }
    }
    $log = array();
    foreach ((isset($c['log']) && is_array($c['log']) ? $c['log'] : array()) as $l) {
        if (is_array($l) && isset($l['t'], $l['x'])) {
            $log[] = array('t' => (int) $l['t'], 'ok' => !empty($l['ok']), 'x' => sk_text($l['x'], 300));
        }
        if (count($log) >= 30) {
            break;
        }
    }
    $miss = array();
    foreach ((isset($c['miss']) && is_array($c['miss']) ? $c['miss'] : array()) as $k => $t) {
        if ((string) $k !== '' && strlen((string) $k) <= 120 && count($miss) < 200) {
            $miss[(string) $k] = (int) $t;
        }
    }
    return array(
        'v' => 1,
        'url' => isset($c['url']) && is_string($c['url']) ? sk_hook_url($c['url']) : '',
        'name' => sk_text(isset($c['name']) ? $c['name'] : '', 80),
        'guild' => sk_snowflake(isset($c['guild']) ? $c['guild'] : ''),            // server, kam webhook vede
        'guildName' => sk_text(isset($c['guildName']) ? $c['guildName'] : '', 80),
        'bot' => sk_bot_token(isset($c['bot']) ? $c['bot'] : ''),                  // token bota: stejně tajný jako adresa webhooku
        'botName' => sk_text(isset($c['botName']) ? $c['botName'] : '', 80),
        'botId' => sk_snowflake(isset($c['botId']) ? $c['botId'] : ''),
        'botIn' => !empty($c['botIn']),                                            // je bot na tom serveru?
        'botAll' => !empty($c['botAll']),                                          // smí číst celý seznam členů?
        'botErr' => sk_text(isset($c['botErr']) ? $c['botErr'] : '', 200),         // co se botovi naposledy nepovedlo
        'miss' => $miss,                                                           // jména, která bot na serveru nenašel (a kdy)
        'on' => array(
            'loan' => !isset($on['loan']) || !empty($on['loan']),
            'item' => !isset($on['item']) || !empty($on['item']),
            'back' => !empty($on['back']),
            'remind' => !isset($on['remind']) || !empty($on['remind']),
        ),
        'rem' => array(
            'before' => !empty($rem['before']),
            'day' => !isset($rem['day']) || !empty($rem['day']),
            'every' => max(0, min(60, isset($rem['every']) ? (int) $rem['every'] : 7)),
        ),
        'ping' => sk_ping(isset($c['ping']) ? $c['ping'] : ''),
        'pingH' => sk_text(isset($c['pingH']) ? $c['pingH'] : '', 90),             // totéž jménem („@opicak“, „&Vedoucí“), když ho známe
        'people' => sk_people(isset($c['people']) ? $c['people'] : null),
        'base' => isset($c['base']) && is_string($c['base']) && preg_match('~^https?://[A-Za-z0-9.-]{1,200}(?::\d{1,5})?/[A-Za-z0-9._\~%@+/-]*\z~', $c['base']) ? $c['base'] : '',
        'sent' => $sent,
        'last' => isset($c['last']) ? (int) $c['last'] : 0,
        'log' => $log,
    );
}

/** Nastavení Discordu (vždy úplné, i když soubor ještě není). */
function sk_hook($fresh = false)
{
    static $c = null;
    if ($c === null || $fresh) {
        $r = sk_file_read(sk_hook_file());
        $c = sk_hook_clean(is_array($r) ? $r : null);
    }
    return $c;
}

/** Změní nastavení Discordu: zamkne, načte čerstvé, zavolá $fn(&$nastaveni), uloží. */
function sk_hook_update($fn)
{
    $h = @fopen(sk_dir() . '/.lock-hook', 'c');
    if ($h && !@flock($h, LOCK_EX)) {
        @fclose($h);
        $h = null;
    }
    try {
        $c = sk_hook(true);
        $res = $fn($c);
        if (!sk_file_write(sk_hook_file(), sk_hook_clean($c))) {
            throw new SkError('Nastavení Discordu se nepodařilo uložit. Zkontroluj práva složky ' . SK_DATA . '.', 500);
        }
        sk_hook(true);
        return $res;
    } finally {
        if ($h) {
            @flock($h, LOCK_UN);
            @fclose($h);
        }
    }
}

/** Co z nastavení smí vidět prohlížeč správce: z adresy webhooku jen jeho číslo, z bota jen jméno. */
function sk_hook_public($c)
{
    return array(
        'set' => $c['url'] !== '',
        'id' => $c['url'] !== '' && preg_match('~/webhooks/(\d+)/~', $c['url'], $m) ? $m[1] : '',
        'name' => $c['name'],
        'guild' => $c['guild'],
        'server' => $c['guildName'],
        'bot' => array('set' => $c['bot'] !== '', 'name' => $c['botName'], 'id' => $c['botId'], 'in' => $c['botIn'], 'all' => $c['botAll'], 'err' => $c['botErr']),
        'on' => $c['on'],
        'rem' => $c['rem'],
        'ping' => $c['ping'],
        'pingH' => $c['pingH'],
        'people' => $c['people'],
        'log' => array_slice($c['log'], 0, 12),
    );
}

function sk_hook_log(&$c, $ok, $text)
{
    array_unshift($c['log'], array('t' => time(), 'ok' => (bool) $ok, 'x' => sk_text($text, 300)));
    $c['log'] = array_slice($c['log'], 0, 30);
}

/** Adresa skladu, jak ji právě vidí prohlížeč („https://web.cz/sklad“), nebo prázdný text. */
function sk_public_base()
{
    $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
    if (!preg_match('/^[A-Za-z0-9.-]{1,200}(?::\d{1,5})?\z/', $host)) {
        return '';
    }
    return (sk_https() ? 'https' : 'http') . '://' . $host . sk_self();
}

/* ───── spojení ───── */

/** Jedno volání Discordu (tělo JSON, $extra = další hlavičky). Vrací stav odpovědi, její tělo a případnou chybu spojení. */
function sk_http($method, $url, $payload = null, $extra = array(), $timeout = 8)
{
    $body = $payload === null ? null : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    // Discord chce, aby se program představil ve tvaru „DiscordBot (adresa, verze)“
    $home = sk_public_base();
    $headers = array_merge(array('User-Agent: DiscordBot (' . ($home !== '' ? $home : 'https://localhost/') . ', ' . SK_VERSION . ') ' . SK_PRODUKT, 'Accept: application/json'), $extra);
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
    }
    if (function_exists('curl_init') && function_exists('curl_exec')) {        // hosting může zakázat jen některou z nich
        $ch = curl_init($url);
        $opt = array(
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => min(4, $timeout),
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => false,
        );
        if ($body !== null) {
            $opt[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $opt);
        $resp = curl_exec($ch);
        $out = array(
            'status' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'body' => $resp === false ? '' : (string) $resp,
            'error' => $resp === false ? (string) curl_error($ch) : '',
        );
        $ch = null;
        return $out;
    }
    if (!function_exists('ini_get') || @ini_get('allow_url_fopen')) {
        $ctx = stream_context_create(array('http' => array(
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $body === null ? '' : $body,
            'timeout' => $timeout,
            'ignore_errors' => true,
            'follow_location' => 0,
        )));
        $resp = @file_get_contents($url, false, $ctx);
        $hdr = function_exists('http_get_last_response_headers') ? http_get_last_response_headers() : (isset($http_response_header) ? $http_response_header : array());
        $status = is_array($hdr) && isset($hdr[0]) && preg_match('~^HTTP/\S+\s+(\d{3})~', $hdr[0], $m) ? (int) $m[1] : 0;
        return array('status' => $status, 'body' => $resp === false ? '' : (string) $resp, 'error' => $resp === false && $status === 0 ? 'spojení se nepodařilo' : '');
    }
    return array('status' => 0, 'body' => '', 'error' => 'hosting nepovoluje odchozí spojení (chybí cURL i allow_url_fopen)');
}

/** Převede odpověď Discordu na srozumitelnou hlášku. Prázdný text = v pořádku. */
function sk_discord_error($r)
{
    $s = (int) $r['status'];
    if ($s >= 200 && $s < 300) {
        return '';
    }
    if ($s === 0) {
        return 'server se s Discordem nespojil (' . ($r['error'] !== '' ? sk_text($r['error'], 120) : 'neznámá chyba') . ')';
    }
    if ($s === 401 || $s === 403 || $s === 404) {
        return 'webhook na Discordu už neexistuje (někdo ho smazal, nebo je adresa špatně)';
    }
    if ($s === 429) {
        return 'Discord říká, ať zpomalíme; zkus to za chvíli';
    }
    $j = json_decode((string) $r['body'], true);
    $msg = is_array($j) && isset($j['message']) && is_string($j['message']) ? sk_text($j['message'], 120) : '';
    return 'Discord zprávu odmítl (chyba ' . $s . ($msg !== '' ? ': ' . $msg : '') . ')';
}

/**
 * Pošle zprávu do kanálu. Vrací prázdný text, nebo chybu česky.
 * $users a $roles jsou ID, která zpráva smí označit; nic jiného (ani @everyone z názvu věci) se neoznačí.
 */
function sk_discord_send($c, $content, $users = array(), $roles = array())
{
    if ($c['url'] === '') {
        return 'Discord není zapnutý';
    }
    if (preg_match('/^.{0,1900}/us', (string) $content, $m)) {
        $content = $m[0];
    }
    $allow = array('parse' => array());
    if ($users) {
        $allow['users'] = array_values(array_unique($users));
    }
    if ($roles) {
        $allow['roles'] = array_values(array_unique($roles));
    }
    $url = $c['url'] . (strpos($c['url'], '?') === false ? '?' : '&') . 'wait=true';
    $payload = array('content' => $content, 'allowed_mentions' => $allow, 'flags' => 4);      // 4 = bez náhledů odkazů
    $r = sk_http('POST', $url, $payload);
    if ($r['status'] === 429) {
        $j = json_decode((string) $r['body'], true);
        $wait = is_array($j) && isset($j['retry_after']) ? (float) $j['retry_after'] : 1.0;
        if ($wait <= 3) {
            usleep((int) ($wait * 1000000) + 150000);
            $r = sk_http('POST', $url, $payload);
        }
    }
    return sk_discord_error($r);
}

/* ───── bot: hledání lidí na serveru podle jména ───── */

/** Token bota v čistém tvaru (bez předpony „Bot “), nebo prázdný text. Tvoří ho tři části oddělené tečkami. */
function sk_bot_token($v)
{
    $v = trim((string) preg_replace('/^Bot\s+/i', '', trim((string) $v)));
    return strlen($v) <= 200 && preg_match('/^[A-Za-z0-9_-]{10,80}(?:\.[A-Za-z0-9_-]{1,120}){2}\z/', $v) ? $v : '';
}

/** Kde je rozhraní Discordu (při zkouškách místo něj odpovídá zkušební server). Jinam se token bota neposílá. */
function sk_discord_api()
{
    if (sk_test_on()) {
        $base = (string) getenv('SKLAD_TEST_HOOK');
        if (preg_match('~^(https?://[A-Za-z0-9.:-]+/api/)webhooks/\z~', $base, $m)) {
            return $m[1];
        }
    }
    return 'https://discord.com/api/v10/';
}

/** Jméno k porovnávání: jen písmena a číslice, malé, bez háčků. „@Opičák 🐒“ i „opicak“ dají totéž. */
function sk_name_key($s)
{
    $s = (string) preg_replace('/[^\p{L}\p{M}\p{N}]+/u', ' ', (string) $s);
    if (function_exists('mb_strtolower')) {
        $s = mb_strtolower($s, 'UTF-8');
    }
    if (class_exists('Normalizer', false)) {
        $d = Normalizer::normalize($s, Normalizer::FORM_D);      // rozloží i písmena, která vlastní tabulka nezná
        if (is_string($d)) {
            $s = $d;
        }
    }
    return sk_fold((string) preg_replace('/\p{M}+/u', '', $s));
}

/** Dotaz na Discord jménem bota. Jednou počká, když Discord řekne „zpomal“. */
function sk_bot_get($token, $path)
{
    $url = sk_discord_api() . $path;
    $auth = array('Authorization: Bot ' . $token);
    $r = sk_http('GET', $url, null, $auth, 5);
    if ($r['status'] === 429) {
        $j = json_decode((string) $r['body'], true);
        $wait = is_array($j) && isset($j['retry_after']) ? (float) $j['retry_after'] : 1.0;
        if ($wait <= 3) {
            usleep((int) ($wait * 1000000) + 150000);
            $r = sk_http('GET', $url, null, $auth, 5);
        }
    }
    return $r;
}

/** Odpověď Discordu botovi česky. Prázdný text = v pořádku. $onServer = dotaz se týkal serveru (403 a 404 pak znamenají, že na něm bot není). */
function sk_bot_error($r, $onServer = true)
{
    $s = (int) $r['status'];
    if ($s >= 200 && $s < 300) {
        return '';
    }
    if ($s === 0) {
        return 'Server se s Discordem nespojil (' . ($r['error'] !== '' ? sk_text($r['error'], 120) : 'neznámá chyba') . ').';
    }
    if ($s === 401) {
        return 'Token bota už neplatí. Vlož v Nastavení nový.';
    }
    if ($onServer && ($s === 403 || $s === 404)) {
        return 'Bot není na serveru, kam vede webhook. Pozvi ho odkazem v Nastavení.';
    }
    if ($s === 429) {
        return 'Discord říká, ať zpomalíme. Zkus to za chvíli.';
    }
    $j = json_decode((string) $r['body'], true);
    $msg = is_array($j) && isset($j['message']) && is_string($j['message']) ? sk_text($j['message'], 120) : '';
    return 'Discord botovi odpověděl chybou ' . $s . ($msg !== '' ? ' (' . $msg . ')' : '') . '.';
}

/**
 * Co tenhle požadavek už ví o členech serveru, ať se Discordu neptá dvakrát na totéž:
 * pool = členové podle ID, full = máme úplně všechny, asked = na co už jsme se ptali, until = dokdy smíme hledat,
 * down = chyba spojení (další dotazy už se nezkouší), state = co jsme cestou zjistili o botovi
 * (in: je na serveru, all: smí číst celý seznam, err: chyba).
 */
function &sk_members()
{
    static $m = array('pool' => array(), 'full' => false, 'listed' => false, 'asked' => array(), 'until' => 0.0, 'down' => '', 'state' => array());
    return $m;
}

/** Člen serveru, jak ho potřebujeme: ID, uživatelské jméno, jméno viditelné na serveru a klíče všech jeho jmen. */
function sk_member($m)
{
    $user = isset($m['user']['username']) ? sk_text($m['user']['username'], 40) : '';
    $names = array();
    // přezdívka na serveru, zobrazované jméno, uživatelské jméno: první, které člověk má, je vidět v seznamu členů
    foreach (array(isset($m['nick']) ? $m['nick'] : '', isset($m['user']['global_name']) ? $m['user']['global_name'] : '', $user) as $n) {
        $n = sk_text($n, 80);
        if ($n !== '' && !in_array($n, $names, true)) {
            $names[] = $n;
        }
    }
    $keys = array();
    foreach ($names as $n) {
        $k = sk_name_key($n);
        if ($k !== '') {
            $keys[$k] = true;
        }
    }
    return array('d' => sk_snowflake($m['user']['id']), 'u' => $user, 'h' => $names ? $names[0] : '', 'names' => $names, 'keys' => $keys);
}

/** „Opičák 🐒 (@opicak)“ */
function sk_member_label($p)
{
    $u = isset($p['u']) ? (string) $p['u'] : '';
    $h = isset($p['h']) ? (string) $p['h'] : '';
    if ($h === '' || $h === $u) {
        return $u !== '' ? '@' . $u : $h;
    }
    return $h . ($u !== '' ? ' (@' . $u . ')' : '');
}

/** Přidá členy z odpovědi Discordu mezi známé (boty vynechá). Vrací [kolik jich přišlo, nejvyšší ID mezi nimi]. */
function sk_members_take($body)
{
    $st = &sk_members();
    $list = json_decode((string) $body, true);
    $n = 0;
    $last = '';
    foreach ((is_array($list) ? $list : array()) as $m) {
        $id = is_array($m) && isset($m['user']['id']) && (is_string($m['user']['id']) || is_int($m['user']['id'])) ? sk_snowflake($m['user']['id']) : '';
        if ($id === '') {
            continue;
        }
        $n++;
        if (strlen($id) > strlen($last) || (strlen($id) === strlen($last) && strcmp($id, $last) > 0)) {
            $last = $id;        // nejvyšší ID na stránce: od něj pokračuje další stránka seznamu
        }
        if (empty($m['user']['bot'])) {
            $st['pool']['u' . $id] = sk_member($m);
        }
    }
    return array($n, $last);
}

/**
 * Zapamatuje si, co odpověď prozradila o botovi (zapíše se do nastavení na konci požadavku), a taky to,
 * že se s Discordem nejde spojit: další dotazy v tomhle požadavku už se pak nezkouší a nečeká se na ně.
 */
function sk_bot_seen($r)
{
    $st = &sk_members();
    $s = (int) $r['status'];
    if ($s === 0) {
        $st['down'] = sk_bot_error($r);
    } elseif ($s === 200) {
        $st['state']['in'] = true;
        $st['state']['err'] = '';
    } elseif ($s === 401) {
        $st['state']['err'] = 'Discord token bota odmítl. Vlož nový.';
    } elseif ($s === 403 || $s === 404) {
        $st['state']['in'] = false;
    }
}

/** Zeptá se Discordu na členy, jejichž přezdívka nebo uživatelské jméno začíná na $query. Vrací prázdný text, nebo chybu. */
function sk_members_search($c, $query, $limit)
{
    $st = &sk_members();
    $key = $limit . ':' . $query;
    if (isset($st['asked'][$key])) {
        return $st['asked'][$key];
    }
    if ($st['down'] !== '') {
        return $st['down'];
    }
    if (microtime(true) > $st['until']) {
        return 'Discord neodpovídá včas. Zkus to za chvíli.';
    }
    $r = sk_bot_get($c['bot'], 'guilds/' . $c['guild'] . '/members/search?limit=' . (int) $limit . '&query=' . rawurlencode($query));
    sk_bot_seen($r);
    $err = $r['status'] === 200 ? '' : sk_bot_error($r);
    if ($err === '') {
        sk_members_take($r['body']);
    }
    $st['asked'][$key] = $err;
    return $err;
}

/**
 * Zkusí načíst celý seznam členů. Discord ho botovi vydá, jen když má v Developer Portalu zapnutý
 * přepínač „Server Members Intent“; jinak odpoví 403 a zůstane jen hledání podle začátku jména.
 */
function sk_members_all($c)
{
    $st = &sk_members();
    if ($st['listed']) {
        return;
    }
    $st['listed'] = true;
    $after = '0';
    for ($page = 0; $page < 5; $page++) {
        if (microtime(true) > $st['until']) {
            return;
        }
        $r = sk_bot_get($c['bot'], 'guilds/' . $c['guild'] . '/members?limit=1000&after=' . $after);
        if ($r['status'] !== 200) {
            if ($page === 0 && $r['status'] === 403) {
                $st['state']['all'] = false;
            }
            return;
        }
        $st['state']['all'] = true;
        list($n, $last) = sk_members_take($r['body']);
        if ($n < 1000 || $last === '') {
            $st['full'] = true;
            return;
        }
        $after = $last;
    }
}

/**
 * Známí členové, jejichž některé jméno odpovídá hledanému. Když jich je víc a $strict není zapnuté, rozhodne
 * přesný zápis: nejdřív jméno napsané přesně tak, jak ho člověk má, potom přesné uživatelské jméno (to je jedinečné).
 */
function sk_members_pick($want, $name, $strict)
{
    $st = &sk_members();
    $hit = array();
    foreach ($st['pool'] as $k => $m) {
        if (isset($m['keys'][$want])) {
            $hit[$k] = $m;
        }
    }
    if (count($hit) > 1 && !$strict) {
        foreach (array(false, true) as $userOnly) {
            $exact = array();
            foreach ($hit as $k => $m) {
                if ($userOnly ? $m['u'] === $name : in_array($name, $m['names'], true)) {
                    $exact[$k] = $m;
                }
            }
            if (count($exact) === 1) {
                return $exact;
            }
        }
    }
    return $hit;
}

/** Nejvýš pět známých členů s podobným jménem, na nabídku „nemyslíš…?“. */
function sk_members_like($want)
{
    $st = &sk_members();
    $head = substr($want, 0, 3);
    $scored = array();
    foreach ($st['pool'] as $m) {
        $best = 0;
        foreach ($m['keys'] as $k => $on) {
            $k = (string) $k;
            if (strpos($k, $want) === 0) {
                $s = 4;
            } elseif (strlen($k) >= 3 && strpos($want, $k) === 0) {
                $s = 3;
            } elseif (strpos($k, $want) !== false) {
                $s = 2;
            } elseif (strlen($head) >= 3 && strpos($k, $head) === 0) {
                $s = 1;
            } else {
                $s = 0;
            }
            $best = max($best, $s);
        }
        if ($best > 0) {
            $scored[] = array($best, count($scored), $m);
        }
    }
    usort($scored, function ($a, $b) {
        return $a[0] !== $b[0] ? $b[0] - $a[0] : $a[1] - $b[1];
    });
    $out = array();
    foreach (array_slice($scored, 0, 5) as $x) {
        $out[] = array('u' => $x[2]['u'], 'h' => $x[2]['h']);
    }
    return $out;
}

/**
 * Najde člověka na serveru podle jména. Vrací ['id' => …, 'u' => uživatelské jméno, 'h' => jméno na serveru],
 * nebo ['error' => text, 'soft' => …, 'cand' => podobní lidé].
 * Bere jen shodu celého jména (bez ohledu na velikost písmen, háčky a smajlíky) a jen když je taková jedna.
 * soft = Discord odpověděl, jen nikoho takového nezná (nebo jich zná víc); takový výsledek má smysl si pamatovat.
 * $strict = bez rozhodování mezi víc lidmi: pro zprávy, které odcházejí samy a nikdo výsledek neuvidí.
 */
function sk_discord_lookup($c, $name, $strict = false)
{
    $name = sk_text(ltrim(trim((string) $name), "@ \t"), 60);
    $want = sk_name_key($name);
    if ($want === '') {
        return array('error' => 'Jméno musí obsahovat aspoň jedno písmeno nebo číslici.', 'soft' => true);
    }
    if ($c['bot'] === '') {
        return array('error' => 'Jména umím dohledat jen s botem. Vlož token bota, nebo sem napiš číselné ID.', 'soft' => false);
    }
    if ($c['guild'] === '') {
        return array('error' => 'Nevím, na kterém serveru hledat. Ulož nejdřív adresu webhooku.', 'soft' => false);
    }
    $st = &sk_members();
    if ($st['until'] <= 0) {
        $st['until'] = microtime(true) + 6;
    }
    $hit = sk_members_pick($want, $name, $strict);
    if (!$hit && !$st['full']) {
        // 1) Discord hledá podle začátku přezdívky na serveru nebo uživatelského jména
        $err = sk_members_search($c, $name, 25);
        if ($err !== '') {
            return array('error' => $err, 'soft' => false);
        }
        $hit = sk_members_pick($want, $name, $strict);
        // 2) uživatelská jména jsou bez háčků: „Opičák“ → hledat „opi“ najde i @opicak
        $short = substr((string) preg_replace('/[^a-z0-9]/', '', $want), 0, 3);
        if (!$hit && strlen($short) >= 2) {
            sk_members_search($c, $short, 100);
            $hit = sk_members_pick($want, $name, $strict);
        }
        // 3) zobrazované jméno Discord hledat neumí: zbývá projít celý seznam členů (když ho bot smí číst)
        if (!$hit) {
            sk_members_all($c);
            $hit = sk_members_pick($want, $name, $strict);
        }
    }
    if (count($hit) === 1) {
        $m = reset($hit);
        return array('id' => $m['d'], 'u' => $m['u'], 'h' => $m['h']);
    }
    if ($hit) {
        $cand = array();
        foreach (array_slice($hit, 0, 5) as $m) {
            $cand[] = array('u' => $m['u'], 'h' => $m['h']);
        }
        return array('error' => 'Jménu „' . $name . '“ odpovídá na serveru víc lidí.', 'ask' => 'Vyber toho pravého:', 'soft' => true, 'many' => true, 'cand' => $cand);
    }
    $cand = sk_members_like($want);
    // s celým seznamem členů je to jisté; bez něj jsme mohli minout člověka, který má jméno jen jako zobrazované
    return array('error' => $st['full'] ? 'Na serveru není nikdo, kdo se jmenuje „' . $name . '“.' : 'Podle jména „' . $name . '“ jsem na serveru nikoho nenašel.',
        'ask' => $cand ? 'Nemyslíš někoho z nich?' : ($st['full'] ? '' : 'Zkus jeho uživatelské jméno z Discordu.'), 'soft' => true, 'cand' => $cand);
}

/** Chyba z hledání do formuláře: věta, za kterou prohlížeč nabídne podobná jména ke kliknutí. */
function sk_lookup_ask($r)
{
    return $r['error'] . (isset($r['ask']) && $r['ask'] !== '' ? ' ' . $r['ask'] : '');
}

/** Chyba z hledání i s vyjmenovanými podobnými jmény (do hlášek, kde se nedá klikat). */
function sk_lookup_text($r)
{
    $labels = array();
    foreach ((isset($r['cand']) ? $r['cand'] : array()) as $p) {
        $labels[] = sk_member_label($p);
    }
    return $labels ? $r['error'] . ' Podobná jména: ' . implode(', ', $labels) . '.' : sk_lookup_ask($r);
}

/** Člen serveru podle ID (kontrola ručně vloženého ID). Vrací ['u', 'h'], ['gone' => true], nebo prázdné pole, když to nejde zjistit. */
function sk_discord_member($c, $id)
{
    $st = &sk_members();
    if (isset($st['pool']['u' . $id])) {
        return array('u' => $st['pool']['u' . $id]['u'], 'h' => $st['pool']['u' . $id]['h']);
    }
    if ($c['bot'] === '' || $c['guild'] === '' || $st['down'] !== '' || ($st['until'] > 0 && microtime(true) > $st['until'])) {
        return array();
    }
    $r = sk_bot_get($c['bot'], 'guilds/' . $c['guild'] . '/members/' . $id);
    if ($r['status'] === 0) {
        sk_bot_seen($r);
    }
    if ($r['status'] === 200) {
        $m = json_decode((string) $r['body'], true);
        if (is_array($m) && isset($m['user']['id']) && sk_snowflake($m['user']['id']) === $id) {
            $p = sk_member($m);
            return array('u' => $p['u'], 'h' => $p['h'], 'bot' => !empty($m['user']['bot']));
        }
        return array();
    }
    $j = json_decode((string) $r['body'], true);
    $code = is_array($j) && isset($j['code']) ? (int) $j['code'] : 0;
    // 10007 = na serveru takový člen není, 10013 = takový uživatel vůbec neexistuje
    return $r['status'] === 404 && ($code === 10007 || $code === 10013) ? array('gone' => true) : array();
}

/** Role na serveru podle názvu. Vrací ['id', 'name'], nebo ['error' => text]. */
function sk_discord_role($c, $name)
{
    $name = sk_text(ltrim(trim((string) $name), "@& \t"), 80);
    $want = sk_name_key($name);
    if ($want === '' || $c['bot'] === '' || $c['guild'] === '') {
        return array('error' => 'Roli umím podle názvu najít jen s botem.');
    }
    $st = &sk_members();
    if ($st['down'] !== '') {
        return array('error' => $st['down']);
    }
    $r = sk_bot_get($c['bot'], 'guilds/' . $c['guild'] . '/roles');
    sk_bot_seen($r);
    if ($r['status'] !== 200) {
        return array('error' => sk_bot_error($r));
    }
    $hit = array();
    $list = json_decode((string) $r['body'], true);
    foreach ((is_array($list) ? $list : array()) as $x) {
        $id = is_array($x) && isset($x['id']) ? sk_snowflake($x['id']) : '';
        $n = is_array($x) && isset($x['name']) ? sk_text($x['name'], 80) : '';
        if ($id !== '' && $id !== $c['guild'] && sk_name_key($n) === $want) {      // role se stejným ID jako server je @everyone
            $hit[] = array('id' => $id, 'name' => $n);
        }
    }
    if (count($hit) === 1) {
        return $hit[0];
    }
    return array('error' => $hit ? 'Roli „' . $name . '“ má server víckrát. Vlož její ID se znakem & na začátku.' : 'Na serveru není role „' . $name . '“.');
}

/**
 * Zeptá se Discordu, jak je na tom bot: jestli token platí, jestli smí číst celý seznam členů a jestli je na serveru,
 * kam vede webhook. Vrací pole k zapsání do nastavení. Co se zjistit nepodařilo (výpadek spojení), to v poli není.
 * $app = odpověď na „kdo jsem“, pokud už ji volající má.
 */
function sk_bot_probe($c, $app = null)
{
    $out = array();
    if ($c['bot'] === '') {
        return $out;
    }
    if ($c['guild'] === '' && $c['url'] !== '') {
        // nastavení z verze 1.1: adresa webhooku je uložená, ale server, kam vede, ještě neznáme
        $r = sk_http('GET', (string) preg_replace('/\?.*$/', '', $c['url']));
        $j = json_decode((string) $r['body'], true);
        if ($r['status'] === 200 && is_array($j) && isset($j['guild_id'])) {
            $out['guild'] = sk_snowflake($j['guild_id']);
        }
    }
    if ($app === null) {
        $r = sk_bot_get($c['bot'], 'applications/@me');
        if ($r['status'] === 401) {
            return $out + array('botErr' => 'Discord token bota odmítl. Vlož nový.', 'botIn' => false);
        }
        if ($r['status'] === 0) {
            sk_bot_seen($r);        // s Discordem se nejde spojit: stav zůstane, jaký byl, a nic dalšího se nezkouší
            return $out;
        }
        $app = $r['status'] === 200 ? json_decode((string) $r['body'], true) : null;
    }
    if (is_array($app) && isset($app['id'])) {
        $bot = isset($app['bot']) && is_array($app['bot']) ? $app['bot'] : array();
        $out['botId'] = sk_snowflake($app['id']);
        $out['botName'] = sk_text(isset($bot['username']) ? $bot['username'] : (isset($app['name']) ? $app['name'] : ''), 80);
        $out['botAll'] = ((isset($app['flags']) ? (int) $app['flags'] : 0) & ((1 << 14) | (1 << 15))) !== 0;     // přepínač Server Members Intent
        $out['botErr'] = '';
    }
    $guild = isset($out['guild']) ? $out['guild'] : $c['guild'];
    if ($guild !== '') {
        $r = sk_bot_get($c['bot'], 'guilds/' . $guild);
        if ($r['status'] === 200) {
            $g = json_decode((string) $r['body'], true);
            $out['botIn'] = true;
            $out['guildName'] = sk_text(is_array($g) && isset($g['name']) ? $g['name'] : '', 80);
        } elseif ($r['status'] === 403 || $r['status'] === 404) {
            $out['botIn'] = false;
        } elseif ($r['status'] === 401) {
            $out['botErr'] = 'Discord token bota odmítl. Vlož nový.';
            $out['botIn'] = false;
        }
    }
    return $out;
}

/** Co se během požadavku samo našlo a nenašlo na Discordu; na konci se to zapíše do nastavení (sk_found_flush). */
function &sk_found()
{
    static $m = array('hit' => array(), 'miss' => array(), 'why' => array());
    return $m;
}

/** Poznámky pro správce o tom, koho se (ne)podařilo na Discordu najít. Bez parametru je vrátí jako [texty, je mezi nimi chyba]. */
function sk_hook_notes($add = null, $bad = false)
{
    static $notes = array();
    static $anyBad = false;
    if ($add !== null) {
        $notes[] = $add;
        $anyBad = $anyBad || $bad;
    }
    return array($notes, $anyBad);
}

/**
 * Zapíše do nastavení, co požadavek zjistil: nově nalezené lidi do seznamu, nenalezená jména mezi zapamatovaná
 * a stav bota. Volá se uvnitř sk_hook_update.
 */
function sk_found_flush(&$c)
{
    $m = &sk_found();
    foreach ($m['hit'] as $key => $p) {
        $known = false;
        foreach ($c['people'] as $q) {
            if (sk_name_key($q['n']) === (string) $key) {
                $known = true;
                break;
            }
        }
        if (!$known) {
            $c['people'][] = $p;
            sk_hook_log($c, true, 'nalezen na Discordu: ' . $p['n'] . ' = ' . sk_member_label($p));
            sk_hook_notes('Na Discordu jsem našel: ' . $p['n'] . ' = ' . sk_member_label($p) . '. Pamatuju si to.');
        }
        unset($c['miss'][$key]);
    }
    $hard = '';
    foreach ($m['why'] as $key => $w) {
        if ($w[2]) {
            $c['miss'][$key] = sk_now();
            if ($w[3]) {
                sk_hook_notes($w[4] ? 'Jménu „' . $w[0] . '“ odpovídá na Discordu víc lidí, zpráva je bez označení. Toho pravého vyber v Nastavení mezi lidmi.'
                    : '„' . $w[0] . '“ jsem na Discordu nenašel, zpráva je bez označení.');
            }
        } elseif ($hard === '') {
            $hard = $w[1];
        }
    }
    if ($hard !== '') {
        sk_hook_log($c, false, 'označení podle jména: ' . $hard);
        sk_hook_notes('Označit podle jména se nepodařilo. ' . $hard, true);
    }
    $old = sk_now() - 86400;
    foreach ($c['miss'] as $key => $t) {
        if ($t < $old) {
            unset($c['miss'][$key]);
        }
    }
    $m = array('hit' => array(), 'miss' => array(), 'why' => array());
    $st = &sk_members();
    if ($c['bot'] !== '') {
        if (isset($st['state']['in'])) {
            $c['botIn'] = $st['state']['in'];
        }
        if (isset($st['state']['all'])) {
            $c['botAll'] = $st['state']['all'];
        }
        if (isset($st['state']['err'])) {
            $c['botErr'] = $st['state']['err'];
        }
    }
    $st['state'] = array();
}

/**
 * Discord ID člověka: ze seznamu lidí, a když tam přesně není a je nastavený bot, zkusí ho podle jména najít
 * na serveru. Nalezené si pamatuje (přidá do seznamu), nenalezené den nezkouší znovu.
 * „Honza Novák“ tak neoznačí Honzu ze seznamu, pokud je na serveru i Honza Novák.
 */
function sk_person_find($c, $who)
{
    list($id, $exact) = sk_person_match($c, $who);
    if ($exact || $c['bot'] === '' || $c['guild'] === '') {
        return $id;
    }
    $parts = preg_split('/\s*[(,;\/+]/u', (string) $who);
    $base = sk_text(ltrim(is_array($parts) ? (string) $parts[0] : (string) $who, '@ '), 60);        // „Opičák (vrátí v pátek)“ → „Opičák“
    $key = sk_name_key($base);
    if ($key === '') {
        return $id;
    }
    list($id2, $exact2) = sk_person_match($c, $base);
    if ($exact2) {
        return $id2;        // jméno bez dovětku v seznamu je
    }
    $m = &sk_found();
    if (isset($m['hit'][$key])) {
        return $m['hit'][$key]['d'];
    }
    if (isset($m['miss'][$key]) || (isset($c['miss'][$key]) && $c['miss'][$key] > sk_now() - 86400)) {
        return $id;
    }
    $r = sk_discord_lookup($c, $base, true);
    if (isset($r['id'])) {
        $m['hit'][$key] = array('n' => $base, 'd' => $r['id'], 'u' => $r['u'], 'h' => $r['h']);
        return $r['id'];
    }
    $m['miss'][$key] = true;
    // [jméno, důvod, má smysl si to pamatovat, je to potřeba říct správci (nikoho jsme neoznačili), odpovídá víc lidí]
    $m['why'][$key] = array($base, $r['error'], !empty($r['soft']), $id === '', !empty($r['many']));
    return $id;
}

/* ───── texty zpráv ───── */

/** Text do zprávy: v rozumné délce, bez formátování Discordu a bez znaků, které by někoho označily. */
function sk_md($s, $max = 120)
{
    $s = sk_text($s, $max);
    $s = (string) preg_replace('/([\\\\*_~`|<\[\]])/', '\\\\$1', $s);
    return str_replace('@', "@\u{200B}", $s);
}

/**
 * Člověk ze seznamu lidí podle jména u výpůjčky. Vrací [Discord ID, je to shoda celého jména].
 * Bez shody celého jména stačí, když jméno ze seznamu stojí na začátku: „Opičák (vrátí v pátek)“ je Opičák.
 */
function sk_person_match($c, $who)
{
    $w = sk_name_key($who);
    if ($w === '') {
        return array('', false);
    }
    $best = '';
    $bestLen = 0;
    foreach ($c['people'] as $p) {
        $n = sk_name_key($p['n']);
        if ($n === '') {
            continue;
        }
        if ($w === $n) {
            return array($p['d'], true);
        }
        $len = strlen($n);
        if ($len > $bestLen && strncmp($w, $n . ' ', $len + 1) === 0) {
            $best = $p['d'];
            $bestLen = $len;
        }
    }
    return array($best, false);
}

/** Člověk ve zprávě: s označením, když známe jeho ID (nebo ho bot na serveru najde). Vrací [text, ID k označení]. */
function sk_who_md($c, $who)
{
    $id = sk_person_find($c, $who);
    $name = sk_md($who, 80);
    if ($id === '') {
        return array('**' . $name . '**', array());      // bez ID jen jméno
    }
    // Označení ukáže jméno z Discordu. Jméno ze skladu se přidá do závorky, když je jiné (i o dovětek), nebo když
    // jméno z Discordu neznáme (ID zapsané ručně): přezdívka tam bývá jiná a zpráva má být čitelná i bez hádání.
    $key = sk_name_key($who);
    $m = &sk_found();
    foreach (array_merge($c['people'], array_values($m['hit'])) as $p) {
        if ($p['d'] !== $id) {
            continue;
        }
        foreach (array('h', 'u') as $f) {
            if (isset($p[$f]) && sk_name_key($p[$f]) === $key) {
                return array('<@' . $id . '>', array($id));
            }
        }
    }
    return array('<@' . $id . '> (' . $name . ')', array($id));
}

/**
 * Věc ve zprávě: tučně název a v závorce počet („99 ks“, „6 kg“). S $part kolik z toho („5 z 99 ks“, „500 g z 6 kg“).
 * Věc bez počtu ukáže text ze soupisu; u výpůjčky, kde se neví kolik ($part === INF), počet chybí.
 */
function sk_item_md($it, $part = null)
{
    $u = sk_unit($it);
    $all = sk_count($it);
    if ($part === INF) {
        $amount = '';
    } elseif (is_int($part)) {
        $amount = sk_part_cs($part, $all, $u);
    } elseif ($all !== null) {
        $amount = sk_qty_cs($all, $u);
    } else {
        $amount = isset($it['qt']) && $it['qt'] !== '-' ? (string) $it['qt'] : '';
    }
    return '**' . sk_md($it['n']) . '**' . ($amount !== '' ? ' (' . sk_md($amount, 60) . ')' : '');
}

/** Kdy si to vyzvedne, slovy: „dnes v 17:00“, „zítra“, „ve středu 21. 10. 2026“. */
function sk_when_cs($from, $at, $today)
{
    $left = sk_days($today, $from);
    if ($left === 0) {
        $w = 'dnes';
    } elseif ($left === 1) {
        $w = 'zítra';
    } else {
        $pre = array(1 => 'v pondělí', 'v úterý', 've středu', 've čtvrtek', 'v pátek', 'v sobotu', 'v neděli');
        $w = $pre[(int) date('N', (int) strtotime($from . ' 12:00:00'))] . ' ' . sk_date_cs($from);
    }
    return $w . ($at !== '' ? ' v ' . $at : '');
}

/** Seznam věcí pod zprávou, nejvýš 15 řádků. $lines = [[věc, kolik (null = neví se), dovětek]]. */
function sk_lines_md($lines)
{
    $out = array();
    foreach (array_slice($lines, 0, 15) as $l) {
        $out[] = '• ' . sk_item_md($l[0], isset($l[1]) ? $l[1] : INF) . (isset($l[2]) && $l[2] !== '' ? ' · ' . $l[2] : '');
    }
    if (count($lines) > 15) {
        $out[] = '• … a další (' . (count($lines) - 15) . ')';
    }
    return $out ? "\n" . implode("\n", $out) : '';
}

/** „Na vědomí“ na konec oznámení: správce nebo role, kterým má zpráva pípnout. Vrací [text, lidé, role]. */
function sk_ping_md($c, $skipUsers = array())
{
    $p = $c['ping'];
    if ($p === '') {
        return array('', array(), array());
    }
    if ($p[0] === '&') {
        return array(' · na vědomí <@&' . substr($p, 1) . '>', array(), array(substr($p, 1)));
    }
    if (in_array($p, $skipUsers, true)) {
        return array('', array(), array());
    }
    return array(' · na vědomí <@' . $p . '>', array($p), array());
}

function sk_link_md($base, $frag)
{
    return $base !== '' ? "\n<" . $base . '#' . $frag . '>' : '';
}

/** Kdy má být věc zpátky, slovy: „dnes“, „zítra“, „za 5 dní“, „3 dny po termínu“. */
function sk_due_words($left)
{
    if ($left === 0) {
        return 'dnes';
    }
    if ($left === 1) {
        return 'zítra';
    }
    $n = abs($left);
    $days = $n . ' ' . sk_plural($n, 'den', 'dny', 'dní');
    return $left > 0 ? 'za ' . $days : $days . ' po termínu';
}

/** Řádek připomínky jedné výpůjčky. */
function sk_remind_line($it, $l, $today)
{
    $due = isset($l['due']) ? (string) $l['due'] : '';
    $md = sk_item_md($it, sk_hold_q($l));
    if ($due === '') {
        $since = isset($l['since']) ? sk_date_cs($l['since']) : '';
        return '• ' . $md . ($since !== '' ? ' · půjčeno ' . $since : '');
    }
    $left = sk_days($today, $due);
    return '• ' . $md . ' · ' . ($left < 0 ? 'mělo být zpátky ' : 'vrátit do ') . sk_date_cs($due) . ' (' . sk_due_words($left) . ')';
}

/* ───── oznámení při změně ve správě ───── */

/**
 * Pošle oznámení o tom, co se právě uložilo. $events = seznam [druh, co, dál]:
 *   'loan'  [věc, {loan: výpůjčka, list: název listu}]               půjčeno
 *   'item'  [věc, název listu]                                         nová věc
 *   'back'  [věc (už po vrácení), {loan, back, lost, rest}]            vráceno (kolik, kolik ubylo, kolik si nechává)
 *   'order' [objednávka, [[věc, kolik]]]                               nová objednávka s vyzvednutím později
 *   'loans' [{who, due}, [[věc, kolik]]]                               půjčeno víc věcí najednou
 *   'backs' [null, [[věc, {loan, back, lost, rest}]]]                  vráceno víc výpůjček najednou
 * Uložení ve skladu na tom nikdy nestojí: když Discord neodpoví, vrátí se jen text chyby.
 */
function sk_hook_events($events)
{
    try {
        $c = sk_hook();
        if (!$events || $c['url'] === '') {
            return '';
        }
        $base = sk_public_base();
        $logs = array();
        $fail = '';
        $switch = array('loan' => 'loan', 'item' => 'item', 'back' => 'back', 'order' => 'loan', 'loans' => 'loan', 'backs' => 'back');
        $today = date('Y-m-d', sk_now());
        foreach ($events as $e) {
            list($type, $it, $list) = $e;
            if (!isset($switch[$type]) || empty($c['on'][$switch[$type]])) {
                continue;
            }
            $users = array();
            $tail = '';     // řádky pod zprávou (seznam věcí)
            if ($type === 'loan') {
                $l = $list['loan'];
                list($who, $users) = sk_who_md($c, $l['who']);
                $due = isset($l['due']) ? (string) $l['due'] : '';
                $text = '📤 **Půjčeno:** ' . sk_item_md($it, sk_hold_q($l)) . ' · má to ' . $who . ($due !== '' ? ' · vrátit do **' . sk_date_cs($due) . '**' : ' · bez termínu vrácení');
                $label = 'půjčeno: ' . $it['n'] . ' (' . $l['who'] . ')';
                $frag = 't=lent';
            } elseif ($type === 'order') {
                list($who, $users) = sk_who_md($c, $it['who']);
                $n = count($list);
                $text = '📅 **Objednávka č. ' . $it['no'] . ':** ' . $who . ' si **' . sk_when_cs($it['from'], $it['at'], $today) . '** vyzvedne '
                    . $n . ' ' . sk_plural($n, 'věc', 'věci', 'věcí') . ($it['due'] !== '' ? ' · vrátit do **' . sk_date_cs($it['due']) . '**' : '')
                    . ($it['note'] !== '' ? ' · ' . sk_md($it['note'], 200) : '');
                $tail = sk_lines_md($list);
                $label = 'objednávka č. ' . $it['no'] . ' (' . $it['who'] . ', ' . $n . ')';
                $frag = 't=lent';
            } elseif ($type === 'loans') {
                list($who, $users) = sk_who_md($c, $it['who']);
                $n = count($list);
                $text = '📤 **Půjčeno:** ' . $n . ' ' . sk_plural($n, 'věc', 'věci', 'věcí') . ' · má to ' . $who
                    . ($it['due'] !== '' ? ' · vrátit do **' . sk_date_cs($it['due']) . '**' : ' · bez termínu vrácení');
                $tail = sk_lines_md($list);
                $label = 'půjčeno: ' . $n . ' věcí (' . $it['who'] . ')';
                $frag = 't=lent';
            } elseif ($type === 'backs') {
                $n = count($list);
                $lines = array();
                foreach (array_slice($list, 0, 15) as $x) {
                    $r = $x[1];
                    $u = sk_unit($x[0]);
                    $lines[] = '• **' . sk_md($x[0]['n']) . '**' . ($r['back'] !== null ? ' (' . sk_part_cs($r['back'], $r['loan']['q'], $u) . ')' : '')
                        . ' · bylo u: **' . sk_md($r['loan']['who'], 80) . '**'
                        . ($r['rest'] > 0 ? ' · ' . sk_qty_cs($r['rest'], $u) . ' si ještě nechává' : '') . ($r['lost'] > 0 ? ' · ubylo ' . sk_qty_cs($r['lost'], $u) : '');
                }
                if ($n > 15) {
                    $lines[] = '• … a další (' . ($n - 15) . ')';
                }
                $text = '✅ **Vráceno:** ' . $n . ' ' . sk_plural($n, 'věc', 'věci', 'věcí');
                $tail = "\n" . implode("\n", $lines);
                $label = 'vráceno: ' . $n . ' věcí';
                $frag = 't=lent';
            } elseif ($type === 'back') {
                $l = $list['loan'];
                $u = sk_unit($it);
                $text = '✅ **Vráceno:** **' . sk_md($it['n']) . '**' . ($list['back'] !== null ? ' (' . sk_part_cs($list['back'], $l['q'], $u) . ')' : '')
                    . ' · bylo u: **' . sk_md($l['who'], 80) . '**';
                if ($list['rest'] > 0) {
                    $text .= ' · ' . sk_qty_cs($list['rest'], $u) . ' si ještě nechává';
                } elseif ($list['lost'] > 0) {
                    $text .= ' · ubylo ' . sk_qty_cs($list['lost'], $u) . (sk_count($it) !== null ? ', ve skladu je teď ' . sk_qty_cs(sk_count($it), $u) : '');
                }
                $label = 'vráceno: ' . $it['n'] . ' (' . $l['who'] . ')';
                $frag = 't=lent';
            } else {
                $p1 = isset($it['p1']) && $it['p1'] !== '-' ? (string) $it['p1'] : '';
                $p2 = isset($it['p2']) && $it['p2'] !== '-' ? (string) $it['p2'] : '';
                $place = trim($p1 . ($p1 !== '' && $p2 !== '' ? ' › ' : '') . $p2);
                $text = '🆕 **Nová věc ve skladu:** ' . sk_item_md($it) . ' · ' . sk_md($list, 60) . ($place !== '' ? ' · ' . sk_md($place, 160) : '');
                $label = 'nová věc: ' . $it['n'];
                $frag = 't=' . $it['s'];
            }
            list($ping, $pu, $pr) = sk_ping_md($c, $users);
            $err = sk_discord_send($c, $text . $ping . $tail . sk_link_md($base, $frag), array_merge($users, $pu), $pr);
            $logs[] = array($err === '', $label . ($err !== '' ? ': ' . $err : ''));
            if ($err !== '') {
                $fail = $err;
                break;      // když jedna zpráva neprojde, další by dopadla stejně
            }
        }
        if ($logs) {
            sk_hook_update(function (&$c) use ($logs, $base) {
                sk_found_flush($c);
                foreach ($logs as $l) {
                    sk_hook_log($c, $l[0], $l[1]);
                }
                if ($base !== '') {
                    $c['base'] = $base;
                }
            });
        }
        return $fail;
    } catch (Throwable $e) {
        return 'oznámení se nepodařilo připravit';
    }
}

/* ───── připomínky ───── */

/** Je čas zkusit připomínky? Levná kontrola při každém načtení stránky. */
function sk_hook_tick_due($c = null)
{
    $c = $c === null ? sk_hook() : $c;
    if ($c['url'] === '' || !$c['on']['remind']) {
        return false;
    }
    $now = sk_now();
    $h = (int) date('G', $now);
    return $h >= 8 && $h < 21 && $now - $c['last'] >= 900;      // ve dne a nejvýš jednou za čtvrt hodiny
}

/**
 * Které připomínky mají dnes odejít. Vrací [seznam [věc, výpůjčka, druh], stav k uložení].
 * Druh: 'b' den předem, 'd' v den vrácení, 'o' po termínu. Stav si pamatuje, co už které výpůjčce odešlo;
 * změní-li se u výpůjčky člověk nebo termín, počítá se od začátku.
 */
function sk_hook_due($c, $d, $today)
{
    $list = array();
    $state = array();
    foreach ($d['items'] as $it) {
        foreach ((isset($it['l']) ? $it['l'] : array()) as $l) {
            $who = (string) $l['who'];
            $due = isset($l['due']) ? (string) $l['due'] : '';
            if ($due === '') {
                continue;
            }
            $id = $l['id'];
            $s = isset($c['sent'][$id]) && $c['sent'][$id]['who'] === $who && $c['sent'][$id]['due'] === $due ? $c['sent'][$id] : array('who' => $who, 'due' => $due);
            $state[$id] = $s;
            $left = sk_days($today, $due);
            if ($left === 1 && $c['rem']['before'] && empty($s['b'])) {
                $list[] = array($it, $l, 'b');
            } elseif ($left === 0 && $c['rem']['day'] && empty($s['d'])) {
                $list[] = array($it, $l, 'd');
            } elseif ($left < 0 && $c['rem']['every'] > 0 && (empty($s['o']) || sk_days($s['o'], $today) >= $c['rem']['every'])) {
                $list[] = array($it, $l, 'o');
            }
        }
    }
    return array($list, $state);
}

/** Zpráva pro jednoho člověka o jeho výpůjčkách ($pairs = [[věc, výpůjčka]]). Vrací [text, ID k označení]. */
function sk_remind_message($c, $who, $pairs, $today, $base)
{
    list($name, $users) = sk_who_md($c, $who);
    $lines = array();
    foreach (array_slice($pairs, 0, 15) as $x) {
        $lines[] = sk_remind_line($x[0], $x[1], $today);
    }
    if (count($pairs) > 15) {
        $lines[] = '• … a další (' . (count($pairs) - 15) . ')';
    }
    return array('⏰ ' . $name . ', připomínka ze skladu:' . "\n" . implode("\n", $lines) . sk_link_md($base, 't=lent'), $users);
}

/**
 * Projde výpůjčky a pošle připomínky, které mají dnes odejít. Vrací počet odeslaných zpráv,
 * nebo -1, když na připomínky zrovna není čas (noc, vypnuto, před chvílí už se kontrolovalo).
 */
function sk_hook_tick()
{
    if (!sk_installed() || !sk_hook_tick_due()) {
        return -1;
    }
    $lock = @fopen(sk_dir() . '/.lock-tick', 'c');
    $busy = 0;
    if ($lock && !@flock($lock, LOCK_EX | LOCK_NB, $busy)) {
        @fclose($lock);
        $lock = null;
        if ($busy) {
            return -1;      // zrovna je posílá jiný požadavek
        }
    }
    try {
        if (!sk_hook_tick_due(sk_hook(true))) {
            return -1;
        }
        $now = sk_now();
        sk_hook_update(function (&$c) use ($now) {
            $c['last'] = $now;
        });
        $c = sk_hook();
        $today = date('Y-m-d', $now);
        list($list, $state) = sk_hook_due($c, sk_data(), $today);
        $groups = array();
        foreach ($list as $x) {
            $key = sk_fold($x[1]['who']);
            if (!isset($groups[$key])) {
                $groups[$key] = array('who' => $x[1]['who'], 'items' => array(), 'marks' => array());
            }
            $groups[$key]['items'][] = array($x[0], $x[1]);
            $groups[$key]['marks'][] = array($x[1]['id'], $x[2]);
        }
        $sent = 0;
        $logs = array();
        $start = microtime(true);
        foreach ($groups as $g) {
            if (microtime(true) - $start > 12) {
                break;      // zbytek při příští kontrole
            }
            list($text, $users) = sk_remind_message($c, $g['who'], $g['items'], $today, $c['base']);
            $err = sk_discord_send($c, $text, $users);
            $logs[] = array($err === '', 'připomínka: ' . $g['who'] . ' (' . count($g['items']) . ')' . ($err !== '' ? ': ' . $err : ''));
            if ($err !== '') {
                break;      // neodeslané zůstávají nepoznačené a zkusí se příště
            }
            $sent++;
            foreach ($g['marks'] as $m) {
                if ($m[1] === 'o') {
                    $state[$m[0]]['o'] = $today;
                } else {
                    $state[$m[0]][$m[1]] = 1;
                }
            }
        }
        sk_hook_update(function (&$c) use ($state, $logs) {
            sk_found_flush($c);
            $c['sent'] = $state;
            foreach ($logs as $l) {
                sk_hook_log($c, $l[0], $l[1]);
            }
        });
        return $sent;
    } finally {
        if ($lock) {
            @flock($lock, LOCK_UN);
            @fclose($lock);
        }
    }
}

/** Adresa pro cron hostingu ( …?cron ): zkontroluje připomínky a odpoví jednou větou. Nic tajného neprozradí. */
function sk_serve_cron()
{
    sk_headers();
    header('Content-Type: text/plain; charset=utf-8');
    try {
        $n = sk_hook_tick();
        echo $n < 0 ? SK_PRODUKT . ": teď není co posílat.\n" : SK_PRODUKT . ': připomínky zkontrolovány, odesláno zpráv: ' . $n . ".\n";
    } catch (Throwable $e) {
        http_response_code(500);
        echo SK_PRODUKT . ": připomínky se nepodařilo zkontrolovat.\n";
    }
}

/* ───── nastavení ve správě ───── */

/** Nová adresa webhooku z formuláře: ověří ji u Discordu a vrátí [adresa, název webhooku, ID serveru]. Když nesedí, řekne proč. */
function sk_save_webhook($raw)
{
    $url = sk_hook_url($raw);
    if ($url === '') {
        throw new SkError('Tohle není adresa webhooku Discordu. Má vypadat jako https://discord.com/api/webhooks/číslo/dlouhý-kód (Discord: Upravit kanál → Integrace → Webhooky → Kopírovat URL webhooku).');
    }
    $r = sk_http('GET', (string) preg_replace('/\?.*$/', '', $url));
    $err = sk_discord_error($r);
    if ($err !== '') {
        throw new SkError('Adresu webhooku se nepodařilo ověřit: ' . $err . '.');
    }
    $j = json_decode((string) $r['body'], true);
    return array(
        $url,
        is_array($j) && isset($j['name']) && is_string($j['name']) ? $j['name'] : '',
        is_array($j) && isset($j['guild_id']) ? sk_snowflake($j['guild_id']) : '',
    );
}

/** Nový token bota z formuláře: ověří ho u Discordu a vrátí [token, odpověď na „kdo jsem“]. Když nesedí, řekne proč. */
function sk_save_token($raw)
{
    $bot = sk_bot_token($raw);
    if ($bot === '') {
        throw new SkError('Tohle nevypadá jako token bota: ten má tři části oddělené tečkami. Najdeš ho v Discord Developer Portalu na stránce Bot pod tlačítkem Reset Token. Client Secret ani Public Key to nejsou.');
    }
    $r = sk_bot_get($bot, 'applications/@me');
    if ($r['status'] === 401) {
        throw new SkError('Discord tenhle token bota nezná. Zkopíruj ho znovu celý (Developer Portal → Bot → Reset Token).');
    }
    $app = $r['status'] === 200 ? json_decode((string) $r['body'], true) : null;
    if (!is_array($app) || !isset($app['id'])) {
        throw new SkError('Token bota se nepodařilo ověřit. ' . ($r['status'] === 200 ? 'Discord odpověděl nečekaně.' : sk_bot_error($r, false)));
    }
    return array($bot, $app);
}

/**
 * „Koho označit u oznámení“ z formuláře: člověk nebo role, číselným ID nebo jménem. $cur = co platí teď,
 * $eff = nastavení, jaké bude po uložení (s ním se hledá), $noBot = proč jména zrovna hledat nejde (prázdné = jde to),
 * $wait = stačí počkat na pozvání bota. Vrací [uložená hodnota, její podoba jménem, chyba, chyba je jen čekání].
 * Když se nové jméno dohledat nepodaří, zůstává v platnosti, co bylo.
 */
function sk_save_ping($raw, $cur, $eff, $noBot, $wait)
{
    $raw = sk_text($raw, 80);
    $ping = $cur['ping'];
    $label = $cur['pingH'];
    $p = sk_ping($raw);
    if ($raw === '') {
        return array('', '', '', false);
    }
    if ($ping !== '' && ($raw === $label || $p === $ping)) {
        // beze změny; k ID zapsanému dřív (bez bota) se jen doplní jméno, když už ho bot umí zjistit
        if ($label === '' && $ping[0] !== '&' && $noBot === '' && $eff['bot'] !== '') {
            $m = sk_discord_member($eff, $ping);
            if (isset($m['u']) && $m['u'] !== '') {
                $label = '@' . $m['u'];
            }
        }
        return array($ping, $label, '', false);
    }
    if ($p !== '') {
        // číselné ID: s botem se u člověka ověří, že na serveru je, a doplní se jméno
        $m = $p[0] !== '&' && $noBot === '' ? sk_discord_member($eff, $p) : array();
        if (!empty($m['gone'])) {
            return array($ping, $label, 'Tohle ID na vašem serveru nikomu nepatří. Není to ID zprávy nebo kanálu?', false);
        }
        return array($p, isset($m['u']) && $m['u'] !== '' ? '@' . $m['u'] : '', '', false);
    }
    if ($noBot !== '') {
        return array($ping, $label, $noBot, $wait);
    }
    $isRole = (bool) preg_match('/^@?&/', $raw);
    $r = $isRole ? array('error' => '', 'soft' => true) : sk_discord_lookup($eff, $raw, false);
    if (isset($r['id'])) {
        return array($r['id'], '@' . ($r['u'] !== '' ? $r['u'] : $r['h']), '', false);
    }
    if (empty($r['soft'])) {
        return array($ping, $label, $r['error'], false);        // bot chybí nebo neodpovídá
    }
    $role = sk_discord_role($eff, $raw);        // člověk takový není: třeba je to role
    if (isset($role['id'])) {
        return array('&' . $role['id'], '&' . $role['name'], '', false);
    }
    if ($isRole) {
        return array($ping, $label, $role['error'], false);
    }
    return array($ping, $label, !empty($r['cand']) ? sk_lookup_text($r) : 'Na serveru není člověk ani role s názvem „' . ltrim($raw, '@ ') . '“.', false);
}

/**
 * Řádky lidí z formuláře: [jméno ve skladu, číselné ID nebo jméno z Discordu]. Parametry jako u sk_save_ping.
 * Vrací [lidé k uložení, řádky k zobrazení]. Řádek, který se nepovedl, se vrátí zpátky tak, jak ho člověk napsal,
 * s vysvětlením (e), případně s nabídkou podobných jmen (c) a s poznámkou, že jen čeká na bota (wait).
 */
function sk_save_people($in, $cur, $eff, $noBot, $wait)
{
    $byKey = array();
    $byId = array();
    $byU = array();
    foreach ($cur['people'] as $q) {
        $byKey[sk_name_key($q['n'])] = $q;
        $byId['u' . $q['d']] = $q;
        if (isset($q['u'])) {
            $byU['@' . $q['u']] = $q;       // přesně tak, jak uložené jméno posílá formulář zpátky
        }
    }
    $people = array();
    $rows = array();
    $seen = array();
    foreach ((is_array($in) ? array_slice($in, 0, 400) : array()) as $row) {
        $n = sk_text(is_array($row) && isset($row['n']) ? $row['n'] : '', 80);
        $raw = sk_text(is_array($row) && isset($row['d']) ? $row['d'] : '', 80);
        if ($n === '' && $raw === '') {
            continue;
        }
        $id = sk_snowflake($raw);
        $name = $n !== '' || $id !== '' ? $n : sk_text(ltrim($raw, "@ \t"), 80);       // vyplněné jen jméno z Discordu: platí i pro sklad
        $key = sk_name_key($name);
        $x = null;
        $fail = array('e' => '', 'c' => array());
        if ($name === '') {
            $fail['e'] = 'K tomuhle ID chybí jméno, pod kterým člověka píšeš u výpůjček.';
        } elseif ($key === '') {
            $fail['e'] = 'Jméno musí obsahovat aspoň jedno písmeno nebo číslici.';
        } elseif (isset($seen[$key])) {
            $fail['e'] = '„' . $name . '“ už v seznamu je.';
        } elseif (count($people) >= 300) {
            $fail['e'] = 'Seznam je plný (300 lidí).';
        } elseif ($id !== '') {
            // číselné ID: s botem se ověří, že na serveru je, a doplní se jméno (u ID, ke kterému ho už známe, ne)
            $old = isset($byId['u' . $id]) && isset($byId['u' . $id]['u']) ? $byId['u' . $id] : null;
            $m = $old !== null ? $old : ($noBot === '' ? sk_discord_member($eff, $id) : array());
            if (!empty($m['gone'])) {
                $fail['e'] = 'Tohle ID na vašem serveru nikomu nepatří. Není to ID zprávy nebo kanálu?';
            } else {
                $x = array('n' => $name, 'd' => $id);
                if (isset($m['u']) && $m['u'] !== '') {
                    $x['u'] = $m['u'];
                    $x['h'] = isset($m['h']) ? $m['h'] : '';
                }
            }
        } else {
            // jméno z Discordu (prázdná druhá kolonka = stejné jako ve skladu)
            $q = ltrim($raw !== '' ? $raw : $name, "@ \t");
            if (isset($byU['@' . $q])) {
                $old = $byU['@' . $q];
                $x = array('n' => $name, 'd' => $old['d'], 'u' => $old['u'], 'h' => isset($old['h']) ? $old['h'] : '');
            } elseif ($noBot !== '') {
                $fail['e'] = $noBot;
                if ($wait) {
                    $fail['wait'] = true;
                }
            } else {
                $r = sk_discord_lookup($eff, $q, false);
                if (isset($r['id'])) {
                    $x = array('n' => $name, 'd' => $r['id'], 'u' => $r['u'], 'h' => $r['h']);
                } else {
                    $fail['e'] = sk_lookup_ask($r);
                    $fail['c'] = isset($r['cand']) ? $r['cand'] : array();
                }
            }
        }
        if ($x !== null) {
            $seen[$key] = true;
            $people[] = $x;
            $rows[] = $x;
            continue;
        }
        $rows[] = array('n' => $n, 'd' => $raw) + $fail;
        if ($key !== '' && !isset($seen[$key]) && isset($byKey[$key]) && count($people) < 300) {
            $seen[$key] = true;
            $people[] = $byKey[$key];       // co platilo dosud, platí dál, dokud to nenahradí něco, co jde dohledat
        }
    }
    return array($people, $rows);
}

/**
 * Uloží nastavení Discordu z formuláře ve správě. Novou adresu webhooku a nový token bota nejdřív u Discordu ověří.
 * Lidi (i toho, kdo má dostávat oznámení) jde zapsat číselným ID, nebo jménem z Discordu: to si sklad dohledá přes bota.
 * Co se dohledat nepodaří, neuloží se a vrátí se s vysvětlením zpátky do formuláře; zbytek se uloží normálně.
 * Vrací ['hook' => nastavení pro prohlížeč, 'rows' => řádky lidí k zobrazení, 'pingError' => proč nejde „koho označit“].
 */
function sk_hook_save($in)
{
    $cur = sk_hook(true);
    $off = !empty($in['off']);
    $botOff = $off || !empty($in['botOff']);

    // 1) nová adresa webhooku a nový token bota: obojí se nejdřív ověří u Discordu
    $urlRaw = isset($in['url']) && is_string($in['url']) ? trim($in['url']) : '';
    list($url, $name, $guild) = !$off && $urlRaw !== '' ? sk_save_webhook($urlRaw) : array('', '', '');
    $botRaw = isset($in['bot']) && is_string($in['bot']) ? trim($in['bot']) : '';
    list($bot, $app) = !$botOff && $botRaw !== '' ? sk_save_token($botRaw) : array('', null);

    // 2) jak bude nastavení vypadat (podle toho se hledají jména) a jak je na tom bot
    $eff = $cur;
    if ($off) {
        $eff['url'] = '';
        $eff['guild'] = '';
    } elseif ($url !== '') {
        $eff['url'] = $url;
        if ($guild !== $eff['guild']) {
            $eff['guild'] = $guild;
            $eff['guildName'] = '';
            $eff['botIn'] = false;
        }
    }
    if ($botOff) {
        $eff['bot'] = '';
    } elseif ($bot !== '') {
        $eff['bot'] = $bot;
        $eff['botIn'] = false;
        $eff['botAll'] = false;
    }
    $probe = sk_bot_probe($eff, $app);
    foreach ($probe as $k => $v) {
        $eff[$k] = $v;
    }
    // proč teď jména hledat nejde (a jestli stačí počkat, až bude bot na serveru)
    $noBot = '';
    $wait = false;
    if ($eff['bot'] !== '' && $eff['botErr'] !== '') {
        $noBot = 'Token bota už neplatí. Vlož nový a ulož znovu.';
    } elseif ($eff['bot'] !== '' && $eff['guild'] !== '' && !$eff['botIn']) {
        $noBot = 'Bot ještě není na vašem serveru. Pozvi ho odkazem nahoře a ulož znovu.';
        $wait = true;
    }

    // 3) jména z formuláře: na jejich dohledání je deset vteřin, co se nestihne, vrátí se do formuláře
    $st = &sk_members();
    $st['until'] = microtime(true) + 10;
    list($ping, $pingH, $pingError, $pingWait) = sk_save_ping(isset($in['ping']) ? $in['ping'] : '', $cur, $eff, $noBot, $wait);
    list($people, $rows) = sk_save_people(isset($in['people']) ? $in['people'] : null, $cur, $eff, $noBot, $wait);

    // 4) zápis
    $base = sk_public_base();
    $hadBot = $cur['bot'] !== '';
    sk_hook_update(function (&$c) use ($in, $people, $ping, $pingH, $off, $botOff, $url, $name, $guild, $bot, $probe, $base, $hadBot) {
        if ($off) {
            $c['url'] = '';
            $c['name'] = '';
            $c['guild'] = '';
            $c['guildName'] = '';
            $c['sent'] = array();
        } elseif ($url !== '') {
            $c['url'] = $url;
            $c['name'] = $name;
            if ($guild !== $c['guild']) {
                $c['guild'] = $guild;
                $c['guildName'] = '';
                $c['botIn'] = false;
            }
        }
        if ($botOff) {
            $c['bot'] = '';
            $c['botName'] = '';
            $c['botId'] = '';
            $c['botIn'] = false;
            $c['botAll'] = false;
            $c['botErr'] = '';
        } elseif ($bot !== '') {
            $c['bot'] = $bot;
            $c['botErr'] = '';
        }
        if ($c['bot'] !== '') {
            foreach ($probe as $k => $v) {
                $c[$k] = $v;
            }
        }
        if (isset($in['on']) && is_array($in['on'])) {
            foreach (array('loan', 'item', 'back', 'remind') as $k) {
                $c['on'][$k] = !empty($in['on'][$k]);
            }
        }
        if (isset($in['rem']) && is_array($in['rem'])) {
            $c['rem'] = array(
                'before' => !empty($in['rem']['before']),
                'day' => !empty($in['rem']['day']),
                'every' => isset($in['rem']['every']) ? (int) $in['rem']['every'] : 0,
            );
        }
        $c['ping'] = $ping;
        $c['pingH'] = $ping !== '' ? $pingH : '';
        $c['people'] = $people;
        $c['miss'] = array();       // po uložení se nenalezená jména zkusí znovu
        if ($base !== '') {
            $c['base'] = $base;
        }
        sk_found_flush($c);         // co se cestou ukázalo o botovi (je na serveru, smí číst seznam členů)
        sk_hook_log($c, true, $off ? 'Discord vypnutý' : ($botOff && $hadBot ? 'bot odebrán' : ($bot !== '' ? 'nastavení uloženo, nový token bota' : 'nastavení uloženo')));
    });
    $out = array('hook' => sk_hook_public(sk_hook()), 'rows' => $rows);
    if ($pingError !== '') {
        $out['pingError'] = $pingError;
        if ($pingWait) {
            $out['pingWait'] = true;
        }
    }
    return $out;
}

/** Zkušební zpráva: obecná, nebo s označením člověka ze seznamu. */
function sk_hook_test($in)
{
    $c = sk_hook();
    if ($c['url'] === '') {
        throw new SkError('Discord ještě není zapnutý. Vlož adresu webhooku a ulož.');
    }
    $person = sk_text(isset($in['person']) ? $in['person'] : '', 80);
    if ($person !== '') {
        list($who, $users) = sk_who_md($c, $person);
        if (!$users) {
            $m = &sk_found();
            $why = $m['why'] ? reset($m['why']) : null;
            throw new SkError('„' . $person . '“ zatím nemá uložené, koho na Discordu označit.' . ($why ? ' ' . $why[1] : '') . ' Doplň to v seznamu lidí a ulož.');
        }
        $text = '👋 ' . $who . ', zkouška označení ze skladu. Jestli ti přišlo upozornění, funguje to.';
        $roles = array();
    } else {
        list($ping, $users, $roles) = sk_ping_md($c);
        $text = '👋 Zkouška ze skladu: oznámení sem chodí.' . $ping;
    }
    $err = sk_discord_send($c, $text . sk_link_md(sk_public_base(), 't=lent'), $users, $roles);
    sk_hook_update(function (&$c) use ($err, $person) {
        sk_found_flush($c);
        sk_hook_log($c, $err === '', 'zkušební zpráva' . ($person !== '' ? ' (' . $person . ')' : '') . ($err !== '' ? ': ' . $err : ''));
    });
    if ($err !== '') {
        throw new SkError('Zkušební zpráva neodešla: ' . $err . '.', 502);
    }
    return sk_hook_public(sk_hook());
}

/** Připomínka jedné výpůjčky hned teď (tlačítko ve správě). $id = id výpůjčky. */
function sk_hook_remind_now($id)
{
    $c = sk_hook();
    if ($c['url'] === '') {
        throw new SkError('Discord není zapnutý. Zapneš ho v Nastavení.');
    }
    $d = sk_data();
    $at = sk_loan_find($d, $id);
    if ($at === null) {
        throw new SkError('Tahle výpůjčka už není. Mezitím ji někdo vrátil.', 404);
    }
    $it = $d['items'][$at[0]];
    $l = $it['l'][$at[1]];
    $today = date('Y-m-d', sk_now());
    list($text, $users) = sk_remind_message($c, $l['who'], array(array($it, $l)), $today, sk_public_base());
    $err = sk_discord_send($c, $text, $users);
    sk_hook_update(function (&$c) use ($err, $it, $l) {
        sk_found_flush($c);
        sk_hook_log($c, $err === '', 'připomínka ručně: ' . $l['who'] . ' (' . $it['n'] . ')' . ($err !== '' ? ': ' . $err : ''));
    });
    if ($err !== '') {
        throw new SkError('Připomínka neodešla: ' . $err . '.', 502);
    }
    return array('pinged' => (bool) $users);
}

/** Zeptá se Discordu znovu, jak je na tom bot (po pozvání na server se to ve správě ukáže samo). */
function sk_hook_check()
{
    $probe = sk_bot_probe(sk_hook(true));
    if ($probe) {
        sk_hook_update(function (&$c) use ($probe) {
            if ($c['bot'] !== '') {
                foreach ($probe as $k => $v) {
                    $c[$k] = $v;
                }
            }
        });
    }
    return sk_hook_public(sk_hook());
}

/* ───────────────────────── stránky ───────────────────────── */

/**
 * Značka v záhlaví (soubor edice: Titio piktogram oddílu, veřejná verze kartotéční lístek, HB logo hnutí).
 * $cls = třída pro styly; bez ní je to obrázek pro ikonu stránky (ta může mít v edici vlastní, menší podobu).
 */
function sk_mark($cls = '', $icon = false)
{
    $svg = trim($icon && sk_asset('ikona') !== '' ? sk_asset('ikona') : sk_asset('znacka'));
    return '<svg' . ($cls !== '' ? ' class="' . $cls . '"' : '') . substr($svg, 4);
}

/** Název do prostého textu: stříška před T (značka projektů Titia) se nepíše. „Sklad^T“ → „SkladT“. */
function sk_title_plain($t)
{
    return str_replace('^', '', (string) $t);
}

/**
 * Název jako značka. S písmem Titio velkými písmeny a s malým nakloněným T (v tom písmu je to malé „t“),
 * jinak obyčejně s T v horním indexu.
 */
function sk_title_html($t, $titio)
{
    $t = (string) $t;
    $p = strrpos($t, '^');
    $base = $p === false ? $t : substr($t, 0, $p);
    $sup = $p === false ? '' : substr($t, $p + 1);
    if ($titio) {
        // Písmo Titio umí jen velká písmena (převede je prohlížeč) a horní „T“ má jako vlastní znak pod malým „t“.
        $mark = $sup === 'T' ? '<span class="wm-m">t</span>' : ($sup !== '' ? '<sup>' . sk_e($sup) . '</sup>' : '');
        return '<span class="wm wm-t"><span class="wm-b">' . sk_e($base) . '</span>' . $mark . '</span>';
    }
    return '<span class="wm">' . sk_e($base) . ($sup !== '' ? '<sup>' . sk_e($sup) . '</sup>' : '') . '</span>';
}

/**
 * Která písma se použijí. Titio: Delm a Titio, HB: Delm, obojí ze serveru (podle toho, co tam leží a co je zapnuté
 * v nastavení). Veřejná verze má písma vložená v souboru (own).
 */
function sk_brand($site)
{
    $f = sk_fonts();
    $on = !isset($site['fonts']) || !empty($site['fonts']);
    $own = sk_own_fonts() !== array();
    return array('delm' => !$own && $on && isset($f['delm']['400']), 'caps' => SK_EDICE === 'titio' && $on && isset($f['delm']['400']) && $f['titio'],
        'found' => isset($f['delm']['400']), 'own' => $own, 'edice' => SK_EDICE);
}

/** Písma vložená v souboru (veřejná verze): [{name, family, weight, data}], jinak prázdné pole. */
function sk_own_fonts()
{
    static $list = null;
    if ($list === null) {
        $j = json_decode(sk_asset('pisma'), true);
        $list = is_array($j) && isset($j['pisma']) && is_array($j['pisma']) ? $j['pisma'] : array();
    }
    return $list;
}

function sk_font_css($brand)
{
    $css = '';
    if ($brand['own']) {
        foreach (sk_own_fonts() as $p) {
            $css .= '@font-face{font-family:"' . $p['family'] . '";font-style:normal;font-weight:' . (int) $p['weight'] . ';font-display:swap;'
                . 'src:url(?pismo=' . $p['name'] . '&v=' . SK_VERSION . ') format("woff2")}';
        }
        return $css;
    }
    if (!$brand['delm']) {
        return $css;
    }
    $f = sk_fonts();
    foreach ($f['delm'] as $w => $file) {
        $head = '@font-face{font-family:"Delm";font-style:normal;font-weight:' . $w . ';font-display:swap;src:url(?pismo=' . $file . '&v=' . SK_VERSION . ') format("woff2");';
        // Číslice zvlášť: Delm přitahuje tečku a čárku pod sedmičku tak silně, že „17.7.“ vypadá jako „177.“
        // a „7,5 kg“ jako „75 kg“. Obě pravidla vedou na stejný soubor, jen se mezi číslicí a okolím nepoužije kerning.
        $css .= $head . 'unicode-range:U+30-39}' . $head . 'unicode-range:U+0-2F,U+3A-10FFFF}';
    }
    if ($brand['caps']) {
        // Nadpisové písmo Titio umí jen velká písmena, číslice a pár znamének; malé „t“ je v něm značka projektů.
        $css .= '@font-face{font-family:"Titio";font-style:normal;font-weight:400;font-display:swap;src:url(?pismo=titio&v=' . SK_VERSION . ') format("woff2");'
            . 'unicode-range:U+21,U+28-29,U+2B-3A,U+3C-3F,U+41-5A,U+5F,U+74,U+7C,U+C0-C5,U+C7-CF,U+D1-D6,U+D9-DD,U+10C,U+10E,U+11A,U+147,U+158,U+160,U+164,U+16E,U+17D,U+2013-2014,U+2026,U+2212}';
    }
    return $css;
}

function sk_head($title, $brand)
{
    $n = sk_nonce();
    $cls = trim(($brand['delm'] ? 'delm ' : '') . ($brand['caps'] ? 'titio ' : '') . ($brand['own'] ? 'own' : ''));
    $h = '<!doctype html>' . "\n" . '<html lang="cs"' . ($cls !== '' ? ' class="' . $cls . '"' : '') . '>' . "\n<head>\n"
        . '<meta charset="utf-8">' . "\n"
        . '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">' . "\n"
        . '<meta name="robots" content="noindex, nofollow">' . "\n"
        . '<meta name="theme-color" content="#ffffff">' . "\n"
        . '<meta name="format-detection" content="telephone=no">' . "\n"
        . '<title>' . sk_e($title) . '</title>' . "\n"
        . '<link rel="icon" href="data:image/svg+xml,' . rawurlencode(sk_mark('', true)) . '">' . "\n";
    if ($brand['delm']) {
        $h .= '<link rel="preload" as="font" type="font/woff2" href="?pismo=delm-400&amp;v=' . SK_VERSION . '" crossorigin>' . "\n";
    }
    foreach ($brand['own'] ? array_slice(sk_own_fonts(), 0, 1) : array() as $p) {
        $h .= '<link rel="preload" as="font" type="font/woff2" href="?pismo=' . $p['name'] . '&amp;v=' . SK_VERSION . '" crossorigin>' . "\n";
    }
    $h .= '<style nonce="' . $n . '">' . sk_font_css($brand) . "\n" . sk_asset('css') . '</style>' . "\n</head>\n";
    return $h;
}

/** Malá stránka s formulářem (první spuštění, přihlášení, nové heslo, chyba). */
function sk_page_small($title, $inner, $site = null)
{
    $site = sk_site($site);
    $brand = sk_brand($site);
    return sk_head($title, $brand) . '<body class="gate">' . "\n"
        . '<main class="gate-in"><div class="brand">' . sk_mark('mark') . sk_title_html($site['title'], $brand['caps']) . '</div>'
        . $inner
        . '<p class="ver">' . SK_PRODUKT . ', verze ' . SK_VERSION . '</p></main>' . "\n</body>\n</html>\n";
}

function sk_site_now()
{
    try {
        $d = sk_data();
        return $d['site'];
    } catch (Throwable $e) {
        return null;
    }
}

function sk_err_html($error)
{
    return $error !== '' ? '<p class="msg bad" role="alert">' . sk_e($error) . '</p>' : '';
}

function sk_page_setup($error, $vals, $writable)
{
    $h = '<form class="card" method="post" autocomplete="off">'
        . '<input type="hidden" name="akce" value="setup">'
        . '<h1>Sklad je skoro otevřený</h1>'
        . '<p class="lead">Zbývá vymyslet hesla. Věci pak přidáš ve správě, nebo je nahraješ z tabulky.</p>';
    if (!$writable) {
        $h .= '<p class="msg bad" role="alert">Vedle souboru se nepodařilo založit složku <b>' . sk_e(SK_DATA) . '</b>. '
            . 'Vytvoř ji přes FTP a povol do ní zápis (práva 755, případně 775), pak tuhle stránku načti znovu.</p>';
    }
    $h .= sk_err_html($error)
        . '<label class="fld"><span>Název</span>'
        . '<input type="text" name="nazev" maxlength="60" value="' . sk_e(isset($vals['nazev']) ? $vals['nazev'] : SK_NAZEV) . '">'
        . '<small>Jak se sklad jmenuje: v záhlaví stránky a v titulku okna.</small></label>'
        . '<label class="fld"><span>Heslo správce</span>'
        . '<input type="password" name="heslo" minlength="8" maxlength="200" required autocomplete="new-password">'
        . '<small>Aspoň 8 znaků. Kdo ho zná, může věci přidávat, měnit i mazat.</small></label>'
        . '<label class="fld"><span>Heslo správce ještě jednou</span>'
        . '<input type="password" name="heslo2" minlength="8" maxlength="200" required autocomplete="new-password"></label>'
        . '<label class="fld"><span>Heslo pro nahlížení <i>(nepovinné)</i></span>'
        . '<input type="password" name="nahled" minlength="4" maxlength="200" autocomplete="new-password">'
        . '<small>S ním jde přehled jen číst. Když ho nevyplníš, uvidí přehled každý, kdo zná adresu. Dá se kdykoli změnit ve správě.</small></label>'
        . '<button class="btn pri wide" type="submit"' . ($writable ? '' : ' disabled') . '>Otevřít sklad</button>'
        . '</form>';
    return sk_page_small(SK_PRODUKT . ': první spuštění', $h, array('title' => isset($vals['nazev']) && $vals['nazev'] !== '' ? $vals['nazev'] : SK_NAZEV));
}

function sk_page_login($admin, $error, $name = '')
{
    $named = $admin && sk_users();      // když jsou uživatelé, přihlašuje se do správy jménem a heslem
    $h = '<form class="card" method="post">'
        . '<input type="hidden" name="akce" value="login">'
        . ($admin
            ? '<h1>Správa skladu</h1><p class="lead">' . ($named ? 'Zadej své jméno a heslo. Hlavní správce jméno vyplňovat nemusí.' : 'Zadej heslo správce.') . '</p>'
            : '<h1>Jen pro své lidi</h1><p class="lead">Přehled se otevře po zadání hesla.</p>')
        . sk_err_html($error);
    if ($named) {
        $h .= '<label class="fld"><span>Jméno</span>'
            . '<input type="text" name="jmeno" maxlength="60" value="' . sk_e($name) . '" autocomplete="username" autocapitalize="off" spellcheck="false"' . ($name === '' ? ' autofocus' : '') . '></label>';
    }
    $h .= '<label class="fld"><span>Heslo</span>'
        . '<input type="password" name="heslo" maxlength="200" required' . (!$named || $name !== '' ? ' autofocus' : '') . ' autocomplete="current-password"></label>'
        . '<button class="btn pri wide" type="submit">' . ($admin ? 'Přihlásit se' : 'Otevřít přehled') . '</button>'
        . '</form>';
    if ($admin) {
        $h .= '<details class="hint"><summary>Heslo ti vypadlo z hlavy?</summary>'
            . ($named ? '<p>Uživatelům nastaví nové heslo hlavní správce (Nastavení → Uživatelé).</p>' : '')
            . '<p>' . ($named ? 'Hlavní správce: nahraj' : 'Nahraj') . ' přes FTP vedle souboru aplikace prázdný soubor <b>reset-hesla.txt</b> a načti tuhle stránku znovu. '
            . 'Zeptá se na nové heslo správce. Věci ve skladu zůstanou, jak jsou.</p></details>'
            . '<p class="back"><a href="' . sk_e(sk_self()) . '">Zpět na přehled</a></p>';
    }
    return sk_page_small($admin ? 'Správa skladu' : sk_title_plain(sk_site(sk_site_now())['title']), $h, sk_site_now());
}

function sk_page_reset($error)
{
    $h = '<form class="card" method="post" autocomplete="off">'
        . '<input type="hidden" name="akce" value="reset">'
        . '<h1>Nové heslo správce</h1>'
        . '<p class="lead">Našel jsem soubor reset-hesla.txt. Vymysli nové heslo, věci ve skladu zůstanou, jak jsou.</p>'
        . sk_err_html($error)
        . '<label class="fld"><span>Nové heslo správce</span>'
        . '<input type="password" name="heslo" minlength="8" maxlength="200" required autofocus autocomplete="new-password">'
        . '<small>Aspoň 8 znaků.</small></label>'
        . '<label class="fld"><span>Nové heslo ještě jednou</span>'
        . '<input type="password" name="heslo2" minlength="8" maxlength="200" required autocomplete="new-password"></label>'
        . '<button class="btn pri wide" type="submit">Uložit nové heslo</button>'
        . '</form>';
    return sk_page_small('Nové heslo správce', $h, sk_site_now());
}

function sk_page_error($message)
{
    return sk_page_small(SK_PRODUKT, '<div class="card"><h1>Tady se něco zadrhlo</h1><p class="msg bad" role="alert">' . sk_e($message) . '</p></div>', null);
}

/** Přehled (a s $admin i správa): kostra stránky, data pro skript a skript. */
function sk_page_app($admin)
{
    $d = sk_data();
    $brand = sk_brand($d['site']);
    $n = sk_nonce();
    $boot = array(
        'v' => SK_VERSION,
        'admin' => $admin,
        'isAdmin' => sk_is_admin(),
        'super' => $admin && sk_is_super(),         // hlavní správce: Discord, hesla, uživatelé, název, písmo a zálohy
        'me' => $admin ? sk_actor() : '',
        'locked' => sk_view_locked(),
        'logged' => sk_session()['r'] !== '',
        'csrf' => sk_csrf(),
        'brand' => array('caps' => $brand['caps'], 'found' => $brand['found']),   // caps: nadpisy jen velkými písmeny (písmo Titio)
        'product' => SK_PRODUKT,
        'tick' => sk_hook_tick_due(),               // je čas zkontrolovat připomínky? (prohlížeč pak potichu zavolá …?api=tick)
        'data' => sk_data_public($d),
    );
    if ($admin) {
        $boot['hook'] = sk_hook_public(sk_hook());  // nastavení Discordu jen do správy, a bez adresy webhooku
        if (sk_is_super()) {
            $boot['users'] = sk_users_public();
            $a = sk_auth();
            $boot['superName'] = isset($a['an']) && is_string($a['an']) ? $a['an'] : '';
        }
    }
    $title = sk_title_plain($d['site']['title']);
    return sk_head($admin ? $title . ' · správa' : $title, $brand)
        . '<body class="app' . ($admin ? ' is-admin' : '') . '">' . "\n"
        . '<a class="skip" href="#list">Přeskočit na seznam</a>' . "\n"
        . '<header class="top"><a class="brand" href="' . sk_e(sk_self() . ($admin ? '?admin' : '')) . '">' . sk_mark('mark') . sk_title_html($d['site']['title'], $brand['caps']) . '</a>'
        . '<div class="top-r" id="topr"></div></header>' . "\n"
        . '<div id="app"><noscript><p class="msg bad">Přehled potřebuje zapnutý JavaScript.</p></noscript></div>' . "\n"
        . sk_asset('icons') . "\n"
        . '<script type="application/json" id="boot">' . sk_json_html($boot) . '</script>' . "\n"
        . '<script nonce="' . $n . '">' . sk_asset('js') . '</script>' . "\n"
        . "</body>\n</html>\n";
}

/* ───────────────────────── vstup ───────────────────────── */

function sk_main()
{
    if (isset($_GET['pismo'])) {
        sk_serve_font($_GET['pismo']);
        return;
    }
    if (isset($_GET['api'])) {
        sk_api(is_string($_GET['api']) ? $_GET['api'] : '');
        return;
    }
    if (isset($_GET['foto'])) {
        sk_serve_photo($_GET['foto'], !empty($_GET['t']));
        return;
    }
    if (isset($_GET['export'])) {
        sk_export();
        return;
    }
    if (isset($_GET['cron'])) {
        sk_serve_cron();
        return;
    }
    sk_headers(true);
    sk_gzip();
    try {
        echo sk_route();
    } catch (SkError $e) {
        http_response_code($e->status >= 400 ? $e->status : 500);
        echo sk_page_error($e->getMessage());
    } catch (Throwable $e) {
        http_response_code(500);
        echo sk_page_error('Na serveru se něco pokazilo. Zkus stránku načíst znovu.');
    }
}

/** Vrátí HTML stránky, která se má ukázat (nebo přesměruje). */
function sk_route()
{
    $post = sk_method() === 'POST';
    $akce = $post && isset($_POST['akce']) && is_string($_POST['akce']) ? $_POST['akce'] : '';
    if ($post && sk_cross_site()) {
        throw new SkError('Formulář přišel z jiné stránky. Otevři sklad znovu a zkus to ještě jednou.', 403);
    }
    $wantAdmin = isset($_GET['admin']);
    $p = function ($k) {
        return isset($_POST[$k]) && is_string($_POST[$k]) ? $_POST[$k] : '';
    };

    /* ── první spuštění ── */
    if (!sk_installed()) {
        $writable = sk_dir_ensure();
        $vals = array('nazev' => $post ? sk_text($p('nazev'), 60) : SK_NAZEV);
        if ($akce !== 'setup' || !$writable) {
            return sk_page_setup('', $vals, $writable);
        }
        $pass = $p('heslo');
        $view = $p('nahled');
        try {
            sk_password_check($pass, 8, 'Heslo správce');
            if ($pass !== $p('heslo2')) {
                throw new SkError('Hesla správce se neshodují. Napiš je obě znovu.');
            }
            if ($view !== '') {
                sk_password_check($view, 4, 'Heslo pro nahlížení');
                if ($view === $pass) {
                    throw new SkError('Heslo pro nahlížení musí být jiné než heslo správce.');
                }
            }
        } catch (SkError $e) {
            return sk_page_setup($e->getMessage(), $vals, $writable);
        }
        sk_locked(function () use ($pass, $view, $vals) {
            if (sk_installed()) {
                return;     // někdo to stihl o chlup dřív
            }
            if (sk_file_read(sk_dir() . '/data.php') === null) {
                $d = sk_seed();
                $d['site']['title'] = $vals['nazev'] !== '' ? $vals['nazev'] : SK_NAZEV;
                sk_log($d, 'sklad založen', '');
                if (!sk_file_write(sk_dir() . '/data.php', $d)) {
                    throw new SkError('Do složky ' . SK_DATA . ' se nedá zapisovat. Povol do ní zápis přes FTP a zkus to znovu.', 500);
                }
            }
            $ok = sk_auth_save(array(
                'v' => 1,
                'admin' => password_hash($pass, PASSWORD_DEFAULT),
                'view' => $view !== '' ? password_hash($view, PASSWORD_DEFAULT) : '',
                'secret' => bin2hex(random_bytes(32)),
                'fails' => array(),
                'reset' => sk_reset_mark(),
            ));
            if (!$ok) {
                throw new SkError('Do složky ' . SK_DATA . ' se nedá zapisovat. Povol do ní zápis přes FTP a zkus to znovu.', 500);
            }
        });
        sk_login('a');
        sk_redirect('admin');
    }

    /* ── zapomenuté heslo ── */
    if ($wantAdmin && !sk_is_admin() && sk_reset_pending()) {
        if ($akce !== 'reset') {
            return sk_page_reset('');
        }
        $pass = $p('heslo');
        try {
            sk_password_check($pass, 8, 'Heslo správce');
            if ($pass !== $p('heslo2')) {
                throw new SkError('Hesla se neshodují. Napiš je obě znovu.');
            }
        } catch (SkError $e) {
            return sk_page_reset($e->getMessage());
        }
        sk_locked(function () use ($pass) {
            $a = sk_auth(true);
            $a['admin'] = password_hash($pass, PASSWORD_DEFAULT);
            $a['reset'] = sk_reset_mark();
            $a['fails'] = array();
            if (!sk_auth_save($a)) {
                throw new SkError('Nové heslo se nepodařilo uložit. Zkontroluj práva složky ' . SK_DATA . '.', 500);
            }
        });
        @unlink(sk_reset_file());
        sk_login('a');
        sk_redirect('admin');
    }

    /* ── přihlášení (do správy jménem a heslem, hlavní správce jen heslem) ── */
    if ($akce === 'login') {
        $name = $wantAdmin ? sk_text($p('jmeno'), 60) : '';
        try {
            list($role, $uid) = sk_try_login($name, $p('heslo'), $wantAdmin);
        } catch (SkError $e) {
            http_response_code(429);
            return sk_page_login($wantAdmin, $e->getMessage(), $name);
        }
        if ($role === '') {
            http_response_code(403);
            return sk_page_login($wantAdmin, $name !== '' ? 'Jméno nebo heslo nesedí. Zkus to znovu.' : 'Heslo nesedí. Zkus to znovu.', $name);
        }
        sk_login($role, $uid);
        sk_redirect($wantAdmin ? 'admin' : '');
    }

    /* ── správa, nebo přehled ── */
    if ($wantAdmin) {
        return sk_is_admin() ? sk_page_app(true) : sk_page_login(true, '');
    }
    return sk_can_view() ? sk_page_app(false) : sk_page_login(false, '');
}

if (!defined('SK_NO_RUN')) {
    sk_main();
}
__halt_compiler();
#SK:css
/* ═══ Sklad: rozvržení a základní tvary ═══
   Vzhled edice (barvy, písma, tvary) je ve stylech až za těmito. Barva tu vždycky něco znamená: červená = chybí,
   oranžová = půjčené, čárkovaný rámeček = objednané (vyzvedne si to později), proužek u místa = barva police. */
:root{
  --ink:#16302a; --ink2:#48625b; --paper:#fff; --tint:#f3f7e2; --hair:rgba(22,48,42,.18);
  --smaragd:#377d69; --limetka:#afcd32; --cervena:#e62355; --cervena-t:#b8123f; --oranzova:#f07832; --zluta:#fffa00;
  --font:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
  --font-d:var(--font);
  --pad:1rem;
  color-scheme:light;
}
html{font-size:106.25%;-webkit-text-size-adjust:100%;text-size-adjust:100%;scroll-padding-top:9rem}
*,*::before,*::after{box-sizing:border-box}
body{margin:0;background:var(--paper);color:var(--ink);font:400 1rem/1.4 var(--font);overflow-wrap:break-word}
a{color:var(--smaragd);text-underline-offset:.15em}
a:hover{color:var(--ink)}
b,strong,h1,h2,h3{font-weight:700}
[hidden]{display:none!important}
:focus-visible{outline:3px solid var(--smaragd);outline-offset:2px}
::selection{background:var(--limetka);color:var(--ink)}
mark{background:var(--zluta);color:inherit;padding:0 .05em}
.sprite{position:absolute;width:0;height:0;overflow:hidden}
.ic{width:1.25em;height:1.25em;flex:none;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:square;stroke-linejoin:miter;vertical-align:-.25em}
.skip{position:absolute;left:-999rem;top:0;background:var(--ink);color:#fff;padding:.6rem 1rem;z-index:200}
.skip:focus{left:0}
.vh{position:absolute;width:1px;height:1px;overflow:hidden;clip-path:inset(50%);white-space:nowrap}

/* barvy označení a míst: --c plocha štítku, --cf písmo na ní, --ct světlý podklad, --cs proužek */
.c-smaragd{--c:#377d69;--cf:#fff;--ct:#e3efeb;--cs:#377d69}
.c-limetka{--c:#afcd32;--cf:#16302a;--ct:#f3f7e2;--cs:#8fae17}
.c-modra{--c:#c4e2ec;--cf:#17495a;--ct:#e9f4f8;--cs:#2b7f99}
.c-piskova{--c:#ffe5a0;--cf:#473821;--ct:#fff6dc;--cs:#c2921a}
.c-zluta{--c:#fffa00;--cf:#16302a;--ct:#fffdd2;--cs:#cfc500}
.c-oranzova{--c:#f07832;--cf:#16302a;--ct:#fdeadf;--cs:#f07832}
.c-cervena{--c:#d81e4f;--cf:#fff;--ct:#fdebf0;--cs:#e62355}
.c-seda{--c:#dfe6e3;--cf:#16302a;--ct:#eef1f0;--cs:#8a9994}
.c-cerna{--c:#16302a;--cf:#fff;--ct:#e6ebe9;--cs:#16302a}

/* ── značka ── */
.top{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.8rem var(--pad) .55rem;max-width:80rem;margin:0 auto}
.brand{display:inline-flex;align-items:center;gap:.55rem;min-width:0;color:var(--ink);text-decoration:none}
.mark{width:2.3rem;height:auto;flex:none}
.wm{min-width:0;font:700 1.55rem/1.05 var(--font);overflow-wrap:anywhere}
.wm sup{font-size:.6em;vertical-align:.75em;margin-left:.05em;color:var(--smaragd)}
.wm-t{font:400 2rem/1.05 var(--font-d);letter-spacing:.01em;padding-top:.2em}
.wm-b{text-transform:uppercase}
.top-r{display:flex;align-items:center;gap:.5rem;flex:none}

/* ── tlačítka ── */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;min-height:2.75rem;padding:.35rem 1rem;border:2px solid var(--ink);border-radius:0;background:var(--paper);color:var(--ink);font:700 1rem/1.2 var(--font);text-decoration:none;cursor:pointer;-webkit-appearance:none;appearance:none}
.btn:hover{background:var(--tint);color:var(--ink)}
.btn.pri{background:var(--smaragd);border-color:var(--smaragd);color:#fff}
.btn.pri:hover{background:var(--ink);border-color:var(--ink)}
.btn.dan{border-color:var(--cervena-t);color:var(--cervena-t)}
.btn.dan:hover{background:var(--cervena-t);color:#fff}
.btn.sm{min-height:2.3rem;padding:.2rem .7rem;font-size:.9rem}
.btn.ico{padding:.2rem;min-width:2.75rem}
.btn.sm.ico{min-width:2.3rem}
.btn.wide{width:100%}
.btn[disabled]{opacity:.45;cursor:not-allowed}
.btn.on{background:var(--ink);color:#fff}
.lnk{background:none;border:0;padding:0;color:var(--smaragd);font:inherit;text-decoration:underline;text-underline-offset:.15em;cursor:pointer}
.lnk:hover{color:var(--ink)}

/* ── hledání a listy (drží se nahoře) ── */
.bar{position:sticky;top:0;z-index:30;background:var(--paper);border-bottom:2px solid var(--ink)}
.bar-in{max-width:80rem;margin:0 auto;padding:.35rem var(--pad) 0}
.srow{display:flex;gap:.5rem}
.search{position:relative;flex:1;min-width:0}
.addb{min-height:3rem;min-width:3rem;padding:0 .9rem}
.addb .ic{width:1.5em;height:1.5em;stroke-width:2.4}
.search #q{width:100%;height:3rem;min-height:0;padding:0 3rem 0 2.9rem;border:2px solid var(--ink);border-radius:0;background:var(--paper);color:var(--ink);font:400 1.05rem/1.2 var(--font);-webkit-appearance:none;appearance:none}
.search #q::placeholder{color:var(--ink2);opacity:1}
.search #q::-webkit-search-cancel-button{display:none}
.search>.ic{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);pointer-events:none}
.search .clr{position:absolute;right:.3rem;top:50%;transform:translateY(-50%);width:2.4rem;height:2.4rem;border:0;background:none;color:var(--ink);cursor:pointer}
.tabs{display:flex;gap:.15rem;margin:.45rem calc(var(--pad) * -1) 0;padding:0 var(--pad);overflow-x:auto;scrollbar-width:none;-webkit-overflow-scrolling:touch}
.tabs::-webkit-scrollbar{display:none}
.tab{flex:none;display:inline-flex;align-items:baseline;gap:.4rem;min-height:2.6rem;padding:.4rem .85rem .35rem;border:0;border-radius:0;background:none;color:var(--ink);font:700 1rem/1.2 var(--font);white-space:nowrap;cursor:pointer}
.tab .n{font-weight:400;font-size:.8rem;color:var(--ink2)}
.tab:hover{background:var(--tint)}
.tab[aria-selected=true]{background:var(--ink);color:#fff;clip-path:polygon(0 0,calc(100% - .6rem) 0,100% .6rem,100% 100%,0 100%)}
.tab[aria-selected=true] .n{color:#fff;opacity:.8}
.tab:focus-visible{outline-offset:-3px}
.tab[aria-selected=true]:focus-visible{outline-color:var(--limetka)}

/* ── řádek s nástroji a filtry ── */
.wrap{max-width:80rem;margin:0 auto}
.tools{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;padding:.7rem var(--pad) .2rem}
.tools .sp{flex:1}
.tools .lnk{padding:.5rem .2rem}
.cnt{color:var(--ink2);font-size:.9rem;white-space:nowrap}
.tools .cnt,.tools .pr,.top-r .tx,.addb .tx{position:absolute;width:1px;height:1px;overflow:hidden;clip-path:inset(50%);white-space:nowrap}
.sel{width:auto;min-height:2.3rem;padding:.2rem 1.9rem .2rem .6rem;border:2px solid var(--ink);border-radius:0;background:var(--paper) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2316302a' stroke-width='3'%3E%3Cpath d='M5 9l7 7 7-7'/%3E%3C/svg%3E") no-repeat right .5rem center/1rem;color:var(--ink);font:700 .9rem/1.2 var(--font);-webkit-appearance:none;appearance:none;max-width:100%}
.filters{padding:.4rem var(--pad) .2rem;display:grid;gap:.7rem}
.fg>b{display:block;font-size:.8rem;color:var(--ink2);font-weight:400;margin-bottom:.3rem}
.chips{display:flex;flex-wrap:wrap;gap:.35rem}
.chip{display:inline-flex;align-items:center;gap:.35rem;min-height:2.1rem;padding:.2rem .65rem;border:1.5px solid var(--ink);border-radius:0;background:var(--paper);color:var(--ink);font:400 .9rem/1.2 var(--font);cursor:pointer}
.chip .n{font-size:.75rem;color:var(--ink2)}
.chip:hover{background:var(--tint)}
.chip[aria-pressed=true]{background:var(--ink);color:#fff}
.chip[aria-pressed=true] .n{color:#fff;opacity:.8}
.chip .dot{width:.7rem;height:.7rem;background:var(--c);border:1px solid rgba(22,48,42,.35);align-self:center}

/* ── seznam ── */
.list{padding-bottom:3rem;min-height:50vh}
.list:focus{outline:none}
.grp-h{display:flex;flex-wrap:wrap;align-items:baseline;gap:.2rem .8rem;padding:1.5rem var(--pad) .55rem}
.grp-t{margin:0;padding:.3rem 1.3rem .3rem .7rem;background:var(--limetka);color:var(--ink);font:700 1.25rem/1.15 var(--font-d);clip-path:polygon(0 0,100% 0,calc(100% - .8rem) 100%,0 100%)}
.grp-n,.grp-u{color:var(--ink2);font-size:.85rem;white-space:nowrap}
.grp-note{flex-basis:100%;margin:.35rem 0 0;color:var(--ink2);font-size:.9rem;white-space:pre-line}
.sub{display:flex;align-items:baseline;gap:.6rem;margin:0;padding:1rem var(--pad) .35rem;font-size:1rem}
.sub .loc{font-weight:700;font-size:1rem}
.sub .n{color:var(--ink2);font-size:.8rem;font-weight:400}
.cols{display:none}
.row{border-top:1px solid var(--hair)}
.row:last-child{border-bottom:1px solid var(--hair)}
.rh{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.28rem .8rem;width:100%;padding:.62rem var(--pad);border:0;border-radius:0;background:none;color:inherit;font:inherit;text-align:left;cursor:pointer}
.rh:hover{background:#f8faf0}
.rh:focus-visible{outline-offset:-3px}
.rh>span:empty{display:none}
.nm{min-width:0;overflow-wrap:anywhere}
.nm b{display:block}
.qt{font-weight:700;text-align:right;max-width:9rem;min-width:0;overflow-wrap:anywhere}
.qt .fr{display:block;font-weight:400;font-size:.78rem;color:var(--ink2);white-space:nowrap}
.qt.nq{font-weight:400;font-style:italic;color:#8a4300}
.ncw{padding:.9rem var(--pad) 0}
.ncb{margin:0}
.ncb p{margin:0}
.ncb .acts{margin-top:.6rem}
.kw{display:block;margin-top:.1rem;color:var(--ink2);font-size:.85rem;line-height:1.3}
.lc,.tags,.nt{grid-column:1/-1;min-width:0}
.loc{display:inline-block;max-width:100%;padding:.12rem .6rem .12rem .5rem;border-left:.4rem solid var(--cs,var(--smaragd));background:var(--ct,var(--tint));font-size:.92rem;line-height:1.3;overflow-wrap:anywhere}
.loc .in{color:var(--ink2)}
.tags{display:flex;flex-wrap:wrap;gap:.3rem;align-items:center}
.tag{display:inline-flex;align-items:center;gap:.25rem;padding:.05rem .45rem;border:1px solid var(--ink2);color:var(--ink);font-size:.78rem;line-height:1.4;max-width:100%}
.tag.own{border-style:dashed}
.tag.pr{border-color:var(--smaragd)}
.tag.pr .ic{color:var(--smaragd)}
.kv small.adm{color:var(--ink2);font-size:.8rem}
.tag .ic{width:1em;height:1em;vertical-align:-.12em}
.mk{display:inline-block;min-width:1.55rem;padding:.05rem .4rem;background:var(--c);color:var(--cf);font-weight:700;font-size:.78rem;line-height:1.4;text-align:center;clip-path:polygon(0 0,calc(100% - .35rem) 0,100% .35rem,100% 100%,0 100%)}
.st{display:inline-flex;align-items:center;gap:.25rem;padding:.05rem .45rem;background:#d81e4f;color:#fff;font-weight:700;font-size:.78rem;line-height:1.4;max-width:100%}
.st>span{min-width:0;overflow-wrap:anywhere}
.st.who{background:var(--oranzova);color:var(--ink)}
.st.who.late{background:#d81e4f;color:#fff}
.st.res{background:var(--paper);color:var(--ink);border:1.5px dashed var(--smaragd);padding:0 .4rem}
.st.res .ic{color:var(--smaragd)}
.due{color:var(--ink2)}
.due.soon{color:#8a4300;font-weight:700}
.due.late{color:var(--cervena-t);font-weight:700}
.st .ic{flex:none;width:1em;height:1em;vertical-align:0}
.nt{color:var(--ink2);font-size:.9rem;line-height:1.35;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;line-clamp:2;overflow:hidden}
.row.open{background:var(--tint)}
.row.open .rh:hover{background:none}
.row.open .nt,.row.open .rh>.lc,.row.open .rh>.tags{display:none}
.rd{padding:.1rem var(--pad) 1rem}
.rh .nm>.kw{display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;line-clamp:2;overflow:hidden}
.pl{white-space:pre-line}
/* náhledy fotek v seznamu: odkaz vedle tlačítka řádku (do tlačítka patřit nesmí) */
.row.wph{display:grid;grid-template-columns:auto minmax(0,1fr)}
.row.wph>.rph{grid-column:1;grid-row:1;align-self:start;margin:.6rem 0 .6rem var(--pad)}
.row.wph>.rh{grid-column:2;grid-row:1;padding-left:.75rem}
.row.wph>.rd{grid-column:1/-1}
.row.wph>.rph.none{display:none}
.row.wph>.rph.none+.rh{grid-column:1/-1;padding-left:var(--pad)}
.rph{position:relative;display:block;width:3rem;height:3rem;flex:none}
.rph img{display:block;width:100%;height:100%;object-fit:cover;background:var(--ink);clip-path:polygon(0 4%,96% 0,100% 94%,5% 100%)}
.rph:hover img{filter:brightness(1.08)}
.rph.err{display:grid;place-items:center;background:var(--tint);color:var(--ink2)}
.rph.err .ic{width:1.4rem;height:1.4rem}
.rph-n{position:absolute;right:-.3rem;bottom:-.25rem;min-width:1.2rem;padding:0 .25rem;background:var(--limetka);color:var(--ink);font:700 .72rem/1.2rem var(--font);text-align:center}
/* tagy */
.tag.tg{border-color:transparent;background:#e6f0c4;font-weight:700}
.tag.ty{border-color:transparent;background:transparent;color:var(--ink2);font-size:.72rem;padding-left:.1rem;padding-right:.1rem}
.tag>span{min-width:0;overflow-wrap:anywhere}
.chip.tgc{max-width:100%;overflow-wrap:anywhere;text-align:left}
.tgs{display:flex;flex-wrap:wrap;gap:.35rem}
.tgb{min-height:2rem;max-width:100%;padding:.15rem .6rem;border:0;border-radius:0;background:#e6f0c4;color:var(--ink);font:700 .92rem/1.3 var(--font);text-align:left;overflow-wrap:anywhere;cursor:pointer}
.tgb:hover{background:var(--limetka)}
.tgx{display:flex;flex-direction:column;gap:.45rem}
.tgx .add{display:flex;gap:.35rem;max-width:26rem}
.tgx .add input{flex:1;min-width:0;min-height:2.1rem;padding:.2rem .5rem;font-size:.9rem}
.tgx .add input::-webkit-calendar-picker-indicator{display:none!important}
.chips.sug{align-items:center}
.chips.sug .sm-hint{margin:0}
.chip.tgc .ic{width:.8rem;height:.8rem}
/* přepínač (fotky v seznamu) */
.sw{position:relative;display:inline-flex;align-items:center;gap:.5rem;min-height:2.3rem;padding:0 .15rem;font-weight:700;font-size:.9rem;cursor:pointer;-webkit-user-select:none;user-select:none}
.sw input{position:absolute;opacity:0;width:1px;height:1px;margin:0}
.sw-t{position:relative;flex:none;width:2.6rem;height:1.45rem;border:2px solid var(--ink);background:var(--paper)}
.sw-t::after{content:'';position:absolute;top:.15rem;left:.15rem;width:.85rem;height:.85rem;background:var(--ink);transition:transform .15s}
.sw input:checked+.sw-t{background:var(--smaragd);border-color:var(--smaragd)}
.sw input:checked+.sw-t::after{transform:translateX(1.15rem);background:#fff}
.sw input:focus-visible+.sw-t{outline:3px solid var(--smaragd);outline-offset:2px}
/* fotka přes celou obrazovku */
dialog.lb{width:100vw;height:100vh;height:100dvh;max-width:none;max-height:none;margin:0;padding:0;border:0;background:transparent;color:#fff;overflow:hidden}
dialog.lb[open]{display:grid;place-items:center}
dialog.lb::backdrop{background:rgba(9,22,19,.93)}
.lb-f{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.7rem;width:100%;height:100%;margin:0;padding:4rem 1rem 1.2rem;box-sizing:border-box}
.lb-f img{display:block;width:auto;height:auto;max-width:100%;max-height:calc(100vh - 8.5rem);max-height:calc(100dvh - 8.5rem);object-fit:contain;background:#000}
.lb-f figcaption{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:.3rem 1rem;max-width:100%;font-size:.95rem;text-align:center;overflow-wrap:anywhere}
.lb-o{display:inline-flex;align-items:center;gap:.3rem;color:#fff;font-size:.85rem}
.lb-o .ic{width:1em;height:1em}
.lb-b{position:fixed;display:grid;place-items:center;width:3rem;height:3rem;padding:0;border:2px solid #fff;border-radius:0;background:rgba(9,22,19,.55);color:#fff;cursor:pointer}
.lb-b:hover{background:rgba(255,255,255,.15)}
.lb-b:focus-visible{outline:3px solid var(--limetka);outline-offset:2px}
.lb-b .ic{width:1.5rem;height:1.5rem}
.lb-x{top:.75rem;right:.75rem}
.lb-p,.lb-n{top:50%;transform:translateY(-50%)}
.lb-p{left:.75rem}.lb-n{right:.75rem}
/* vzor souboru pro import */
.isample{margin:.6rem 0 .9rem}
.isample .acc-b{padding-top:.2rem}
pre.csv{margin:.2rem 0 .6rem;padding:.6rem .7rem;max-width:100%;overflow-x:auto;background:var(--tint);border-left:.35rem solid var(--limetka);font:.74rem/1.55 ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;white-space:pre;tab-size:4}
.ilist.rules{margin-bottom:.6rem}
.ilist.rules li{margin:.3rem 0;font-size:.9rem}
.kv{display:grid;grid-template-columns:1fr;gap:0;margin:0}
.kv dt{margin-top:.55rem;color:var(--ink2);font-size:.8rem}
.kv dd{margin:0;white-space:pre-line;overflow-wrap:anywhere}
.kv dd .mk{margin-right:.3rem}
.ph{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:.3rem;align-items:center}
.ph .img{display:block;width:6.5rem;height:6.5rem;background:var(--ink);clip-path:polygon(0 5%,95% 0,100% 93%,6% 100%)}
.ph .img:nth-child(even){clip-path:polygon(4% 0,100% 6%,94% 100%,0 95%)}
.ph .img img{display:block;width:100%;height:100%;object-fit:cover}
.acts{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem}
.empty{padding:3rem var(--pad);text-align:center;color:var(--ink2)}
.empty b{display:block;color:var(--ink);font-size:1.15rem;margin-bottom:.3rem}
.empty .btn{margin-top:1rem}
.empty .acts{justify-content:center}
.empty .acts .btn{margin-top:0}
.foot{max-width:80rem;margin:0 auto;padding:1rem var(--pad) 2rem;color:var(--ink2);font-size:.85rem;display:flex;flex-wrap:wrap;gap:.4rem 1.2rem;align-items:center;border-top:1px solid var(--hair)}

/* ── půjčené ── */
.lcols{display:none}
.lent .rh{grid-template-columns:minmax(0,1fr)}
.lent .rh i{font-style:normal;color:var(--ink2);font-size:.8rem}
.l-who,.l-since,.l-due,.l-home{min-width:0;font-size:.92rem;overflow-wrap:anywhere}
.l-since{order:4}
.l-home{order:5}
.l-sec{font-weight:700}
.l-home .loc{margin-left:.25rem;vertical-align:baseline}
.row.lent.late{box-shadow:inset .4rem 0 0 var(--cervena)}
.lentv .sub{margin-top:1.2rem;font-weight:700}

/* ── objednávky ── */
.grp-h.next{padding-top:2.2rem}
.ords{display:grid;gap:.9rem;padding:.2rem var(--pad) .4rem}
.ord{border:2px solid var(--ink);background:var(--paper);min-width:0}
.ord.now{border-color:var(--oranzova);box-shadow:inset .4rem 0 0 var(--oranzova)}
.ord-h{display:grid;grid-template-columns:auto minmax(0,1fr);gap:.2rem .9rem;padding:.7rem .8rem .6rem;align-items:start}
.ord-d{display:flex;flex-direction:column;align-items:flex-start;min-width:5.6rem;padding:.35rem .9rem .35rem .55rem;background:var(--limetka);clip-path:polygon(0 0,calc(100% - .6rem) 0,100% .6rem,100% 100%,0 100%)}
.ord-d b{font:700 1.1rem/1.15 var(--font-d);white-space:nowrap}
.ord-dow{font-weight:700;font-size:.8rem;line-height:1.2}
.ord-at{font-weight:700;font-size:.95rem}
.ord-d small{font-size:.78rem;color:var(--ink)}
.ord-w{min-width:0;display:flex;flex-direction:column;gap:.15rem;overflow-wrap:anywhere}
.ord-who{display:inline-flex;align-items:center;gap:.35rem;font-size:1.05rem}
.ord-who .ic{color:var(--smaragd)}
.ord-m{color:var(--ink2);font-size:.88rem}
.ord-m>span{white-space:nowrap}
.ord-note{font-size:.9rem}
.olines{margin:0;padding:0 .8rem;list-style:none;border-top:1px solid var(--hair);overflow-wrap:anywhere}
.oline{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.2rem .8rem;padding:.45rem 0;border-top:1px solid var(--hair);align-items:start;min-width:0}
.oline:first-child{border-top:0}
.oline>.lc,.oline>.ow{grid-column:1/-1;min-width:0}
.oline>.lc:empty{display:none}
.ow{display:flex;gap:.35rem;align-items:flex-start;font-size:.85rem;line-height:1.3;overflow-wrap:anywhere}
.ow .ic{width:1.05em;height:1.05em;margin-top:.05em}
.oline.w .nm b{color:var(--ink2)}
.ord>.acts{margin:0;padding:.65rem .8rem .8rem;border-top:1px solid var(--hair)}
.resl{margin:0;padding-left:1.1rem}
.resl li{margin:.1rem 0}
.loans{margin:0;padding:0;list-style:none}
.loans li{padding:.35rem 0;border-top:1px solid var(--hair)}
.loans li:first-child{border-top:0;padding-top:0}
.loans li.hot{box-shadow:inset .3rem 0 0 var(--oranzova);padding-left:.7rem}
.loans .la{display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.35rem}
/* objednávka v okně */
.fld>.chips.quick{margin:0 0 .5rem}
.two.dt{grid-template-columns:minmax(0,1fr) auto;gap:.5rem;align-items:center}
.two.dt .at{display:flex;align-items:center;gap:.4rem;font-size:.9rem;color:var(--ink2)}
.two.dt input[type=time]{width:7.2rem}
.sm-hint.when{margin:.4rem 0 0}
.sm-hint.when.late{color:var(--cervena-t);font-weight:700}
.olines.ed{border:2px solid var(--ink);padding:0 .7rem}
.olines.ed:empty{display:none}
.olines.ed .oline{grid-template-columns:minmax(0,1fr) 2.3rem;align-items:center}
.olines.ed .oline>.nm{grid-column:1/-1}
.olines.ed .oq{max-width:11rem}
.oq{display:flex;align-items:center;gap:.35rem}
.oq input{flex:1;min-height:2.3rem;padding:.2rem .5rem;font-size:.92rem;min-width:0}
.oq .un{flex:none;font-size:.85rem;color:var(--ink2)}
.ow.fr{color:var(--ink2)}
.osearch{margin-top:.6rem}
.osearch .fld{margin-bottom:.4rem}
.chips.found{margin-bottom:.4rem}
.chips.found .chip{align-items:center;text-align:left}
/* výběr víc věcí */
.rh.pk .nm{display:grid;grid-template-columns:1.35rem minmax(0,1fr);column-gap:.6rem;align-items:start}
.rh.pk .nm>b,.rh.pk .nm>.kw{grid-column:2}
.rh.pk .nm>.bx{grid-row:1/span 2;display:inline-grid;place-items:center;width:1.35rem;height:1.35rem;border:2px solid var(--ink);background:var(--paper);color:transparent}
.rh.pk .bx .ic{width:1rem;height:1rem;stroke-width:3.5}
.rh.pk[aria-pressed=true] .bx{background:var(--smaragd);border-color:var(--smaragd);color:#fff}
.row.picked{background:#eef5d7}
.pickbar{position:fixed;left:0;right:0;bottom:0;z-index:40;background:var(--ink);color:#fff;box-shadow:0 -.3rem 1rem rgba(22,48,42,.3)}
.pb-in{max-width:80rem;margin:0 auto;display:flex;flex-wrap:wrap;align-items:center;gap:.5rem .6rem;padding:.6rem var(--pad) calc(.6rem + env(safe-area-inset-bottom,0px))}
.pb-n{flex:1 1 9rem;font-weight:700}
.pickbar .btn{border-color:#fff;background:transparent;color:#fff}
.pickbar .btn:hover{background:rgba(255,255,255,.12)}
.pickbar .btn.pri{background:var(--limetka);border-color:var(--limetka);color:var(--ink)}
.pickbar .btn.pri:hover{background:#fff;border-color:#fff}
.pickbar .btn:focus-visible{outline-color:var(--limetka)}
body.picking .list{padding-bottom:10rem}
body.picking .toast{bottom:7.5rem}
/* import z tabulky */
.imap{display:grid}
.irow{display:grid;grid-template-columns:1fr;gap:.25rem .8rem;padding:.45rem 0;border-top:1px solid var(--hair);align-items:center;min-width:0}
.irow:first-child{border-top:0}
.irow .ih{min-width:0;overflow-wrap:anywhere}
.irow .ih b{display:block}
.irow .ih small{display:block;color:var(--ink2);font-size:.8rem}
.irow select{min-height:2.3rem;padding:.2rem 2rem .2rem .5rem;font-size:.92rem}
.irow.dup select{border-color:var(--cervena);box-shadow:inset 0 0 0 1px var(--cervena)}
.ilist{margin:.3rem 0 .8rem;padding-left:1.2rem}
.ilist li{margin:.15rem 0;overflow-wrap:anywhere}
.ilist.pv small{color:var(--ink2)}
.isum .msg{margin:.4rem 0}
.iprev>b{display:block;font-size:.9rem;margin-top:.6rem}
.slip{display:none}

/* ── přání ── */
.wish .rh{grid-template-columns:minmax(0,1fr) auto}
.wish .qt.long{display:none}
.w-meta{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:.1rem 1rem;font-size:.9rem;min-width:0}
.w-meta>span{min-width:0}
.w-meta>span:empty{display:none}
.w-meta i{font-style:normal;color:var(--ink2);font-size:.8rem;margin-right:.3rem}
.w-why{grid-column:1/-1;font-size:.92rem;min-width:0}
.w-why:empty{display:none}
.w-why .nt{display:block;-webkit-line-clamp:unset;line-clamp:unset}
.w-st{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:.3rem .9rem}
.wcols{display:none}
.wish.open .rh>.w-meta,.wish.open .rh>.w-why,.wish.open .rh>.w-st{display:none}
.rd>.w-st{margin-top:.8rem}
.ck{display:inline-flex;align-items:center;gap:.35rem;font-size:.85rem;color:var(--ink2)}
.ck .bx{display:inline-grid;place-items:center;width:1.15rem;height:1.15rem;border:1.5px solid var(--ink2);background:var(--paper);color:transparent;flex:none}
.ck .bx .ic{width:.9rem;height:.9rem;stroke-width:3.5}
.ck.y{color:var(--ink);font-weight:700}
.ck.y .bx{background:var(--smaragd);border-color:var(--smaragd);color:#fff}
.wish.bought{box-shadow:inset .4rem 0 0 var(--limetka)}
.wrow-acts{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.9rem}
button.ck{border:0;background:none;padding:.35rem .2rem;font:inherit;font-size:.9rem;cursor:pointer;min-height:2.3rem}
button.ck:hover{color:var(--ink)}

/* ── okna ── */
dialog.dlg{width:min(42rem,calc(100vw - 1.5rem));max-width:none;max-height:92vh;max-height:92dvh;padding:0;border:2px solid var(--ink);background:var(--paper);color:var(--ink);overflow:hidden}
dialog.dlg[open]{display:flex;flex-direction:column}
dialog.dlg::backdrop{background:rgba(22,48,42,.6)}
dialog.dlg.narrow{width:min(28rem,calc(100vw - 1.5rem))}
.dlg>form{display:flex;flex-direction:column;min-height:0;flex:1}
.dlg-h{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.55rem .6rem .55rem 1rem;border-bottom:2px solid var(--ink)}
.dlg-h h2{margin:0;font:700 1.15rem/1.2 var(--font)}
.dlg-b{padding:1rem;overflow:auto;flex:1;min-height:0;-webkit-overflow-scrolling:touch}
.dlg-b>p:first-child{margin-top:0}
.dlg-f{display:flex;flex-wrap:wrap;gap:.6rem;justify-content:flex-end;padding:.7rem 1rem;border-top:2px solid var(--ink)}
.dlg-f .l{margin-right:auto}

/* ── formuláře ── */
.fld{display:block;margin:0 0 .95rem;min-width:0}
.fld>span,.fld>legend{display:block;padding:0;font-weight:700;font-size:.9rem;margin-bottom:.25rem}
.fld>span i,.fld>legend i{font-weight:400;font-style:normal;color:var(--ink2)}
.fld small,.sm-hint{display:block;color:var(--ink2);font-size:.8rem;line-height:1.35;margin-top:.3rem}
fieldset.fld{border:0;padding:0}
input[type=text],input[type=password],input[type=url],input[type=date],input[type=time],input[type=number],input[type=search],select,textarea{width:100%;min-height:2.75rem;padding:.4rem .6rem;border:2px solid var(--ink);border-radius:0;background:var(--paper);color:var(--ink);font:400 1rem/1.3 var(--font)}
textarea{min-height:5rem;resize:vertical;display:block}
select{-webkit-appearance:none;appearance:none;padding-right:2rem;background:var(--paper) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2316302a' stroke-width='3'%3E%3Cpath d='M5 9l7 7 7-7'/%3E%3C/svg%3E") no-repeat right .6rem center/1rem}
input:disabled,select:disabled,textarea:disabled{background:#eef1f0;color:var(--ink2)}
.two{display:grid;grid-template-columns:1fr;gap:0 .8rem}
fieldset .two{gap:.4rem .8rem}
.chk{display:flex;align-items:center;gap:.6rem;min-height:2.75rem;margin:0 0 .6rem;cursor:pointer}
.chk input{-webkit-appearance:none;appearance:none;flex:none;width:1.5rem;height:1.5rem;margin:0;border:2px solid var(--ink);border-radius:0;background:var(--paper);display:inline-grid;place-items:center;cursor:pointer}
.chk input::after{content:"";width:.8rem;height:.45rem;border:solid #fff;border-width:0 0 3px 3px;transform:translateY(-.1rem) rotate(-45deg);opacity:0}
.chk input:checked{background:var(--smaragd);border-color:var(--smaragd)}
.chk input:checked::after{opacity:1}
/* volba jedné z možností: kolečko s tečkou, ať se nesplete se zaškrtávátkem */
.chk input[type=radio]{border-radius:50%}
.chk input[type=radio]::after{width:.6rem;height:.6rem;border:0;border-radius:50%;background:#fff;transform:none}
.pick{display:flex;flex-wrap:wrap;gap:.35rem;align-items:center}
.pick .add{display:flex;gap:.35rem;flex:1 1 12rem;min-width:10rem}
.pick .add input{min-height:2.1rem;padding:.2rem .5rem;font-size:.9rem}
.phs{display:grid;gap:.4rem;margin:0 0 .5rem;padding:0;list-style:none}
.phs li{display:flex;align-items:center;gap:.6rem;min-width:0}
.phs img{width:3rem;height:3rem;object-fit:cover;flex:none;background:var(--ink)}
.phs .u{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.85rem}
.inl{display:flex;gap:.4rem}
.inl>input{flex:1;min-width:0}
.qrow{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.5rem;align-items:center}
.qrow select{width:auto}
.qrow .un{font-weight:700;min-width:1.5rem}
.sm-hint.ckh{margin:-.5rem 0 .95rem}
input[aria-invalid=true]{border-color:var(--cervena);box-shadow:inset 0 0 0 1px var(--cervena)}
/* vracení (jedna i víc výpůjček) */
.rrow{display:grid;grid-template-columns:minmax(0,1fr);gap:.35rem .8rem;padding:.6rem 0;border-top:1px solid var(--hair);min-width:0}
.rrows .rrow:first-child{border-top:0}
.rrow .chk.rc{margin:0;align-items:flex-start;min-height:0}
.chk.rc input{margin-top:.1rem}
.chk.rc>span{display:flex;flex-direction:column;min-width:0;overflow-wrap:anywhere}
.chk.rc small{color:var(--ink2);font-size:.82rem;line-height:1.3}
.rin{display:flex;flex-wrap:wrap;gap:.4rem;align-items:center;min-width:0}
.rin:empty{display:none}
.rin>.qrow{flex:0 0 8.5rem}
.rin input{min-height:2.3rem;padding:.2rem .5rem}
.rin select{flex:1 1 12rem;min-width:0;min-height:2.3rem;font-size:.85rem;padding:.2rem 1.8rem .2rem .5rem;background-position:right .4rem center}
.rrow.off .rin,.rrow.off .chk.rc small{opacity:.5}
.rmsg{color:var(--ink2);font-size:.82rem;line-height:1.3}
.rmsg:empty{display:none}
.rsum{font-weight:700;color:var(--ink)}
.rsum:empty{display:none}
fieldset.prices{margin-top:.2rem}
fieldset.prices .fld{margin-bottom:.6rem}
/* doplnění počtů */
.sub.cs{padding:.9rem 0 .2rem}
.sub.cs:first-child{padding-top:0}
.crow{display:grid;grid-template-columns:minmax(0,1fr);gap:.35rem .7rem;padding:.55rem 0;border-top:1px solid var(--hair);align-items:center;min-width:0}
.crows .crow:first-child{border-top:0}
.cn{min-width:0;overflow-wrap:anywhere}
.cn b{display:block}
.cn small{display:block;color:var(--ink2);font-size:.8rem;line-height:1.3}
.cin{display:grid;grid-template-columns:minmax(0,1fr) 4.6rem;gap:.35rem}
.cin input,.cin select{min-height:2.3rem;padding:.2rem .5rem;font-size:.95rem}
.cin select{padding-right:1.6rem;background-position:right .4rem center}
.crow .chips{flex-wrap:nowrap}
.crow .chip{min-height:2.3rem}
.cnt-f{margin-right:auto;align-self:center;color:var(--ink2);font-size:.9rem}
.msg{margin:0 0 1rem;padding:.6rem .8rem;border-left:.4rem solid var(--smaragd);background:var(--tint);font-size:.95rem}
.msg.bad{border-left-color:var(--cervena);background:#fdebf0}
.msg.warn{border-left-color:var(--oranzova);background:#fdeadf}

/* ── nastavení ── */
.set{padding:1rem var(--pad) 2rem;max-width:52rem}
.set h2{margin:2rem 0 .3rem;font:700 1.25rem/1.2 var(--font-d)}
.set h2:first-child{margin-top:.5rem}
.set>p{margin:.2rem 0 .9rem;color:var(--ink2);font-size:.92rem}
.acc{border:2px solid var(--ink);margin:0 0 .5rem}
.acc>summary{display:flex;align-items:center;gap:.6rem;padding:.6rem .8rem;font-weight:700;cursor:pointer;list-style:none;min-height:2.75rem}
.acc>summary::-webkit-details-marker{display:none}
.acc>summary .n{font-weight:400;color:var(--ink2);font-size:.85rem}
.acc>summary .ic{margin-left:auto;transition:transform .15s}
.acc[open]>summary .ic{transform:rotate(180deg)}
.acc[open]>summary{border-bottom:1px solid var(--hair)}
.acc-b{padding:.9rem .8rem .8rem}
.frow{display:grid;grid-template-columns:1fr;gap:.1rem .6rem;align-items:center;margin-bottom:.6rem}
.frow .chk{margin:0;min-height:2.3rem;font-size:.9rem}
.frow input[type=text]{min-height:2.3rem;padding:.2rem .5rem;font-size:.92rem}
.lrow{display:grid;grid-template-columns:3.6rem minmax(0,1fr) 2.3rem;gap:.35rem;margin-bottom:.7rem;align-items:center}
.lrow select{grid-column:1/3;grid-row:2}
.lrow.p{grid-template-columns:minmax(0,1fr) 2.3rem}
.lrow.p select{grid-column:1}
.lrow input,.lrow select{min-height:2.3rem;padding:.2rem .5rem;font-size:.92rem}
.lrow.ppl{grid-template-columns:minmax(0,1fr) auto 2.3rem}
.lrow.ty{grid-template-columns:minmax(0,1fr) auto 2.3rem 2.3rem 2.3rem;margin-bottom:.4rem}
.lrow.ty .sm-hint{margin:0;white-space:nowrap}
.lrow.ppl>input:first-child{grid-column:1/-1}
.chips.quick{margin:-.5rem 0 .95rem}
.chk.nump{flex-wrap:wrap;gap:.4rem .5rem;cursor:default}
.chk.nump input.num{width:4.2rem;min-height:2.3rem;padding:.2rem .4rem;text-align:center}
.dcd .fld .chk{margin:0;min-height:2.4rem}
.dcd code{font:inherit;font-weight:700;overflow-wrap:anywhere}
.sm-hint.dc{margin:0 0 .4rem}
.pp{margin-bottom:.7rem}
.pp .lrow{margin-bottom:0}
.dcd .pp-n{display:block;margin-top:.25rem;color:var(--ink2);font-size:.8rem;line-height:1.35;font-weight:400}
.dcd .pp-n.bad{color:var(--cervena-t);font-weight:700}
.pp-n .chips{margin-top:.35rem}
.pp-n .chip{align-items:center;text-align:left;overflow-wrap:anywhere}
.dcd [aria-invalid=true]{border-color:var(--cervena);box-shadow:inset 0 0 0 1px var(--cervena)}
.dcd .bot .msg{margin-bottom:.6rem}
.dcd .bot .acts{margin:0 0 .95rem}
.dcd .bot>small{margin:0 0 .7rem}
.steps{margin:0;padding-left:1.3rem}
.steps li{margin:0 0 .45rem}
.steps li:last-child{margin-bottom:0}
.logl{margin:0;padding:0;list-style:none;font-size:.9rem}
.logl li{display:grid;grid-template-columns:8.5rem minmax(0,1fr);gap:.6rem;padding:.3rem 0;border-top:1px solid var(--hair)}
.logl time{color:var(--ink2);font-size:.85rem}
.bkl{margin:0 0 1rem;padding:0;list-style:none}
.logl small.by{color:var(--ink2);font-size:.82rem}
/* uživatelé */
.usrl{margin:0 0 1rem;padding:0;list-style:none}
.usrl li{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.4rem .8rem;padding:.55rem 0;border-top:1px solid var(--hair)}
.usrl li:last-child{border-bottom:1px solid var(--hair)}
.usrl .un{display:inline-flex;flex-wrap:wrap;align-items:baseline;gap:.2rem .5rem;min-width:0;overflow-wrap:anywhere}
.usrl .un .ic{align-self:center;color:var(--smaragd)}
.usrl .un small{color:var(--ink2);font-size:.82rem}
.usrl .acts{margin:0}
.sub0{margin:.6rem 0 .5rem;font-size:1rem}
.usrme{margin-top:1.2rem}
.kv.pw dd{font-weight:700;overflow-wrap:anywhere}
.kv.pw code{font:inherit;font-size:1.15rem;letter-spacing:.03em}
.bkl li{display:flex;align-items:center;justify-content:space-between;gap:.8rem;padding:.35rem 0;border-top:1px solid var(--hair);font-size:.92rem}

/* ── hláška dole, tlačítko pro přidání ── */
.toast{position:fixed;left:1rem;right:1rem;bottom:1.1rem;margin:0 auto;width:-webkit-fit-content;width:-moz-fit-content;width:fit-content;max-width:min(42rem,calc(100vw - 2rem));z-index:120;padding:.65rem 1.1rem;background:var(--ink);color:#fff;font-weight:700;overflow-wrap:anywhere;white-space:pre-line;box-shadow:0 .3rem 1rem rgba(22,48,42,.3)}
.toast.bad{background:var(--cervena-t)}

/* ── malé stránky (hesla) ── */
body.gate{min-height:100vh;padding:7vh 1rem 2rem;background:var(--tint);overflow-x:hidden;position:relative}
body.gate::before{content:"";position:fixed;right:-18vmin;bottom:-22vmin;width:78vmin;height:78vmin;background:var(--limetka);clip-path:polygon(40% 0,100% 16%,84% 100%,10% 86%,0 46%);z-index:0}
.gate-in{position:relative;z-index:1;width:100%;max-width:27rem;margin:0 auto}
.card{margin-top:1.3rem;padding:1.4rem 1.2rem 1.5rem;border:2px solid var(--ink);background:var(--paper)}
.card h1{margin:0 0 .4rem;font:700 1.45rem/1.2 var(--font)}
.lead{margin:0 0 1.1rem;color:var(--ink2)}
.ver,.back{margin:.9rem 0 0;color:var(--ink2);font-size:.8rem}
.hint{margin-top:1rem;font-size:.9rem}
.hint summary{cursor:pointer;color:var(--smaragd);text-decoration:underline;text-underline-offset:.15em}
.hint p{margin:.5rem 0 0;color:var(--ink2)}

/* ── širší obrazovky a papír: seznam jako tabulka ── */
@media print,(min-width:40rem){
  :root{--pad:1.5rem}
  .two{grid-template-columns:1fr 1fr}
  .tools .cnt,.tools .pr,.top-r .tx,.addb .tx{position:static;width:auto;height:auto;overflow:visible;clip-path:none}
  .row.open .rh>.lc{display:block}
  .row.open .rh>.tags{display:flex}
  .frow{grid-template-columns:minmax(11rem,17rem) 1fr;margin-bottom:.35rem}
  .olines.ed .oline{grid-template-columns:minmax(0,1fr) 8.2rem 2.3rem}
  .olines.ed .oline>.nm{grid-column:auto}
  .olines.ed .oq{max-width:none}
  .lrow{grid-template-columns:4.2rem minmax(0,1fr) 8.2rem 2.3rem;margin-bottom:.35rem}
  .lrow select,.lrow.p select{grid-column:auto;grid-row:auto}
  .lrow.ppl{grid-template-columns:minmax(0,1fr) minmax(0,1.2fr) auto 2.3rem}
  .lrow.ppl>input:first-child{grid-column:auto}
  .lrow.p{grid-template-columns:minmax(0,1fr) 8.2rem 2.3rem}
  .irow{grid-template-columns:minmax(0,1fr) 15rem}
  .crow{grid-template-columns:minmax(0,1fr) 10.5rem 7.5rem}
  .rrow{grid-template-columns:minmax(0,1fr) 21rem;align-items:start}
  .rrow .rmsg{grid-column:1/-1}
  .crow .chips{justify-content:flex-start}
  body.picking .toast{bottom:5rem}
  .filters{display:flex;flex-wrap:wrap;gap:.7rem 2rem;align-items:flex-start}
  .fg select{width:auto;min-width:14rem;max-width:26rem}
  .kv{grid-template-columns:minmax(8rem,max-content) minmax(0,1fr);gap:.3rem 1.2rem}
  .kv dt{margin-top:0;padding-top:.15rem}
  .w-st{gap:.3rem 1.4rem}
}
@media print,(min-width:62rem){
  html{font-size:100%}
  .cols,.inv .rh{display:grid;grid-template-columns:minmax(11rem,2.1fr) 6rem minmax(11rem,2fr) minmax(8rem,1.35fr) minmax(8rem,1.7fr);gap:.2rem 1.1rem;align-items:start}
  .cols{padding:.1rem var(--pad) .3rem;color:var(--ink2);font-size:.8rem;border-bottom:2px solid var(--ink)}
  .cols span:nth-child(2){text-align:right}
  .inv .rows>.row:first-child{border-top:0}
  .inv .rh{padding:.45rem var(--pad)}
  .inv .rh>span{grid-row:1;display:block}
  .inv .nm{grid-column:1}.inv .qt{grid-column:2;max-width:none}.inv .lc{grid-column:3}.inv .rh>.tags{grid-column:4;display:flex}.inv .rh>.nt{grid-column:5;-webkit-line-clamp:3;line-clamp:3;display:-webkit-box}
  .inv .rh>span:empty{display:block}
  .row.wph>.rph{margin-top:.45rem}
  .row.wph>.rph.none{display:block;height:0}
  .row.wph>.rph.none+.rh{grid-column:2;padding-left:.75rem}
  .grp.wph .cols{padding-left:calc(var(--pad) + 3.75rem)}
  .lcols,.lent .rh{display:grid;grid-template-columns:minmax(11rem,2fr) minmax(8rem,1.2fr) 6.5rem minmax(10rem,1.4fr) minmax(11rem,2fr);gap:.2rem 1.1rem;align-items:start}
  .lcols{padding:.1rem var(--pad) .3rem;color:var(--ink2);font-size:.8rem;border-bottom:2px solid var(--ink)}
  .lent .rh{padding:.45rem var(--pad)}
  .lent .rh>span{grid-row:1;display:block;order:0}
  .lent .nm{grid-column:1}.lent .l-who{grid-column:2}.lent .l-since{grid-column:3}.lent .l-due{grid-column:4}.lent .l-home{grid-column:5}
  .lent .rh i{display:none}
  .lentv .rows>.row:first-child{border-top:0}
  .ord-h{grid-template-columns:auto minmax(0,1fr) auto}
  .ords .oline{grid-template-columns:minmax(11rem,2fr) 6rem minmax(11rem,2fr);gap:.2rem 1.1rem}
  .ords .oline>.lc{grid-column:3;grid-row:1}
  .ords .oline>.lc:empty{display:block}
  .ords .oline>.ow{grid-column:1/-1}
  .rd{padding:.2rem var(--pad) 1.1rem}
}
@media print,(min-width:72rem){
  .wcols,.wish .rh{display:grid;grid-template-columns:minmax(9rem,1.6fr) 5.5rem 5.5rem 6.5rem minmax(9rem,2fr) 5.5rem 5rem 3.4rem 4.6rem;gap:.2rem .8rem;align-items:start}
  .wcols{padding:.1rem var(--pad) .3rem;color:var(--ink2);font-size:.8rem;line-height:1.25;border-bottom:2px solid var(--ink);align-items:end}
  .wcols span:nth-child(n+7){text-align:center}
  .wish .rh{padding:.45rem var(--pad)}
  .wish .rh>span,.wish .w-meta>span,.wish .rh .w-st>.ck{grid-row:1;display:block;min-width:0}
  .wish .rh>.w-meta,.wish .rh>.w-st,.wish.open .rh>.w-meta,.wish.open .rh>.w-st{display:contents}
  .wish.open .rh>.w-why{display:block}
  .wish .nm{grid-column:1}
  .wish .rh>.qt,.wish .rh>.qt.long{grid-column:2;display:block;max-width:none;white-space:normal;text-align:left;font-weight:400;overflow:visible}
  .wish .w-who{grid-column:3}.wish .w-where{grid-column:4}.wish .rh>.w-why{grid-column:5}.wish .w-by{grid-column:6}
  .wish .w-meta>.w-q{display:none}
  .wish .w-meta i{display:none}
  .wish .rh .w-st>.ck:nth-child(1){grid-column:7}.wish .rh .w-st>.ck:nth-child(2){grid-column:8}.wish .rh .w-st>.ck:nth-child(3){grid-column:9}
  .wish .rh .w-st>.ck{display:grid;place-items:center;padding-top:.1rem}
  .wish .rh .w-st>.ck .lb{position:absolute;width:1px;height:1px;overflow:hidden;clip-path:inset(50%)}
  .wish .w-meta>span:empty,.wish .rh>.w-why:empty{display:block}
  .rows>.wish:first-child{border-top:0}
}
@media (max-width:22rem){
  .mark{width:2rem}
  .wm-t{font-size:1.7rem}
}
@media (prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}

/* ── tisk: jen seznam ── */
@media print{
  :root{--pad:0rem}
  html{font-size:62.5%}
  body{background:#fff}
  .bar,.tools,.filters,.top-r,.toast,.acts,.skip,.foot,.rd .ph,.wrow-acts,.pickbar,.rph{display:none!important}
  .row.wph{display:block}
  .grp.wph .cols{padding-left:var(--pad)}
  .ord{break-inside:avoid;border-width:1px}
  .grp-h.next{padding-top:1.2rem}
  /* výdejka: z celé stránky jen ona */
  body.pslip>*:not(#slip){display:none!important}
  body.pslip #slip{display:block}
  .sl{font-size:11pt;line-height:1.35;color:#000;break-after:page}
  .sl:last-child{break-after:auto}
  .sl header{display:flex;justify-content:space-between;align-items:baseline;gap:1rem;padding-bottom:.25rem;border-bottom:2px solid #000;font-size:9pt}
  .sl h1{margin:0;font:700 18pt/1.2 var(--font)}
  .sl dl{display:grid;grid-template-columns:max-content minmax(0,1fr);gap:.15rem 1.2rem;margin:.8rem 0 1rem}
  .sl dt{color:#444}
  .sl dd{margin:0;font-weight:700}
  .sl table{width:100%;border-collapse:collapse}
  .sl th,.sl td{border:1px solid #000;padding:.2rem .35rem;text-align:left;vertical-align:top}
  .sl th{font-size:8.5pt;font-weight:400;color:#444}
  .sl td small{font-weight:400;color:#444}
  .sl .cb{width:2.6rem;text-align:center;font-size:13pt}
  .sl th.cb{font-size:7.5pt}
  .sl tr{break-inside:avoid}
  .sl footer{display:flex;gap:1.5rem;margin-top:1.6rem;font-size:9pt}
  .sl footer span{flex:1;padding-bottom:1.6rem;border-bottom:1px solid #000}
  .top{padding:0 0 .5rem}
  .list{padding:0;min-height:0}
  .grp-h{padding:1rem 0 .3rem;break-after:avoid}
  .rh{padding:.25rem 0;break-inside:avoid}
  .rh:hover{background:none}
  .row{break-inside:avoid}
  .row.open{background:none}
  .rd{padding:0 0 .4rem}
  .nt{display:block;-webkit-line-clamp:unset;line-clamp:unset}
  .grp-t,.loc,.mk,.st,mark{-webkit-print-color-adjust:exact;print-color-adjust:exact}
  a{color:inherit;text-decoration:none}
}
/* ═══ Veřejná verze: kartotéka ═══
   Sklad jako kartotéka: bílé lístky s modrými linkami, nahoře u záhlaví červená linka jako na katalogovém lístku,
   listy jsou rozřazovače z manily (záložky nahoře i štítky listů). Inkoust je modročerný, akce modré jako razítko.
   Barva tu pořád něco znamená: jantarová = půjčené, cihlová = chybí nebo po termínu, čárkovaný modrý rámeček = objednané.
   Písma: Archivo (všechno), Zilla Slab (nadpisy a štítky), obě vložená v souboru. */
:root{
  --ink:#1c2430; --ink2:#5c6573; --paper:#fff; --tint:#f3f6fa; --hair:#ccd9e8;
  --smaragd:#2a4a9b; --limetka:#e9d7a6; --cervena:#c23a24; --cervena-t:#a52f1c; --oranzova:#f0a30a; --zluta:#ffe27a;
  --manila:#e9d7a6; --manila-d:#b89b59; --modra:#2a4a9b; --okraj:#8a93a1; --cara:#d9645a;
  --font:"Archivo","Helvetica Neue",Arial,system-ui,-apple-system,"Segoe UI",sans-serif;
  --font-d:"Zilla Slab",Rockwell,Georgia,"Times New Roman",serif;
}
body{font-feature-settings:"kern"}
::selection{background:#f2e3b8;color:var(--ink)}
:focus-visible{outline:3px solid var(--modra);outline-offset:2px}
mark{background:var(--zluta)}

/* barvy označení a míst (názvy zůstávají, odstíny ladí s kartotékou) */
.c-smaragd{--c:#2e7d5b;--cf:#fff;--ct:#e5f2eb;--cs:#2e7d5b}
.c-limetka{--c:#c3d86b;--cf:#1c2430;--ct:#f3f7e1;--cs:#8fae3a}
.c-modra{--c:#a9c3e6;--cf:#1c2430;--ct:#ecf2fa;--cs:#2a4a9b}
.c-piskova{--c:#e9d7a6;--cf:#1c2430;--ct:#faf4e4;--cs:#b89b59}
.c-zluta{--c:#f6d64a;--cf:#1c2430;--ct:#fff8d6;--cs:#cfae16}
.c-oranzova{--c:#f0a30a;--cf:#1c2430;--ct:#fdf0d8;--cs:#d98c00}
.c-cervena{--c:#c23a24;--cf:#fff;--ct:#fbe9e5;--cs:#c23a24}
.c-seda{--c:#d5dae1;--cf:#1c2430;--ct:#eff2f6;--cs:#8a93a1}
.c-cerna{--c:#1c2430;--cf:#fff;--ct:#e7eaef;--cs:#1c2430}
.chip .dot{border-color:rgba(28,36,48,.35)}

/* ── značka ── */
.top{padding-top:.9rem}
.mark{width:2.75rem}
.wm{font:700 1.75rem/1 var(--font-d);letter-spacing:-.005em}
.wm sup{color:var(--modra)}

/* ── tlačítka ── */
.btn{border-width:1.5px;border-radius:3px}
.btn:hover{background:var(--tint)}
.btn.pri{background:var(--ink);border-color:var(--ink);color:#fff}
.btn.pri:hover{background:var(--modra);border-color:var(--modra);color:#fff}
.btn.on{background:var(--ink);border-color:var(--ink)}
.lnk{color:var(--modra)}

/* ── hledání a rozřazovače ── */
.bar{border-bottom:0;background:linear-gradient(var(--ink),var(--ink)) left bottom/100% 1.5px no-repeat var(--paper)}
.search #q{border-width:1.5px;border-radius:3px}
.tabs{gap:.15rem;margin-top:.6rem;padding-top:.1rem;align-items:flex-end}
.tab{position:relative;z-index:0;min-height:0;margin-bottom:1.5px;padding:.62rem 1.2rem .55rem;background:none;color:var(--ink)}
.tab::before{content:"";position:absolute;inset:0;z-index:-1;background:var(--manila);border:1px solid var(--manila-d);border-bottom:0;border-radius:.35rem .35rem 0 0;transform:perspective(.6rem) rotateX(1.6deg);transform-origin:50% 100%}
.tab:hover{background:none}
.tab:hover::before{background:#e1cb90}
.tab .n{color:#6d5a2c}
.tab[aria-selected=true]{background:none;color:var(--ink);clip-path:none;margin-bottom:0;padding-bottom:calc(.55rem + 1.5px)}
.tab[aria-selected=true]::before{background:var(--paper);border:1.5px solid var(--ink);border-bottom:0}
.tab[aria-selected=true] .n{color:var(--ink2);opacity:1}
.tab[aria-selected=true]:focus-visible{outline-color:var(--modra)}

/* ── nástroje a filtry ── */
.sel,select{border-width:1.5px;border-radius:3px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231c2430' stroke-width='3'%3E%3Cpath d='M5 9l7 7 7-7'/%3E%3C/svg%3E")}
.sel{border-color:var(--ink)}
.chip{border:1.5px solid var(--okraj);border-radius:3px}
.chip:hover{background:var(--tint)}
.chip[aria-pressed=true]{background:var(--ink);border-color:var(--ink)}

/* ── seznam: lístky s linkami ── */
.grp-h{padding:1.7rem var(--pad) .6rem;gap:.25rem .9rem}
.grp-t{background:var(--manila);clip-path:none;border:1px solid var(--manila-d);border-radius:2px;padding:.2rem .75rem .12rem;font:700 1.4rem/1.15 var(--font-d)}
.row{border-top-color:var(--hair)}
.row:last-child{border-bottom-color:var(--hair)}
.rh:hover{background:#f6f8fb}
.row.open{background:#f6f8fb}
.qt{font-variant-numeric:tabular-nums}
.qt.nq{color:#94590a}
.loc{border-left-width:.3rem;border-radius:0 2px 2px 0}
.tag{border-color:var(--okraj);border-radius:2px}
.tag.pr{border-color:var(--modra)}
.tag.pr .ic{color:var(--modra)}
.tag.tg{background:#e9eef7;border-color:transparent;color:#243a73}
.tgb{background:#e9eef7;border-radius:2px;color:#243a73}
.tgb:hover{background:#d7e1f1}
.mk{clip-path:none;border-radius:2px}
.st{border-radius:2px;background:var(--cervena)}
.st.who{background:var(--oranzova);color:var(--ink)}
.st.who.late{background:var(--cervena);color:#fff}
.st.res{border-color:var(--modra);border-radius:2px}
.st.res .ic{color:var(--modra)}
.due.soon{color:#94590a}
.rph img{clip-path:none;border-radius:2px}
.rph-n{background:var(--manila);border-radius:2px}
.ph .img,.ph .img:nth-child(even){clip-path:none;border-radius:2px}
.row.lent.late{box-shadow:inset .3rem 0 0 var(--cervena)}
.wish.bought{box-shadow:inset .3rem 0 0 var(--manila-d)}
.ck.y .bx{background:var(--ink);border-color:var(--ink)}
.rh.pk .bx{border-width:1.5px;border-radius:3px}
.rh.pk[aria-pressed=true] .bx{background:var(--ink);border-color:var(--ink)}
.row.picked{background:#edf2fa}
.empty b{font:700 1.35rem/1.2 var(--font-d)}
/* nový sklad: prázdný katalogový lístek s červenou linkou a volnými řádky */
.empty.first{max-width:32rem;margin:2.5rem auto 0;padding:1.3rem 1.5rem 1.2rem;border:1.5px solid var(--ink);border-radius:3px;background:var(--paper);text-align:left}
.empty.first b{margin-bottom:.7rem;padding-bottom:.5rem;border-bottom:2px solid var(--cara)}
.empty.first .acts{justify-content:flex-start}
.empty.first::after{content:"";display:block;height:4.6rem;margin-top:1.1rem;background:repeating-linear-gradient(to bottom,transparent 0 1.45rem,var(--hair) 1.45rem 1.53rem)}

/* ── objednávky ── */
.ord{border-width:1.5px;border-radius:3px}
.ord.now{box-shadow:inset .3rem 0 0 var(--oranzova)}
.ord-d{background:var(--manila);clip-path:none;border-radius:2px}
.ord-d b{font-family:var(--font-d)}
.ord-who .ic{color:var(--modra)}
.olines.ed{border-width:1.5px;border-radius:3px}

/* ── výběr víc věcí ── */
.pickbar{box-shadow:0 -.25rem .8rem rgba(28,36,48,.25)}
.pickbar .btn.pri{background:var(--manila);border-color:var(--manila);color:var(--ink)}
.pickbar .btn.pri:hover{background:#fff;border-color:#fff}
.pickbar .btn:focus-visible{outline-color:var(--manila)}

/* ── přepínač, fotka přes obrazovku, vzor importu ── */
.sw-t{border-width:1.5px;border-radius:3px}
.sw-t::after{border-radius:2px}
.sw input:checked+.sw-t{background:var(--ink);border-color:var(--ink)}
.sw input:focus-visible+.sw-t{outline-color:var(--modra)}
dialog.lb::backdrop{background:rgba(16,21,29,.94)}
.lb-b{border-radius:3px;background:rgba(16,21,29,.55)}
.lb-b:focus-visible{outline-color:var(--manila)}
pre.csv{border-left-color:var(--manila-d);border-radius:0 2px 2px 0}

/* ── okna: lístek vytažený z kartotéky ── */
dialog.dlg{border-width:1.5px;border-radius:4px}
dialog.dlg::backdrop{background:rgba(28,36,48,.55)}
.dlg-h{background:var(--manila);border-bottom:1.5px solid var(--ink)}
.dlg-h h2{font:700 1.3rem/1.2 var(--font-d)}
.dlg-h .btn{background:var(--paper)}
.dlg-f{border-top:1px solid var(--hair)}

/* ── formuláře ── */
input[type=text],input[type=password],input[type=url],input[type=date],input[type=time],input[type=number],input[type=search],select,textarea{border:1.5px solid var(--okraj);border-radius:3px}
input:focus,select:focus,textarea:focus{border-color:var(--ink)}
input:disabled,select:disabled,textarea:disabled{background:#f0f2f5}
.chk input{border-width:1.5px;border-radius:3px}
.chk input[type=radio]{border-radius:50%}
.chk input:checked{background:var(--ink);border-color:var(--ink)}
.msg{border-left-color:var(--modra);background:#f0f4fa}
.msg.bad{border-left-color:var(--cervena);background:#fbe9e5}
.msg.warn{border-left-color:var(--oranzova);background:#fdf0d8}

/* ── nastavení ── */
.set h2{font:700 1.45rem/1.2 var(--font-d)}
.acc{border:1.5px solid var(--okraj);border-radius:3px}
.acc[open]>summary{border-bottom-color:var(--hair)}
.usrl .un .ic{color:var(--modra)}
.toast{border-radius:3px;box-shadow:0 .25rem .8rem rgba(28,36,48,.3)}

/* ── malé stránky: lístek v šuplíku ── */
body.gate{background:var(--manila)}
body.gate::before{display:none}
.gate-in>.brand .wm{font-size:1.9rem}
.card{border-width:1.5px;border-radius:3px;box-shadow:0 .2rem 0 var(--manila-d)}
.card h1{margin-bottom:.8rem;padding-bottom:.55rem;border-bottom:2px solid var(--cara);font:700 1.65rem/1.15 var(--font-d)}
.hint summary{color:var(--modra)}

/* ── širší obrazovky: záhlaví sloupců s červenou linkou jako na lístku ── */
@media print,(min-width:62rem){
  .cols,.lcols,.wcols{border-bottom:2px solid var(--cara)}
}
@media (max-width:22rem){
  .mark{width:2.3rem}
  .wm{font-size:1.5rem}
}
@media print{
  .grp-t{border-color:#999}
}
#SK:icons
<svg xmlns="http://www.w3.org/2000/svg" class="sprite" width="0" height="0" aria-hidden="true" focusable="false">
<symbol id="i-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5 21 21"/></symbol>
<symbol id="i-x" viewBox="0 0 24 24"><path d="M5 5l14 14M19 5 5 19"/></symbol>
<symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 4v16M4 12h16"/></symbol>
<symbol id="i-filter" viewBox="0 0 24 24"><path d="M3 5h18l-7 8v6l-4 2v-8z"/></symbol>
<symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21 6 11l2.5-7h7L18 11z"/><path d="M10.5 8.5h3v3h-3z"/></symbol>
<symbol id="i-box" viewBox="0 0 24 24"><path d="M3.5 8 12 4l8.5 4v9L12 21l-8.5-4zM3.5 8 12 12l8.5-4M12 12v9"/></symbol>
<symbol id="i-note" viewBox="0 0 24 24"><path d="M5 4h14v11l-5 5H5zM14 20v-5h5"/></symbol>
<symbol id="i-photo" viewBox="0 0 24 24"><path d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="3.5"/></symbol>
<symbol id="i-edit" viewBox="0 0 24 24"><path d="M4 20l1-4.5L16.5 4 20 7.5 8.5 19zM14 6.5l3.5 3.5"/></symbol>
<symbol id="i-trash" viewBox="0 0 24 24"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v6M14 11v6"/></symbol>
<symbol id="i-check" viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></symbol>
<symbol id="i-out" viewBox="0 0 24 24"><path d="M11 4H4v16h7M9 12h11M16 8l4 4-4 4"/></symbol>
<symbol id="i-in" viewBox="0 0 24 24"><path d="M13 4h7v16h-7M3 12h11M10 8l4 4-4 4"/></symbol>
<symbol id="i-set" viewBox="0 0 24 24"><path d="M4 7h9M17 7h3M4 17h3M11 17h9M13 4.5h4v5h-4zM7 14.5h4v5H7z"/></symbol>
<symbol id="i-down" viewBox="0 0 24 24"><path d="M12 4v11M7 11l5 5 5-5M4 20h16"/></symbol>
<symbol id="i-up" viewBox="0 0 24 24"><path d="M12 16V5M7 9l5-5 5 5M4 20h16"/></symbol>
<symbol id="i-print" viewBox="0 0 24 24"><path d="M7 9V4h10v5M7 17H4V9h16v8h-3M7 14h10v6H7z"/></symbol>
<symbol id="i-ext" viewBox="0 0 24 24"><path d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6"/></symbol>
<symbol id="i-chev" viewBox="0 0 24 24"><path d="M5 9l7 7 7-7"/></symbol>
<symbol id="i-left" viewBox="0 0 24 24"><path d="M15 5l-7 7 7 7"/></symbol>
<symbol id="i-right" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></symbol>
<symbol id="i-tag" viewBox="0 0 24 24"><path d="M9 4 7 20M17 4l-2 16M4.5 9h16M3.5 15h16"/></symbol>
<symbol id="i-arrup" viewBox="0 0 24 24"><path d="M12 20V5M6 11l6-6 6 6"/></symbol>
<symbol id="i-arrdown" viewBox="0 0 24 24"><path d="M12 4v15M6 13l6 6 6-6"/></symbol>
<symbol id="i-lock" viewBox="0 0 24 24"><path d="M6 11h12v9H6zM8.5 11V7.5a3.5 3.5 0 0 1 7 0V11"/></symbol>
<symbol id="i-cal" viewBox="0 0 24 24"><path d="M4 6h16v14H4zM4 10.5h16M8.5 3.5v5M15.5 3.5v5"/></symbol>
<symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3.5 2"/></symbol>
<symbol id="i-copy" viewBox="0 0 24 24"><path d="M8 8h12v12H8zM16 8V4H4v12h4"/></symbol>
<symbol id="i-warn" viewBox="0 0 24 24"><path d="M12 4l9 16H3zM12 10v5M12 17.5v.5"/></symbol>
<symbol id="i-cart" viewBox="0 0 24 24"><path d="M3 5h3l2 11h10l2-8H7M9 20h.01M17 20h.01"/></symbol>
<symbol id="i-user" viewBox="0 0 24 24"><path d="M8 8.5 12 4l4 4.5-4 4zM4.5 20l2-5.5h11l2 5.5"/></symbol>
<symbol id="i-bell" viewBox="0 0 24 24"><path d="M6.5 17V10.5a5.5 5.5 0 0 1 11 0V17M4 17h16M10 20.5h4M12 5V3.5"/></symbol>
<symbol id="i-more" viewBox="0 0 24 24"><path d="M5 12h.01M12 12h.01M19 12h.01"/></symbol>
<symbol id="i-price" viewBox="0 0 24 24"><path d="M3 12.5V4h8.5L21 13.5 13.5 21z"/><circle cx="7.6" cy="8.1" r="1.4"/></symbol>
<symbol id="i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.8-3.8 3.4-6 6.5-6s5.7 2.2 6.5 6"/><path d="M15.5 4.6a3.5 3.5 0 0 1 0 6.8M18 14.4c2 .9 3.2 2.8 3.5 5.6"/></symbol>
<symbol id="i-key" viewBox="0 0 24 24"><circle cx="8" cy="15" r="4"/><path d="M11 12l9-9M16 7l3 3M14 9l2 2"/></symbol>
</svg>
#SK:js
(function () {
  'use strict';

  /* ═══ Sklad: přehled a správa v prohlížeči ═══
     Data přijdou se stránkou (BOOT.data). Hledání a filtry běží tady; změny posílá správce na …?api=…
     a server pokaždé vrátí celá čerstvá data. Nikde se neskládá HTML z textů: všechno jde přes h(). */

  var BOOT = JSON.parse(document.getElementById('boot').textContent);
  var ADMIN = !!BOOT.admin;
  var SUPER = ADMIN && !!BOOT.super;  // hlavní správce: Discord, hesla, uživatelé, název, písmo a zálohy; ostatní uživatelé spravují všechno ostatní
  var CSRF = BOOT.csrf || '';
  var HOOK = BOOT.hook || null;       // nastavení Discordu, jen ve správě (adresa webhooku v něm není)
  var D = BOOT.data;
  var FIELDS = ['q', 'ok', 'who', 'p1', 'p2', 'mk', 'own', 'cat', 'kw', 'ph', 'note'];
  var ITEM_KEYS = ['q', 'u', 'ok', 'p1', 'p2', 'mk', 'own', 'cat', 'tg', 'kw', 'ph', 'note'];       // co posílá formulář věci (výpůjčky mají vlastní tlačítka)
  var FIELD_DEF = { q: 'počet', ok: 'Ve skladu?', who: 'Kdo to má půjčené', p1: 'Kde najdu?', p2: 'Kde najdu?', mk: 'označení', own: 'Čí', cat: 'kategorie', kw: 'popis', ph: 'foto', note: 'poznámka' };
  var FIELD_WHAT = { q: 'počet (kusy, gramy nebo litry); má ho každý list', ok: 'zaškrtávátko, jestli věc nechybí', who: 'nadpis nad výpůjčkami', p1: 'místo (police, roh, skříň)', p2: 'v čem to je (krabice, obal)', mk: 'označení písmenem', own: 'čí to je', cat: 'kategorie', kw: 'popis (hledá se v něm); má ho každý list', ph: 'fotky; má je každý list', note: 'poznámka' };
  var FIXED = { q: 1, kw: 1, ph: 1 };      // pole, která má každý list (jde jim jen změnit název)
  var COLORS = [['smaragd', 'smaragdová'], ['limetka', 'limetková'], ['modra', 'modrá'], ['piskova', 'písková'], ['zluta', 'žlutá'], ['oranzova', 'oranžová'], ['cervena', 'červená'], ['seda', 'šedá'], ['cerna', 'černá']];
  var WKEYS = ['who', 'n', 'q', 'where', 'why', 'alt', 'by', 'ok1', 'ok2', 'ok3', 'note'];

  var S = { tab: 'all', q: '', cats: new Set(), tags: new Set(), marks: new Set(), types: new Set(), place: '', own: '', out: false, nocount: false, sort: 'orig', open: new Set(), filt: false, wf: 'all', pick: false, sel: new Set(), lsel: new Set(),
    photos: store('fotky') !== '0' };       // náhledy fotek v seznamu (přepínač „Fotky“, pamatuje si ho prohlížeč)
  var items = [], wishes = [], orders = [], loans = [], secBy = new Map(), markBy = new Map(), typeBy = new Map(), itemBy = new Map(), loanBy = new Map(), resBy = new Map(), tokens = [];
  var tagLabels = new Map();               // tag bez háčků a velkých písmen → jak se píše (nejčastější zápis)
  var app = document.getElementById('app');
  var el = {};

  /* ───────── pomůcky ───────── */

  function h(tag, attrs) {
    var e = document.createElement(tag), k, v;
    if (attrs) {
      for (k in attrs) {
        v = attrs[k];
        if (v === null || v === undefined || v === false) continue;
        if (k === 'class') e.className = v;
        else if (k === 'text') e.textContent = v;
        else if (k.slice(0, 2) === 'on') e.addEventListener(k.slice(2), v);
        else if (k === 'value' || k === 'checked' || k === 'disabled' || k === 'selected' || k === 'hidden' || k === 'open' || k === 'required' || k === 'multiple') e[k] = v;
        else e.setAttribute(k, v === true ? '' : String(v));
      }
    }
    for (var i = 2; i < arguments.length; i++) put(e, arguments[i]);
    return e;
  }
  function put(e, kid) {
    if (kid === null || kid === undefined || kid === false || kid === '') return;
    if (Array.isArray(kid)) { for (var i = 0; i < kid.length; i++) put(e, kid[i]); return; }
    e.appendChild(kid.nodeType ? kid : document.createTextNode(String(kid)));
  }
  /** Vymění obsah prvku (bere uzly, texty i pole, prázdné přeskočí). */
  function fill(parent) {
    while (parent.firstChild) parent.removeChild(parent.firstChild);
    for (var i = 1; i < arguments.length; i++) put(parent, arguments[i]);
    return parent;
  }
  function icon(name, cls) {
    var s = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    s.setAttribute('class', 'ic' + (cls ? ' ' + cls : ''));
    s.setAttribute('aria-hidden', 'true');
    s.setAttribute('focusable', 'false');
    var u = document.createElementNS('http://www.w3.org/2000/svg', 'use');
    u.setAttribute('href', '#i-' + name);
    s.appendChild(u);
    return s;
  }
  function fold(s) { return String(s).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); }
  function plural(n, one, few, many) { return n === 1 ? one : (n >= 2 && n <= 4 ? few : many); }
  function things(n) { return n + ' ' + plural(n, 'věc', 'věci', 'věcí'); }
  /** Sloveso k počtu věcí: 1 věc čeká, 2 věci čekají, 5 věcí čeká. */
  function verb(n, one, few) { return n >= 2 && n <= 4 ? few : one; }
  /** „1 věc čeká, až se uvolní.“ / „2 věci čekají, až se uvolní.“ */
  function waitText(n) { return things(n) + ' ' + verb(n, 'čeká', 'čekají') + ', až se uvolní (někdo ještě nevrátil, co si vzal).'; }
  function loansN(n) { return n + ' ' + plural(n, 'výpůjčka', 'výpůjčky', 'výpůjček'); }
  function fmtDate(iso) { var m = /^(\d{4})-(\d\d)-(\d\d)$/.exec(iso || ''); return m ? (+m[3]) + '. ' + (+m[2]) + '. ' + m[1] : ''; }
  function fmtTime(t) {
    var d = new Date(t * 1000), p = function (x) { return x < 10 ? '0' + x : x; };
    return d.getDate() + '. ' + (d.getMonth() + 1) + '. ' + d.getFullYear() + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
  }
  function today() { var d = new Date(), p = function (x) { return x < 10 ? '0' + x : x; }; return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()); }
  function cmp(a, b) { return a.localeCompare(b, 'cs', { numeric: true, sensitivity: 'base' }); }
  function shortDate(iso) { var m = /^(\d{4})-(\d\d)-(\d\d)$/.exec(iso || ''); return m ? (+m[3]) + '. ' + (+m[2]) + '.' : ''; }
  function addDays(n) { var d = new Date(), p = function (x) { return x < 10 ? '0' + x : x; }; d.setDate(d.getDate() + n); return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()); }
  /** Kolik dní zbývá do data (záporné = po termínu); null, když datum není. */
  function daysTo(iso) {
    var m = /^(\d{4})-(\d\d)-(\d\d)$/.exec(iso || ''), t = today().split('-');
    return m ? Math.round((Date.UTC(+m[1], +m[2] - 1, +m[3]) - Date.UTC(+t[0], +t[1] - 1, +t[2])) / 86400000) : null;
  }
  function days(n) { return n + ' ' + plural(n, 'den', 'dny', 'dní'); }
  var DOW = ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'], DOW2 = ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'];
  function isoDate(iso) { var m = /^(\d{4})-(\d\d)-(\d\d)$/.exec(iso || ''); return m ? new Date(+m[1], +m[2] - 1, +m[3], 12) : null; }
  /** Den krátce: „st 21. 10.“ (rok jen když není letošní). */
  function dayShort(iso) { var d = isoDate(iso); return d ? DOW2[d.getDay()] + ' ' + d.getDate() + '. ' + (d.getMonth() + 1) + '.' + (d.getFullYear() !== new Date().getFullYear() ? ' ' + d.getFullYear() : '') : ''; }
  /** Den celý: „středa 21. 10. 2026“. */
  function dayLong(iso) { var d = isoDate(iso); return d ? DOW[d.getDay()] + ' ' + fmtDate(iso) : ''; }
  var DOW_IN = ['v neděli', 'v pondělí', 'v úterý', 've středu', 've čtvrtek', 'v pátek', 'v sobotu'];
  /** Kdy, do věty: „ve středu 21. 10. 2026“. */
  function dayWhen(iso) { var d = isoDate(iso); return d ? DOW_IN[d.getDay()] + ' ' + fmtDate(iso) : ''; }
  /** Datum bez dne v týdnu: „21. 10.“ (rok jen když není letošní). */
  function dayNum(iso) { var d = isoDate(iso); return d ? d.getDate() + '. ' + (d.getMonth() + 1) + '.' + (d.getFullYear() !== new Date().getFullYear() ? ' ' + d.getFullYear() : '') : ''; }
  function nowHM() { var d = new Date(), p = function (x) { return x < 10 ? '0' + x : x; }; return p(d.getHours()) + ':' + p(d.getMinutes()); }
  /** Kdy si to vyzvedne, slovy: „dnes“, „zítra“, „za 6 dní“. */
  function pickWords(left) {
    if (left === null) return '';
    if (left < 0) return left === -1 ? 'mělo být včera' : 'mělo být před ' + (-left) + ' dny';
    return left === 0 ? 'dnes' : (left === 1 ? 'zítra' : (left === 2 ? 'pozítří' : 'za ' + days(left)));
  }
  /** Termín slovy: „dnes“, „zítra“, „za 5 dní“, „3 dny po termínu“. */
  function dueWords(left) { return left === 0 ? 'dnes' : (left === 1 ? 'zítra' : (left > 0 ? 'za ' + days(left) : days(-left) + ' po termínu')); }
  function dueClass(left) { return 'due' + (left === null ? '' : (left < 0 ? ' late' : (left <= 1 ? ' soon' : ''))); }
  function store(k, v) { try { if (v === undefined) return localStorage.getItem('sklad.' + k) || ''; localStorage.setItem('sklad.' + k, v); } catch (e) {} return ''; }

  /* ── počty: celá čísla v kusech („ks“), v gramech („g“), nebo v mililitrech („ml“, lidem se ukazují litry) ── */
  function grp(n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' '); }
  /** Počet česky: „5 ks“, „1 200 ks“, „500 g“, „2,5 kg“, „330 ml“, „1,5 l“ (stejně jako na serveru). */
  function fmtQ(n, u) {
    if (u === 'g' || u === 'ml') {
      if (n < 1000) return n + ' ' + u;
      var p = (n / 1000).toFixed(3).replace(/0+$/, '').replace(/\.$/, '').split('.');
      return grp(p[0]) + (p[1] ? ',' + p[1] : '') + (u === 'g' ? ' kg' : ' l');
    }
    return grp(n) + ' ks';
  }
  /** Kolik z kolika: „5 z 99 ks“, „500 g z 6 kg“; když je to všechno (nebo celek neznáme), jen „5 ks“. */
  function partQ(part, all, u) { return all === null || all === part ? fmtQ(part, u) : (u === 'ks' ? grp(part) : fmtQ(part, u)) + ' z ' + fmtQ(all, u); }
  /** Počet věci, jak se ukazuje: číslo s jednotkou, nebo text ze soupisu, který číslem není. */
  function qtyText(it) { return it.q !== null ? fmtQ(it.q, it.u) : val(it.qt); }
  /** Jednotka u políčka: „ks“, „g“, „l“ (litry se ukládají v mililitrech). */
  function uLabel(u) { return u === 'ml' ? 'l' : u; }
  /** Počet do políčka v jednotce, kterou políčko ukazuje: litry s desetinnou čárkou, jinak celé číslo. */
  function inQ(n, u) { return n === null ? '' : (u === 'ml' ? String(n / 1000).replace('.', ',') : String(n)); }
  var UNIT_MUL = { x: [1, 'ks'], '×': [1, 'ks'], ks: [1, 'ks'], kus: [1, 'ks'], kusy: [1, 'ks'], kusu: [1, 'ks'], g: [1, 'g'], kg: [1000, 'g'],
    ml: [1, 'ml'], dl: [100, 'ml'], l: [1000, 'ml'], litr: [1000, 'ml'], litry: [1000, 'ml'], litru: [1000, 'ml'] };
  var UNITS = [['ks', 'ks (kusy)'], ['g', 'g (gramy)'], ['ml', 'l (litry)']];
  /**
   * Počet z políčka: „5“, „5 ks“, „4x“, „1 200“; „500 g“, „2,5 kg“; „0,5 l“, „2 dl“, „330 ml“. Holé číslo je v jednotce
   * políčka u (ks, g, nebo ml, které se píší v litrech). Vrací { n: celé číslo v ks, g nebo ml, u: jednotka }, nebo null.
   */
  function parseQty(text, u) {
    var t = fold(String(text)).replace(/ /g, ' ').trim().replace(/(\d) (?=\d{3}(?!\d))/g, '$1');
    var m = /^(\d+(?:[.,]\d+)?) *([a-z×]+)?$/.exec(t), mu;
    if (!m) return null;
    if (m[2]) { mu = UNIT_MUL[m[2]]; if (!mu) return null; } else mu = u === 'ml' ? [1000, 'ml'] : [1, u === 'g' ? 'g' : 'ks'];
    var num = parseFloat(m[1].replace(',', '.')) * mu[0], whole = Math.round(num);
    return Math.abs(num - whole) < 1e-6 && whole <= 999999999 ? { n: whole, u: mu[1] } : null;
  }
  /** Kolik v jednotce věci (kladné celé číslo), nebo null: „2 kg“ u věci v gramech je 2000, „2 kg“ u věci v kusech nedává smysl. */
  function amountIn(text, u) { var p = parseQty(text, u); return p && p.n > 0 && p.u === u ? p.n : null; }
  /** Jednotka za políčkem s počtem („ks“, „g“, „l“). Když v políčku jednotka už je („2 kg“), schová se, ať tam není dvakrát. */
  function unitTag(inp, u) {
    var tag = h('span', { class: 'un', 'aria-hidden': 'true' }, uLabel(u));
    var paint = function () { tag.style.visibility = /[a-z×]/i.test(inp.value) ? 'hidden' : ''; };
    inp.addEventListener('input', paint);
    paint();
    return tag;
  }
  function unitWord(u) { return u === 'g' ? 'v gramech (třeba 500 g nebo 2,5 kg)' : (u === 'ml' ? 'v litrech (třeba 1,5 l nebo 330 ml)' : 'v kusech'); }
  function unitSel(cur, label, onchange, short) {
    return h('select', { 'aria-label': label, onchange: onchange }, UNITS.map(function (x) { return h('option', { value: x[0], selected: cur === x[0] }, short ? uLabel(x[0]) : x[1]); }));
  }

  /* ── ceny: v haléřích, lidem v korunách ── */
  /** Cena česky: „1 200 Kč“, „12,50 Kč“ (stejně jako na serveru). */
  function fmtPrice(h0) { var k = Math.floor(h0 / 100), c = h0 % 100; return grp(k) + (c ? ',' + (c < 10 ? '0' : '') + c : '') + ' Kč'; }
  /** Cena z políčka v haléřích: „1200“, „12,50“, „80 Kč“, „80,-“. Prázdné = bez ceny (null), nesmysl = NaN. */
  function parsePrice(text) {
    var t = fold(String(text)).replace(/ /g, ' ').trim().replace(/\s*(kc|czk)\.?$/, '').replace(/,-$/, '').trim().replace(/(\d) (?=\d{3}(?!\d))/g, '$1');
    if (!t) return null;
    var m = /^(\d{1,7})(?:[.,](\d{1,2}))?$/.exec(t);
    return m ? +m[1] * 100 + (m[2] ? +(m[2] + '0').slice(0, 2) : 0) : NaN;
  }
  function inPrice(h0) { return h0 === null ? '' : (h0 % 100 ? (h0 / 100).toFixed(2).replace('.', ',') : String(h0 / 100)); }
  /** Výpůjční cena, jak ji vidí všichni: „50 Kč za den“. */
  function rentText(it) { return it.rc ? fmtPrice(it.rc) + (it.rn ? ' ' + it.rn : '') : ''; }

  /**
   * Co z věci kdy drží výpůjčky a objednávky (stejná pravidla jako na serveru): výpůjčka od dneška do dne vrácení
   * (bez něj, nebo když minul, pořád), objednávka od vyzvednutí (nejdřív dnes) do dne vrácení (bez něj pořád).
   */
  function holdsFor(it, skipOrder, skipLoan) {
    var t = today(), out = [], qx = function (q) { return q === null ? 'nezapsáno kolik' : fmtQ(q, it.u); };
    it.l.forEach(function (l) {
      if (l.id === skipLoan) return;
      out.push({ a: t, b: l.due && l.due >= t ? l.due : null, q: l.q === null ? Infinity : l.q,
        who: l.who + ' (' + qx(l.q) + ', ' + (!l.due ? 'bez data vrácení' : (l.due < t ? 'termín vrácení ' + fmtDate(l.due) + ' už minul' : 'vrátit do ' + fmtDate(l.due))) + ')' });
    });
    (resBy.get(it.id) || []).forEach(function (x) {
      if (x.o.id === skipOrder) return;
      var a = x.o.from > t ? x.o.from : t;
      out.push({ a: a, b: x.o.due ? (x.o.due >= a ? x.o.due : a) : null, q: x.l.q === null ? Infinity : x.l.q,
        who: 'objednávka' + (x.o.no ? ' č. ' + x.o.no : '') + ' – ' + x.o.who + ' (' + qx(x.l.q) + ', ' + (x.o.from <= t ? 'čeká na vydání' : 'od ' + fmtDate(x.o.from) + (x.o.due ? ' do ' + fmtDate(x.o.due) : ', bez data vrácení')) + ')' });
    });
    return out;
  }
  /** Nejvíc, kolik věc drží najednou v některém dni od a do b (b null = bez konce), a kdo ji tehdy má. */
  function peakOf(holds, a, b) {
    var days = [a], best = 0, who = [];
    holds.forEach(function (x) { if (x.a > a && (b === null || x.a <= b)) days.push(x.a); });
    days.forEach(function (d) {
      var sum = 0, at = [];
      holds.forEach(function (x) { if (x.a <= d && (x.b === null || x.b >= d)) { sum += x.q; at.push(x.who); } });
      if (sum > best) { best = sum; who = at; }
    });
    return { peak: best, who: who };
  }
  /** Kolik z věci je volné v čase od a do b. */
  function freeIn(it, a, b, skipOrder, skipLoan) {
    if (it.q === null || it.ok === false) return 0;
    return Math.max(0, it.q - peakOf(holdsFor(it, skipOrder, skipLoan), a, b).peak);
  }
  /** Proč q z věci v čase od a do b půjčit nejde (text), nebo null, když to jde. */
  function fitText(it, q, a, b, skipOrder, skipLoan) {
    if (it.q === null) return 'nemá zapsaný počet' + (it.qt ? ' (v soupisu „' + it.qt + '“)' : '') + ', doplň ho u věci';
    if (it.ok === false) return 'chybí (u věci není zaškrtnuté „' + okLabel(secBy.get(it.s).f) + '?“)';
    var p = peakOf(holdsFor(it, skipOrder, skipLoan), a, b);
    if (p.peak + q <= it.q) return null;
    return (p.peak >= it.q ? 'v tu dobu není volné nic' : 'v tu dobu je volno jen ' + fmtQ(it.q - p.peak, it.u) + ' z ' + fmtQ(it.q, it.u)) + (p.who.length ? '. Mají to: ' + p.who.join(', ') : '');
  }

  /** Název listu jako nadpis: s nadpisovým písmem z velkých písmen (caps) se koncové „T“ píše jako malá značka. */
  function headText(name) {
    if (!BOOT.brand.caps) return name;
    var m = /^(.*\p{Ll})T$/u.exec(name);
    return m ? m[1].toLocaleUpperCase('cs') + 't' : name.toLocaleUpperCase('cs');
  }

  /** Text s vyznačenými hledanými slovy. */
  function hl(text) {
    if (!tokens.length || !text) return text;
    var chars = Array.from(text), flat = '', i;
    for (i = 0; i < chars.length; i++) { var f = fold(chars[i]); if (f.length !== 1) return text; flat += f; }
    var hit = new Uint8Array(chars.length), any = false;
    tokens.forEach(function (t) { var p = 0; while ((p = flat.indexOf(t, p)) >= 0) { hit.fill(1, p, p + t.length); p += t.length; any = true; } });
    if (!any) return text;
    var out = [], buf = '', on = false;
    for (i = 0; i < chars.length; i++) {
      if (!!hit[i] !== on) { if (buf) out.push(on ? h('mark', null, buf) : buf); buf = ''; on = !!hit[i]; }
      buf += chars[i];
    }
    if (buf) out.push(on ? h('mark', null, buf) : buf);
    return out;
  }

  /** Text, ve kterém jsou webové adresy odkazy. */
  function linkify(text) {
    if (!text || text.indexOf('http') < 0) return text;
    var out = [], re = /https?:\/\/[^\s<>"'`]+/g, last = 0, m;
    while ((m = re.exec(text))) {
      var url = m[0].replace(/[.,;:!?)\]]+$/, '');
      if (m.index > last) out.push(text.slice(last, m.index));
      out.push(extLink(url, shortUrl(url)));
      last = m.index + url.length;
      re.lastIndex = last;
    }
    if (last < text.length) out.push(text.slice(last));
    return out;
  }
  function shortUrl(url) {
    try { var u = new URL(url), p = u.pathname.length > 22 ? u.pathname.slice(0, 20) + '…' : u.pathname; return u.hostname.replace(/^www\./, '') + (p === '/' ? '' : p); } catch (e) { return url; }
  }
  function safeUrl(url) { return /^https?:\/\//i.test(url) ? url : '#'; }
  function extLink(url, label, cls) { return h('a', { href: safeUrl(url), target: '_blank', rel: 'noopener noreferrer', class: cls || null }, label); }

  var toastT = 0;
  function toast(msg, bad, ms) {
    var old = document.querySelector('.toast');
    if (old) old.remove();
    var t = h('div', { class: 'toast' + (bad ? ' bad' : ''), role: bad ? 'alert' : 'status' }, msg);
    (document.querySelector('dialog[open]') || document.body).appendChild(t);
    clearTimeout(toastT);
    toastT = setTimeout(function () { t.remove(); }, ms || (bad ? 7000 : 2600));
  }

  /* ───────── server ───────── */

  function api(name, body) {
    var isForm = typeof FormData !== 'undefined' && body instanceof FormData;
    var headers = { 'X-Sklad-Csrf': CSRF };
    if (body !== undefined && !isForm) headers['Content-Type'] = 'application/json';
    return fetch(location.pathname + '?api=' + encodeURIComponent(name), {
      method: body === undefined ? 'GET' : 'POST', headers: headers, credentials: 'same-origin', cache: 'no-store',
      body: body === undefined ? undefined : (isForm ? body : JSON.stringify(body))
    }).then(function (res) {
      return res.json().catch(function () { return null; }).then(function (j) {
        if (res.ok && j && j.ok) return j;
        var err = new Error(j && j.error ? j.error : (res.status === 413 ? 'Tohle je na server moc velké.' : 'Server neodpověděl, jak měl. Zkus to znovu.'));
        err.status = res.status;
        throw err;
      });
    }, function () {
      throw new Error('Nejde to odeslat. Vypadá to, že jsi bez připojení.');
    });
  }
  /** Zavolá změnu, převezme čerstvá data a překreslí. */
  function change(name, body, okMsg) {
    dataSeq++;
    return api(name, body).then(function (r) {
      lastSync = Date.now();
      if (r.hook) HOOK = r.hook;
      if (r.data) setData(r.data);
      if (typeof okMsg === 'function') okMsg = okMsg(r);
      var note = r.discordNote && r.discordNote.length ? r.discordNote.join(' ') : '';
      if (r.discordError) toast((okMsg ? okMsg + ' ' : '') + 'Na Discord se to ale poslat nepodařilo: ' + r.discordError + '.', true);
      else if (note) toast((okMsg ? okMsg + ' ' : '') + note, !!r.discordBad, 7000);
      else if (okMsg) toast(okMsg);
      return r;
    });
  }
  function fail(e) {
    toast(e && e.message ? e.message : 'Něco se nepovedlo.', true);
    // změnil to mezitím někdo jiný: ukázat rovnou, jak to vypadá teď
    if (e && e.status === 409) api('data').then(function (r) { if (r.data) setData(r.data); }, function () {});
  }

  var chain = Promise.resolve();
  /** Rychlá klepnutí po sobě (dvě zaškrtávátka hned za sebou): další změna počká na výsledek předchozí a vezme si čerstvý stav. */
  function serial(fn) { var p = chain.then(fn); chain = p.then(null, function () {}); return p; }

  var lastSync = Date.now(), dataSeq = 0;
  /** Stránka bývá v telefonu otevřená celé dny. Po návratu na ni se potichu zeptá, jestli se sklad mezitím nezměnil. */
  function resync() {
    var quiet = function () { return S.tab === 'set' || !!document.querySelector('dialog[open]'); };   // rozepsanou práci nepřekreslovat
    if (document.hidden || Date.now() - lastSync < 60000 || quiet()) return;
    lastSync = Date.now();
    var asked = dataSeq;
    api('data').then(function (r) {
      if (r.tick) tick();
      if (asked !== dataSeq || quiet() || !r.data) return;      // mezitím přišla novější data nebo se něco ukládá
      if (JSON.stringify(r.data) !== JSON.stringify(D)) setData(r.data);
    }, function () {});
  }
  /** Připomínky na Discord posílá server; stránka mu jen dá vědět, že je vhodná chvíle se na ně podívat. */
  function tick() {
    try { fetch(location.pathname + '?api=tick', { method: 'POST', credentials: 'same-origin', cache: 'no-store', keepalive: true }).catch(function () {}); } catch (e) {}
  }

  /* ───────── data ───────── */

  function normLoan(l) { return { id: l.id, who: l.who || '', q: typeof l.q === 'number' ? l.q : null, since: l.since || '', due: l.due || '', no: l.no || 0 }; }
  function normItem(r, i) {
    var it = {
      id: r.id, s: r.s, n: r.n || '', q: typeof r.q === 'number' ? r.q : null, u: r.u === 'g' || r.u === 'ml' ? r.u : 'ks', qt: r.qt || '', ok: (r.ok === true || r.ok === false) ? r.ok : null,
      l: (r.l || []).map(normLoan), p1: r.p1 || '', p2: r.p2 || '', mk: r.mk || [], own: r.own || '', cat: r.cat || [], tg: r.tg || [], kw: r.kw || '', ph: r.ph || [], note: r.note || '',
      rc: typeof r.rc === 'number' && r.rc > 0 ? r.rc : null, rn: r.rn || '', pc: typeof r.pc === 'number' && r.pc > 0 ? r.pc : null, ty: r.ty || '', t: r.t || 0, _i: i
    };
    it.lent = it.l.reduce(function (n, l) { return n + (l.q === null ? Infinity : l.q); }, 0);         // kolik je venku (neví se kolik = všechno)
    it.lentKnown = it.l.reduce(function (n, l) { return n + (l.q === null ? 0 : l.q); }, 0);
    it.free = it.q === null ? null : Math.max(0, (it.ok === false ? 0 : it.q) - it.lent);               // kolik jde teď půjčit
    it._n = fold(it.n);
    it._tg = it.tg.map(fold);
    it._ph = it.ph.filter(function (p) { return p.f; });       // nahrané fotky (odkazy na cizí weby náhled v seznamu nemají)
    // hledá se jen v názvu, tazích a popisu (ne v místě, kategorii ani poznámce)
    it._x = fold([it.n, tagText(it), it.kw].join('\n'));
    return it;
  }
  /** Tagy věci jako text k hledání: „#tábor #elektro“ (najde se „tábor“ i „#tábor“). */
  function tagText(it) { return it.tg.map(function (t) { return '#' + t; }).join(' '); }
  function normWish(r, i) {
    var w = { id: r.id, who: r.who || '', n: r.n || '', q: r.q || '', where: r.where || '', why: r.why || '', alt: r.alt || '', by: r.by || '', ok1: !!r.ok1, ok2: !!r.ok2, ok3: !!r.ok3, note: r.note || '', t: r.t || 0, _i: i };
    w._x = fold([w.n, w.who, w.q, w.where, w.why, w.alt, w.by, w.note].join('\n'));
    return w;
  }
  function normOrder(r, i) {
    var o = { id: r.id, no: r.no || 0, who: r.who || '', from: r.from || '', at: r.at || '', due: r.due || '', note: r.note || '', t: r.t || 0, _i: i,
      items: (r.items || []).filter(function (l) { return itemBy.has(l.i); }).map(function (l) { return { i: l.i, q: typeof l.q === 'number' ? l.q : null, w: !!l.w }; }) };
    o._x = fold([o.who].concat(o.items.map(function (l) { var it = itemBy.get(l.i); return it.n + ' ' + tagText(it) + ' ' + it.kw; })).join('\n'));
    return o;
  }
  function index() {
    secBy = new Map(D.sections.map(function (s) { return [s.id, s]; }));
    markBy = new Map(D.marks.map(function (m) { return [m.k, m]; }));
    typeBy = new Map((D.types || []).map(function (t) { return [t.id, t]; }));
    items = D.items.map(normItem);
    itemBy = new Map(items.map(function (it) { return [it.id, it]; }));
    loans = [];
    // výpůjčka se najde podle věci (název, tagy, popis) nebo podle toho, kdo ji má
    items.forEach(function (it) { it.l.forEach(function (l) { loans.push({ l: l, it: it, _x: fold([it.n, tagText(it), it.kw, l.who].join('\n')) }); }); });
    var spell = new Map();
    items.forEach(function (it) { it.tg.forEach(function (t, i) { var k = it._tg[i], m = spell.get(k) || new Map(); m.set(t, (m.get(t) || 0) + 1); spell.set(k, m); }); });
    tagLabels = new Map();
    spell.forEach(function (m, k) { var best = '', n = 0; m.forEach(function (c, t) { if (c > n) { best = t; n = c; } }); tagLabels.set(k, best); });
    loanBy = new Map(loans.map(function (x) { return [x.l.id, x]; }));
    wishes = D.wishes.map(normWish);
    orders = (D.orders || []).map(normOrder).filter(function (o) { return o.items.length; });
    orders.sort(function (a, b) { return cmp(a.from + (a.at || '99'), b.from + (b.at || '99')) || a.no - b.no; });
    resBy = new Map();
    orders.forEach(function (o) { o.items.forEach(function (l) { if (!resBy.has(l.i)) resBy.set(l.i, []); resBy.get(l.i).push({ o: o, l: l }); }); });
    placeMemo = new Map();
  }
  function setData(d) {
    dataSeq++;
    D = d;
    index();
    if (S.tab !== 'all' && S.tab !== 'lent' && !(S.tab === 'wish' && D.wish.on) && S.tab !== 'set' && !secBy.has(S.tab)) S.tab = 'all';
    S.sel.forEach(function (id) { if (!itemBy.has(id)) S.sel.delete(id); });
    S.types.forEach(function (k) { if (k !== '-' && !typeBy.has(k)) S.types.delete(k); });      // typ, který zmizel, se z filtru vyřadí
    S.lsel.forEach(function (id) { if (!loanBy.has(id)) S.lsel.delete(id); });
    refresh();
  }
  var placeMemo = new Map();
  /** Barva místa podle pravidel z nastavení (první, které se v textu najde). */
  function placeColor(p) {
    if (!p) return '';
    if (placeMemo.has(p)) return placeMemo.get(p);
    var f = fold(p), c = '';
    for (var i = 0; i < D.places.length; i++) { if (f.indexOf(fold(D.places[i].t)) >= 0) { c = D.places[i].c; break; } }
    placeMemo.set(p, c);
    return c;
  }
  function val(v) { return v && v !== '-' ? v : ''; }
  // půjčit jde věc z kteréhokoli listu; pole „who“ v listu říká jen, jak se nadpis nad výpůjčkami jmenuje
  function isOut(it) { return it.ok === false || it.l.length > 0; }
  function whoLabel(f) { return 'who' in f ? f.who : 'Kdo to má půjčené'; }
  function okLabel(f) { return ('ok' in f ? f.ok : 'Ve skladu?').replace(/\?\s*$/, ''); }
  /** Výpůjčky, které odpovídají hledání (podle věci nebo člověka). */
  function lentLoans() { return loans.filter(function (x) { return secBy.has(x.it.s) && matchText(x._x); }); }
  /** Objednávky, které odpovídají hledání (podle člověka, poznámky nebo některé z věcí). */
  function visibleOrders() { return orders.filter(function (o) { return matchText(o._x); }); }
  /** Věci, které chybí (odškrtnuté „Ve skladu?“). */
  function missingItems() { return items.filter(function (it) { return secBy.has(it.s) && it.ok === false && matchText(it._x); }); }
  /** Jméno k porovnávání: jen písmena a číslice, malé, bez háčků (stejné pravidlo jako na serveru). */
  function nameKey(v) { return fold(String(v).replace(/[^\p{L}\p{M}\p{N}]+/gu, ' ')).trim(); }
  /** Člověk ze seznamu v Nastavení podle jména u výpůjčky: celé jméno, nebo jméno a za ním dovětek. */
  function person(name) {
    var w = nameKey(name), best = null, len = 0;
    if (!HOOK || !w) return null;
    HOOK.people.forEach(function (p) {
      var n = nameKey(p.n);
      if (!n) return;
      if (w === n) { best = p; len = 1e9; } else if (n.length > len && w.indexOf(n + ' ') === 0) { best = p; len = n.length; }
    });
    return best;
  }
  /** Jak se člověk jmenuje na Discordu: jméno viditelné na serveru a v závorce uživatelské jméno se zavináčem. */
  function discordName(p) { return p.u ? (p.h && p.h !== p.u ? p.h + ' (@' + p.u + ')' : '@' + p.u) : (p.h || ''); }
  function botReady() { return !!(HOOK && HOOK.bot && HOOK.bot.set && HOOK.bot.in && !HOOK.bot.err); }

  /* ───────── výběr toho, co se ukáže ───────── */

  function matchText(x) { for (var i = 0; i < tokens.length; i++) if (x.indexOf(tokens[i]) < 0) return false; return true; }
  function matchFilters(it, skip) {
    var sec = secBy.get(it.s);
    if (!sec) return false;
    if (skip !== 'cat' && S.cats.size && !('cat' in sec.f && it.cat.some(function (c) { return S.cats.has(c); }))) return false;
    if (skip !== 'tg' && S.tags.size && !Array.from(S.tags).every(function (t) { return it._tg.indexOf(t) >= 0; })) return false;      // tagy: věc musí mít všechny vybrané
    if (skip !== 'mk' && S.marks.size && !('mk' in sec.f && it.mk.some(function (m) { return S.marks.has(m); }))) return false;
    if (skip !== 'ty' && S.types.size && !S.types.has(it.ty && typeBy.has(it.ty) ? it.ty : '-')) return false;      // „-“ = věci bez typu
    if (skip !== 'place' && S.place && !('p1' in sec.f && it.p1 === S.place)) return false;
    if (skip !== 'own' && S.own && !('own' in sec.f && it.own === S.own)) return false;
    if (skip !== 'out' && S.out && !isOut(it)) return false;
    if (skip !== 'nc' && S.nocount && it.q !== null) return false;
    return true;
  }
  function inTab(it) { return S.tab === 'all' || it.s === S.tab; }
  function visibleItems() { return items.filter(function (it) { return inTab(it) && matchText(it._x) && matchFilters(it); }); }
  function wishOk(w) {
    if (!matchText(w._x)) return false;
    if (S.wf === 'todo') return !w.ok1;
    if (S.wf === 'ok') return w.ok1 && !w.ok2;
    if (S.wf === 'bought') return w.ok2;
    return true;
  }
  function filterCount() { return S.cats.size + S.tags.size + S.marks.size + S.types.size + (S.place ? 1 : 0) + (S.own ? 1 : 0) + (S.out ? 1 : 0) + (S.nocount ? 1 : 0); }
  function clearFilters() { S.cats.clear(); S.tags.clear(); S.marks.clear(); S.types.clear(); S.place = ''; S.own = ''; S.out = false; S.nocount = false; }
  /** Ukáže věci s tímhle tagem (klepnutí na tag u věci). */
  function filterTag(t) { clearFilters(); S.tags.add(fold(t)); S.filt = true; if (S.tab === 'lent' || S.tab === 'wish' || S.tab === 'set') S.tab = 'all'; refresh(); window.scrollTo(0, 0); }
  function rank(a, b) {
    if (tokens.length) {
      var t = tokens[0], ra = a._n.indexOf(t) === 0 ? 0 : (a._n.indexOf(t) > 0 ? 1 : 2), rb = b._n.indexOf(t) === 0 ? 0 : (b._n.indexOf(t) > 0 ? 1 : 2);
      if (ra !== rb) return ra - rb;
    }
    return a._i - b._i;
  }

  /* ───────── kostra ───────── */

  function build() {
    el.search = h('input', { type: 'search', id: 'q', placeholder: 'Co hledáš?', autocomplete: 'off', autocapitalize: 'off', spellcheck: 'false', 'aria-label': 'Hledat ve skladu', enterkeyhint: 'search', value: S.q,
      oninput: function () { S.q = el.search.value; el.clr.hidden = !S.q; debounce(refresh); },
      onkeydown: function (e) { if (e.key === 'Escape' && S.q) { e.preventDefault(); setQuery(''); } if (e.key === 'Enter') el.search.blur(); } });
    el.clr = h('button', { type: 'button', class: 'clr', 'aria-label': 'Smazat hledání', hidden: !S.q, onclick: function () { setQuery(''); el.search.focus(); } }, icon('x'));
    el.tabs = h('div', { class: 'tabs', role: 'tablist', 'aria-label': 'Listy' });
    el.tools = h('div', { class: 'tools' });
    el.filters = h('div', { class: 'filters', id: 'filters', hidden: true });
    el.list = h('main', { class: 'list', id: 'list' });
    el.foot = h('footer', { class: 'foot' });
    fill(app, 
      h('div', { class: 'bar' }, h('div', { class: 'bar-in' }, h('div', { class: 'srow' }, h('div', { class: 'search' }, icon('search'), el.search, el.clr),
        ADMIN ? (el.add = h('button', { type: 'button', class: 'btn pri addb', id: 'addb', onclick: function () { addThing(); } }, icon('plus'), h('span', { class: 'tx' }, 'Přidat'))) : null), el.tabs)),
      h('div', { class: 'wrap' }, el.tools, el.filters, el.list),
      el.foot
    );
    var topr = document.getElementById('topr');
    if (ADMIN) {
      fill(topr, h('button', { type: 'button', class: 'btn sm', id: 'setbtn', 'aria-label': 'Nastavení', onclick: function () { go(S.tab === 'set' ? 'all' : 'set'); } }, icon('set'), h('span', { class: 'tx' }, 'Nastavení')));
    } else if (BOOT.isAdmin) {
      fill(topr, h('a', { class: 'btn sm', href: '?admin' }, icon('edit'), 'Správa'));
    }
    var links = [h('span', null, BOOT.product + ' ' + BOOT.v)];
    if (ADMIN) {
      if (BOOT.me) links.push(h('span', null, 'Ve správě: ' + BOOT.me));
      links.push(h('a', { href: location.pathname }, 'Přehled bez správy'));
      links.push(h('button', { type: 'button', class: 'lnk', onclick: logout }, 'Odhlásit'));
links.push(h('span', null, 'Ať slouží lidem dobré vůle. Vytvořeno ve ', h('a', { href: 'https://opicakuz.desitka.cz' }, 'vOPičíně'), ', poskytnuto zdarma.'));  
  } else {
      links.push(h('a', { href: '?admin' }, 'Správa'));
      if (BOOT.logged) links.push(h('button', { type: 'button', class: 'lnk', onclick: logout }, 'Odhlásit'));
links.push(h('span', null, 'Ať slouží lidem dobré vůle. Vytvořeno ve ', h('a', { href: 'https://opicakuz.desitka.cz' }, 'vOPičíně'), ', poskytnuto zdarma.'));
    }
    fill(el.foot, links);
  }
  var dT = 0;
  function debounce(fn) { clearTimeout(dT); dT = setTimeout(fn, 70); }
  function setQuery(q) { S.q = q; el.search.value = q; el.clr.hidden = !q; refresh(); }
  function go(tab) {
    S.tab = tab;
    if (tab === 'set' || tab === 'wish' || tab === 'lent') S.filt = false;
    clearFilters();
    refresh();
    window.scrollTo(0, 0);
  }
  function logout() {
    api('logout', {}).then(function () { location.href = location.pathname; }, function () { location.href = location.pathname; });
  }
  function addThing() {
    if (S.tab === 'wish') wishForm(null); else itemForm(null);
  }

  function refresh() {
    tokens = fold(S.q).split(/\s+/).filter(Boolean);
    renderTabs();
    renderTools();
    renderFilters();
    if (S.tab === 'set' && ADMIN) renderSettings();
    else if (S.tab === 'wish') renderWishes();
    else if (S.tab === 'lent') renderLent();
    else renderList();
    var sb = document.getElementById('setbtn');
    if (sb) sb.classList.toggle('on', S.tab === 'set');
    renderPickBar();
    if (el.add) { el.add.hidden = S.tab === 'set'; el.add.setAttribute('aria-label', S.tab === 'wish' ? 'Přidat přání' : 'Přidat věc'); }
    syncHash();
  }

  function renderTabs() {
    var counts = new Map(), total = 0;
    items.forEach(function (it) {
      if (matchText(it._x) && (S.tab === 'wish' || S.tab === 'set' || matchFilters(it))) { counts.set(it.s, (counts.get(it.s) || 0) + 1); total++; }
    });
    var wn = wishes.filter(function (w) { return matchText(w._x); }).length;
    var tab = function (id, label, n) {
      var on = S.tab === id;
      return h('button', { type: 'button', class: 'tab', role: 'tab', 'aria-selected': String(on), tabindex: on ? '0' : '-1', 'data-tab': id, onclick: function () { go(id); } }, label, h('span', { class: 'n' }, n));
    };
    var kids = [tab('all', 'Vše', total), tab('lent', 'Výpůjčky', lentLoans().length + visibleOrders().reduce(function (n, o) { return n + o.items.length; }, 0))];
    D.sections.forEach(function (s) { kids.push(tab(s.id, s.name, counts.get(s.id) || 0)); });
    if (D.wish.on) kids.push(tab('wish', D.wish.name, wn));
    fill(el.tabs, kids);
    var cur = el.tabs.querySelector('[aria-selected=true]');
    if (cur && cur.scrollIntoView && S.tab !== 'set') {
      var r = cur.getBoundingClientRect(), tr = el.tabs.getBoundingClientRect();
      if (r.left < tr.left || r.right > tr.right) el.tabs.scrollLeft += r.left - tr.left - 16;
    }
  }
  el_tabsKeys();
  function el_tabsKeys() {
    document.addEventListener('keydown', function (e) {
      if (!el.tabs || !el.tabs.contains(document.activeElement)) return;
      if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
      var all = Array.prototype.slice.call(el.tabs.querySelectorAll('.tab')), i = all.indexOf(document.activeElement);
      var next = all[(i + (e.key === 'ArrowRight' ? 1 : all.length - 1)) % all.length];
      if (next) { e.preventDefault(); go(next.getAttribute('data-tab')); el.tabs.querySelector('[aria-selected=true]').focus(); }
    });
  }

  function renderTools() {
    if (S.tab === 'set') { fill(el.tools); el.tools.hidden = true; return; }
    el.tools.hidden = false;
    var kids = [];
    if (S.tab === 'wish') {
      var defs = [['all', 'Všechna'], ['todo', 'Neodsouhlasená'], ['ok', 'Ke koupi'], ['bought', 'Koupená']];
      defs.forEach(function (d) {
        var n = wishes.filter(function (w) { var keep = S.wf; S.wf = d[0]; var r = wishOk(w); S.wf = keep; return r; }).length;
        kids.push(h('button', { type: 'button', class: 'chip', 'aria-pressed': String(S.wf === d[0]), onclick: function () { S.wf = d[0]; refresh(); } }, d[1], h('span', { class: 'n' }, n)));
      });
      kids.push(h('span', { class: 'sp' }));
    } else if (S.tab === 'lent') {
      if (ADMIN) kids.push(pickBtn());
      if (orders.length) kids.push(h('button', { type: 'button', class: 'btn sm', onclick: function () { printOrders(visibleOrders()); } }, icon('print'), 'Výdejky'));
      kids.push(h('span', { class: 'sp' }));
    } else {
      var fc = filterCount();
      kids.push(h('button', { type: 'button', class: 'btn sm' + (S.filt ? ' on' : ''), 'aria-expanded': String(S.filt), 'aria-controls': 'filters', onclick: function () { S.filt = !S.filt; refresh(); } }, icon('filter'), 'Filtry' + (fc ? ' (' + fc + ')' : '')));
      if (fc) kids.push(h('button', { type: 'button', class: 'lnk', onclick: function () { clearFilters(); refresh(); } }, 'Zrušit filtry'));
      var sel = h('select', { class: 'sel', 'aria-label': 'Řazení', onchange: function () { S.sort = sel.value; refresh(); } },
        h('option', { value: 'orig', selected: S.sort === 'orig' }, 'Podle soupisu'),
        h('option', { value: 'az', selected: S.sort === 'az' }, 'Podle abecedy'),
        h('option', { value: 'place', selected: S.sort === 'place' }, 'Podle místa'));
      kids.push(sel);
      if (items.some(function (it) { return inTab(it) && it._ph.length; })) kids.push(photoSwitch());
      if (ADMIN) kids.push(pickBtn());
      kids.push(h('span', { class: 'sp' }));
    }
    kids.push(h('span', { class: 'cnt', id: 'cnt', 'aria-live': 'polite' }));
    kids.push(h('button', { type: 'button', class: 'btn sm ico', title: 'Stáhnout jako tabulku (CSV)', 'aria-label': 'Stáhnout jako tabulku (CSV)', onclick: exportCsv }, icon('down')));
    kids.push(h('button', { type: 'button', class: 'btn sm ico pr', title: 'Vytisknout', 'aria-label': 'Vytisknout', onclick: printList }, icon('print')));
    fill(el.tools, kids);
  }
  /** Přepínač „Fotky“: náhledy nahraných fotek v seznamu ano/ne (pamatuje si ho prohlížeč). */
  function photoSwitch() {
    return h('label', { class: 'sw', title: 'Ukazovat v seznamu náhledy fotek' },
      h('input', { type: 'checkbox', role: 'switch', checked: S.photos, onchange: function (e) { S.photos = e.target.checked; store('fotky', S.photos ? '1' : '0'); refresh(); } }),
      h('span', { class: 'sw-t', 'aria-hidden': 'true' }), h('span', null, 'Fotky'));
  }
  function pickBtn() {
    var lent = S.tab === 'lent';
    return h('button', { type: 'button', class: 'btn sm' + (S.pick ? ' on' : ''), 'aria-pressed': String(S.pick),
      title: lent ? 'Zaškrtat víc výpůjček a vrátit je najednou' : 'Vybrat víc věcí a půjčit nebo objednat je najednou', onclick: pickToggle },
      icon('check'), S.pick ? 'Vybírám' : (lent ? 'Vrátit víc najednou' : 'Vybrat víc'));
  }

  function renderFilters() {
    if (!S.filt || S.tab === 'wish' || S.tab === 'set' || S.tab === 'lent') { el.filters.hidden = true; fill(el.filters); return; }
    el.filters.hidden = false;
    var scope = items.filter(function (it) { return inTab(it) && matchText(it._x); });
    var tally = function (skip, get) {
      var m = new Map();
      scope.forEach(function (it) { if (!matchFilters(it, skip)) return; get(it).forEach(function (v) { if (v) m.set(v, (m.get(v) || 0) + 1); }); });
      return m;
    };
    var has = function (it, k) { var s = secBy.get(it.s); return s && k in s.f; };
    var groups = [];
    var outN = scope.filter(function (it) { return matchFilters(it, 'out') && isOut(it); }).length;
    var ncN = scope.filter(function (it) { return matchFilters(it, 'nc') && it.q === null; }).length;
    if (outN || S.out || ncN || S.nocount) {
      groups.push(h('div', { class: 'fg' }, h('b', null, 'Stav'), h('div', { class: 'chips' },
        (outN || S.out) ? h('button', { type: 'button', class: 'chip', 'aria-pressed': String(S.out), onclick: function () { S.out = !S.out; refresh(); } }, 'Chybí nebo je půjčené', h('span', { class: 'n' }, outN)) : null,
        (ncN || S.nocount) ? h('button', { type: 'button', class: 'chip', 'aria-pressed': String(S.nocount), title: 'Počet není zapsaný jako číslo, takže věc nejde půjčit', onclick: function () { S.nocount = !S.nocount; refresh(); } }, 'Bez počtu', h('span', { class: 'n' }, ncN)) : null)));
    }
    // tagy: počet u tagu říká, kolik věcí zůstane, když se přidá (vybrané tagy musí věc mít všechny)
    var tgs = new Map();
    scope.forEach(function (it) { if (matchFilters(it)) it._tg.forEach(function (k) { tgs.set(k, (tgs.get(k) || 0) + 1); }); });
    S.tags.forEach(function (k) { if (!tgs.has(k)) tgs.set(k, 0); });
    if (tgs.size) {
      groups.push(h('div', { class: 'fg' }, h('b', null, 'Tagy'), h('div', { class: 'chips' }, Array.from(tgs.keys()).sort(function (a, b) { return cmp(tagLabels.get(a) || a, tagLabels.get(b) || b); }).map(function (k) {
        return h('button', { type: 'button', class: 'chip tgc', 'aria-pressed': String(S.tags.has(k)), onclick: function () { if (S.tags.has(k)) S.tags.delete(k); else S.tags.add(k); refresh(); } }, '#' + (tagLabels.get(k) || k), h('span', { class: 'n' }, tgs.get(k)));
      }))));
    }
    var cats = tally('cat', function (it) { return has(it, 'cat') ? it.cat : []; });
    S.cats.forEach(function (c) { if (!cats.has(c)) cats.set(c, 0); });
    if (cats.size) {
      groups.push(h('div', { class: 'fg' }, h('b', null, 'Kategorie'), h('div', { class: 'chips' }, Array.from(cats.keys()).sort(cmp).map(function (c) {
        return h('button', { type: 'button', class: 'chip', 'aria-pressed': String(S.cats.has(c)), onclick: function () { if (S.cats.has(c)) S.cats.delete(c); else S.cats.add(c); refresh(); } }, c, h('span', { class: 'n' }, cats.get(c)));
      }))));
    }
    var mks = tally('mk', function (it) { return has(it, 'mk') ? it.mk : []; });
    S.marks.forEach(function (c) { if (!mks.has(c)) mks.set(c, 0); });
    if (mks.size) {
      var order = D.marks.map(function (m) { return m.k; });
      var keys = Array.from(mks.keys()).sort(function (a, b) { var ia = order.indexOf(a), ib = order.indexOf(b); return (ia < 0 ? 99 : ia) - (ib < 0 ? 99 : ib) || cmp(a, b); });
      groups.push(h('div', { class: 'fg' }, h('b', null, 'Označení'), h('div', { class: 'chips' }, keys.map(function (k) {
        var m = markBy.get(k);
        return h('button', { type: 'button', class: 'chip c-' + (m ? m.c : 'seda'), 'aria-pressed': String(S.marks.has(k)), title: m && m.l ? m.l : null, onclick: function () { if (S.marks.has(k)) S.marks.delete(k); else S.marks.add(k); refresh(); } },
          h('span', { class: 'dot' }), k + (m && m.l ? ' · ' + m.l : ''), h('span', { class: 'n' }, mks.get(k)));
      }))));
    }
    // typ položky (inventarizace): jen správa; věci bez typu jsou pod „Bez typu“
    if (ADMIN && D.types && D.types.length) {
      var tys = tally('ty', function (it) { return [it.ty && typeBy.has(it.ty) ? it.ty : '-']; });
      S.types.forEach(function (c) { if (!tys.has(c)) tys.set(c, 0); });
      if (S.types.size || Array.from(tys.keys()).some(function (k) { return k !== '-'; })) {
        var tyOrder = D.types.map(function (t) { return t.id; });
        var tyKeys = Array.from(tys.keys()).sort(function (a, b) { return (a === '-' ? 999 : tyOrder.indexOf(a)) - (b === '-' ? 999 : tyOrder.indexOf(b)); });
        groups.push(h('div', { class: 'fg' }, h('b', null, 'Typ položky'), h('div', { class: 'chips' }, tyKeys.map(function (k) {
          return h('button', { type: 'button', class: 'chip', 'aria-pressed': String(S.types.has(k)), onclick: function () { if (S.types.has(k)) S.types.delete(k); else S.types.add(k); refresh(); } },
            k === '-' ? 'Bez typu' : (typeBy.get(k) || { n: k }).n, h('span', { class: 'n' }, tys.get(k)));
        }))));
      }
    }
    var sel = function (label, skip, key, cur, set, allLabel) {
      var m = tally(skip, function (it) { return has(it, key) ? [it[key]] : []; });
      if (cur && !m.has(cur)) m.set(cur, 0);
      if (m.size < 2 && !cur) return null;
      var s = h('select', { 'aria-label': label, onchange: function () { set(s.value); refresh(); } }, h('option', { value: '' }, allLabel),
        Array.from(m.keys()).sort(cmp).map(function (v) { return h('option', { value: v, selected: v === cur }, v + ' (' + m.get(v) + ')'); }));
      return h('div', { class: 'fg' }, h('b', null, label), s);
    };
    groups.push(sel('Místo', 'place', 'p1', S.place, function (v) { S.place = v; }, 'Všechna místa'));
    groups.push(sel('Čí to je', 'own', 'own', S.own, function (v) { S.own = v; }, 'Všichni'));
    fill(el.filters, groups.filter(Boolean));
    if (!el.filters.childNodes.length) el.filters.appendChild(h('p', { class: 'cnt' }, 'Tady není podle čeho filtrovat.'));
  }

  /* ───────── seznam věcí ───────── */

  /** Výzva pro správce: věci bez počtu nejdou půjčit, ať se doplní. */
  function countBanner() {
    if (!ADMIN) return null;
    var n = items.filter(function (it) { return inTab(it) && secBy.has(it.s) && it.q === null; }).length;
    if (!n) return null;
    return h('div', { class: 'msg warn ncb', role: 'status' },
      h('p', null, h('b', null, things(n) + ' ' + verb(n, 'nemá', 'nemají') + ' počet.'), ' Dokud ' + (n === 1 ? 'ho nedoplníš, nejde' : 'je nedoplníš, nejdou') + ' půjčit ani objednat. '
        + 'Text ze soupisu (třeba „99+“) přepiš na číslo: kolik toho máte, u potravin v gramech.'),
      h('div', { class: 'acts' }, h('button', { type: 'button', class: 'btn sm pri', onclick: function () { countsForm(); } }, icon('edit'), 'Doplnit počty'),
        S.nocount ? null : h('button', { type: 'button', class: 'btn sm', onclick: function () { S.nocount = true; S.filt = true; refresh(); } }, 'Ukázat je v seznamu')));
  }
  function renderList() {
    var vis = visibleItems(), frag = [], banner = countBanner();
    var cnt = document.getElementById('cnt');
    if (cnt) cnt.textContent = things(vis.length);
    if (banner) frag.push(h('div', { class: 'ncw' }, banner));
    if (!vis.length) {
      var filtered = tokens.length || filterCount(), none = !items.length;      // none: ve skladu není vůbec nic (nový sklad)
      el.list.className = 'list';
      fill(el.list, frag, h('div', { class: 'empty' + (none && !filtered ? ' first' : '') }, h('b', null, filtered ? 'Nic takového tu není' : (none ? 'Sklad je zatím prázdný' : 'Tenhle list je zatím prázdný')),
        filtered ? (tokens.length ? 'Hledám v názvech, tazích a popisech věcí. Místo nebo kategorii najdeš ve Filtrech. ' : '') + 'Zkus jiné slovo, jiný list, nebo zruš filtry.'
          : (ADMIN ? (none ? 'Přidej první věc, nebo nahraj celý soupis z tabulky (Excel, Google Tabulky).' : 'První věc přidáš tlačítkem Přidat.') : ''),
        filtered ? h('div', null, h('button', { type: 'button', class: 'btn', onclick: function () { clearFilters(); S.tab = 'all'; setQuery(''); } }, 'Ukázat všechno'))
          : (ADMIN && none ? h('div', { class: 'acts' }, h('button', { type: 'button', class: 'btn pri', onclick: function () { addThing(); } }, icon('plus'), 'Přidat věc'),
            h('button', { type: 'button', class: 'btn', onclick: function () { importForm(); } }, icon('up'), 'Nahrát z tabulky')) : null)));
      return;
    }
    var bySec = new Map();
    vis.forEach(function (it) { if (!bySec.has(it.s)) bySec.set(it.s, []); bySec.get(it.s).push(it); });
    D.sections.forEach(function (sec) {
      var arr = bySec.get(sec.id);
      if (!arr) return;
      var wph = S.photos && arr.some(function (it) { return it._ph.length; });       // sloupec s náhledy fotek
      var grp = h('section', { class: 'grp' + (wph ? ' wph' : '') });
      grp.appendChild(h('div', { class: 'grp-h' },
        h('h2', { class: 'grp-t' }, headText(sec.name)),
        h('span', { class: 'grp-n' }, things(arr.length)),
        sec.upd ? h('span', { class: 'grp-u' }, 'aktualizováno: ' + fmtDate(sec.upd)) : null,
        sec.note ? h('p', { class: 'grp-note' }, linkify(sec.note)) : null));
      if (S.sort === 'place' && 'p1' in sec.f) {
        arr.sort(function (a, b) { return (a.p1 ? 0 : 1) - (b.p1 ? 0 : 1) || cmp(a.p1, b.p1) || cmp(a.p2, b.p2) || a._i - b._i; });
        var last = null, box = null;
        arr.forEach(function (it) {
          if (it.p1 !== last) {
            last = it.p1;
            var n = arr.filter(function (x) { return x.p1 === last; }).length, c = placeColor(last);
            grp.appendChild(h('h3', { class: 'sub' }, h('span', { class: 'loc' + (c ? ' c-' + c : '') }, last || 'místo neuvedeno'), h('span', { class: 'n' }, things(n))));
            box = h('div', { class: 'rows' });
            grp.appendChild(box);
          }
          box.appendChild(rowEl(it, sec, true, wph));
        });
      } else {
        if (S.sort === 'az') arr.sort(function (a, b) { return cmp(a.n, b.n) || a._i - b._i; });
        else arr.sort(rank);
        grp.appendChild(colsEl(sec, arr));
        var rows = h('div', { class: 'rows' });
        arr.forEach(function (it) { rows.appendChild(rowEl(it, sec, false, wph)); });
        grp.appendChild(rows);
      }
      frag.push(grp);
    });
    el.list.className = 'list inv';
    fill(el.list, frag);
  }
  function colsEl(sec, arr) {
    var f = sec.f, a = [];
    if ('cat' in f) a.push(f.cat);
    if (arr.some(function (it) { return it.tg.length; })) a.push('tagy');
    if ('own' in f) a.push(f.own);
    if ('mk' in f) a.push(f.mk);
    return h('div', { class: 'cols', 'aria-hidden': 'true' },
      h('span', null, sec.nl), h('span', null, 'q' in f ? f.q : ''), h('span', null, 'p1' in f ? f.p1 : ('p2' in f ? f.p2 : '')),
      h('span', null, a.join(', ')), h('span', null, 'note' in f ? f.note : ''));
  }
  function locTag(it, f, noPlace) {
    var a = 'p1' in f && !noPlace ? val(it.p1) : '', b = 'p2' in f ? val(it.p2) : '';
    if (!a && !b) return null;
    var c = placeColor('p1' in f ? val(it.p1) : '');
    return h('span', { class: 'loc' + (c ? ' c-' + c : '') }, a || null, b ? h('span', { class: 'in' }, a ? ' › ' : null, b) : null);
  }
  function markEl(k) {
    var m = markBy.get(k);
    return h('span', { class: 'mk c-' + (m ? m.c : 'seda'), title: m && m.l ? k + ' = ' + m.l : null }, k);
  }
  function statusChips(it, f) {
    var out = [];
    if (it.ok === false) out.push(h('span', { class: 'st' }, okLabel(f) + ': ne'));
    it.l.slice(0, 2).forEach(function (l) {
      var left = daysTo(l.due);
      out.push(h('span', { class: 'st who' + (left !== null && left < 0 ? ' late' : ''), title: l.due ? 'vrátit do ' + fmtDate(l.due) + ' (' + dueWords(left) + ')' : 'bez termínu vrácení' },
        icon('user'), h('span', null, l.who + (l.q !== null && l.q !== it.q ? ' · ' + fmtQ(l.q, it.u) : '') + (l.due ? ' · do ' + shortDate(l.due) : ''))));
    });
    if (it.l.length > 2) out.push(h('span', { class: 'st who', title: it.l.slice(2).map(function (l) { return l.who; }).join(', ') }, icon('user'), h('span', null, 'a další ' + (it.l.length - 2))));
    var rs = resBy.get(it.id);
    if (rs) {
      var r0 = rs[0];
      out.push(h('span', { class: 'st res', title: rs.map(function (x) { return x.o.who + ' si vyzvedne ' + dayWhen(x.o.from) + (x.o.at ? ' v ' + x.o.at : '') + (x.l.q !== null ? ' (' + fmtQ(x.l.q, it.u) + ')' : ''); }).join('; ') },
        icon('cal'), h('span', null, r0.o.who + ' · od ' + dayShort(r0.o.from) + (rs.length > 1 ? ' a další ' + (rs.length - 1) : ''))));
    }
    return out;
  }
  /** Sloupec s počtem: kolik toho je, a když je něco venku, kolik zbývá volné. Bez počtu text ze soupisu. */
  function qtCell(it) {
    if (it.q === null) return it.qt ? it.qt : (ADMIN ? 'bez počtu' : '');
    var busy = it.lent > 0 || it.ok === false;
    return [fmtQ(it.q, it.u), busy ? h('small', { class: 'fr' }, it.free > 0 ? 'volno ' + fmtQ(it.free, it.u) : (it.ok === false ? 'chybí' : 'všechno venku')) : null];
  }
  /** Hlava řádku: v režimu výběru věc vybírá, jinak rozbaluje podrobnosti. */
  function headBtn(it, open) {
    if (S.pick) return h('button', { type: 'button', class: 'rh pk', 'aria-pressed': String(S.sel.has(it.id)), onclick: function () { togglePick(it.id); } });
    return h('button', { type: 'button', class: 'rh', 'aria-expanded': String(open), onclick: function () { toggleRow(it.id); } });
  }
  function pickBox() { return S.pick ? h('span', { class: 'bx', 'aria-hidden': 'true' }, icon('check')) : null; }
  /**
   * Řádek věci. wph = list má sloupec s náhledy fotek: náhled je odkaz vedle tlačítka řádku (klepnutím se fotka zvětší),
   * věc bez nahrané fotky má místo něj prázdné místo, ať řádky lícují.
   */
  function rowEl(it, sec, byPlace, wph) {
    var f = sec.f, open = S.open.has(it.id) && !S.pick, thumb = wph && it._ph.length;
    var head = fill(headBtn(it, open),
      h('span', { class: 'nm' }, pickBox(), h('b', null, hl(it.n)), it.kw ? h('small', { class: 'kw' }, hl(it.kw)) : null),
      h('span', { class: 'qt' + (it.q === null ? ' nq' : ''), title: it.q === null ? 'Počet není zapsaný jako číslo, věc zatím nejde půjčit' : null }, qtCell(it)),
      h('span', { class: 'lc' }, locTag(it, f, byPlace)),
      h('span', { class: 'tags' }, statusChips(it, f), 'cat' in f ? it.cat.map(function (c) { return h('span', { class: 'tag' }, h('span', null, c)); }) : null,
        it.tg.map(function (t) { return h('span', { class: 'tag tg' }, h('span', null, '#', hl(t))); }),
        ('own' in f && it.own) ? h('span', { class: 'tag own', title: f.own }, icon('user'), h('span', null, it.own)) : null, 'mk' in f ? it.mk.map(markEl) : null,
        it.rc ? h('span', { class: 'tag pr', title: 'Výpůjční cena' }, icon('price'), h('span', null, rentText(it))) : null,
        (it.ph.length && !thumb) ? h('span', { class: 'tag', title: 'Fotky' }, icon('photo'), ' ' + it.ph.length) : null,
        (ADMIN && it.ty && typeBy.has(it.ty)) ? h('span', { class: 'tag ty', title: 'Typ položky (vidí jen správa)' }, h('span', null, typeBy.get(it.ty).n)) : null),
      h('span', { class: 'nt' }, 'note' in f ? it.note : ''));
    return h('div', { class: 'row' + (open ? ' open' : '') + (S.pick && S.sel.has(it.id) ? ' picked' : '') + (wph ? ' wph' : ''), id: 'r-' + it.id, 'data-bp': byPlace ? '1' : null, 'data-wph': wph ? '1' : null },
      wph ? thumbEl(it) : null, head, open ? detailEl(it, sec) : null);
  }
  /** Náhled první nahrané fotky věci; klepnutím se fotky zvětší. Bez fotky prázdné místo. */
  function thumbEl(it) {
    var p = it._ph[0];
    if (!p) return h('span', { class: 'rph none', 'aria-hidden': 'true' });
    var n = it._ph.length;
    return h('a', { class: 'rph', href: photoSrc(p), 'aria-label': 'Fotka: ' + it.n + (n > 1 ? ' (' + n + ' fotky)' : ''), onclick: function (e) { e.preventDefault(); lightbox(it, 0); } },
      h('img', { src: photoSrc(p, true), alt: '', loading: 'lazy', width: 56, height: 56, onerror: thumbFail }), n > 1 ? h('span', { class: 'rph-n', 'aria-hidden': 'true' }, n) : null);
  }
  /** Náhled se nenačetl (soubor chybí, přihlášení vypršelo): místo rozbitého obrázku ikonka. */
  function thumbFail() {
    var a = this.parentNode;
    if (!a) return;
    a.classList.add('err');
    a.replaceChild(icon('photo'), this);
  }
  /** Překreslí jeden řádek věci (v seznamu nebo mezi chybějícími) a vrátí na něj fokus. */
  function redrawRow(id) {
    var old = document.getElementById('r-' + id), it = itemBy.get(id);
    if (!old || !it) return;
    var neu = old.getAttribute('data-miss') === '1' ? missRowEl(it) : rowEl(it, secBy.get(it.s), old.getAttribute('data-bp') === '1', old.getAttribute('data-wph') === '1');
    old.replaceWith(neu);
    neu.querySelector('.rh').focus({ preventScroll: true });
  }
  /** Vybere věc do objednávky (nebo z výběru vyřadí) a překreslí jen její řádek a lištu dole. */
  function togglePick(id) {
    if (S.sel.has(id)) S.sel.delete(id); else S.sel.add(id);
    redrawRow(id);
    renderPickBar();
  }
  function toggleRow(id) {
    if (S.open.has(id)) S.open.delete(id); else S.open.add(id);
    redrawRow(id);
  }
  /** Totéž pro řádek výpůjčky (v přehledu výpůjček). */
  function redrawLoan(lid) {
    var old = document.getElementById('r-L' + lid), x = loanBy.get(lid);
    if (!old || !x) return;
    var neu = lentRowEl(x);
    old.replaceWith(neu);
    neu.querySelector('.rh').focus({ preventScroll: true });
  }
  function toggleLoanPick(lid) {
    if (S.lsel.has(lid)) S.lsel.delete(lid); else S.lsel.add(lid);
    redrawLoan(lid);
    renderPickBar();
  }
  function toggleLoanRow(lid) {
    if (S.open.has('L' + lid)) S.open.delete('L' + lid); else S.open.add('L' + lid);
    redrawLoan(lid);
  }
  function photoSrc(p, thumb) { return '?foto=' + encodeURIComponent(p.f) + (thumb && p.th ? '&t=1' : ''); }
  /** Jedna výpůjčka v podrobnostech věci: kdo, kolik, odkdy, do kdy; správce ji tu vrací, upravuje a připomíná. */
  function loanLi(l, it, hot) {
    var left = daysTo(l.due);
    return h('li', { class: hot ? 'hot' : null },
      h('b', null, l.who), ' – ', l.q === null ? h('span', { class: 'due late' }, 'nezapsáno kolik') : partQ(l.q, it.q, it.u),
      l.since ? ', od ' + fmtDate(l.since) : '', ', ', l.due ? ['vrátit do ' + fmtDate(l.due) + ' ', h('span', { class: dueClass(left) }, '(' + dueWords(left) + ')')] : 'bez termínu vrácení',
      l.no ? ' · objednávka č. ' + l.no : '',
      ADMIN ? h('span', { class: 'la' },
        h('button', { type: 'button', class: 'btn sm pri', onclick: function () { returnFor(l); } }, icon('in'), 'Vrátit'),
        h('button', { type: 'button', class: 'btn sm', onclick: function () { loanForm(l, it); } }, icon('clock'), 'Upravit'),
        (HOOK && HOOK.set) ? h('button', { type: 'button', class: 'btn sm', onclick: function () { remindNow(l); } }, icon('bell'), 'Připomenout') : null) : null);
  }
  function detailEl(it, sec, hotLoan) {
    var f = sec.f, dl = h('dl', { class: 'kv' });
    var add = function (label, value) { if (value === '' || value === null || value === undefined || (Array.isArray(value) && !value.length)) return; dl.appendChild(h('dt', null, label)); dl.appendChild(h('dd', null, value)); };
    if (S.tab === 'all' || S.tab === 'lent') add('List', sec.name);
    if ('p1' in f && 'p2' in f && f.p1 === f.p2) add(f.p1, [it.p1, it.p2].filter(Boolean).join(' › '));
    else { if ('p1' in f) add(f.p1, it.p1); if ('p2' in f) add(f.p2, it.p2); }
    if (it.q === null) add('q' in f ? f.q : 'počet', [it.qt ? '„' + it.qt + '“ ' : '', h('span', { class: 'due late' }, it.qt ? '(není to číslo, věc zatím nejde půjčit)' : 'není zapsaný, věc zatím nejde půjčit')]);
    else add('q' in f ? f.q : 'počet', fmtQ(it.q, it.u) + (it.lent > 0 || it.ok === false ? ', volno teď ' + fmtQ(it.free, it.u) : ''));
    if (('ok' in f && it.ok !== null) || it.ok === false) add('ok' in f ? f.ok : 'Ve skladu?', it.ok ? 'ano' : 'ne, chybí');
    if (it.l.length) add(whoLabel(f), h('ul', { class: 'loans' }, it.l.map(function (l) { return loanLi(l, it, hotLoan && hotLoan.id === l.id && it.l.length > 1); })));
    var rs = resBy.get(it.id);
    if (rs) add('Objednávky', h('ul', { class: 'resl' }, rs.map(function (x) { return h('li', null, resText(x.o, x.l, it), ADMIN ? [' ', h('button', { type: 'button', class: 'lnk', onclick: function () { orderForm(x.o); } }, 'upravit')] : null); })));
    if ('mk' in f) add(f.mk, it.mk.map(function (k) { var m = markBy.get(k); return [markEl(k), m && m.l ? m.l + ' ' : null]; }));
    if ('own' in f) add(f.own, it.own);
    if ('cat' in f) add(f.cat, it.cat.join(', '));
    if (it.tg.length) add('Tagy', h('span', { class: 'tgs' }, it.tg.map(function (t) {
      return h('button', { type: 'button', class: 'tgb', title: 'Ukázat všechny věci s tagem #' + t, onclick: function () { filterTag(t); } }, '#' + t);
    })));
    if (it.kw) add('kw' in f ? f.kw : FIELD_DEF.kw, h('span', { class: 'pl' }, linkify(it.kw)));
    if ('note' in f) add(f.note, linkify(it.note));
    add('Výpůjční cena', rentText(it));
    if (ADMIN && it.pc) add('Pořizovací cena', [fmtPrice(it.pc), h('small', { class: 'adm' }, ' (vidí jen správa)')]);
    if (ADMIN && it.ty && typeBy.has(it.ty)) add('Typ položky', [typeBy.get(it.ty).n, h('small', { class: 'adm' }, ' (vidí jen správa)')]);
    if (it.ph.length) {
      var n = 0;
      add('ph' in f ? f.ph : FIELD_DEF.ph, h('div', { class: 'ph' }, it.ph.map(function (p) {
        n++;
        if (p.f) {
          var k = it._ph.indexOf(p);
          return h('a', { class: 'img', href: photoSrc(p), 'aria-label': 'Zvětšit fotku ' + n, onclick: function (e) { e.preventDefault(); lightbox(it, k); } },
            h('img', { src: photoSrc(p, true), alt: it.n + ', fotka ' + n, loading: 'lazy', width: 110, height: 110 }));
        }
        return extLink(p.u, [icon('ext'), 'Fotka ' + n], 'btn sm');
      })));
    }
    var acts = null;
    if (ADMIN) {
      var lendable = it.q !== null && it.ok !== false;
      acts = h('div', { class: 'acts' },
        h('button', { type: 'button', class: 'btn sm pri', onclick: function () { itemForm(it); } }, icon('edit'), it.q === null ? 'Doplnit počet' : 'Upravit'),
        lendable && it.free > 0 ? h('button', { type: 'button', class: 'btn sm', onclick: function () { orderForm(null, [it]); } }, icon('out'), 'Půjčit') : null,
        lendable ? h('button', { type: 'button', class: 'btn sm', onclick: function () { orderForm(null, [it], true); } }, icon('cal'), 'Objednat') : null,
        it.ok === false ? h('button', { type: 'button', class: 'btn sm', onclick: function () { foundItems([it]); } }, icon('in'), 'Našlo se') : null,
        it.ph.length < 12 ? h('button', { type: 'button', class: 'btn sm', onclick: function (e) { quickPhoto(it, e.currentTarget); } }, icon('photo'), 'Přidat fotku') : null,
        h('button', { type: 'button', class: 'btn sm', onclick: function () { itemForm(it, true); } }, icon('copy'), 'Kopie'),
        h('button', { type: 'button', class: 'btn sm dan', onclick: function () { removeItem(it); } }, icon('trash'), 'Smazat'));
    }
    return h('div', { class: 'rd' }, dl.childNodes.length ? dl : h('p', { class: 'cnt' }, 'Víc toho o téhle věci v soupisu není.'), acts);
  }

  /* ───────── výpůjčky a objednávky ───────── */

  function renderLent() {
    var ls = lentLoans(), miss = missingItems(), ords = visibleOrders(), far = 1e6;
    ls.sort(function (a, b) {
      var da = daysTo(a.l.due), db = daysTo(b.l.due);
      return (da === null ? far : da) - (db === null ? far : db) || cmp(a.l.since, b.l.since) || a.it._i - b.it._i;
    });
    var late = ls.filter(function (x) { var l = daysTo(x.l.due); return l !== null && l < 0; }).length;
    var nOrd = ords.reduce(function (n, o) { return n + o.items.length; }, 0);
    var cnt = document.getElementById('cnt');
    if (cnt) cnt.textContent = things(ls.length + nOrd);
    var kids = [];
    if (ords.length) {
      kids.push(h('div', { class: 'grp-h' }, h('h2', { class: 'grp-t' }, headText('K vyzvednutí')),
        h('span', { class: 'grp-n' }, ords.length + ' ' + plural(ords.length, 'objednávka', 'objednávky', 'objednávek') + ', ' + things(nOrd))));
      kids.push(h('div', { class: 'ords' }, ords.map(orderEl)));
    }
    kids.push(h('div', { class: 'grp-h' + (ords.length ? ' next' : '') }, h('h2', { class: 'grp-t' }, headText('Půjčené')), h('span', { class: 'grp-n' }, loansN(ls.length)),
      late ? h('span', { class: 'grp-u due late' }, late + ' po termínu') : null));
    if (ls.length) {
      kids.push(h('div', { class: 'lcols', 'aria-hidden': 'true' }, ['věc a kolik', 'kdo to má', 'půjčeno', 'vrátit do', 'kam patří'].map(function (t) { return h('span', null, t); })));
      kids.push(h('div', { class: 'rows' }, ls.map(lentRowEl)));
    } else {
      kids.push(h('div', { class: 'empty' }, h('b', null, tokens.length ? 'Nic takového půjčené není' : 'Všechno je na místě'),
        tokens.length ? 'Zkus jiné slovo.' : 'Nikdo teď nemá nic půjčeného.',
        tokens.length ? h('div', null, h('button', { type: 'button', class: 'btn', onclick: function () { setQuery(''); } }, 'Ukázat všechny výpůjčky')) : null));
    }
    if (miss.length) {
      kids.push(h('h3', { class: 'sub' }, h('span', null, 'Chybí (nejsou ve skladu a nikdo je zapsané nemá)'), h('span', { class: 'n' }, things(miss.length))));
      kids.push(h('div', { class: 'rows' }, miss.map(missRowEl)));
    }
    el.list.className = 'list lentv';
    fill(el.list, h('section', { class: 'grp' }, kids));
  }
  /** Řádek výpůjčky: věc a kolik, kdo ji má, odkdy, do kdy a kam patří. Rozbalí se do podrobností věci. */
  function lentRowEl(x) {
    var it = x.it, l = x.l, sec = secBy.get(it.s), f = sec.f, open = S.open.has('L' + l.id) && !S.pick, left = daysTo(l.due), loc = locTag(it, f);
    var head = S.pick ? h('button', { type: 'button', class: 'rh pk', 'aria-pressed': String(S.lsel.has(l.id)), onclick: function () { toggleLoanPick(l.id); } })
      : h('button', { type: 'button', class: 'rh', 'aria-expanded': String(open), onclick: function () { toggleLoanRow(l.id); } });
    fill(head,
      h('span', { class: 'nm' }, pickBox(), h('b', null, hl(it.n)), h('small', { class: 'kw' }, l.q === null ? 'nezapsáno kolik' : partQ(l.q, it.q, it.u))),
      h('span', { class: 'l-who' }, h('span', { class: 'st who' + (left !== null && left < 0 ? ' late' : '') }, icon('user'), h('span', null, hl(l.who)))),
      h('span', { class: 'l-since' }, l.since ? [h('i', null, 'půjčeno '), fmtDate(l.since)] : null),
      h('span', { class: 'l-due' }, l.due ? [h('i', null, 'vrátit do '), fmtDate(l.due) + ' ', h('span', { class: dueClass(left) }, dueWords(left))] : h('span', { class: 'due' }, 'bez termínu')),
      h('span', { class: 'l-home' }, h('i', null, 'patří: '), h('span', { class: 'l-sec' }, sec.name), loc ? [' ', loc] : null));
    return h('div', { class: 'row lent' + (open ? ' open' : '') + (left !== null && left < 0 ? ' late' : '') + (S.pick && S.lsel.has(l.id) ? ' picked' : ''), id: 'r-L' + l.id }, head, open ? detailEl(it, sec, l) : null);
  }
  /** Řádek věci, která chybí (v přehledu výpůjček). */
  function missRowEl(it) {
    var sec = secBy.get(it.s), f = sec.f, open = S.open.has(it.id) && !S.pick, loc = locTag(it, f);
    var head = fill(h('button', { type: 'button', class: 'rh', 'aria-expanded': String(open), onclick: function () { toggleRow(it.id); } }),
      h('span', { class: 'nm' }, h('b', null, hl(it.n)), it.q !== null ? h('small', { class: 'kw' }, fmtQ(it.q, it.u)) : null),
      h('span', { class: 'l-who' }, h('span', { class: 'st' }, okLabel(f) + ': ne')),
      h('span', { class: 'l-since' }), h('span', { class: 'l-due' }),
      h('span', { class: 'l-home' }, h('i', null, 'patří: '), h('span', { class: 'l-sec' }, sec.name), loc ? [' ', loc] : null));
    return h('div', { class: 'row lent' + (open ? ' open' : ''), id: 'r-' + it.id, 'data-miss': '1' }, head, open ? detailEl(it, sec) : null);
  }
  /** Věta o objednávce u věci: „Opičák si vyzvedne ve středu 21. 10. 2026 v 17:00 (za 7 dní), 2 z 5 ks, vrátí do 28. 10. 2026“ */
  function resText(o, l, it) {
    var left = daysTo(o.from), warn = orderWarn(o, l, it);
    return [h('b', null, o.who), ' si vyzvedne ' + dayWhen(o.from) + (o.at ? ' v ' + o.at : '') + ' ', h('span', { class: 'due' + (left !== null && left <= 1 ? ' soon' : '') }, '(' + pickWords(left) + ')'),
      ', ' + (l.q === null ? 'nezapsáno kolik' : partQ(l.q, it.q, it.u)), o.due ? ', vrátí do ' + fmtDate(o.due) : '', o.note ? ' · ' + o.note : '',
      warn ? h('span', { class: 'due late' }, ' · ' + warn) : null];
  }
  /** Objednávka jako karta: kdy, kdo, do kdy, a seznam věcí s místem, kde je najít. */
  function orderEl(o) {
    var left = daysTo(o.from), wait = o.items.filter(function (l) { return l.w; }).length;
    var lines = o.items.map(function (l) {
      var it = itemBy.get(l.i), sec = secBy.get(it.s), f = sec.f, warn = orderWarn(o, l, it);
      return h('li', { class: 'oline' + (l.w ? ' w' : '') },
        h('span', { class: 'nm' }, h('b', null, hl(it.n)), h('small', { class: 'kw' }, sec.name)),
        h('span', { class: 'qt' }, l.q === null ? '?' : partQ(l.q, it.q, it.u)),
        h('span', { class: 'lc' }, locTag(it, f)),
        warn ? h('span', { class: 'ow due late' }, icon('warn'), warn) : null);
    });
    var acts = [h('button', { type: 'button', class: 'btn sm', onclick: function () { printOrders([o]); } }, icon('print'), 'Vytisknout')];
    if (ADMIN) {
      acts.push(h('button', { type: 'button', class: 'btn sm pri', onclick: function () { orderForm(o); } }, icon('edit'), 'Upravit'),
        h('button', { type: 'button', class: 'btn sm', onclick: function () { issueOrder(o); } }, icon('out'), 'Vydat hned'),
        h('button', { type: 'button', class: 'btn sm dan', onclick: function () { cancelOrder(o); } }, icon('trash'), 'Zrušit'));
    }
    return h('article', { class: 'ord' + (left !== null && left <= 0 ? ' now' : ''), id: 'o-' + o.id },
      h('div', { class: 'ord-h' },
        h('div', { class: 'ord-d' }, h('span', { class: 'ord-dow' }, DOW[isoDate(o.from).getDay()]), h('b', null, dayNum(o.from)),
          o.at ? h('span', { class: 'ord-at' }, o.at) : null, h('small', null, pickWords(left))),
        h('div', { class: 'ord-w' }, h('span', { class: 'ord-who' }, icon('user'), h('b', null, hl(o.who))),
          h('span', { class: 'ord-m' }, h('span', null, o.due ? 'vrátí do ' + dayShort(o.due) : 'bez termínu vrácení'), ' ', h('span', null, '· ' + things(o.items.length)), o.no ? [' ', h('span', null, '· č. ' + o.no)] : null),
          o.note ? h('span', { class: 'ord-note' }, hl(o.note)) : null,
          wait ? h('span', { class: 'ord-note due late' }, wait === o.items.length ? 'Čeká, až se věci uvolní (někdo ještě nevrátil, co si vzal).' : waitText(wait)) : null)),
      h('ul', { class: 'olines' }, lines),
      h('div', { class: 'acts' }, acts));
  }
  /** Čas, který objednávka drží: od vyzvednutí (nejdřív dnes) do vrácení (null = bez konce). */
  function orderSpan(o) { var t = today(), a = o.from > t ? o.from : t; return [a, o.due ? (o.due >= a ? o.due : a) : null]; }
  /** Co objednávce věci brání: v tu dobu na ni nezbývá (s tím, kdo ji má), věc nemá počet, chybí. Prázdné = nic. */
  function orderWarn(o, l, it) {
    if (l.q === null) return 'není zapsané, kolik si bere: uprav objednávku';
    var sp = orderSpan(o), why = fitText(it, l.q, sp[0], sp[1], o.id);
    return why ? (o.from <= today() ? 'čeká: ' : '') + why : '';
  }
  function issueOrder(o) {
    confirmBox('Vydat objednávku hned?', o.who + ' si věci bere už teď, ne až ' + dayWhen(o.from) + '. Zapíšou se jako výpůjčky s dnešním dnem.', 'Vydat hned', function () {
      change('order.issue', { id: o.id }).then(function (r) {
        toast((r.issued ? 'Vydáno: ' + things(r.issued) + '.' : 'Zatím nic nevydáno.') + (r.waiting ? ' ' + waitText(r.waiting) : ''), false, r.waiting ? 7000 : 0);
      }, fail);
    });
  }
  function cancelOrder(o) {
    confirmBox('Zrušit objednávku?', 'Objednávka' + (o.no ? ' č. ' + o.no : '') + ' (' + o.who + ', ' + dayWhen(o.from) + ', ' + things(o.items.length) + ') zmizí. Věci zůstanou, kde jsou.', 'Zrušit objednávku', function () {
      change('order.del', { id: o.id }, 'Objednávka zrušena.').catch(fail);
    }, true);
  }

  /* ───────── přání ───────── */

  function renderWishes() {
    var L = D.wish.labels, vis = wishes.filter(wishOk);
    var cnt = document.getElementById('cnt');
    if (cnt) cnt.textContent = vis.length + ' ' + plural(vis.length, 'přání', 'přání', 'přání');
    var head = h('div', { class: 'grp-h' }, h('h2', { class: 'grp-t' }, headText(D.wish.name)),
      D.wish.upd ? h('span', { class: 'grp-u' }, 'aktualizováno: ' + fmtDate(D.wish.upd)) : null,
      D.wish.note ? h('p', { class: 'grp-note' }, linkify(D.wish.note)) : null);
    el.list.className = 'list';
    if (!vis.length) {
      fill(el.list, head, h('div', { class: 'empty' }, h('b', null, wishes.length ? 'Nic takového tu není' : 'Zatím tu žádné přání není'),
        wishes.length ? 'Zkus jiné slovo nebo jiný výběr.' : (ADMIN ? 'První přidáš tlačítkem Přidat.' : '')));
      return;
    }
    var rows = h('div', { class: 'rows' });
    vis.forEach(function (w) { rows.appendChild(wishEl(w, L)); });
    var cols = h('div', { class: 'wcols', 'aria-hidden': 'true' }, ['n', 'q', 'who', 'where', 'why', 'by', 'ok1', 'ok2', 'ok3'].map(function (k) { return h('span', null, L[k]); }));
    fill(el.list, h('section', { class: 'grp' }, head, cols, rows));
  }
  function ck(label, on) { return h('span', { class: 'ck' + (on ? ' y' : ''), title: label + (on ? ': ano' : ': ne') }, h('span', { class: 'bx' }, icon('check')), h('span', { class: 'lb' }, label), h('span', { class: 'vh' }, on ? ': ano' : ': ne')); }
  function wishEl(w, L) {
    var open = S.open.has(w.id);
    var long = w.q.length > 12;
    var pair = function (cls, label, value) { return h('span', { class: cls }, value ? [h('i', null, label), hl(value)] : null); };
    var head = h('button', { type: 'button', class: 'rh', 'aria-expanded': String(open), onclick: function () { toggleWish(w.id); } },
      h('span', { class: 'nm' }, h('b', null, hl(w.n))),
      h('span', { class: 'qt' + (long ? ' long' : '') }, hl(w.q)),
      h('span', { class: 'w-meta' }, pair('w-who', L.who, w.who), pair('w-where', L.where, w.where), pair('w-by', L.by, w.by), long ? pair('w-q', L.q, w.q) : null),
      h('span', { class: 'w-why' }, hl(w.why), w.note ? h('span', { class: 'nt' }, hl(w.note)) : null),
      h('span', { class: 'w-st' }, ck(L.ok1, w.ok1), ck(L.ok2, w.ok2), ck(L.ok3, w.ok3)));
    var det = null;
    if (open) {
      var dl = h('dl', { class: 'kv' });
      var add = function (label, value) { if (!value) return; dl.appendChild(h('dt', null, label)); dl.appendChild(h('dd', null, value)); };
      add(L.who, w.who); add(L.q, w.q); add(L.where, w.where); add(L.why, w.why); add(L.alt, w.alt); add(L.by, w.by); add(L.note, linkify(w.note));
      var acts = h('div', { class: 'w-st' }, ck(L.ok1, w.ok1), ck(L.ok2, w.ok2), ck(L.ok3, w.ok3));
      if (ADMIN) {
        var tg = function (k) {
          return h('button', { type: 'button', class: 'ck' + (w[k] ? ' y' : ''), 'aria-pressed': String(w[k]), onclick: function () {
            serial(function () {
              var x = wishes.find(function (y) { return y.id === w.id; });
              if (!x) return null;
              var c = wishRaw(x);
              c[k] = !x[k];
              return change('wish.save', { wish: c, t: x.t }, x[k] ? 'Odškrtnuto.' : 'Zaškrtnuto.');
            }).catch(fail);
          } }, h('span', { class: 'bx' }, icon('check')), L[k]);
        };
        acts = [h('div', { class: 'w-st' }, tg('ok1'), tg('ok2'), tg('ok3')),
          h('div', { class: 'wrow-acts' },
            h('button', { type: 'button', class: 'btn sm pri', onclick: function () { wishForm(w); } }, icon('edit'), 'Upravit'),
            h('button', { type: 'button', class: 'btn sm', onclick: function () { itemForm({ n: w.n, qt: w.q, p1: '', note: '' }, true); } }, icon('box'), 'Zařadit do skladu'),
            h('button', { type: 'button', class: 'btn sm dan', onclick: function () { removeWish(w); } }, icon('trash'), 'Smazat'))];
      }
      det = h('div', { class: 'rd' }, dl.childNodes.length ? dl : null, acts);
    }
    return h('div', { class: 'row wish' + (open ? ' open' : '') + (w.ok2 ? ' bought' : ''), id: 'r-' + w.id }, head, det);
  }
  function toggleWish(id) {
    if (S.open.has(id)) S.open.delete(id); else S.open.add(id);
    var old = document.getElementById('r-' + id), w = wishes.find(function (x) { return x.id === id; });
    if (!old || !w) return;
    var neu = wishEl(w, D.wish.labels);
    old.replaceWith(neu);
    neu.querySelector('.rh').focus({ preventScroll: true });
  }
  function wishRaw(w) { var o = {}; ['id', 'who', 'n', 'q', 'where', 'why', 'alt', 'by', 'ok1', 'ok2', 'ok3', 'note'].forEach(function (k) { o[k] = w[k]; }); return o; }

  /* ───────── okna ───────── */

  /** Otevře okno. o = { title, body, actions(close) → uzly do patičky, onsubmit(close), narrow } */
  function dialog(o) {
    var d = h('dialog', { class: 'dlg' + (o.narrow ? ' narrow' : ''), 'aria-label': o.title });
    var close = function () { d.close(); };
    var form = h('form', { method: 'dialog', novalidate: o.novalidate || null, onsubmit: function (e) { e.preventDefault(); if (o.onsubmit) o.onsubmit(close, form); } },
      h('div', { class: 'dlg-h' }, h('h2', null, o.title), h('button', { type: 'button', class: 'btn sm ico', 'aria-label': 'Zavřít', onclick: close }, icon('x'))),
      h('div', { class: 'dlg-b' }, o.body),
      h('div', { class: 'dlg-f' }, o.actions(close)));
    d.appendChild(form);
    d.addEventListener('close', function () {
      var t = d.querySelector('.toast');
      if (t) document.body.appendChild(t);      // hláška o uložení má okno přežít
      d.remove();
      if (o.onclose) o.onclose();
    });
    document.body.appendChild(d);
    d.showModal();
    return d;
  }
  function confirmBox(title, text, yes, fn, danger) {
    dialog({ title: title, narrow: true, body: h('p', null, text),
      actions: function (close) { return [h('button', { type: 'button', class: 'btn', onclick: close }, 'Nechat být'), h('button', { type: 'submit', class: 'btn ' + (danger ? 'dan' : 'pri') }, yes)]; },
      onsubmit: function (close) { close(); fn(); } });
  }
  function busy(form, on) { Array.prototype.forEach.call(form.querySelectorAll('button[type=submit]'), function (b) { b.disabled = on; }); }
  function fld(label, input, hint, opt) { return h('label', { class: 'fld' }, h('span', null, label, opt ? h('i', null, ' ' + opt) : null), input, hint ? h('small', null, hint) : null); }
  function dlist(id, values) { return h('datalist', { id: id }, values.map(function (v) { return h('option', { value: v }); })); }
  function uniq(arr) { return Array.from(new Set(arr.filter(Boolean))).sort(cmp); }
  function names() {
    return uniq(items.map(function (i) { return i.own; }).concat(loans.map(function (x) { return x.l.who; }), orders.map(function (o) { return o.who; }),
      wishes.map(function (w) { return w.who; }), wishes.map(function (w) { return w.by; }),
      HOOK ? HOOK.people.map(function (p) { return p.n; }) : [], [store('kdo')]));
  }

  /* ── věc: přidat, upravit ── */
  function itemForm(src, asNew) {
    var isNew = !src || asNew;
    var cur = { id: '', s: (src && src.s) || (secBy.has(S.tab) ? S.tab : D.sections[0].id), n: '', q: null, u: 'ks', qt: '', ok: true, p1: '', p2: '', mk: [], own: '', cat: [], tg: [], kw: '', ph: [], note: '', rc: null, rn: '', pc: null, ty: '', t: 0 };
    if (src) Object.keys(cur).forEach(function (k) { if (src[k] !== undefined && src[k] !== null) cur[k] = Array.isArray(src[k]) ? src[k].slice() : src[k]; });
    if (cur.q === null && cur.qt) { var g0 = parseQty(cur.qt, 'ks'); if (g0) { cur.q = g0.n; cur.u = g0.u; cur.qt = ''; } }      // „2“ z přání je počet
    var qText = inQ(cur.q, cur.u);     // rozepsaný počet (přežije přepnutí listu)
    var prices = { rc: inPrice(cur.rc), rn: cur.rn || '', pc: inPrice(cur.pc) };
    var lentKnown = src && !isNew ? src.lentKnown : 0;
    if (isNew) { cur.id = ''; cur.t = 0; if (asNew && src && src.id) { cur.ok = true; cur.ph = cur.ph.filter(function (p) { return p.u; }); } }
    var body = h('div');
    var inputs = {};
    var secSel = h('select', { onchange: function () { collect(); cur.s = secSel.value; draw(); } }, D.sections.map(function (s) { return h('option', { value: s.id, selected: s.id === cur.s }, s.name); }));

    function collect() {
      ['n', 'p1', 'p2', 'own', 'kw', 'note'].forEach(function (k) { if (inputs[k]) cur[k] = inputs[k].value; });
      if (inputs.q) { qText = inputs.q.value; cur.u = inputs.u.value; }
      if (inputs.rc) { prices.rc = inputs.rc.value; prices.rn = inputs.rn.value; prices.pc = inputs.pc.value; }
      if (inputs.ok) cur.ok = inputs.ok.checked;
      if (inputs.ty) cur.ty = inputs.ty.value;
      if (inputs.tg && inputs.tg.value.trim()) addTags(inputs.tg.value);       // rozepsaný tag, který nikdo nepotvrdil Enterem
    }
    /** Přidá tagy z textu („tábor, elektro“ nebo „#tábor“); stejný tag podruhé ne, zápis jako u ostatních věcí. */
    function addTags(text) {
      String(text).split(',').forEach(function (t) {
        t = t.replace(/^\s*#+/, '').replace(/\s+/g, ' ').trim().slice(0, 40);
        var k = fold(t);
        if (!t || cur.tg.some(function (x) { return fold(x) === k; })) return;
        if (cur.tg.length >= 20) { toast('Víc než 20 tagů se k jedné věci nevejde.', true); return; }
        cur.tg.push(tagLabels.get(k) || t);
      });
      if (inputs.tg) inputs.tg.value = '';
    }
    /** Tagy: ty, co věc má (klepnutím pryč), políčko na nový (Enter nebo čárka) a nabídka častých tagů. */
    function tagsBox() {
      var box = h('div', { class: 'tgx' });
      var freq = new Map();
      items.forEach(function (it) { it._tg.forEach(function (k) { freq.set(k, (freq.get(k) || 0) + 1); }); });
      var paint = function (focus) {
        var mine = cur.tg.map(fold);
        var sug = Array.from(freq.keys()).filter(function (k) { return mine.indexOf(k) < 0; })
          .sort(function (a, b) { return freq.get(b) - freq.get(a) || cmp(tagLabels.get(a), tagLabels.get(b)); }).slice(0, 12);
        inputs.tg = h('input', { type: 'text', maxlength: 60, list: 'dl-tg', autocomplete: 'off', placeholder: 'nový tag, třeba tábor', 'aria-label': 'Nový tag',
          onkeydown: function (e) { if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addTags(inputs.tg.value); paint(true); } } });
        fill(box,
          cur.tg.length ? h('div', { class: 'chips' }, cur.tg.map(function (t, i) {
            return h('button', { type: 'button', class: 'chip tgc', 'aria-pressed': 'true', 'aria-label': 'Odebrat tag ' + t, onclick: function () { cur.tg.splice(i, 1); paint(); } }, '#' + t, icon('x'));
          })) : null,
          h('span', { class: 'add' }, inputs.tg, h('button', { type: 'button', class: 'btn sm', onclick: function () { addTags(inputs.tg.value); paint(true); } }, 'Přidat')),
          sug.length ? h('div', { class: 'chips sug' }, h('span', { class: 'sm-hint' }, 'Časté:'), sug.map(function (k) {
            return h('button', { type: 'button', class: 'chip tgc', onclick: function () { addTags(tagLabels.get(k)); paint(); } }, '#' + tagLabels.get(k));
          })) : null,
          dlist('dl-tg', Array.from(freq.keys()).map(function (k) { return tagLabels.get(k); }).sort(cmp)));
        if (focus) inputs.tg.focus();
      };
      paint();
      return box;
    }
    function picker(key, options, colored) {
      var box = h('div', { class: 'pick' });
      var paint = function () {
        var all = uniq(options.concat(cur[key]));
        if (colored) { var ord = D.marks.map(function (m) { return m.k; }); all.sort(function (a, b) { var ia = ord.indexOf(a), ib = ord.indexOf(b); return (ia < 0 ? 99 : ia) - (ib < 0 ? 99 : ib) || cmp(a, b); }); }
        var inp = h('input', { type: 'text', placeholder: colored ? 'jiné…' : 'nová…', 'aria-label': colored ? 'Jiné označení' : 'Nová kategorie', maxlength: colored ? 16 : 60,
          onkeydown: function (e) { if (e.key === 'Enter') { e.preventDefault(); addNew(); } } });
        var addNew = function () { var v = inp.value.trim(); if (v && cur[key].indexOf(v) < 0) cur[key].push(v); paint(); var i2 = box.querySelector('input'); if (i2 && v) i2.focus(); };
        fill(box, all.map(function (v) {
          var on = cur[key].indexOf(v) >= 0, m = colored ? markBy.get(v) : null;
          return h('button', { type: 'button', class: 'chip' + (colored ? ' c-' + (m ? m.c : 'seda') : ''), 'aria-pressed': String(on), title: m && m.l ? m.l : null,
            onclick: function () { var i = cur[key].indexOf(v); if (i >= 0) cur[key].splice(i, 1); else cur[key].push(v); paint(); } }, colored ? h('span', { class: 'dot' }) : null, v + (m && m.l ? ' · ' + m.l : ''));
        }), h('span', { class: 'add' }, inp, h('button', { type: 'button', class: 'btn sm', onclick: addNew }, 'Přidat')));
      };
      paint();
      return box;
    }
    function photos() {
      var wrap = h('div'), urlIn, fileIn;
      var paint = function () {
        var list = h('ul', { class: 'phs' }, cur.ph.map(function (p, i) {
          return h('li', null, p.f ? h('img', { src: photoSrc(p, true), alt: '' }) : icon('ext'),
            p.f ? h('span', { class: 'u' }, 'nahraná fotka') : h('span', { class: 'u' }, extLink(p.u, shortUrl(p.u))),
            h('button', { type: 'button', class: 'btn sm ico', 'aria-label': 'Odebrat fotku', onclick: function () { cur.ph.splice(i, 1); paint(); } }, icon('x')));
        }));
        urlIn = h('input', { type: 'url', placeholder: 'https://… odkaz na fotku', 'aria-label': 'Odkaz na fotku', onkeydown: function (e) { if (e.key === 'Enter') { e.preventDefault(); addUrl(); } } });
        fileIn = h('input', { type: 'file', accept: 'image/*', hidden: true, onchange: function () { if (fileIn.files && fileIn.files[0]) upload(fileIn.files[0]); } });
        var upBtn = h('button', { type: 'button', class: 'btn sm', onclick: function () { fileIn.click(); } }, icon('photo'), 'Nahrát fotku');
        var addUrl = function () {
          var v = urlIn.value.trim();
          if (!v) return;
          if (!/^https?:\/\/\S+$/i.test(v)) { toast('Odkaz musí začínat https://', true); return; }
          if (cur.ph.length >= 12) { toast('Víc než 12 fotek se k jedné věci nevejde.', true); return; }
          cur.ph.push({ u: v }); paint();
        };
        var upload = function (file) {
          if (cur.ph.length >= 12) { toast('Víc než 12 fotek se k jedné věci nevejde.', true); return; }
          upBtn.disabled = true; upBtn.lastChild.textContent = 'Nahrávám…';
          uploadPhoto(file).then(function (photo) { cur.ph.push(photo); paint(); }, function (e) { fail(e); paint(); });
        };
        fill(wrap, cur.ph.length ? list : null, h('div', { class: 'inl' }, urlIn, h('button', { type: 'button', class: 'btn sm', onclick: addUrl }, 'Přidat odkaz')),
          h('div', { class: 'acts' }, upBtn, fileIn));
      };
      paint();
      return wrap;
    }
    function draw() {
      var sec = secBy.get(cur.s), f = sec.f, kids = [];
      inputs = {};
      var text = function (k, label, max, listId, ph) { inputs[k] = h('input', { type: 'text', value: cur[k], maxlength: max, list: listId || null, placeholder: ph || null, autocomplete: 'off' }); return inputs[k]; };
      var inSec = items.filter(function (i) { return i.s === cur.s; });
      if (D.sections.length > 1) kids.push(fld('List', secSel));
      inputs.n = h('input', { type: 'text', value: cur.n, maxlength: 200, required: true, autocomplete: 'off' });
      kids.push(fld(sec.nl, inputs.n));
      if ('p1' in f && 'p2' in f && f.p1 === f.p2) {
        kids.push(h('fieldset', { class: 'fld' }, h('legend', null, f.p1), h('div', { class: 'two' },
          h('label', null, h('span', { class: 'vh' }, 'místo'), text('p1', '', 300, 'dl-p1', 'místo: police, roh, stěna…')),
          h('label', null, h('span', { class: 'vh' }, 'v čem'), text('p2', '', 300, 'dl-p2', 'v čem: krabice, obal, pytel…')))));
      } else if ('p1' in f || 'p2' in f) {
        kids.push(h('div', { class: 'two' }, 'p1' in f ? fld(f.p1, text('p1', '', 300, 'dl-p1')) : null, 'p2' in f ? fld(f.p2, text('p2', '', 300, 'dl-p2')) : null));
      }
      if ('p1' in f) kids.push(dlist('dl-p1', uniq(inSec.map(function (i) { return i.p1; }))));
      if ('p2' in f) kids.push(dlist('dl-p2', uniq(inSec.map(function (i) { return i.p2; }))));
      // počet: povinný, celé číslo v kusech, nebo u potravin v gramech
      var qHint = h('small', { id: 'q-hint' });
      inputs.q = h('input', { type: 'text', inputmode: 'decimal', value: qText, maxlength: 24, autocomplete: 'off', required: true, 'aria-describedby': 'q-hint', placeholder: 'kolik toho máte',
        oninput: function () { paintQ(); } });
      inputs.u = unitSel(cur.u, 'Jednotka', function () { paintQ(); });
      var paintQ = function () {
        var p = parseQty(inputs.q.value, inputs.u.value);
        if (p && p.u !== inputs.u.value) inputs.u.value = p.u;        // „2,5 kg“ přepne na gramy, „0,5 l“ na litry
        var u = inputs.u.value, bits = [];
        if (p && u !== 'ks' && fmtQ(p.n, u) !== inputs.q.value.trim()) bits.push('= ' + fmtQ(p.n, u) + '.');
        if (lentKnown) bits.push('Půjčeno je ' + fmtQ(lentKnown, u) + ', méně to být nemůže.');
        bits.push(u === 'g' ? 'V gramech: 500 g, 2,5 kg (= 2500 g).' : (u === 'ml' ? 'V litrech: 1,5 l, 0,33 l (= 330 ml).' : 'Všechno, co máte, i to, co je zrovna půjčené.'));
        qHint.textContent = bits.join(' ');
        if (inputs.q.value.trim() && !p) inputs.q.setAttribute('aria-invalid', 'true'); else inputs.q.removeAttribute('aria-invalid');
      };
      kids.push(h('div', { class: 'fld' }, h('span', null, 'q' in f ? f.q : 'počet'),
        cur.qt ? h('p', { class: 'msg warn' }, 'Zatím je tu zapsané „' + cur.qt + '“. Přepiš to na číslo, jinak věc nepůjde půjčit.') : null,
        h('div', { class: 'qrow' }, inputs.q, inputs.u), qHint));
      paintQ();
      // ceny: výpůjční vidí všichni, pořizovací jen správa
      inputs.rc = h('input', { type: 'text', inputmode: 'decimal', value: prices.rc, maxlength: 14, autocomplete: 'off', placeholder: 'Kč' });
      inputs.rn = h('input', { type: 'text', value: prices.rn, maxlength: 40, autocomplete: 'off', list: 'dl-rn', placeholder: 'za den, za výpůjčku…' });
      inputs.pc = h('input', { type: 'text', inputmode: 'decimal', value: prices.pc, maxlength: 14, autocomplete: 'off', placeholder: 'Kč' });
      kids.push(h('fieldset', { class: 'fld prices' }, h('legend', null, 'Ceny ', h('i', null, '(nepovinné)')),
        h('div', { class: 'two' }, h('label', { class: 'fld' }, h('span', null, 'Výpůjční cena'), h('div', { class: 'qrow' }, inputs.rc, h('span', { class: 'un' }, 'Kč')), h('small', null, 'Vidí ji všichni.')),
          h('label', { class: 'fld' }, h('span', null, 'Za co'), inputs.rn)),
        h('label', { class: 'fld' }, h('span', null, 'Pořizovací cena'), h('div', { class: 'qrow' }, inputs.pc, h('span', { class: 'un' }, 'Kč')), h('small', null, 'Vidí jen správa.')),
        dlist('dl-rn', uniq(['za den', 'za výpůjčku', 'za víkend', 'za kus a den'].concat(items.map(function (i) { return i.rn; }))))));
      // typ položky: inventarizační údaj, nenápadně pod cenami; typy přidává hlavní správce v Nastavení
      if (D.types && D.types.length) {
        inputs.ty = h('select', { 'aria-label': 'Typ položky' }, h('option', { value: '' }, 'neurčeno'),
          D.types.map(function (t) { return h('option', { value: t.id, selected: t.id === cur.ty }, t.n); }));
        kids.push(fld('Typ položky', inputs.ty, 'Inventarizační údaj. Vidí ho jen správa.', '(nepovinné)'));
      }
      if ('own' in f) { kids.push(fld(f.own, text('own', '', 120, 'dl-own'))); kids.push(dlist('dl-own', names())); }
      if ('ok' in f || cur.ok === false) {
        inputs.ok = h('input', { type: 'checkbox', checked: cur.ok !== false });
        kids.push(h('label', { class: 'chk' }, inputs.ok, h('span', null, 'ok' in f ? f.ok : 'Ve skladu?')));
        kids.push(h('small', { class: 'sm-hint ckh' }, 'Odškrtni, když věc chybí (ztratila se a nikdo ji zapsanou nemá). Půjčit ji pak nejde.'));
      }
      if ('mk' in f) kids.push(h('div', { class: 'fld' }, h('span', null, f.mk), picker('mk', D.marks.map(function (m) { return m.k; }), true)));
      if ('cat' in f) kids.push(h('div', { class: 'fld' }, h('span', null, f.cat), picker('cat', inSec.reduce(function (a, i) { return a.concat(i.cat); }, []), false)));
      kids.push(h('div', { class: 'fld' }, h('span', null, 'Tagy ', h('i', null, '(nepovinné)')), tagsBox(),
        h('small', null, 'Krátká slova, podle kterých věc najdeš a vyfiltruješ. Enter nebo čárka tag přidá.')));
      inputs.kw = h('textarea', { maxlength: 600, rows: 2, placeholder: 'třeba: modrý, s pouzdrem, pro 6 lidí' }, cur.kw);
      kids.push(fld('kw' in f ? f.kw : FIELD_DEF.kw, inputs.kw, 'Hledá se v něm.', '(nepovinný)'));
      if ('note' in f) { inputs.note = h('textarea', { maxlength: 3000, rows: 3 }, cur.note); kids.push(fld(f.note, inputs.note)); }
      kids.push(h('div', { class: 'fld' }, h('span', null, 'ph' in f ? f.ph : FIELD_DEF.ph, ' ', h('i', null, '(nepovinné)')), photos()));
      fill(body, kids);
    }
    draw();
    dialog({
      title: isNew ? 'Nová věc' : 'Upravit věc', body: body, novalidate: true,
      actions: function (close) { return [h('button', { type: 'button', class: 'btn', onclick: close }, 'Zrušit'), h('button', { type: 'submit', class: 'btn pri' }, isNew ? 'Přidat do skladu' : 'Uložit')]; },
      onsubmit: function (close, form) {
        collect();
        if (!cur.n.trim()) { toast('Bez názvu to nepůjde.', true); inputs.n.focus(); return; }
        var pq = parseQty(qText, cur.u);
        if (!pq) { toast(qText.trim() ? 'Počet musí být číslo ' + unitWord(cur.u) + '.' : 'Napiš počet: kolik toho máte. Bez něj věc nejde půjčit.', true); inputs.q.focus(); return; }
        cur.q = pq.n;
        cur.u = pq.u;
        var rc = parsePrice(prices.rc), pc = parsePrice(prices.pc);
        if (rc !== rc) { toast('Výpůjční cena musí být částka v korunách, třeba 50 nebo 12,50.', true); inputs.rc.focus(); return; }
        if (pc !== pc) { toast('Pořizovací cena musí být částka v korunách, třeba 1200 nebo 349,90.', true); inputs.pc.focus(); return; }
        cur.rc = rc; cur.pc = pc; cur.rn = rc ? prices.rn.trim() : '';
        if (src && !isNew && lentKnown > cur.q && (cur.q !== src.q || cur.u !== src.u)) {
          toast('Půjčeno je ' + fmtQ(lentKnown, cur.u) + ', tak počet nemůže být menší. Až se to vrátí, zapiš vrácení u výpůjčky (co se nevrátí, z počtu ubude samo).', true);
          inputs.q.focus();
          return;
        }
        var sec = secBy.get(cur.s), send = { id: cur.id, s: cur.s, n: cur.n };
        ITEM_KEYS.forEach(function (k) { if (k in sec.f || k === 'q' || k === 'u' || k === 'tg' || k === 'kw' || k === 'ph' || (src && !isNew)) send[k] = cur[k]; });
        send.rc = cur.rc; send.rn = cur.rn; send.pc = cur.pc; send.ty = cur.ty;
        if (!('ok' in sec.f) && isNew) delete send.ok;
        busy(form, true);
        var payload = { item: send };
        if (!isNew) payload.t = cur.t;
        change('item.save', payload, isNew ? 'Přidáno.' : 'Uloženo.').then(function (r) {
          close();
          if (isNew) { if (S.tab !== 'all' && S.tab !== cur.s) S.tab = cur.s; S.open.add(r.id); refresh(); var rowN = document.getElementById('r-' + r.id); if (rowN) rowN.scrollIntoView({ block: 'center' }); }
        }, function (e) { busy(form, false); fail(e); });
      }
    });
    if (isNew && inputs.n) inputs.n.focus();
    else if (cur.q === null && inputs.q) inputs.q.focus();
  }

  /** Zmenší fotku v prohlížeči (delší strana nejvýš max bodů) a vrátí ji jako JPEG. */
  function shrink(file, max, quality) {
    var draw = function (img, w, h0) {
      var s = Math.min(1, max / Math.max(w, h0)), c = document.createElement('canvas');
      c.width = Math.max(1, Math.round(w * s)); c.height = Math.max(1, Math.round(h0 * s));
      var ctx = c.getContext('2d');
      ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
      ctx.drawImage(img, 0, 0, c.width, c.height);
      return new Promise(function (res, rej) { c.toBlob(function (b) { if (b) res(b); else rej(new Error('Fotku se nepodařilo zmenšit.')); }, 'image/jpeg', quality); });
    };
    return new Promise(function (res, rej) {
      var url = URL.createObjectURL(file), img = new Image();
      img.onload = function () { draw(img, img.naturalWidth, img.naturalHeight).then(res, rej).then(function () { URL.revokeObjectURL(url); }); };
      img.onerror = function () { URL.revokeObjectURL(url); rej(new Error('Tenhle soubor se jako fotka načíst nedá.')); };
      img.src = url;
    });
  }

  /* ── výpůjčka: upravit, připomenout, vrátit ── */
  /** Chips s termíny vrácení pod políčkem s datem. */
  function dueQuick(due, base, onpick) {
    var box = h('div', { class: 'chips quick' });
    var paint = function () {
      var plus = function (n) { var d = isoDate(base()), p = function (x) { return x < 10 ? '0' + x : x; }; d.setDate(d.getDate() + n); return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()); };
      fill(box, [[1, 'na den'], [7, 'na týden'], [14, 'na 14 dní'], [30, 'na měsíc'], [null, 'bez termínu']].map(function (q) {
        var v = q[0] === null ? '' : plus(q[0]);
        return h('button', { type: 'button', class: 'chip', 'aria-pressed': String(due.value === v), onclick: function () { due.value = v; paint(); if (onpick) onpick(); } }, q[1]);
      }));
    };
    paint();
    return { el: box, paint: paint };
  }
  function discordHint(p, name) {
    p.hidden = !(HOOK && HOOK.set && (HOOK.on.loan || HOOK.on.remind) && name);
    if (p.hidden) return;
    var x = person(name);
    p.textContent = x ? 'Discord: zprávy označí ' + (x.u ? '@' + x.u : 'člověka, který má u tohohle jména uložené ID') + '.'
      : (botReady() ? 'Discord: tohle jméno zkusím při odeslání najít na serveru. Když tam je, zprávy ho označí.'
        : 'Discord: tohle jméno v Nastavení mezi lidmi není, zprávy přijdou do kanálu bez označení.');
  }
  /** Úprava zapsané výpůjčky: kdo to má, kolik si vzal, do kdy to má vrátit. Nová výpůjčka jde přes orderForm. */
  function loanForm(l, it) {
    var who = h('input', { type: 'text', value: l.who, maxlength: 80, required: true, list: 'dl-loan', autocomplete: 'off', oninput: function () { discordHint(note, who.value.trim()); } });
    var qty = h('input', { type: 'text', inputmode: it.u === 'ks' ? 'numeric' : 'decimal', value: inQ(l.q, it.u), maxlength: 24, autocomplete: 'off', required: true, 'aria-describedby': 'lq-hint', oninput: function () { paintQ(); } });
    var qHint = h('small', { id: 'lq-hint' });
    var due = h('input', { type: 'date', value: l.due, 'aria-label': 'Vrátit do', oninput: function () { quick.paint(); paintQ(); } });
    var quick = dueQuick(due, today, function () { paintQ(); }), note = h('p', { class: 'sm-hint dc' });
    function paintQ() {
      var n = amountIn(qty.value, it.u), t = today();
      var room = freeIn(it, t, due.value && due.value >= t ? due.value : null, '', l.id);
      qHint.textContent = (n === null && qty.value.trim() ? 'Napiš celé číslo ' + unitWord(it.u) + '. ' : '') + 'Když nic dalšího nevrátí, může mít nejvýš ' + fmtQ(room, it.u) + (it.q !== null ? ' z ' + fmtQ(it.q, it.u) : '') + '.';
    }
    paintQ();
    discordHint(note, l.who);
    dialog({
      title: 'Výpůjčka: ' + it.n, narrow: true, novalidate: true,
      body: [fld('Kdo to má', who), dlist('dl-loan', names()),
        h('div', { class: 'fld' }, h('span', null, 'Kolik si vzal'), h('div', { class: 'qrow' }, qty, unitTag(qty, it.u)), qHint),
        h('div', { class: 'fld' }, h('span', null, 'Vrátit do', h('i', null, ' (nepovinné)')), quick.el, due), note,
        h('p', { class: 'sm-hint' }, 'Když něco vrátil, zapiš to tlačítkem Vrátit, ne tady: tady se opravuje, co se zapsalo.')],
      actions: function (close) { return [h('button', { type: 'button', class: 'btn', onclick: close }, 'Zrušit'), h('button', { type: 'submit', class: 'btn pri' }, 'Uložit')]; },
      onsubmit: function (close, form) {
        var name = who.value.trim(), n = amountIn(qty.value, it.u);
        if (!name) { toast('Napiš, kdo to má.', true); who.focus(); return; }
        if (n === null) { toast('Napiš, kolik si vzal: celé číslo ' + unitWord(it.u) + '.', true); qty.focus(); return; }
        busy(form, true);
        change('loan.save', { loan: { id: l.id, who: name, q: n, due: due.value } }, 'Výpůjčka upravena.').then(close, function (e) { busy(form, false); fail(e); });
      }
    });
    who.focus();
    who.select();
  }
  /** Připomene výpůjčku na Discordu hned teď. */
  function remindNow(l) {
    api('loan.remind', { id: l.id }).then(function (r) {
      if (r.hook) HOOK = r.hook;
      var note = r.discordNote && r.discordNote.length ? ' ' + r.discordNote.join(' ') : '';
      if (r.pinged) toast('Připomínka odešla i s označením.' + note, false, note ? 7000 : 0);
      else toast('Připomínka odešla do kanálu, ale bez označení.' + (note || ' „' + l.who + '“ v Nastavení mezi lidmi není.'), !!r.discordBad, 7000);
    }, fail);
  }
  /** Vrátit od výpůjčky: v okně jsou i další věci, které má týž člověk (nezaškrtnuté), ať jde vrátit víc najednou. */
  function returnFor(l) {
    var key = nameKey(l.who), mine = loans.filter(function (x) { return nameKey(x.l.who) === key && x.l.id !== l.id; });
    mine.sort(function (a, b) { return a.it._i - b.it._i; });
    returnForm([loanBy.get(l.id)].concat(mine), [l.id]);
  }
  /**
   * Vrácení výpůjček, jedné nebo víc najednou. U každé zaškrtnuté se píše, kolik se vrátilo (předvyplněné je všechno);
   * co chybí, se buď spotřebovalo nebo ztratilo (o tolik ubude z počtu věci), nebo si to člověk ještě nechává.
   * list = výpůjčky [{ l, it }] v okně, checked = id těch, které jsou zaškrtnuté hned.
   */
  function returnForm(list, checked) {
    list = list.filter(Boolean);
    var people = uniq(list.map(function (x) { return x.l.who; })), one = people.length === 1;
    var submit = h('button', { type: 'submit', class: 'btn pri' }, 'Zapsat vrácení'), sum = h('p', { class: 'sm-hint rsum', 'aria-live': 'polite' });
    var rows = list.map(function (x) {
      var l = x.l, it = x.it, u = it.u, r = { x: x };
      r.ck = h('input', { type: 'checkbox', checked: checked.indexOf(l.id) >= 0, 'aria-label': 'Vrátit: ' + it.n + (one ? '' : ' (' + l.who + ')'), onchange: function () { paint(); } });
      if (l.q !== null) {
        r.inp = h('input', { type: 'text', inputmode: u === 'ks' ? 'numeric' : 'decimal', value: inQ(l.q, u), maxlength: 24, autocomplete: 'off', 'aria-label': 'Kolik vrátil: ' + it.n,
          oninput: function () { r.ck.checked = true; paint(); } });
        r.rest = h('select', { 'aria-label': 'Co se zbytkem: ' + it.n, onchange: function () { paint(); } },
          h('option', { value: 'lost' }, 'zbytek ubyl (spotřeba, ztráta)'), h('option', { value: 'keep' }, 'zbytek si ještě nechává'));
      }
      r.msg = h('small', { class: 'rmsg' });
      r.el = h('div', { class: 'rrow' },
        h('label', { class: 'chk rc' }, r.ck, h('span', null, h('b', null, it.n), h('small', null, (one ? '' : l.who + ' · ') + 'má ' + (l.q === null ? 'nezapsáno kolik' : fmtQ(l.q, u))
          + (l.since ? ' od ' + fmtDate(l.since) : '') + (l.due ? ', vrátit do ' + fmtDate(l.due) : '')))),
        l.q !== null ? h('span', { class: 'rin' }, h('span', { class: 'qrow' }, r.inp, unitTag(r.inp, u)), r.rest) : h('span', { class: 'rin' }),
        r.msg);
      return r;
    });
    /** Kolik se vrátilo (v jednotce věci), nebo null, když to číslo není. */
    function amount(r) { var p = parseQty(r.inp.value, r.x.it.u); return p && p.u === r.x.it.u ? p.n : null; }
    function paint() {
      var n = 0, bad = false, lost = [];
      rows.forEach(function (r) {
        var on = r.ck.checked, l = r.x.l, u = r.x.it.u;
        r.el.classList.toggle('off', !on);
        if (!r.inp) { r.msg.textContent = on ? 'Kolik si vzal, se nezapsalo (výpůjčka ze starší verze): vrátí se celá, počet ve skladu se nezmění.' : ''; if (on) n++; return; }
        var b = amount(r);
        r.rest.hidden = true;
        r.inp.removeAttribute('aria-invalid');
        if (!on) { r.msg.textContent = ''; return; }
        n++;
        if (b === null || b > l.q) { bad = true; r.inp.setAttribute('aria-invalid', 'true'); r.msg.textContent = b === null ? 'Napiš číslo ' + unitWord(u) + '.' : 'Víc, než si vzal (' + fmtQ(l.q, u) + '), vrátit nejde.'; return; }
        var miss = l.q - b;
        r.rest.hidden = !miss;
        if (!miss) { r.msg.textContent = ''; return; }
        if (r.rest.value === 'keep' && !b) { n--; r.msg.textContent = 'Nevrací nic: výpůjčka zůstane, jak je.'; return; }
        if (r.rest.value === 'keep') r.msg.textContent = 'Nevrátil ' + fmtQ(miss, u) + ', výpůjčka na ně zůstane.';
        else { r.msg.textContent = 'Nevrátil ' + fmtQ(miss, u) + ': z počtu ' + (r.x.it.q !== null ? fmtQ(r.x.it.q, u) + ' se odečtou (zbyde ' + fmtQ(Math.max(0, r.x.it.q - miss), u) + ').' : 'se odečtou.'); lost.push(r.x.it.n + ' ' + fmtQ(miss, u)); }
      });
      submit.disabled = !n || bad;
      submit.textContent = n > 1 ? 'Vrátit ' + loansN(n) : 'Zapsat vrácení';
      sum.textContent = !n ? 'Zaškrtni, co se vrací.' : (lost.length ? 'Z počtu ubude: ' + lost.join(', ') + '.' : '');
    }
    var all = function (on) { rows.forEach(function (r) { r.ck.checked = on; }); paint(); };
    paint();
    dialog({
      title: one ? 'Vrátit: ' + people[0] : 'Vrátit vybrané', novalidate: true,
      body: [h('p', { class: 'sm-hint' }, 'Zaškrtni, co se vrací, a u každé věci napiš, kolik toho vrátil. Co chybí, buď ubude z počtu (snědlo se, ztratilo), nebo to u něj zůstane jako výpůjčka.'),
        rows.length > 1 ? h('div', { class: 'chips quick' }, h('button', { type: 'button', class: 'chip', onclick: function () { all(true); } }, 'Vybrat všechno'),
          h('button', { type: 'button', class: 'chip', onclick: function () { all(false); } }, 'Nic')) : null,
        h('div', { class: 'rrows' }, rows.map(function (r) { return r.el; })), sum],
      actions: function (close) { return [h('button', { type: 'button', class: 'btn', onclick: close }, 'Zrušit'), submit]; },
      onsubmit: function (close, form) {
        var send = [], lostN = 0, keepN = 0;
        for (var i = 0; i < rows.length; i++) {
          var r = rows[i];
          if (!r.ck.checked) continue;
          if (!r.inp) { send.push({ id: r.x.l.id }); continue; }
          var b = amount(r);
          if (b === null || b > r.x.l.q) { r.inp.focus(); return; }
          var keep = b < r.x.l.q && r.rest.value === 'keep';
          if (keep && !b) continue;          // nevrací nic: výpůjčka zůstane, jak je
          if (b < r.x.l.q) { if (keep) keepN++; else lostN++; }
          send.push({ id: r.x.l.id, back: b, keep: keep });
        }
        if (!send.length) return;
        busy(form, true);
        change('loans.back', { items: send }, function (res) {
          return (res.back === 1 ? 'Vráceno.' : 'Vráceno: ' + loansN(res.back) + '.') + (lostN ? ' Co se nevrátilo, se odečetlo z počtu.' : '') + (keepN ? ' Co si kdo nechal, zůstalo jako výpůjčka.' : '');
        }).then(function () {
          send.forEach(function (x) { S.lsel.delete(x.id); });
          close();
          renderPickBar();
        }, function (e) { busy(form, false); fail(e); });
      }
    });
    var first = rows.find(function (r) { return r.ck.checked && r.inp; });
    if (first) { first.inp.focus(); first.inp.select(); }
  }
  /**
   * Doplnění počtů: seznam věcí bez počtu (v listu, kde správce je, jinak všech), u každé políčko a jednotka.
   * Enter skočí na další věc. Uloží se jen vyplněné; prázdné zůstanou na příště.
   */
  function countsForm() {
    var inSec = secBy.has(S.tab) ? S.tab : '';
    var list = items.filter(function (it) { return it.q === null && secBy.has(it.s) && (!inSec || it.s === inSec); });
    if (!list.length) { toast('Všechny věci tu počet mají.'); return; }
    var rows = [], filled = h('span', { class: 'cnt-f', 'aria-live': 'polite' });
    var submit = h('button', { type: 'submit', class: 'btn pri' }, 'Uložit');
    var paintCount = function () {
      var n = rows.filter(function (r) { return r.inp.value.trim(); }).length;
      filled.textContent = 'Vyplněno ' + n + ' z ' + list.length + '.';
      submit.textContent = n ? 'Uložit ' + n + ' ' + plural(n, 'počet', 'počty', 'počtů') : 'Uložit';
      submit.disabled = !n;
    };
    var groups = [];
    D.sections.forEach(function (sec) {
      var mine = list.filter(function (it) { return it.s === sec.id; });
      if (!mine.length) return;
      if (!inSec) groups.push(h('h3', { class: 'sub cs' }, h('span', null, sec.name), h('span', { class: 'n' }, things(mine.length))));
      groups.push(h('div', { class: 'crows' }, mine.map(function (it) {
        var r = { it: it };
        r.unit = unitSel(it.u, 'Jednotka: ' + it.n, function () { r.inp.dispatchEvent(new Event('input')); }, true);
        r.inp = h('input', { type: 'text', inputmode: 'decimal', maxlength: 24, autocomplete: 'off', 'aria-label': 'Počet: ' + it.n,
          oninput: function () {
            var p = parseQty(r.inp.value, r.unit.value);
            if (p && p.u !== r.unit.value) r.unit.value = p.u;
            if (r.inp.value.trim() && !p) r.inp.setAttribute('aria-invalid', 'true'); else r.inp.removeAttribute('aria-invalid');
            paintCount();
          },
          onkeydown: function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            var i = rows.indexOf(r);
            if (rows[i + 1]) { rows[i + 1].inp.focus(); rows[i + 1].inp.scrollIntoView({ block: 'nearest' }); } else submit.focus();
          } });
        rows.push(r);
        var set = function (n, u) { r.inp.value = String(n); r.unit.value = u; r.inp.removeAttribute('aria-invalid'); paintCount(); var i = rows.indexOf(r); if (rows[i + 1]) rows[i + 1].inp.focus(); };
        // nabídka z textu ze soupisu: jen když je v něm jediné číslo („cca 20x“ → 20 ks); rozhodne správce
        var m = /^\D*?(\d{1,6})\D*$/.exec(it.qt || ''), sug = m ? +m[1] : null;
        var sec2 = secBy.get(it.s), place = 'p1' in sec2.f ? val(it.p1) : '';
        return h('div', { class: 'crow' },
          h('span', { class: 'cn' }, h('b', null, it.n), h('small', null, [it.qt ? 'v soupisu: „' + it.qt + '“' : 'v soupisu bez počtu', place].filter(Boolean).join(' · '))),
          h('span', { class: 'cin' }, r.inp, r.unit),
          h('span', { class: 'chips' },
            sug !== null && sug > 0 ? h('button', { type: 'button', class: 'chip', title: 'Podle textu ze soupisu', onclick: function () { set(sug, 'ks'); } }, sug + ' ks') : null,
            sug !== 1 ? h('button', { type: 'button', class: 'chip', onclick: function () { set(1, 'ks'); } }, '1 ks') : null));
      })));
    });
    paintCount();
    dialog({
      title: 'Doplnit počty' + (inSec ? ': ' + secBy.get(inSec).name : ''), novalidate: true,
      body: [h('p', { class: 'sm-hint' }, 'Kolik toho máte, i s tím, co je zrovna půjčené. V kusech, gramech nebo litrech (stačí napsat třeba 2,5 kg nebo 0,5 l). '
        + 'Enter skočí na další věc. Uloží se jen vyplněné, zbytek počká na příště.'), groups],
      actions: function (close) { return [filled, h('button', { type: 'button', class: 'btn', onclick: close }, 'Zavřít'), submit]; },
      onsubmit: function (close, form) {
        var out = [], bad = null;
        rows.forEach(function (r) {
          if (!r.inp.value.trim() || bad) return;
          var p = parseQty(r.inp.value, r.unit.value);
          if (!p) { bad = r; return; }
          out.push({ id: r.it.id, q: p.n, u: p.u });
        });
        if (bad) { toast('„' + bad.it.n + '“: počet musí být číslo ' + unitWord(bad.unit.value) + '.', true); bad.inp.focus(); return; }
        if (!out.length) return;
        busy(form, true);
        change('items.counts', { counts: out }, function (r) {
          var left = items.filter(function (it) { return it.q === null && secBy.has(it.s) && (!inSec || it.s === inSec); }).length;
          return 'Uloženo: ' + r.saved + ' ' + plural(r.saved, 'počet', 'počty', 'počtů') + '.' + (left ? ' Bez počtu ' + verb(left, 'zbývá', 'zbývají') + ' ' + things(left) + '.' : ' Všechno má počet.');
        }).then(close, function (e) { busy(form, false); fail(e); });
      }
    });
    if (rows[0]) rows[0].inp.focus();
  }
  /** Věci, které chyběly, jsou zase ve skladu. */
  function foundItems(list) {
    change('items.found', { ids: list.map(function (it) { return it.id; }) }, list.length === 1 ? 'Zapsáno: „' + list[0].n + '“ je zase ve skladu.' : 'Zapsáno: ' + things(list.length) + ' je zase ve skladu.').catch(fail);
  }
  /** Zmenší fotku v prohlížeči (velká a náhled) a nahraje ji. Vrací slib s údaji fotky pro věc. */
  function uploadPhoto(file) {
    return shrink(file, 1600, 0.82).then(function (big) {
      return shrink(file, 360, 0.72).then(function (small) {
        var fd = new FormData();
        fd.append('f', big, 'foto.jpg');
        fd.append('t', small, 'nahled.jpg');
        return api('photo.up', fd);
      });
    }).then(function (r) { return r.photo; });
  }
  var qpIn = null;
  /** „Přidat fotku“ u věci: vybrat (nebo v telefonu vyfotit) a hned uložit, bez formuláře. */
  function quickPhoto(it, btn) {
    if (qpIn) qpIn.remove();
    qpIn = h('input', { type: 'file', accept: 'image/*', hidden: true, onchange: function () {
      var file = qpIn.files && qpIn.files[0];
      if (!file) return;
      if (btn) { btn.disabled = true; btn.lastChild.textContent = 'Nahrávám…'; }
      uploadPhoto(file).then(function (photo) {
        return change('item.photo', { id: it.id, photo: photo }, 'Fotka přidána.');
      }).catch(function (e) { if (btn && btn.isConnected) { btn.disabled = false; btn.lastChild.textContent = 'Přidat fotku'; } fail(e); });
    } });
    document.body.appendChild(qpIn);
    qpIn.click();
  }
  /** Fotky věci zvětšené přes celou obrazovku: šipky (i na klávesnici) listují, Esc nebo klepnutí vedle zavře. */
  function lightbox(it, i) {
    var list = it._ph;
    if (!list.length) return;
    var img = h('img', { alt: '' }), cap = h('span', { class: 'lb-c' }), big = h('a', { class: 'lb-o', target: '_blank', rel: 'noopener' }, icon('ext'), 'Otevřít celou');
    var dlg;
    var show = function (n) {
      i = (n + list.length) % list.length;
      img.src = photoSrc(list[i]);
      img.alt = it.n + ', fotka ' + (i + 1);
      big.href = photoSrc(list[i]);
      cap.textContent = it.n + (list.length > 1 ? ' · ' + (i + 1) + ' z ' + list.length : '');
    };
    var nav = list.length > 1 ? [h('button', { type: 'button', class: 'lb-b lb-p', 'aria-label': 'Předchozí fotka', onclick: function () { show(i - 1); } }, icon('left')),
      h('button', { type: 'button', class: 'lb-b lb-n', 'aria-label': 'Další fotka', onclick: function () { show(i + 1); } }, icon('right'))] : null;
    var close = h('button', { type: 'button', class: 'lb-b lb-x', 'aria-label': 'Zavřít', onclick: function () { dlg.close(); } }, icon('x'));
    dlg = h('dialog', { class: 'lb', 'aria-label': 'Fotka: ' + it.n,
      onclick: function (e) { if (e.target === dlg || e.target.classList.contains('lb-f')) dlg.close(); },
      onkeydown: function (e) { if (list.length > 1 && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) { e.preventDefault(); show(i + (e.key === 'ArrowLeft' ? -1 : 1)); } } },
      h('figure', { class: 'lb-f' }, img, h('figcaption', null, cap, big)), nav, close);
    dlg.addEventListener('close', function () { dlg.remove(); });
    document.body.appendChild(dlg);
    show(i);
    dlg.showModal();
    close.focus();
  }
  function removeItem(it) {
    confirmBox('Smazat věc?', '„' + it.n + '“ zmizí ze soupisu. Vrátit se dá jen obnovou ze zálohy' + (SUPER ? '.' : ' a tu udělá hlavní správce.'), 'Smazat', function () {
      S.open.delete(it.id);
      change('item.del', { id: it.id }, 'Smazáno.').catch(fail);
    }, true);
  }
  function removeWish(w) {
    confirmBox('Smazat přání?', '„' + w.n + '“ zmizí ze seznamu. Vrátit se dá jen obnovou ze zálohy' + (SUPER ? '.' : ' a tu udělá hlavní správce.'), 'Smazat', function () {
      S.open.delete(w.id);
      change('wish.del', { id: w.id }, 'Smazáno.').catch(fail);
    }, true);
  }

  /* ── objednávka: věci, kdo, kdy si je vyzvedne, do kdy je vrátí ── */
  /**
   * orderForm(o)               upraví objednávku o
   * orderForm(null, věci)      nová výpůjčka nebo objednávka těchto věcí (z tlačítka Půjčit nebo z výběru)
   * orderForm(null, věci, 1)   totéž, ale rovnou s vyzvednutím později (tlačítko Objednat)
   * Vyzvednutí teď = výpůjčka hned; vyzvednutí později = objednávka, ze které se v ten den stane výpůjčka sama.
   * U každé věci se píše, kolik si bere; půjčit ani objednat nejde víc, než je v tu dobu volné.
   */
  function orderForm(o, preset, later) {
    var edit = !!o, fromPick = !edit && preset && preset.length > 1;
    var firstQ = function (it) { return it.q === 1 && it.ok !== false ? '1' : ''; };
    var cur = edit ? { who: o.who, from: o.from, at: o.at, due: o.due, note: o.note, lines: o.items.map(function (l) { return { i: l.i, q: l.q === null ? '' : String(l.q) }; }) }
      : { who: store('kdo'), from: later ? addDays(1) : today(), at: '', due: '', note: '', lines: preset.map(function (it) { return { i: it.id, q: firstQ(it) }; }) };
    if (edit) cur.lines.forEach(function (l) { var it = itemBy.get(l.i); if (it && l.q) l.q = inQ(+l.q, it.u); });
    var who = h('input', { type: 'text', value: cur.who, maxlength: 80, required: true, list: 'dl-ord', autocomplete: 'off', oninput: function () { discordHint(hintP, who.value.trim()); } });
    var from = h('input', { type: 'date', value: cur.from, min: edit ? null : today(), 'aria-label': 'Den vyzvednutí', oninput: function () { paintWhen(); dueQ.paint(); paintDue(); paintMsgs(); } });
    var at = h('input', { type: 'time', value: cur.at, 'aria-label': 'Čas vyzvednutí (nepovinný)', oninput: function () { paintWhen(); } });
    var due = h('input', { type: 'date', value: cur.due, 'aria-label': 'Vrátit do', oninput: function () { dueQ.paint(); paintDue(); paintMsgs(); } });
    var note = h('input', { type: 'text', value: cur.note, maxlength: 500, autocomplete: 'off', placeholder: 'třeba: vyzvednu v klubovně po schůzce' });
    var whenChips = h('div', { class: 'chips quick' }), whenTxt = h('p', { class: 'sm-hint when' }), dueTxt = h('p', { class: 'sm-hint when' });
    var dueQ = dueQuick(due, function () { return from.value || today(); }, function () { paintDue(); paintMsgs(); });
    var linesBox = h('ul', { class: 'olines ed' }), found = h('div', { class: 'chips found' }), hintP = h('p', { class: 'sm-hint dc' });
    var search = h('input', { type: 'search', placeholder: 'název věci', 'aria-label': 'Přidat další věc', autocomplete: 'off', oninput: function () { paintFound(); },
      onkeydown: function (e) { if (e.key === 'Enter') { e.preventDefault(); var b = found.querySelector('button'); if (b) b.click(); } } });
    var titleCount = h('span', { class: 'n' });
    var msgs = [];       // [řádek, prvek se zprávou o tom, kolik je volné]
    function isNow() { return from.value === today() && (!at.value || at.value <= nowHM()); }
    function span() { var t = today(), a = from.value && from.value > t ? from.value : t; return [a, due.value ? (due.value >= a ? due.value : a) : null]; }
    function draft() {
      return { id: edit ? o.id : '', no: edit ? o.no : 0, who: who.value.trim(), from: from.value, at: at.value, due: due.value, note: note.value.trim(),
        items: cur.lines.map(function (l) { var it = itemBy.get(l.i); return { i: l.i, q: it ? amountIn(l.q, it.u) : null, w: false }; }) };
    }
    function paintWhen() {
      var left = daysTo(from.value);
      fill(whenChips, [['now', 'Teď'], [1, 'Zítra'], [2, 'Pozítří'], [7, 'Za týden']].map(function (q) {
        var on = q[0] === 'now' ? from.value === today() && !at.value : (from.value === addDays(q[0]));
        return h('button', { type: 'button', class: 'chip', 'aria-pressed': String(on), onclick: function () { from.value = q[0] === 'now' ? today() : addDays(q[0]); if (q[0] === 'now') at.value = ''; paintWhen(); dueQ.paint(); paintDue(); paintMsgs(); } }, q[1]);
      }));
      whenTxt.textContent = !from.value ? 'Vyber den vyzvednutí.' : (isNow() ? 'Hned teď: věci se zapíšou jako půjčené.' :
        'Vyzvedne si to ' + dayWhen(from.value) + (at.value ? ' v ' + at.value : '') + ' (' + pickWords(left) + '). Do té doby je to objednávka, kterou vidí všichni.');
      whenTxt.classList.toggle('late', !!from.value && left < 0);
      submit.textContent = edit ? 'Uložit' : (isNow() ? 'Zapsat výpůjčku' : 'Objednat');
    }
    function paintDue() {
      var base = from.value || today();
      dueTxt.textContent = due.value ? 'Vrátit do: ' + dayLong(due.value) + (due.value < base ? '. To je před vyzvednutím!' : '') : 'Bez termínu vrácení. Věci pak drží, dokud je nevrátí: v tu dobu je nikdo jiný objednat nemůže.';
      dueTxt.classList.toggle('late', !!due.value && due.value < base);
    }
    /** U řádku: kolik je v tu dobu volné; když chce víc, proč to nejde. */
    function lineMsg(l, box, inp) {
      var it = itemBy.get(l.i), sp = span(), n = amountIn(l.q, it.u), kids = [];
      if (it.q === null) kids = [icon('warn'), 'Nemá zapsaný počet' + (it.qt ? ' (v soupisu „' + it.qt + '“)' : '') + '. Doplň ho u věci, pak půjde půjčit.'];
      else if (it.ok === false) kids = [icon('warn'), 'Chybí: u věci není zaškrtnuté „' + okLabel(secBy.get(it.s).f) + '?“.'];
      else {
        var room = freeIn(it, sp[0], sp[1], edit ? o.id : '');
        if (l.q.trim() && n === null) kids = [icon('warn'), 'Napiš celé číslo ' + unitWord(it.u) + '.'];
        else if (n !== null && n > room) kids = [icon('warn'), fitText(it, n, sp[0], sp[1], edit ? o.id : '') || ''];
        else { box.className = 'ow fr'; fill(box, 'Volno v tu dobu: ' + fmtQ(room, it.u) + ' z ' + fmtQ(it.q, it.u) + '.'); inp.removeAttribute('aria-invalid'); return; }
      }
      box.className = 'ow due late';
      fill(box, kids);
      if (it.q !== null && it.ok !== false) inp.setAttribute('aria-invalid', 'true'); else inp.removeAttribute('aria-invalid');
    }
    function paintMsgs() { msgs.forEach(function (m) { lineMsg(m[0], m[1], m[2]); }); }
    function paintLines() {
      titleCount.textContent = cur.lines.length ? '(' + cur.lines.length + ')' : '';
      msgs = [];
      fill(linesBox, cur.lines.map(function (l, i) {
        var it = itemBy.get(l.i), sec = secBy.get(it.s), f = sec.f, box = h('span', { class: 'ow', 'aria-live': 'polite' });
        var q = h('input', { type: 'text', value: l.q, maxlength: 24, autocomplete: 'off', inputmode: it.u === 'ks' ? 'numeric' : 'decimal', 'aria-label': 'Kolik (' + uLabel(it.u) + '): ' + it.n,
          placeholder: uLabel(it.u), oninput: function () { l.q = q.value; lineMsg(l, box, q); } });
        msgs.push([l, box, q]);
        lineMsg(l, box, q);
        return h('li', { class: 'oline' },
          h('span', { class: 'nm' }, h('b', null, it.n), h('small', { class: 'kw' }, sec.name, ('p1' in f && it.p1) ? ' · ' + it.p1 : '')),
          h('label', { class: 'oq' }, h('span', { class: 'vh' }, 'Kolik'), q, unitTag(q, it.u)),
          h('button', { type: 'button', class: 'btn sm ico', 'aria-label': 'Odebrat z objednávky: ' + it.n, onclick: function () { cur.lines.splice(i, 1); paintLines(); paintFound(); } }, icon('x')),
          box);
      }));
    }
    function paintFound() {
      var t = fold(search.value).split(/\s+/).filter(Boolean), have = new Set(cur.lines.map(function (l) { return l.i; }));
      if (!t.length) { fill(found); return; }
      var hits = items.filter(function (it) { return !have.has(it.id) && t.every(function (x) { return it._x.indexOf(x) >= 0; }); }).sort(function (a, b) { return (a._n.indexOf(t[0]) < 0) - (b._n.indexOf(t[0]) < 0) || a._i - b._i; }).slice(0, 8);
      fill(found, hits.length ? hits.map(function (it) {
        return h('button', { type: 'button', class: 'chip', onclick: function () { cur.lines.push({ i: it.id, q: firstQ(it) }); search.value = ''; paintLines(); paintFound(); var ins = linesBox.querySelectorAll('.oq input'); if (ins.length) ins[ins.length - 1].focus(); } },
          icon('plus'), it.n, h('span', { class: 'n' }, secBy.get(it.s).name + ' · ' + (it.q === null ? 'bez počtu' : (it.ok === false ? 'chybí' : 'volno ' + fmtQ(it.free, it.u)))));
      }) : h('span', { class: 'sm-hint' }, 'Nic takového ve skladu není.'));
    }
    var submit = h('button', { type: 'submit', class: 'btn pri' }, 'Uložit');
    var title = edit ? 'Objednávka č. ' + o.no : (preset.length === 1 ? (later ? 'Objednat: ' : 'Půjčit: ') + preset[0].n : 'Půjčit nebo objednat');
    dialog({
      title: title, novalidate: true,
      body: [fld('Kdo si to bere', who), dlist('dl-ord', names()),
        h('div', { class: 'fld' }, h('span', null, 'Kdy si to vyzvedne'), whenChips, h('div', { class: 'two dt' }, from, h('label', { class: 'at' }, h('span', null, 'v'), at)), whenTxt),
        h('div', { class: 'fld' }, h('span', null, 'Vrátit do', h('i', null, ' (nepovinné)')), dueQ.el, due, dueTxt),
        fld('Poznámka', note, 'Uvidí ji všichni, kdo vidí přehled.', '(nepovinná)'),
        h('div', { class: 'fld' }, h('span', null, 'Věci a kolik ', titleCount), linesBox,
          h('div', { class: 'osearch' }, h('label', { class: 'fld' }, h('span', null, 'Přidat další věc'), search), found)),
        hintP],
      actions: function (close) {
        return [h('button', { type: 'button', class: 'btn', onclick: close }, 'Zrušit'),
          h('button', { type: 'button', class: 'btn', onclick: function () { if (cur.lines.length) printOrders([draft()]); } }, icon('print'), 'Vytisknout'),
          submit];
      },
      onsubmit: function (close, form) {
        var dr = draft(), sp = span();
        if (!dr.who) { toast('Napiš, kdo si to bere.', true); who.focus(); return; }
        if (!dr.from) { toast('Vyber, kdy si to vyzvedne.', true); from.focus(); return; }
        if (!edit && dr.from < today()) { toast('Den vyzvednutí je v minulosti. Zkontroluj datum.', true); from.focus(); return; }
        if (dr.due && dr.due < dr.from) { toast('Vrátit se to má až po vyzvednutí. Zkontroluj data.', true); due.focus(); return; }
        if (!cur.lines.length) { toast('V objednávce není žádná věc.', true); search.focus(); return; }
        for (var i = 0; i < cur.lines.length; i++) {
          var it = itemBy.get(cur.lines[i].i), inp = msgs[i] ? msgs[i][2] : null;
          if (!edit && (it.q === null || it.ok === false)) { toast('„' + it.n + '“: ' + fitText(it, 1, sp[0], sp[1], '') + '.', true); if (inp) inp.focus(); return; }
          if (dr.items[i].q === null) { toast('Napiš, kolik si bere: „' + it.n + '“ (celé číslo ' + unitWord(it.u) + ').', true); if (inp) inp.focus(); return; }
          var why = edit ? null : fitText(it, dr.items[i].q, sp[0], sp[1], '');
          if (why) { toast('„' + it.n + '“: ' + why + '.', true); if (inp) inp.focus(); return; }
        }
        store('kdo', dr.who);
        var payload = { order: { id: dr.id, who: dr.who, from: dr.from, at: dr.at, due: dr.due, note: dr.note, items: dr.items.map(function (l) { return { i: l.i, q: l.q }; }) } };
        if (edit) payload.t = o.t;
        busy(form, true);
        change('order.save', payload, function (r) {
          var wait = r.waiting ? ' ' + waitText(r.waiting) : '';
          if (edit) return 'Objednávka uložena.' + wait;
          if (r.issued || r.waiting) return (r.issued ? (cur.lines.length === 1 ? 'Zapsáno. Má to ' + dr.who + '.' : 'Zapsáno. ' + dr.who + ' má ' + things(r.issued) + '.') : 'Zapsáno.') + wait;
          return 'Objednáno. ' + dr.who + ' si to vyzvedne ' + dayWhen(dr.from) + (dr.at ? ' v ' + dr.at : '') + '.';
        }).then(function () {
          close();
          if (fromPick) { S.sel.clear(); S.pick = false; refresh(); }
        }, function (e) { busy(form, false); fail(e); });
      }
    });
    paintWhen(); paintDue(); paintLines(); discordHint(hintP, who.value.trim());
    if (!edit && cur.who && preset.length === 1) { var q0 = linesBox.querySelector('.oq input'); if (q0 && !q0.value) { q0.focus(); return; } }
    who.focus();
    who.select();
  }

  /* ── výběr více věcí ── */
  function pickToggle() {
    S.pick = !S.pick;
    if (!S.pick) { S.sel.clear(); S.lsel.clear(); }
    refresh();
  }
  function renderPickBar() {
    var bar = document.getElementById('pickbar');
    if (!S.pick || S.tab === 'set') {
      if (bar) bar.remove();
      document.body.classList.remove('picking');
      return;
    }
    document.body.classList.add('picking');
    if (!bar) { bar = h('div', { id: 'pickbar', class: 'pickbar', role: 'region', 'aria-label': 'Vybrané' }); document.body.appendChild(bar); }
    var stop = h('button', { type: 'button', class: 'btn', onclick: function () { S.sel.clear(); S.lsel.clear(); S.pick = false; refresh(); } }, 'Zrušit výběr');
    if (S.tab === 'lent') {
      // v přehledu výpůjček se vybírají výpůjčky: vrátit je najednou
      var ls = Array.from(S.lsel).map(function (id) { return loanBy.get(id); }).filter(Boolean);
      fill(bar, h('div', { class: 'pb-in' },
        h('span', { class: 'pb-n', 'aria-live': 'polite' }, ls.length ? 'Vybráno: ' + loansN(ls.length) : 'Klepnutím na výpůjčku ji vybereš.'),
        h('button', { type: 'button', class: 'btn pri', disabled: !ls.length, onclick: function () { returnForm(ls, ls.map(function (x) { return x.l.id; })); } }, icon('in'), 'Vrátit vybrané'), stop));
      return;
    }
    var sel = Array.from(S.sel).map(function (id) { return itemBy.get(id); }).filter(Boolean);
    fill(bar, h('div', { class: 'pb-in' },
      h('span', { class: 'pb-n', 'aria-live': 'polite' }, sel.length ? 'Vybráno: ' + things(sel.length) : 'Klepnutím na věc ji vybereš.'),
      h('button', { type: 'button', class: 'btn pri', disabled: !sel.length, onclick: function () { orderForm(null, sel); } }, icon('out'), 'Půjčit nebo objednat'), stop));
  }
  /* ── tisk výdejky ── */
  /** Vytiskne objednávky (výdejky): kdo, kdy, do kdy, a věci s místem, kde je najít, a políčky na odškrtnutí. */
  function printOrders(list) {
    var old = document.getElementById('slip');
    if (old) old.remove();
    var site = D.site.title.replace(/\^/g, '');
    var slip = h('div', { id: 'slip', class: 'slip' }, list.map(function (o) {
      var ready = o.from === today() && (!o.at || o.at <= nowHM());
      var rows = o.items.map(function (l) {
        var it = itemBy.get(l.i), sec = secBy.get(it.s), f = sec.f;
        var place = ['p1' in f ? val(it.p1) : '', 'p2' in f ? val(it.p2) : ''].filter(Boolean).join(' › ');
        return h('tr', null, h('td', { class: 'cb' }, '☐'), h('td', null, h('b', null, it.n), l.w ? h('small', null, ' čeká, až se uvolní') : null),
          h('td', null, l.q === null ? '' : fmtQ(l.q, it.u)), h('td', null, place), h('td', null, sec.name), h('td', { class: 'cb' }, '☐'));
      });
      return h('section', { class: 'sl' },
        h('header', null, h('span', null, site), h('h1', null, o.no ? 'Objednávka č. ' + o.no : (ready ? 'Výpůjčka' : 'Objednávka')), h('span', null, 'vytištěno ' + fmtDate(today()))),
        h('dl', null,
          h('dt', null, 'Kdo'), h('dd', null, o.who || '…'),
          h('dt', null, 'Vyzvedne'), h('dd', null, ready ? 'hned (' + dayLong(o.from) + ')' : dayLong(o.from) + (o.at ? ' v ' + o.at : '')),
          h('dt', null, 'Vrátit do'), h('dd', null, o.due ? dayLong(o.due) : 'bez termínu'),
          o.note ? [h('dt', null, 'Poznámka'), h('dd', null, o.note)] : null),
        h('table', null, h('thead', null, h('tr', null, h('th', { class: 'cb' }, 'připr.'), h('th', null, 'věc'), h('th', null, 'kolik'), h('th', null, 'kde najdu'), h('th', null, 'list'), h('th', { class: 'cb' }, 'vráceno'))),
          h('tbody', null, rows)),
        h('footer', null, h('span', null, 'Vydal:'), h('span', null, 'Převzal:'), h('span', null, 'Vráceno dne:')));
    }));
    document.body.appendChild(slip);
    document.body.classList.add('pslip');
    var done = function () { document.body.classList.remove('pslip'); slip.remove(); window.removeEventListener('afterprint', done); };
    window.addEventListener('afterprint', done);
    window.print();
  }
  function printList() {
    var old = document.getElementById('slip');
    if (old) old.remove();
    document.body.classList.remove('pslip');
    window.print();
  }

  /* ── přání: přidat, upravit ── */
  function wishForm(w) {
    var L = D.wish.labels, isNew = !w, cur = w ? wishRaw(w) : { id: '', who: store('kdo'), n: '', q: '', where: '', why: '', alt: '', by: '', ok1: false, ok2: false, ok3: false, note: '' };
    var I = {};
    var t = function (k, max, list) { I[k] = h('input', { type: 'text', value: cur[k], maxlength: max, list: list || null, autocomplete: 'off' }); return I[k]; };
    I.n = h('input', { type: 'text', value: cur.n, maxlength: 300, required: true, autocomplete: 'off' });
    I.why = h('textarea', { rows: 3, maxlength: 1000 }, cur.why);
    I.note = h('textarea', { rows: 2, maxlength: 3000 }, cur.note);
    ['ok1', 'ok2', 'ok3'].forEach(function (k) { I[k] = h('input', { type: 'checkbox', checked: cur[k] }); });
    dialog({
      title: isNew ? 'Nové přání' : 'Upravit přání', novalidate: true,
      body: [fld(L.n, I.n), h('div', { class: 'two' }, fld(L.who, t('who', 120, 'dl-wn')), fld(L.q, t('q', 200))),
        dlist('dl-wn', names()), dlist('dl-wh', uniq(wishes.map(function (x) { return x.where; }))),
        fld(L.where, t('where', 200, 'dl-wh')), fld(L.why, I.why, D.wish.hint), fld(L.alt, t('alt', 300)), fld(L.by, t('by', 120, 'dl-wn')),
        h('label', { class: 'chk' }, I.ok1, h('span', null, L.ok1)), h('label', { class: 'chk' }, I.ok2, h('span', null, L.ok2)), h('label', { class: 'chk' }, I.ok3, h('span', null, L.ok3)),
        fld(L.note, I.note)],
      actions: function (close) { return [h('button', { type: 'button', class: 'btn', onclick: close }, 'Zrušit'), h('button', { type: 'submit', class: 'btn pri' }, isNew ? 'Přidat přání' : 'Uložit')]; },
      onsubmit: function (close, form) {
        ['who', 'n', 'q', 'where', 'why', 'alt', 'by', 'note'].forEach(function (k) { cur[k] = I[k].value; });
        ['ok1', 'ok2', 'ok3'].forEach(function (k) { cur[k] = I[k].checked; });
        if (!cur.n.trim()) { toast('Napiš aspoň, co to je.', true); I.n.focus(); return; }
        busy(form, true);
        var payload = { wish: cur };
        if (!isNew) payload.t = w.t;
        change('wish.save', payload, isNew ? 'Přidáno.' : 'Uloženo.').then(function (r) {
          close();
          if (isNew) { S.wf = 'all'; S.open.add(r.id); refresh(); var rowN = document.getElementById('r-' + r.id); if (rowN) rowN.scrollIntoView({ block: 'center' }); }
        }, function (e) { busy(form, false); fail(e); });
      }
    });
    if (isNew) I.n.focus();
  }

  /* ───────── nastavení (jen správce) ───────── */

  var setOpen = '';
  function renderSettings() {
    el.filters.hidden = true;
    el.list.className = 'list';
    var root = h('div', { class: 'set' });

    /* listy */
    root.appendChild(h('h2', null, BOOT.brand.caps ? 'LISTY' : 'Listy'));
    root.appendChild(h('p', null, 'List je jako list v tabulce: má svoje věci, svoje pole a jejich názvy. Pole, které vypneš, se jen přestane ukazovat. Co v něm je, zůstává.'));
    D.sections.forEach(function (sec, idx) { root.appendChild(sectionBox(sec, idx)); });
    root.appendChild(h('div', { class: 'acts' }, h('button', { type: 'button', class: 'btn sm', onclick: function () {
      change('section.save', { section: { name: 'Nový list', nl: 'věc', f: { q: 'počet', p1: 'Kde najdu?', p2: 'Kde najdu?', cat: 'kategorie', note: 'poznámka' } } }, 'List přidán. Pojmenuj si ho.').then(function (r) { setOpen = r.id; refresh(); }, fail);
    } }, icon('plus'), 'Přidat list')));

    /* označení, barvy míst, přání, název */
    var marks = D.marks.map(function (m) { return { k: m.k, l: m.l, c: m.c }; });
    var places = D.places.map(function (p) { return { t: p.t, c: p.c }; });
    var colorSel = function (obj) {
      var s = h('select', { 'aria-label': 'Barva', onchange: function () { obj.c = s.value; } }, COLORS.map(function (c) { return h('option', { value: c[0], selected: obj.c === c[0] }, c[1]); }));
      return s;
    };
    var mBox = h('div'), pBox = h('div');
    var paintMarks = function () {
      fill(mBox, marks.map(function (m, i) {
        var k = h('input', { type: 'text', value: m.k, maxlength: 16, 'aria-label': 'Označení', oninput: function () { m.k = k.value; } });
        var l = h('input', { type: 'text', value: m.l, maxlength: 120, placeholder: 'co znamená', 'aria-label': 'Co označení znamená', oninput: function () { m.l = l.value; } });
        return h('div', { class: 'lrow' }, k, l, colorSel(m), h('button', { type: 'button', class: 'btn sm ico', 'aria-label': 'Odebrat označení', onclick: function () { marks.splice(i, 1); paintMarks(); } }, icon('x')));
      }), h('button', { type: 'button', class: 'btn sm', onclick: function () { marks.push({ k: '', l: '', c: 'seda' }); paintMarks(); var ins = mBox.querySelectorAll('.lrow input'); if (ins.length) ins[ins.length - 2].focus(); } }, icon('plus'), 'Přidat označení'));
    };
    var paintPlaces = function () {
      fill(pBox, places.map(function (p, i) {
        var t = h('input', { type: 'text', value: p.t, maxlength: 80, placeholder: 'co má být v názvu místa', 'aria-label': 'Text v názvu místa', oninput: function () { p.t = t.value; } });
        return h('div', { class: 'lrow p' }, t, colorSel(p), h('button', { type: 'button', class: 'btn sm ico', 'aria-label': 'Odebrat pravidlo', onclick: function () { places.splice(i, 1); paintPlaces(); } }, icon('x')));
      }), h('button', { type: 'button', class: 'btn sm', onclick: function () { places.push({ t: '', c: 'seda' }); paintPlaces(); var ins = pBox.querySelectorAll('.lrow input'); if (ins.length) ins[ins.length - 1].focus(); } }, icon('plus'), 'Přidat barvu místa'));
    };
    paintMarks(); paintPlaces();
    root.appendChild(h('h2', null, BOOT.brand.caps ? 'OZNAČENÍ' : 'Označení'));
    root.appendChild(h('p', null, 'Písmena ze sloupce „označení“. Když k nim doplníš, co znamenají, bude to vidět u každé věci.'));
    root.appendChild(mBox);
    root.appendChild(h('h2', null, BOOT.brand.caps ? 'BARVY MÍST' : 'Barvy míst'));
    root.appendChild(h('p', null, 'Místo, které má v názvu tenhle text, dostane barevný proužek. V tabulce to tak má červená a černá police.'));
    root.appendChild(pBox);

    var WL = {}, wl = D.wish.labels;
    var wOn = h('input', { type: 'checkbox', checked: D.wish.on });
    var wName = h('input', { type: 'text', value: D.wish.name, maxlength: 60 });
    var wHint = h('textarea', { rows: 3, maxlength: 600 }, D.wish.hint);
    var wNote = h('textarea', { rows: 2, maxlength: 2000 }, D.wish.note);
    root.appendChild(h('h2', null, BOOT.brand.caps ? 'PŘÁNÍ' : 'Přání'));
    root.appendChild(h('label', { class: 'chk' }, wOn, h('span', null, 'Ukazovat seznam přání (co chcete koupit)')));
    root.appendChild(h('details', { class: 'acc' }, h('summary', null, 'Název a popisky seznamu přání', icon('chev')),
      h('div', { class: 'acc-b' }, fld('Název listu', wName), fld('Poznámka nahoře', wNote, null, '(nepovinná)'),
        h('div', { class: 'two' }, WKEYS.map(function (k) { WL[k] = h('input', { type: 'text', value: wl[k], maxlength: 80 }); return fld('Popisek', WL[k]); })),
        fld('Nápověda u pole „' + wl.why + '“', wHint))));

    var title = h('input', { type: 'text', value: D.site.title, maxlength: 60 });
    var fonts = h('input', { type: 'checkbox', checked: D.site.fonts });
    var saveSet = h('div', { class: 'acts' }, h('button', { type: 'button', class: 'btn pri', onclick: function () {
      var labels = {};
      WKEYS.forEach(function (k) { labels[k] = WL[k].value; });
      var body = { marks: marks, places: places, wish: { on: wOn.checked, name: wName.value, labels: labels, hint: wHint.value, note: wNote.value } };
      var reload = SUPER && (title.value !== D.site.title || fonts.checked !== D.site.fonts);
      if (SUPER) body.site = { title: title.value, fonts: fonts.checked };          // název a písmo mění jen hlavní správce
      change('settings.save', body, 'Nastavení uloženo.').then(function () { if (reload) location.reload(); }, fail);
    } }, 'Uložit nastavení'));
    if (SUPER) {
      root.appendChild(h('h2', null, 'Název'));
      root.appendChild(fld('Název', title, 'Jak se sklad jmenuje: v záhlaví stránky a v titulku okna.'));
      root.appendChild(saveSet);
    } else {
      // uživatel: tlačítko ukládá označení, barvy míst a přání; název a písmo jen vidí
      root.appendChild(saveSet);
      root.appendChild(h('h2', null, 'Název'));
      root.appendChild(h('p', null, 'Název skladu nastavuje hlavní správce.'));
    }

    /* typy položek (inventarizace): spravuje jen hlavní správce */
    typesBox(root);

    /* Discord: jen hlavní správce */
    root.appendChild(h('h2', null, BOOT.brand.caps ? 'DISCORD' : 'Discord'));
    if (SUPER) root.appendChild(discordBox());
    else root.appendChild(h('p', null, HOOK && HOOK.set ? 'Sklad posílá zprávy na Discord. Nastavuje to hlavní správce.' : 'Discord nastavuje hlavní správce.'));

    /* uživatelé: jen hlavní správce */
    if (SUPER) {
      root.appendChild(h('h2', null, BOOT.brand.caps ? 'UŽIVATELÉ' : 'Uživatelé'));
      root.appendChild(h('p', null, 'Další lidé, kteří smějí spravovat sklad: půjčovat, vracet, upravovat věci, listy i nastavení. Discord, hesla, uživatele, název skladu, písmo, typy položek a zálohy smíš jen ty. Do správy se přihlašují svým jménem a heslem.'));
      root.appendChild(usersBox());
    }

    /* hesla: hlavní správce všechna, uživatel to svoje */
    if (SUPER) {
      root.appendChild(h('h2', null, BOOT.brand.caps ? 'HESLA' : 'Hesla'));
      root.appendChild(h('p', null, BOOT.locked ? 'Přehled se teď otevírá jen s heslem pro nahlížení.' : 'Přehled teď vidí každý, kdo zná adresu. Heslem pro nahlížení ho zavřeš jen pro ty, kdo heslo znají.'));
      var curP = h('input', { type: 'password', autocomplete: 'current-password', maxlength: 200 });
      var newA = h('input', { type: 'password', autocomplete: 'new-password', maxlength: 200 });
      var newV = h('input', { type: 'password', autocomplete: 'new-password', maxlength: 200 });
      var access = function (body, msg) {
        if (!curP.value) { toast('Nejdřív napiš současné heslo správce.', true); curP.focus(); return; }
        body.current = curP.value;
        api('access.save', body).then(function (r) { CSRF = r.csrf; BOOT.locked = r.viewLocked; toast(msg); refresh(); }, fail);
      };
      root.appendChild(h('form', { onsubmit: function (e) { e.preventDefault(); } },
        fld('Současné heslo správce', curP, 'Je potřeba ke každé změně hesel.'),
        h('div', { class: 'two' },
          h('div', null, fld('Nové heslo správce', newA, 'Aspoň 8 znaků. Ostatní, kdo jsou přihlášení jako hlavní správce, se odhlásí. Uživatelů se to netýká.'),
            h('button', { type: 'button', class: 'btn sm', onclick: function () { if (newA.value.length < 8) { toast('Heslo správce musí mít aspoň 8 znaků.', true); return; } access({ admin: newA.value }, 'Heslo správce změněno.'); } }, 'Změnit heslo správce')),
          h('div', null, fld('Heslo pro nahlížení', newV, 'Aspoň 4 znaky. Kdo ho zná, může přehled jen číst.'),
            h('div', { class: 'acts' },
              h('button', { type: 'button', class: 'btn sm', onclick: function () { if (newV.value.length < 4) { toast('Heslo pro nahlížení musí mít aspoň 4 znaky.', true); return; } access({ view: newV.value }, 'Heslo pro nahlížení nastaveno.'); } }, BOOT.locked ? 'Změnit heslo pro nahlížení' : 'Zavřít přehled heslem'),
              BOOT.locked ? h('button', { type: 'button', class: 'btn sm dan', onclick: function () { access({ view: '' }, 'Přehled je zase otevřený všem.'); } }, 'Zrušit heslo pro nahlížení') : null)))));
    } else {
      root.appendChild(h('h2', null, BOOT.brand.caps ? 'TVOJE HESLO' : 'Tvoje heslo'));
      var myCur = h('input', { type: 'password', autocomplete: 'current-password', maxlength: 200 });
      var myNew = h('input', { type: 'password', autocomplete: 'new-password', maxlength: 200 });
      root.appendChild(h('form', { onsubmit: function (e) {
        e.preventDefault();
        if (!myCur.value) { toast('Napiš současné heslo.', true); myCur.focus(); return; }
        if (myNew.value.length < 8) { toast('Nové heslo musí mít aspoň 8 znaků.', true); myNew.focus(); return; }
        api('me.pass', { current: myCur.value, pass: myNew.value }).then(function (r) { CSRF = r.csrf; myCur.value = ''; myNew.value = ''; toast('Heslo změněno. Na ostatních zařízeních se přihlas znovu.', false, 6000); }, fail);
      } }, h('div', { class: 'two' }, fld('Současné heslo', myCur), fld('Nové heslo', myNew, 'Aspoň 8 znaků.')),
        h('div', { class: 'acts' }, h('button', { type: 'submit', class: 'btn sm' }, 'Změnit heslo'))));
      root.appendChild(h('p', { class: 'sm-hint' }, 'Když heslo zapomeneš, nové ti nastaví hlavní správce. Heslo pro nahlížení a Discord nastavuje on.'));
    }

    /* import z tabulky */
    root.appendChild(h('h2', null, BOOT.brand.caps ? 'IMPORT Z TABULKY' : 'Import z tabulky'));
    root.appendChild(h('p', null, 'Víc věcí najednou nahraješ ze souboru CSV (Excel, Google Tabulky, LibreOffice) nebo vložením zkopírovaných buněk. Sloupce se přiřadí podle názvů, před nahráním uvidíš náhled.'));
    root.appendChild(h('div', { class: 'acts' }, h('button', { type: 'button', class: 'btn sm', onclick: function () { importForm(); } }, icon('up'), 'Nahrát věci z tabulky')));

    /* zálohy: stáhnout, nahrát a obnovit smí jen hlavní správce */
    root.appendChild(h('h2', null, BOOT.brand.caps ? 'ZÁLOHY' : 'Zálohy'));
    if (SUPER) backupBox(root);
    else root.appendChild(h('p', null, 'Před první změnou každého dne si sklad sám odloží kopii. Zálohy stahuje, nahrává a obnovuje hlavní správce.'));

    /* historie */
    var lg = h('ul', { class: 'logl' }, h('li', null, 'Načítám…'));
    root.appendChild(h('h2', null, BOOT.brand.caps ? 'HISTORIE' : 'Historie'));
    root.appendChild(h('details', { class: 'acc', ontoggle: function (e) { if (e.target.open) loadLog(lg); } }, h('summary', null, 'Co se kdy změnilo', icon('chev')), h('div', { class: 'acc-b' }, lg)));

    fill(el.list, root);
    var cnt = document.getElementById('cnt');
    if (cnt) cnt.textContent = '';
  }
  /** Typy položek (inventarizace): přidávat, přejmenovat, řadit a mazat smí jen hlavní správce; ostatním se jen vysvětlí, co to je. */
  function typesBox(root) {
    root.appendChild(h('h2', null, BOOT.brand.caps ? 'TYPY POLOŽEK' : 'Typy položek'));
    if (!SUPER) {
      root.appendChild(h('p', null, 'Typ položky je inventarizační údaj u věci (třeba spotřební materiál nebo drobný hmotný majetek). Vybereš ho u každé věci, seznam typů ale spravuje hlavní správce.'));
      return;
    }
    root.appendChild(h('p', null, 'Inventarizační údaj u každé věci: podle typu jde filtrovat a je ve sloupci tabulky ke stažení. Vidí ho jen správa. Typy si přidávej, přejmenovávej a řaď, jak potřebuješ; přejmenování se projeví u všech věcí s tím typem.'));
    var used = new Map();
    items.forEach(function (it) { if (it.ty) used.set(it.ty, (used.get(it.ty) || 0) + 1); });
    var list = (D.types || []).map(function (t) { return { id: t.id, n: t.n }; });
    var box = h('div');
    var paint = function () {
      fill(box, list.map(function (t, i) {
        var inp = h('input', { type: 'text', value: t.n, maxlength: 80, placeholder: 'název typu', 'aria-label': 'Název typu položky', oninput: function () { t.n = inp.value; } });
        var n = t.id ? (used.get(t.id) || 0) : 0;
        return h('div', { class: 'lrow ty' }, inp, h('span', { class: 'sm-hint' }, n ? things(n) : ''),
          h('button', { type: 'button', class: 'btn sm ico', 'aria-label': 'Posunout nahoru', disabled: i === 0, onclick: function () { list.splice(i - 1, 0, list.splice(i, 1)[0]); paint(); } }, icon('arrup')),
          h('button', { type: 'button', class: 'btn sm ico', 'aria-label': 'Posunout dolů', disabled: i === list.length - 1, onclick: function () { list.splice(i + 1, 0, list.splice(i, 1)[0]); paint(); } }, icon('arrdown')),
          h('button', { type: 'button', class: 'btn sm ico', 'aria-label': 'Odebrat typ', onclick: function () { list.splice(i, 1); paint(); } }, icon('x')));
      }), h('button', { type: 'button', class: 'btn sm', onclick: function () {
        list.push({ id: '', n: '' }); paint();
        var ins = box.querySelectorAll('.lrow input'); if (ins.length) ins[ins.length - 1].focus();
      } }, icon('plus'), 'Přidat typ'));
    };
    paint();
    root.appendChild(box);
    root.appendChild(h('div', { class: 'acts' }, h('button', { type: 'button', class: 'btn pri', onclick: function () {
      var named = list.filter(function (t) { return t.n.trim(); });
      var seen = new Set(), dup = '';
      named.forEach(function (t) { var k = nameKey(t.n); if (seen.has(k)) dup = t.n.trim(); seen.add(k); });
      if (dup) { toast('Typ „' + dup + '“ tu je dvakrát.', true); return; }
      var keep = new Set(named.map(function (t) { return t.id; })), lost = 0;
      (D.types || []).forEach(function (t) { if (!keep.has(t.id)) lost += used.get(t.id) || 0; });
      var send = function () { change('types.save', { types: named }, 'Typy položek uloženy.').catch(fail); };
      if (lost) confirmBox('Smazat typy, které věci mají?', things(lost) + ' ' + verb(lost, 'má', 'mají') + ' typ, který mizí, a zůstanou bez typu. Stav před tím se uloží mezi zálohy' + '.', 'Uložit a smazat', send, true);
      else send();
    } }, 'Uložit typy')));
  }
  /** Zálohy (jen hlavní správce): stažení, nahrání ze souboru a denní kopie k obnovení. */
  function backupBox(root) {
    root.appendChild(h('p', null, 'Před první změnou každého dne si sklad sám odloží kopii. Fotky v záloze nejsou, ty leží ve složce sklad-data/foto.'));
    var bk = h('ul', { class: 'bkl' }, h('li', null, 'Načítám…'));
    var file = h('input', { type: 'file', accept: '.json,application/json', hidden: true, onchange: function () {
      var f0 = file.files && file.files[0];
      if (!f0) return;
      f0.text().then(function (txt) {
        var data;
        try { data = JSON.parse(txt); } catch (e) { toast('Tohle není záloha skladu.', true); return; }
        confirmBox('Nahrát zálohu?', 'Všechno, co je teď ve skladu (' + things(items.length) + '), se nahradí obsahem souboru. Současný stav se předtím odloží mezi zálohy.', 'Nahradit obsah skladu', function () {
          change('import', { data: data }, 'Záloha nahrána.').catch(fail);
        }, true);
      });
      file.value = '';
    } });
    root.appendChild(h('div', { class: 'acts' }, h('a', { class: 'btn sm', href: '?export=json' }, icon('down'), 'Stáhnout zálohu'),
      h('button', { type: 'button', class: 'btn sm', onclick: function () { file.click(); } }, icon('up'), 'Nahrát zálohu ze souboru'), file));
    root.appendChild(h('details', { class: 'acc', ontoggle: function (e) { if (e.target.open) loadBackups(bk); } }, h('summary', null, 'Denní kopie', icon('chev')), h('div', { class: 'acc-b' }, bk)));
  }
  var dcDraft = null, dcGrab = null, dcSave = null, dcChecked = 0;
  /** Vymění jen okno s nastavením Discordu; d = rozepsaný stav formuláře, který má zůstat. */
  function repaintDiscord(d) {
    var old = el.list.querySelector('.dcd');
    if (!old) return;
    dcDraft = d || null;
    old.replaceWith(discordBox());
  }
  /** Po návratu z Discordu (pozvání bota) se zeptá, jestli už bot na serveru je. Rozepsaný formulář nechá být. */
  function botCheck() {
    if (!ADMIN || S.tab !== 'set' || document.hidden || !HOOK || !HOOK.bot || !HOOK.bot.set || HOOK.bot.in || !HOOK.guild || Date.now() - dcChecked < 4000) return;
    dcChecked = Date.now();
    api('hook.check', {}).then(function (r) {
      HOOK = r.hook;
      if (!r.hook.bot.in || S.tab !== 'set' || !dcGrab) return;
      var d = dcGrab(), pend = 0;
      // co čekalo jen na bota, už čekat nemusí: jména z formuláře se rovnou dohledají
      d.people.forEach(function (p) { if (p.wait) { p.e = ''; p.wait = false; pend++; } });
      if (d.pingWait) { d.pingError = ''; d.pingWait = false; pend++; }
      repaintDiscord(d);
      if (pend && dcSave) dcSave();
      else toast('Bot je na serveru. Teď stačí psát jména.', false, 6000);
    }, function () {});
  }
  /** Nastavení Discordu: webhook, co posílat, připomínky, bot na hledání jmen a lidé. */
  function discordBox() {
    var H = HOOK || { set: false, id: '', name: '', guild: '', server: '', on: { loan: true, item: true, back: false, remind: true }, rem: { before: false, day: true, every: 7 }, ping: '', pingH: '', people: [], log: [] };
    var B = H.bot || { set: false, name: '', id: '', in: false, all: false, err: '' };
    var F = dcDraft || {};      // rozepsaný stav z minula (po uložení s chybou, po ověření bota)
    dcDraft = null;
    var named = B.set && !B.err;        // jména místo ID: jakmile je bot nastavený (i když ještě čeká na pozvání)
    var WAIT = 'Dohledám, až bude bot na serveru.';
    var url = h('input', { type: 'url', maxlength: 300, autocomplete: 'off', spellcheck: 'false', placeholder: 'https://discord.com/api/webhooks/…', value: F.url || '' });
    var on = {}, ck = function (k, label) { on[k] = h('input', { type: 'checkbox', checked: F.on ? !!F.on[k] : !!H.on[k] }); return h('label', { class: 'chk' }, on[k], h('span', null, label)); };
    var before = h('input', { type: 'checkbox', checked: F.rem ? F.rem.before : !!H.rem.before }), day = h('input', { type: 'checkbox', checked: F.rem ? F.rem.day : !!H.rem.day });
    var every = h('input', { type: 'number', class: 'num', min: 0, max: 60, step: 1, value: F.rem ? F.rem.every : H.rem.every, inputmode: 'numeric', 'aria-label': 'Po termínu připomínat každých kolik dní' });
    var token = h('input', { type: 'text', maxlength: 200, autocomplete: 'off', autocapitalize: 'off', spellcheck: 'false', value: F.token || '' });
    var tokenOpen = !B.set || !!B.err || !!F.tokenOpen;
    var ping = h('input', { type: 'text', value: F.ping !== undefined ? F.ping : (H.pingH || H.ping), maxlength: 80, autocomplete: 'off', spellcheck: 'false', placeholder: named ? '@jméno nebo &role' : 'číselné ID z Discordu', 'aria-describedby': 'dc-ping-e',
      oninput: function () { pingErr.hidden = true; pingWait = false; ping.removeAttribute('aria-invalid'); } });
    var pingWait = !!F.pingWait;
    var pingErr = h('small', { class: 'pp-n' + (pingWait ? '' : ' bad'), id: 'dc-ping-e', hidden: !F.pingError }, pingWait ? WAIT : (F.pingError || ''));
    if (F.pingError && !pingWait) ping.setAttribute('aria-invalid', 'true');
    var people = F.people || H.people.map(function (p) { return { n: p.n, d: p.u ? '@' + p.u : p.d, h: p.h || '', saved: true }; });
    var pBox = h('div');
    var paintPeople = function () {
      fill(pBox, people.map(function (p, i) {
        var id = 'dc-p' + i;
        var note = h('div', { class: 'pp-n', id: id });
        var paintNote = function () {
          var err = p.e && !p.wait;
          note.hidden = !(p.e || p.pick || (p.h && nameKey(p.h) !== nameKey(p.n)));
          note.className = 'pp-n' + (err ? ' bad' : '');
          fill(note, p.wait ? WAIT : (p.e ? [h('span', null, p.e), (p.c || []).length ? h('span', { class: 'chips' }, p.c.map(function (c) {
            return h('button', { type: 'button', class: 'chip', onclick: function () { p.d = '@' + c.u; p.h = c.h || ''; p.e = ''; p.c = null; p.pick = true; p.saved = false; paintPeople(); } }, discordName(c));
          })) : null] : (p.pick ? 'Vybráno' + (p.h ? ': ' + p.h : '') + '. Platit začne po uložení.' : 'na serveru jako ' + p.h)));
          if (err) d.setAttribute('aria-invalid', 'true'); else d.removeAttribute('aria-invalid');
        };
        // zkoušet jde jen řádek, který je uložený a nikdo ho od té doby nepřepsal: zkouška bere, co platí na serveru
        var tryB = h('button', { type: 'button', class: 'btn sm', disabled: !H.set || !p.saved, title: p.saved ? null : 'Zkusit jde až po uložení', onclick: function () { api('hook.test', { person: p.n }).then(tested('Zkušební zpráva s označením odešla.'), fail); } }, 'Zkusit');
        var touched = function () { if (p.saved) { p.saved = false; tryB.disabled = true; tryB.title = 'Zkusit jde až po uložení'; } };
        var n = h('input', { type: 'text', value: p.n, maxlength: 80, list: 'dl-ppl', placeholder: 'jméno jako u výpůjček', 'aria-label': 'Jméno', autocomplete: 'off', oninput: function () { p.n = n.value; touched(); } });
        var d = h('input', { type: 'text', value: p.d, maxlength: 80, placeholder: named ? '@jméno na Discordu' : 'Discord ID, třeba 123456789012345678', 'aria-label': 'Na Discordu', 'aria-describedby': id, autocomplete: 'off', spellcheck: 'false',
          oninput: function () { p.d = d.value; touched(); if (p.e || p.h || p.pick) { p.e = ''; p.c = null; p.h = ''; p.pick = false; p.wait = false; paintNote(); } } });
        paintNote();
        return h('div', { class: 'pp' }, h('div', { class: 'lrow ppl' }, n, d, tryB,
          h('button', { type: 'button', class: 'btn sm ico', 'aria-label': 'Odebrat člověka', onclick: function () { people.splice(i, 1); paintPeople(); } }, icon('x'))), note);
      }), h('button', { type: 'button', class: 'btn sm', onclick: function () { people.push({ n: '', d: '' }); paintPeople(); var ins = pBox.querySelectorAll('.lrow input'); if (ins.length) ins[ins.length - 2].focus(); } }, icon('plus'), 'Přidat člověka'));
    };
    paintPeople();
    var grab = function () {
      return { url: url.value, token: token.value, tokenOpen: tokenOpen, ping: ping.value, pingError: pingErr.hidden ? '' : pingErr.textContent, pingWait: pingWait && !pingErr.hidden, people: people,
        on: { loan: on.loan.checked, item: on.item.checked, back: on.back.checked, remind: on.remind.checked }, rem: { before: before.checked, day: day.checked, every: every.value } };
    };
    dcGrab = grab;
    var saveBtn = h('button', { type: 'button', class: 'btn pri', onclick: function () { save(); } }, 'Uložit Discord');
    var send = function (body, msg) {
      var typed = ping.value.trim();
      saveBtn.disabled = true;
      saveBtn.textContent = 'Ukládám…';
      api('hook.save', body).then(function (r) {
        HOOK = r.hook;
        var rows = r.rows || [], bad = rows.filter(function (x) { return x.e && !x.wait; }).length + (r.pingError && !r.pingWait ? 1 : 0);
        var d = { people: rows.map(function (x) { return x.e ? { n: x.n, d: x.d, e: x.e, c: x.c || [], wait: !!x.wait } : { n: x.n, d: x.u ? '@' + x.u : x.d, h: x.h || '', saved: true }; }) };
        if (r.pingError) { d.ping = typed; d.pingError = r.pingError; d.pingWait = !!r.pingWait; }
        repaintDiscord(d);
        var k = r.hook;
        if (bad) toast('Uloženo, až na ' + bad + ' ' + plural(bad, 'řádek', 'řádky', 'řádků') + '. Proč, je napsané u ' + (bad === 1 ? 'něj' : 'nich') + '.', true);
        else if (k.bot.set && k.guild && !k.bot.in && !k.bot.err) toast('Uloženo. Bot ještě není na serveru: pozvi ho tlačítkem.', false, 7000);
        else toast(msg(k));
      }, function (e) { saveBtn.disabled = false; saveBtn.textContent = 'Uložit Discord'; fail(e); });
    };
    var logBox = h('details', { class: 'acc' });
    var paintLog = function () {
      logBox.hidden = !HOOK || !HOOK.log.length;
      fill(logBox, h('summary', null, 'Co se naposledy posílalo', icon('chev')),
        h('div', { class: 'acc-b' }, h('ul', { class: 'logl' }, (HOOK ? HOOK.log : []).map(function (x) { return h('li', null, h('time', null, fmtTime(x.t)), h('span', { class: x.ok ? null : 'due late' }, (x.ok ? '' : 'nepovedlo se: ') + x.x)); }))));
    };
    var tested = function (msg) { return function (r) { HOOK = r.hook; paintLog(); toast(msg); }; };
    paintLog();
    var form = function () {
      return { url: url.value.trim(), bot: token.value.trim(), on: { loan: on.loan.checked, item: on.item.checked, back: on.back.checked, remind: on.remind.checked },
        rem: { before: before.checked, day: day.checked, every: parseInt(every.value, 10) || 0 }, ping: ping.value.trim(), people: people.map(function (p) { return { n: p.n, d: p.d }; }) };
    };
    var save = dcSave = function () {
      send(form(), function (k) {
        return k.set ? 'Discord uložen.' + (k.name ? ' Webhook „' + k.name + '“ odpovídá.' : '') : 'Uloženo. Discord se zapne, až vložíš adresu webhooku.';
      });
    };
    var cron = location.origin + location.pathname + '?cron';
    var bn = B.name ? ' „' + B.name + '“' : '';
    var invite = B.id && H.guild ? 'https://discord.com/oauth2/authorize?client_id=' + B.id + '&scope=bot&permissions=0&guild_id=' + H.guild + '&disable_guild_select=true' : '';
    var botMsg, botActs = null;
    if (!B.set) botMsg = h('p', { class: 'msg' }, 'Bez bota jde lidi označit jen číselným ID. S botem stačí napsat jméno, třeba @Opičák, a sklad si člověka na serveru najde sám.');
    else if (B.err) botMsg = h('p', { class: 'msg bad' }, 'Bot' + bn + ': ' + B.err);
    else if (!H.guild) botMsg = h('p', { class: 'msg warn' }, 'Bot' + bn + ' je připravený. Na kterém serveru má hledat, pozná sklad z webhooku: vlož nahoře jeho adresu a ulož.');
    else if (!B.in) {
      botMsg = h('p', { class: 'msg warn' }, 'Bot' + bn + ' ještě není na vašem serveru. Pozvi ho; žádná práva nepotřebuje. Až se sem vrátíš, stav se obnoví sám.');
      botActs = h('a', { class: 'btn sm pri', href: invite, target: '_blank', rel: 'noopener noreferrer' }, 'Pozvat bota na server');
    } else botMsg = h('p', { class: 'msg' }, 'Bot' + bn + ' je na serveru' + (H.server ? ' „' + H.server + '“' : '') + '. Stačí psát jména, najde si je sám.');
    var tokenFld = h('div', { hidden: !tokenOpen },
      fld('Token bota', token, B.set ? 'Vyplň, jen když ho chceš vyměnit. Uložený token se tu neukazuje.' : 'Token je jako heslo: uloží se jen na serveru a sem se zpátky neposílá.', B.set ? null : '(nepovinné)'),
      h('details', { class: 'acc' }, h('summary', null, 'Jak bota založit', icon('chev')),
        h('div', { class: 'acc-b' }, h('ol', { class: 'steps' },
          h('li', null, 'Otevři ', extLink('https://discord.com/developers/applications', 'discord.com/developers/applications'), ', klikni na New Application a pojmenuj ji třeba Sklad.'),
          h('li', null, 'Vlevo zvol Bot a klikni na Reset Token. Token zkopíruj a vlož ho sem. Discord ho ukáže jen jednou.'),
          h('li', null, 'Na téže stránce zapni Server Members Intent. Bot pak lidi pozná i podle zobrazovaného jména.'),
          h('li', null, 'Ulož. Objeví se tu odkaz, kterým bota pozveš na váš server.')))));
    return h('div', { class: 'dcd' },
      h('p', { class: H.set ? 'msg' : 'msg warn' }, H.set ? 'Zapnuto. Zprávy chodí přes webhook' + (H.name ? ' „' + H.name + '“' : '') + ' (číslo končí …' + H.id.slice(-4) + ').'
        : 'Vypnuto. Po vložení adresy webhooku bude sklad psát do kanálu, když si někdo něco půjčí nebo přibude nová věc, a připomínat termíny vrácení.'),
      fld('Adresa webhooku', url, H.set ? 'Adresa je uložená a už se tu neukazuje. Vyplň ji, jen když ji chceš vyměnit.' : 'V Discordu: Upravit kanál → Integrace → Webhooky → Nový webhook → Kopírovat URL webhooku.'),
      h('div', { class: 'fld' }, h('span', null, 'Co posílat'),
        ck('loan', 'Někdo si něco půjčil'), ck('item', 'Ve skladu přibyla nová věc'), ck('back', 'Věc se vrátila'), ck('remind', 'Připomínky termínu vrácení')),
      h('div', { class: 'fld' }, h('span', null, 'Kdy připomínat'),
        h('label', { class: 'chk' }, before, h('span', null, 'den před termínem')), h('label', { class: 'chk' }, day, h('span', null, 'v den vrácení')),
        h('label', { class: 'chk nump' }, h('span', null, 'po termínu hned druhý den a pak každých'), every, h('span', null, 'dní (0 = po termínu už ne)'))),
      h('div', { class: 'fld bot' }, h('span', null, 'Označování jménem'), botMsg,
        named && B.in && !B.all ? h('small', null, 'Bot teď lidi pozná podle přezdívky na serveru a podle uživatelského jména. Aby je poznal i podle zobrazovaného jména, zapni mu v Developer Portalu na stránce Bot přepínač Server Members Intent a ulož tu nastavení znovu.') : null,
        botActs || B.set ? h('div', { class: 'acts' }, botActs,
          B.set && !B.err ? h('button', { type: 'button', class: 'btn sm', 'aria-expanded': String(tokenOpen), onclick: function (e) { tokenOpen = !tokenOpen; tokenFld.hidden = !tokenOpen; e.currentTarget.setAttribute('aria-expanded', String(tokenOpen)); if (tokenOpen) token.focus(); } }, 'Vyměnit token') : null,
          B.set ? h('button', { type: 'button', class: 'btn sm dan', onclick: function () {
            confirmBox('Odebrat bota?', 'Token bota se ze skladu smaže. Lidé, které už bot našel, zůstanou a budou se označovat dál; nová jména už sklad hledat nebude.', 'Odebrat bota', function () { var f = form(); f.bot = ''; f.botOff = true; send(f, function () { return 'Bot odebrán.'; }); }, true);
          } }, 'Odebrat bota') : null) : null,
        tokenFld),
      h('label', { class: 'fld' }, h('span', null, 'Koho označit u výpůjček a nových věcí', h('i', null, ' (nepovinné)')), ping, pingErr,
        h('small', null, named ? 'Tvoje jméno na Discordu, nebo role se znakem & na začátku, třeba &Vedoucí. Bez toho zpráva jen přijde do kanálu.' : 'Tvoje ID z Discordu, nebo ID role se znakem & na začátku. Bez toho zpráva jen přijde do kanálu.')),
      h('div', { class: 'fld' }, h('span', null, 'Lidé'), pBox, dlist('dl-ppl', names()),
        h('small', null, named
          ? 'První kolonka je jméno, jak ho píšeš u výpůjček, druhá jméno z Discordu. Když jsou stejná, stačí vyplnit jednu. Koho tu nemáš, toho sklad zkusí při první výpůjčce najít sám a zapíše si ho sem.'
          : 'První kolonka je jméno, jak ho píšeš u výpůjček. Do druhé patří číselné ID: v Discordu zapni Režim vývojáře (Nastavení → Pokročilé), pak pravým tlačítkem, na mobilu podržením, na člověka → Kopírovat ID uživatele.')),
      h('div', { class: 'acts' },
        saveBtn,
        h('button', { type: 'button', class: 'btn', disabled: !H.set, onclick: function () { api('hook.test', {}).then(tested('Zkušební zpráva odešla. Podívej se do kanálu.'), fail); } }, 'Poslat zkušební zprávu'),
        H.set ? h('button', { type: 'button', class: 'btn dan', onclick: function () {
          confirmBox('Vypnout Discord?', 'Adresa webhooku' + (B.set ? ' i token bota se smažou' : ' se smaže') + ' a sklad přestane do kanálu psát. Lidé zůstanou.', 'Vypnout', function () { var f = form(); f.url = ''; f.bot = ''; f.off = true; send(f, function () { return 'Discord vypnutý.'; }); }, true);
        } }, 'Vypnout') : null),
      h('p', { class: 'sm-hint' }, 'Připomínky odejdou, když sklad mezi 8. a 21. hodinou někdo otevře. Aby chodily i bez toho, nech hosting jednou za hodinu volat adresu ',
        h('code', null, cron), ' (u Wedosu v zákaznické administraci v detailu webhostingu, položka Cron).'),
      logBox);
  }
  function loadBackups(ul) {
    api('backup.list', {}).then(function (r) {
      if (!r.list.length) { fill(ul, h('li', null, 'Zatím žádná kopie není. První vznikne s první změnou.')); return; }
      fill(ul, r.list.map(function (b) {
        var m = /^data-(\d{4}-\d\d-\d\d)(?:-(\d\d)(\d\d)\d\d-([a-z-]+))?$/.exec(b.name);
        var why = { 'pred-obnovou': 'stav před obnovou', 'pred-importem': 'stav před nahráním z tabulky', 'pred-aktualizaci': 'stav před aktualizací na novou verzi' };
        var label = m ? fmtDate(m[1]) + (m[4] ? ' ' + m[2] + ':' + m[3] + ', ' + (why[m[4]] || m[4]) : ', stav z rána') : b.name;
        return h('li', null, h('span', null, label), h('button', { type: 'button', class: 'btn sm', onclick: function () {
          confirmBox('Obnovit sklad z kopie?', 'Sklad se vrátí do stavu „' + label + '“. Současný stav se předtím odloží mezi kopie.', 'Obnovit', function () {
            change('backup.restore', { name: b.name }, 'Sklad obnoven.').catch(fail);
          }, true);
        } }, 'Obnovit'));
      }));
    }, fail);
  }
  function loadLog(ul) {
    api('log', {}).then(function (r) {
      if (!r.log.length) { fill(ul, h('li', null, 'Zatím se nic nezměnilo.')); return; }
      fill(ul, r.log.map(function (x) { return h('li', null, h('time', null, fmtTime(x.t)), h('span', null, h('b', null, x.a), x.x ? ' ' + x.x : null, x.u ? h('small', { class: 'by' }, ' · ' + x.u) : null)); }));
    }, fail);
  }
  /** Vymyslí heslo, které jde přečíst a nadiktovat: slabiky a dvě číslice (třeba „kapomelu-27“). */
  function makePass() {
    var c = 'bcdfghjklmnprstvz', v = 'aeiouy', r = new Uint32Array(9), out = '';
    crypto.getRandomValues(r);
    for (var i = 0; i < 4; i++) out += c[r[i] % c.length] + v[r[i + 4] % v.length];
    return out + '-' + (10 + r[8] % 90);
  }
  /** Uživatelé (jen hlavní správce): přidat, přejmenovat, nové heslo, smazat; a jméno hlavního správce do historie. */
  function usersBox() {
    var users = BOOT.users || [];
    var box = h('div', { class: 'usr' });
    var paint = function () {
      var list = users.length ? h('ul', { class: 'usrl' }, users.map(function (u) {
        return h('li', null, h('span', { class: 'un' }, icon('user'), h('b', null, u.n), h('small', null, u.s ? 'poslední přihlášení ' + fmtTime(u.s) : 'přihlášení zatím žádné')),
          h('span', { class: 'acts' },
            h('button', { type: 'button', class: 'btn sm', onclick: function () { userPass(u); } }, icon('key'), 'Nové heslo'),
            h('button', { type: 'button', class: 'btn sm', onclick: function () { userRename(u); } }, icon('edit'), 'Přejmenovat'),
            h('button', { type: 'button', class: 'btn sm dan', onclick: function () { userDel(u); } }, icon('trash'), 'Smazat')));
      })) : h('p', { class: 'sm-hint' }, 'Zatím tu nikdo není. Do správy se dostaneš jen ty.');
      var nIn = h('input', { type: 'text', maxlength: 60, autocomplete: 'off', placeholder: 'třeba Kája' });
      var pIn = h('input', { type: 'text', maxlength: 200, autocomplete: 'off', spellcheck: 'false', placeholder: 'aspoň 8 znaků' });
      var add = h('form', { class: 'usra', onsubmit: function (e) {
        e.preventDefault();
        if (!nIn.value.trim()) { toast('Napiš jméno.', true); nIn.focus(); return; }
        if (pIn.value.length < 8) { toast('Heslo musí mít aspoň 8 znaků.', true); pIn.focus(); return; }
        var name = nIn.value.trim(), pass = pIn.value;
        api('user.save', { n: name, pass: pass }).then(function (r) { users = BOOT.users = r.users; paint(); passNote(name, pass, true); }, fail);
      } }, h('div', { class: 'two' }, fld('Jméno', nIn, 'Tím se bude přihlašovat a uvidíš ho v historii.'),
        fld('Heslo', h('div', { class: 'inl' }, pIn, h('button', { type: 'button', class: 'btn sm', onclick: function () { pIn.value = makePass(); } }, 'Vymyslet')), 'Předej mu ho osobně.')),
        h('div', { class: 'acts' }, h('button', { type: 'submit', class: 'btn sm pri' }, icon('plus'), 'Přidat uživatele')));
      var meIn = h('input', { type: 'text', maxlength: 60, autocomplete: 'off', value: BOOT.superName || '', placeholder: 'hlavní správce' });
      var me = h('form', { class: 'usrme', onsubmit: function (e) {
        e.preventDefault();
        api('super.save', { n: meIn.value }).then(function (r) { BOOT.me = r.me; BOOT.superName = meIn.value.trim(); build(); refresh(); toast('Uloženo.'); }, fail);
      } }, fld('Tvoje jméno', h('div', { class: 'inl' }, meIn, h('button', { type: 'submit', class: 'btn sm' }, 'Uložit')), 'Ukazuje se v historii u toho, co změníš. Přihlásit se jde i s ním, nebo jen heslem.', '(nepovinné)'));
      fill(box, list, h('h3', { class: 'sub0' }, 'Nový uživatel'), add, me);
    };
    /** Po založení nebo novém hesle: ukáže heslo, ať ho jde opsat a předat. */
    var passNote = function (name, pass, isNew) {
      dialog({ title: isNew ? 'Uživatel přidán' : 'Nové heslo nastaveno', narrow: true,
        body: [h('p', null, 'Údaje pro přihlášení do správy. Předej je osobně.'),
          h('dl', { class: 'kv pw' }, h('dt', null, 'Adresa'), h('dd', null, location.origin + location.pathname + '?admin'), h('dt', null, 'Jméno'), h('dd', null, name), h('dt', null, 'Heslo'), h('dd', null, h('code', null, pass))),
          h('p', { class: 'sm-hint' }, 'Heslo se tu ukáže jen teď. Sklad ho nikde neukládá čitelně.')],
        actions: function (close) { return [h('button', { type: 'button', class: 'btn pri', onclick: close }, 'Hotovo')]; } });
    };
    var userPass = function (u) {
      var pIn = h('input', { type: 'text', maxlength: 200, autocomplete: 'off', spellcheck: 'false', value: makePass() });
      dialog({ title: 'Nové heslo: ' + u.n, narrow: true, novalidate: true,
        body: [h('p', null, 'Staré heslo uživatele „' + u.n + '“ tím přestane platit a všechna přihlášení s ním skončí.'),
          fld('Nové heslo', h('div', { class: 'inl' }, pIn, h('button', { type: 'button', class: 'btn sm', onclick: function () { pIn.value = makePass(); } }, 'Jiné')), 'Aspoň 8 znaků.')],
        actions: function (close) { return [h('button', { type: 'button', class: 'btn', onclick: close }, 'Zrušit'), h('button', { type: 'submit', class: 'btn pri' }, 'Nastavit heslo')]; },
        onsubmit: function (close, form) {
          if (pIn.value.length < 8) { toast('Heslo musí mít aspoň 8 znaků.', true); pIn.focus(); return; }
          var pass = pIn.value;
          busy(form, true);
          api('user.pass', { id: u.id, pass: pass }).then(function (r) { users = BOOT.users = r.users; paint(); close(); passNote(u.n, pass, false); }, function (e) { busy(form, false); fail(e); });
        } });
      pIn.select();
    };
    var userRename = function (u) {
      var nIn = h('input', { type: 'text', maxlength: 60, autocomplete: 'off', value: u.n });
      dialog({ title: 'Přejmenovat: ' + u.n, narrow: true, novalidate: true, body: [fld('Jméno', nIn, 'Nové jméno platí i pro přihlášení.')],
        actions: function (close) { return [h('button', { type: 'button', class: 'btn', onclick: close }, 'Zrušit'), h('button', { type: 'submit', class: 'btn pri' }, 'Uložit')]; },
        onsubmit: function (close, form) {
          busy(form, true);
          api('user.save', { id: u.id, n: nIn.value }).then(function (r) { users = BOOT.users = r.users; paint(); close(); toast('Přejmenováno.'); }, function (e) { busy(form, false); fail(e); });
        } });
      nIn.select();
    };
    var userDel = function (u) {
      confirmBox('Smazat uživatele?', 'Uživatel „' + u.n + '“ zmizí a do správy se tím jménem už nikdo nedostane. Historie změn zůstane, jak je.', 'Smazat uživatele', function () {
        api('user.del', { id: u.id }).then(function (r) { users = BOOT.users = r.users; paint(); toast('Uživatel smazán.'); }, fail);
      }, true);
    };
    paint();
    return box;
  }
  function sectionBox(sec, idx) {
    var f = {}, lab = {};
    FIELDS.forEach(function (k) { f[k] = k in sec.f; lab[k] = k in sec.f ? sec.f[k] : FIELD_DEF[k]; });
    var name = h('input', { type: 'text', value: sec.name, maxlength: 60 });
    var nl = h('input', { type: 'text', value: sec.nl, maxlength: 60 });
    var note = h('textarea', { rows: 2, maxlength: 2000 }, sec.note);
    var n = items.filter(function (i) { return i.s === sec.id; }).length;
    Object.keys(FIXED).forEach(function (k) { f[k] = true; });     // počet, popis a fotky má každý list
    var rows = FIELDS.map(function (k) {
      var c = h('input', { type: 'checkbox', checked: f[k], disabled: !!FIXED[k], onchange: function () { f[k] = c.checked; t.disabled = !c.checked; } });
      var t = h('input', { type: 'text', value: lab[k], maxlength: 80, disabled: !f[k], 'aria-label': 'Název pole: ' + FIELD_WHAT[k], oninput: function () { lab[k] = t.value; } });
      return h('div', { class: 'frow' }, h('label', { class: 'chk' }, c, h('span', null, FIELD_WHAT[k])), t);
    });
    var save = function () {
      var out = {};
      FIELDS.forEach(function (k) { if (f[k]) out[k] = lab[k]; });
      setOpen = sec.id;
      change('section.save', { section: { id: sec.id, name: name.value, nl: nl.value, note: note.value, f: out } }, 'List uložen.').catch(fail);
    };
    var move = function (dir) {
      var ids = D.sections.map(function (s) { return s.id; }), j = idx + dir;
      if (j < 0 || j >= ids.length) return;
      ids.splice(j, 0, ids.splice(idx, 1)[0]);
      setOpen = sec.id;
      change('section.order', { ids: ids }).catch(fail);
    };
    var del = function () {
      if (D.sections.length < 2) { toast('Poslední list smazat nejde.', true); return; }
      if (!n) { confirmBox('Smazat list?', 'List „' + sec.name + '“ je prázdný a zmizí.', 'Smazat list', function () { change('section.del', { id: sec.id }, 'List smazán.').catch(fail); }, true); return; }
      var to = h('select', null, h('option', { value: '' }, 'nikam, smazat je s listem'), D.sections.filter(function (s) { return s.id !== sec.id; }).map(function (s) { return h('option', { value: s.id }, 'do listu ' + s.name); }));
      dialog({ title: 'Smazat list ' + sec.name + '?', narrow: true,
        body: [h('p', null, 'Na listu je ' + things(n) + '. Kam s nimi?'), fld('Věci přesunout', to)],
        actions: function (close) { return [h('button', { type: 'button', class: 'btn', onclick: close }, 'Nechat být'), h('button', { type: 'submit', class: 'btn dan' }, 'Smazat list')]; },
        onsubmit: function (close) { close(); change('section.del', to.value ? { id: sec.id, to: to.value } : { id: sec.id, purge: true }, 'List smazán.').catch(fail); } });
    };
    return h('details', { class: 'acc', open: setOpen === sec.id }, h('summary', null, sec.name, h('span', { class: 'n' }, things(n)), icon('chev')),
      h('div', { class: 'acc-b' },
        h('div', { class: 'two' }, fld('Název listu', name), fld('Jak se tu říká věci', nl, 'V tabulce „věc“ nebo „Co“.')),
        fld('Poznámka nahoře', note, null, '(nepovinná)'),
        h('div', { class: 'fld' }, h('span', null, 'Pole a jejich názvy'), rows),
        h('div', { class: 'acts' },
          h('button', { type: 'button', class: 'btn sm pri', onclick: save }, 'Uložit list'),
          h('button', { type: 'button', class: 'btn sm ico', 'aria-label': 'Posunout list výš', disabled: idx === 0, onclick: function () { move(-1); } }, icon('arrup')),
          h('button', { type: 'button', class: 'btn sm ico', 'aria-label': 'Posunout list níž', disabled: idx === D.sections.length - 1, onclick: function () { move(1); } }, icon('arrdown')),
          h('button', { type: 'button', class: 'btn sm dan', onclick: del }, icon('trash'), 'Smazat list'))));
  }

  /* ───────── import z tabulky (CSV) ───────── */

  // výpůjčky se z tabulky nenahrávají: zapisují se v aplikaci (Půjčit), aby seděly počty
  var IMP_KEYS = ['s', 'id', 'n', 'q', 'u', 'ok', 'rc', 'rn', 'pc', 'ty', 'p1', 'p2', 'mk', 'own', 'cat', 'tg', 'tga', 'kw', 'ph', 'note'];
  var IMP_LABEL = { s: 'list', id: 'kód věci', n: 'název věci', q: 'počet', u: 'jednotka (ks, g, kg, l, ml)', ok: 've skladu (ano / ne)', rc: 'výpůjční cena (Kč)', rn: 'výpůjční cena: za co', pc: 'pořizovací cena (Kč)', ty: 'typ položky (inventarizace)',
    p1: 'místo', p2: 'v čem (krabice)', mk: 'označení', own: 'čí to je', cat: 'kategorie', tg: 'tagy (nahradí ty, co věc má)', tga: 'přidat tagy (k těm, co věc má)', kw: 'popis', ph: 'foto (odkazy)', note: 'poznámka' };
  var IMP_SYN = {
    s: ['list', 'zalozka', 'sekce'], id: ['kod', 'id', 'kod veci'],
    n: ['vec', 'nazev', 'co', 'polozka', 'predmet', 'nazev veci', 'co to je'],
    q: ['pocet', 'kolik', 'mnozstvi', 'ks', 'pocet kusu', 'kusu'],
    u: ['jednotka', 'jednotky', 'jedn', 'mj', 'merna jednotka'],
    rc: ['vypujcni cena', 'pujcovne', 'cena za pujceni', 'cena pujceni'], rn: ['za co', 'vypujcni cena za co', 'pujcovne za co'], pc: ['porizovaci cena', 'nakupni cena', 'cena', 'porizovaci'], ty: ['typ polozky', 'typ', 'typ majetku', 'druh majetku', 'inventarizacni typ'],
    ok: ['na miste', 've skladu', 'v klubovne', 'je na miste', 'skladem'],
    p1: ['misto', 'kde najdu', 'kde', 'umisteni', 'umisteni krabice', 'police'], p2: ['v cem', 'krabice', 'obal'],
    mk: ['oznaceni', 'znacka'], own: ['ci', 'ci to je', 'vlastnik', 'majitel'], cat: ['kategorie'], tg: ['tagy', 'tag', 'stitky', 'stitek', 'klicova slova', 'tags'],
    tga: ['pridat tagy', 'pridat tag', 'tagy pridat', 'dalsi tagy', 'nove tagy', 'tagy navic', 'pridat stitky'],
    kw: ['popis', 'popis klicova slova', 'pripadny popis klicova slova k dohledani', 'description'],
    ph: ['foto', 'fotky', 'fotka', 'pripadne foto'], note: ['poznamka', 'poznamky', 'pozn']
  };
  function headKey(t) { return fold(t).replace(/[^a-z0-9]+/g, ' ').trim(); }
  /** Bajty souboru jako text: UTF-8 (i s BOM), jinak Windows-1250, jak ukládá CSV český Excel. */
  function decodeTable(buf) {
    try { return { text: new TextDecoder('utf-8', { fatal: true }).decode(buf), enc: 'UTF-8' }; } catch (e) {}
    try { return { text: new TextDecoder('windows-1250').decode(buf), enc: 'Windows-1250' }; } catch (e) {}
    return { text: new TextDecoder('utf-8').decode(buf), enc: 'UTF-8' };
  }
  /** Rozloží CSV nebo text zkopírovaný z tabulky na řádky a buňky. Oddělovač (tabulátor, středník, čárka) pozná z prvního řádku. */
  function parseTable(text) {
    text = String(text).replace(/^﻿/, '');
    var first = '', inQ = false, i, c;
    for (i = 0; i < text.length; i++) { c = text[i]; if (c === '"') inQ = !inQ; else if (!inQ && (c === '\n' || c === '\r')) break; first += c; }
    var count = function (ch) { var n = 0, q = false; for (var j = 0; j < first.length; j++) { if (first[j] === '"') q = !q; else if (!q && first[j] === ch) n++; } return n; };
    var sep = ',', best = 0;
    ['\t', ';', ','].forEach(function (ch) { var n = count(ch); if (n > best) { best = n; sep = ch; } });
    var rows = [], row = [], cell = '', q = false;
    for (i = 0; i < text.length; i++) {
      c = text[i];
      if (q) {
        if (c === '"') { if (text[i + 1] === '"') { cell += '"'; i++; } else q = false; } else cell += c;
      } else if (c === '"' && cell === '') q = true;
      else if (c === sep) { row.push(cell); cell = ''; }
      else if (c === '\r' || c === '\n') { if (c === '\r' && text[i + 1] === '\n') i++; row.push(cell); rows.push(row); row = []; cell = ''; }
      else cell += c;
    }
    if (cell !== '' || row.length) { row.push(cell); rows.push(row); }
    return { sep: sep, rows: rows.filter(function (r) { return r.some(function (x) { return x.trim() !== ''; }); }) };
  }
  /** K čemu sloupec patří (podle názvu v záhlaví). used = co už je obsazené. */
  function guessField(head, used) {
    var k = headKey(head), hit = '';
    IMP_KEYS.some(function (f) { if (IMP_SYN[f].indexOf(k) >= 0) { hit = f; return true; } return false; });
    if (!hit) {
      D.sections.some(function (sec) {
        if (headKey(sec.nl) === k) { hit = 'n'; return true; }
        return FIELDS.some(function (f) { if (f !== 'ph' && IMP_KEYS.indexOf(f) >= 0 && headKey(sec.f[f] || '') === k) { hit = f; return true; } return false; });
      });
    }
    if (hit === 'p1' && used.p1) hit = 'p2';       // „Kde najdu?“ bývá v tabulce dvakrát: místo a v čem
    return hit && !used[hit] ? hit : '';
  }
  function parseBool(v) {
    var k = fold(v).trim();
    if (['ano', 'a', 'yes', 'y', 'true', 'pravda', '1', 'x', '✓', '✔', '☑', '✅'].indexOf(k) >= 0) return true;
    if (['ne', 'n', 'no', 'false', 'nepravda', '0', '✗', '✘', '☐'].indexOf(k) >= 0) return false;
    return null;
  }
  /** Počet z tabulky tak, jak ho pochopí server: jednotka ze sloupce platí pro holé číslo. Vrací { n, u } nebo null. */
  function importQty(q, u) {
    var units = { ks: 'ks', kus: 'ks', kusy: 'ks', kusu: 'ks', x: 'ks', g: 'g', gram: 'g', gramy: 'g', gramu: 'g', kg: 'kg', kilo: 'kg', kila: 'kg', kilogram: 'kg', kilogramy: 'kg', kilogramu: 'kg',
      l: 'l', litr: 'l', litry: 'l', litru: 'l', ml: 'ml', mililitr: 'ml', mililitry: 'ml', mililitru: 'ml', dl: 'dl' };
    var t = String(q || '').trim(), uk = units[fold(String(u || '')).trim()];
    if (uk && /^\d[\d .,]*$/.test(t)) t += ' ' + uk;
    var p = parseQty(t, 'ks');
    return p ? { n: p.n, u: p.u } : null;
  }

  /** Vzor souboru pro import: záhlaví a tři věci (list je první list skladu, ať jde vzor rovnou nahrát). */
  function importSample() {
    var l = D.sections[0].name;
    return [['list', 'věc', 'počet', 'jednotka', 'místo', 'v čem', 'kategorie', 'tagy', 'popis', 'výpůjční cena', 'za co', 'poznámka'],
      [l, 'sekera', '2', 'ks', 'černá police', 'bedna s nářadím', 'nářadí', 'dřevo, tábor', 'malá, s pouzdrem', '', '', ''],
      [l, 'hladká mouka', '2,5', 'kg', 'spíž', '', 'trvanlivé jídlo', 'jídlo', '', '', '', 'spotřebovat do 3/2027'],
      [l, 'týpí 5 m', '1', 'ks', 'hangár', 'bílý obal', 'stany', 'tábor, spaní', 'pro 6 lidí', '50', 'za den', '']];
  }
  /** Stáhne tabulku jako CSV pro Excel: UTF-8 s BOM, středníky, konce řádků jako ve Windows. */
  function downloadCsv(rows, name) {
    var cell = function (v) { v = String(v === null || v === undefined ? '' : v); return /[";\n\r]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v; };
    var csv = '\ufeff' + rows.map(function (r) { return r.map(cell).join(';'); }).join('\r\n') + '\r\n';
    var a = h('a', { href: URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' })), download: fold(name).replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') + '.csv' });
    document.body.appendChild(a);
    a.click();
    setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 1000);
  }
  /** Okno pro import: zdroj (soubor nebo vložený text) → přiřazení sloupců → náhled → nahrání. */
  function importForm() {
    var T = null, map = [], src = '';
    var fileIn = h('input', { type: 'file', accept: '.csv,.tsv,.txt,text/csv,text/plain', hidden: true, onchange: function () {
      var f0 = fileIn.files && fileIn.files[0];
      if (!f0) return;
      f0.arrayBuffer().then(function (buf) { var dt = decodeTable(new Uint8Array(buf)); load(dt.text, f0.name + ' · ' + dt.enc); }, function () { toast('Soubor se nepodařilo přečíst.', true); });
      fileIn.value = '';
    } });
    var paste = h('textarea', { rows: 4, placeholder: 'Sem vlož buňky zkopírované z tabulky (i s řádkem záhlaví), nebo obsah souboru CSV.', 'aria-label': 'Vložený text z tabulky' });
    var defSec = h('select', { 'aria-label': 'Do listu', onchange: function () { paint(); } }, D.sections.map(function (sc) { return h('option', { value: sc.id, selected: sc.id === (secBy.has(S.tab) ? S.tab : D.sections[0].id) }, sc.name); }));
    var modes = [['add', 'Přidat jako nové věci'], ['update', 'Upravit věci, které už ve skladu jsou (podle kódu, jinak podle názvu), ostatní přidat'], ['replace', 'Nahradit všechny věci v listu tím, co je v souboru']];
    var mode = 'add', radios = {};
    var modeBox = h('div', { class: 'fld', role: 'radiogroup', 'aria-label': 'Co s věcmi ze souboru' }, h('span', null, 'Co s nimi'), modes.map(function (m) {
      var r = radios[m[0]] = h('input', { type: 'radio', name: 'imp-mode', value: m[0], checked: m[0] === mode, onchange: function () { mode = m[0]; paint(); } });
      return h('label', { class: 'chk' }, r, h('span', null, m[1]));
    }));
    var info = h('p', { class: 'sm-hint' }), mapBox = h('div', { class: 'imap' }), prev = h('div', { class: 'iprev' }), sum = h('div', { class: 'isum' });
    var go = h('button', { type: 'submit', class: 'btn pri', disabled: true }, 'Nahrát');
    var defFld = fld('Řádky bez sloupce „list“ dát do listu', defSec);
    var step2 = h('div', { hidden: true }, info, h('div', { class: 'fld' }, h('span', null, 'Sloupce'), mapBox,
      h('small', null, 'Přiřazeno podle názvů v záhlaví. Sloupce, které nechceš, nastav na „nepoužít“.')),
      defFld, modeBox, sum, prev);
    // vzor: jak má soubor vypadat, čím oddělovat sloupce a hodnoty; jde si ho stáhnout a vyplnit
    var smp = importSample();
    var wide = !window.matchMedia || window.matchMedia('(min-width: 40rem)').matches;      // v telefonu se vzor rozbalí klepnutím
    var sample = h('details', { class: 'acc isample', open: wide }, h('summary', null, 'Vzor souboru a jak ho vyplnit', icon('chev')),
      h('div', { class: 'acc-b' },
        h('pre', { class: 'csv', tabindex: '0', 'aria-label': 'Vzor souboru CSV' }, smp.slice(0, 4).map(function (r) { return r.join(';'); }).join('\n')),
        h('ul', { class: 'ilist rules' },
          h('li', null, 'První řádek je záhlaví s názvy sloupců. Nutný je jen sloupec s názvem věci (při úpravě stačí kód věci), ostatní můžou chybět a pořadí je jedno.'),
          h('li', null, h('b', null, 'Sloupce odděluj středníkem (;)'), ', tak ukládá CSV český Excel. Funguje i tabulátor (buňky zkopírované z tabulky) a čárka (Google Tabulky). Oddělovač se pozná sám podle záhlaví.'),
          h('li', null, 'Víc hodnot v jedné buňce (kategorie, tagy, označení) odděl čárkou. Když je oddělovačem sloupců čárka, dej takovou buňku do uvozovek: "dřevo, tábor". Excel i Google Tabulky to při ukládání udělají samy.'),
          h('li', null, 'Sloupec „tagy“ nahradí tagy, které věc má. Sloupec „přidat tagy“ je k nim jen přidá.'),
          h('li', null, 'Počet je číslo (2, 2,5 i 2.5). Jednotka patří do sloupce „jednotka“ (ks, g, kg, l, ml), nebo rovnou k číslu: 2,5 kg. Ceny v korunách: 50, 12,50, 80 Kč.'),
          h('li', null, 'Excel: Uložit jako → CSV (oddělený středníkem). Google Tabulky: Soubor → Stáhnout → CSV. Kódování (UTF-8 i Windows-1250) se pozná samo.'),
          h('li', null, 'Tabulka, kterou sklad dává ke stažení, jde upravit a nahrát zpátky; podle sloupce „kód“ pozná, kterou věc upravit.'))));
    var sampleBtn = h('button', { type: 'button', class: 'btn sm', title: 'Soubor CSV se záhlavím a třemi věcmi: vyplň ho v Excelu a nahraj', onclick: function () { downloadCsv(smp, 'vzor-importu-skladu'); } }, icon('down'), 'Stáhnout vzor');
    function load(text, label) {
      var t = parseTable(text);
      if (t.rows.length < 2) { toast('V tabulce musí být řádek se záhlavím a pod ním aspoň jedna věc.', true); return; }
      sample.open = false;
      T = { head: t.rows[0], rows: t.rows.slice(1), sep: t.sep };
      src = label;
      var used = {};
      map = T.head.map(function (hd) { var f = guessField(hd, used); if (f) used[f] = true; return f; });
      if (used.id) { mode = 'update'; radios.update.checked = true; }        // sloupec s kódem: věci ze skladu se upravují
      step2.hidden = false;
      paint();
    }
    /** Řádky tak, jak půjdou na server, a co s nimi bude. */
    function build() {
      var out = { rows: [], lines: [], lists: [], skipped: [], bad: [], badPrice: [], badType: [], newLists: new Map(), labels: {} };
      var col = {};
      map.forEach(function (f, i) { if (f) { col[f] = i; out.labels[f] = T.head[i].trim(); } });
      T.rows.forEach(function (r, ri) {
        var g = function (f) { return f in col ? String(r[col[f]] === undefined ? '' : r[col[f]]).trim() : undefined; };
        var n = g('n'), code = g('id');
        if (!n && !(mode === 'update' && code)) { out.skipped.push(ri + 2); return; }      // při úpravě stačí kód
        var row = {}, sname = g('s');
        if (n) row.n = n;
        // list jen ze sloupce „list“; bez něj nová věc půjde do vybraného listu a věc nalezená podle kódu zůstane ve svém
        if (sname) {
          var sec = D.sections.find(function (sc) { return headKey(sc.name) === headKey(sname); });
          if (sec) row.s = sec.id;
          else { if (!out.newLists.has(headKey(sname))) out.newLists.set(headKey(sname), { key: 'n' + out.newLists.size, name: sname, n: 0 }); row.s = out.newLists.get(headKey(sname)).key; out.newLists.get(headKey(sname)).n++; }
        }
        if (code) row.id = code;
        IMP_KEYS.forEach(function (f) {
          if (!(f in col) || f === 's' || f === 'id' || f === 'n') return;
          var v = g(f);
          if (f === 'ok') row.ok = v === '' ? null : parseBool(v);
          else if (f === 'mk' || f === 'cat' || f === 'tg' || f === 'tga') row[f] = v.split(',').map(function (x) { return x.trim(); }).filter(Boolean);
          else if (f === 'ty') {
            // typ se hledá podle názvu; neznámý se nenahraje (nové typy přidává hlavní správce v Nastavení), prázdná buňka typ věci smaže
            var ty = (D.types || []).find(function (t) { return headKey(t.n) === headKey(v); });
            if (v === '') row.ty = '';
            else if (ty) row.ty = ty.id;
            else out.badType.push((ri + 2) + ': „' + v + '“');
          }
          else if (f === 'ph') row.ph = v.split(/\s+/).filter(function (u) { return /^https?:\/\/\S+$/i.test(u); }).map(function (u) { return { u: u }; });
          else row[f] = v;
        });
        if ('q' in row && !importQty(row.q, row.u)) out.bad.push((ri + 2) + (row.q ? ': „' + row.q + '“' : ': prázdný'));
        ['rc', 'pc'].forEach(function (f) { if (row[f] && isNaN(parsePrice(row[f]))) out.badPrice.push((ri + 2) + ': „' + row[f] + '“'); });
        out.rows.push(row);
        out.lines.push(ri + 2);
      });
      out.lists = Array.from(out.newLists.values()).map(function (l) { return { key: l.key, name: l.name }; });
      return out;
    }
    function paint() {
      if (!T) return;
      info.textContent = src + ' · ' + T.rows.length + ' ' + plural(T.rows.length, 'řádek', 'řádky', 'řádků') + ' pod záhlavím · oddělovač ' + ({ '\t': 'tabulátor', ';': 'středník', ',': 'čárka' })[T.sep];
      var used = {};
      map.forEach(function (f) { if (f) used[f] = (used[f] || 0) + 1; });
      fill(mapBox, T.head.map(function (hd, i) {
        var sample = T.rows.map(function (r) { return (r[i] || '').trim(); }).filter(Boolean).slice(0, 2).join(' · ');
        var sel = h('select', { 'aria-label': 'Sloupec „' + hd + '“', onchange: function () { map[i] = sel.value; paint(); } },
          h('option', { value: '' }, 'nepoužít'), IMP_KEYS.map(function (f) { return h('option', { value: f, selected: map[i] === f }, IMP_LABEL[f]); }));
        return h('div', { class: 'irow' + (map[i] && used[map[i]] > 1 ? ' dup' : '') }, h('span', { class: 'ih' }, h('b', null, hd || '(bez názvu)'), sample ? h('small', null, sample) : null), sel);
      }));
      var B = build(), dup = Object.keys(used).filter(function (f) { return used[f] > 1; });
      var errs = [];
      if (!('n' in used) && !(mode === 'update' && 'id' in used)) {
        errs.push('id' in used ? 'Chybí sloupec s názvem věci. Když jen upravuješ věci podle kódu, vyber níž „Upravit věci, které už ve skladu jsou“.' : 'Chybí sloupec s názvem věci. Přiřaď ho v seznamu sloupců.');
      }
      if (dup.length) errs.push('Víc sloupců má stejný význam: ' + dup.map(function (f) { return IMP_LABEL[f]; }).join(', ') + '.');
      // co se stane (řádek bez listu: nová věc do vybraného listu, věc nalezená podle kódu zůstane ve svém)
      var upd = 0, add = 0, lost = [], targets = new Set();
      var sidOf = function (r) { return r.s || (mode === 'update' && r.id && itemBy.has(r.id) ? itemBy.get(r.id).s : defSec.value); };
      if (mode === 'update') {
        var byName = new Map(), taken = new Set();
        items.forEach(function (it) { var k = it.s + '|' + fold(it.n); if (!byName.has(k)) byName.set(k, []); byName.get(k).push(it.id); });
        B.rows.forEach(function (r, i) {
          var hit = r.id && itemBy.has(r.id) && !taken.has(r.id) ? r.id : '';
          if (!hit && r.n) { var c = (byName.get(sidOf(r) + '|' + fold(r.n)) || []).filter(function (id) { return !taken.has(id); }); hit = c[0] || ''; }
          if (hit) { taken.add(hit); upd++; } else if (r.n) add++; else lost.push(B.lines[i]);        // jen kód, který ve skladu není
        });
      } else add = B.rows.length;
      B.rows.forEach(function (r) { targets.add(sidOf(r)); });
      // výběr listu jen tehdy, když na něm záleží: řádek s názvem a bez listu, který nejde k věci nalezené podle kódu
      defFld.hidden = !B.rows.some(function (r) { return !r.s && r.n && !(mode === 'update' && r.id && itemBy.has(r.id)); });
      var goneList = mode === 'replace' ? items.filter(function (it) { return targets.has(it.s); }) : [], gone = goneList.length;
      var busyN = goneList.filter(function (it) { return it.l.length || resBy.has(it.id); }).length;
      var lines = [];
      if (add) lines.push(verb(add, 'Přidá', 'Přidají') + ' se ' + things(add) + '.');
      if (upd) lines.push('Upraví se ' + things(upd) + (upd === 1 ? ', která už ve skladu je.' : ', které už ve skladu jsou.'));
      if (gone) lines.push(verb(gone, 'Smaže', 'Smažou') + ' se ' + things(gone) + (gone === 1 ? ', která teď v ' : ', které teď v ') + (targets.size > 1 ? 'listech ' : 'listu ') + Array.from(targets).map(function (id) { return secBy.has(id) ? secBy.get(id).name : ''; }).filter(Boolean).join(', ') + (gone === 1 ? ' je.' : ' jsou.'));
      B.lists.forEach(function (l) { lines.push('Vznikne nový list „' + l.name + '“ (' + things(B.newLists.get(headKey(l.name)).n) + ').'); });
      if (B.skipped.length) lines.push('Řádky bez názvu' + (mode === 'update' && 'id' in used ? ' a kódu' : '') + ' se přeskočí: ' + B.skipped.slice(0, 12).join(', ') + (B.skipped.length > 12 ? ' a další' : '') + '.');
      if (lost.length) lines.push('Kód ve skladu není a řádek nemá název, tak se přeskočí: řádek ' + lost.slice(0, 12).join(', ') + (lost.length > 12 ? ' a další (' + lost.length + ')' : '') + '.');
      if (B.bad.length) lines.push('Počet není číslo (věc se nahraje, ale půjčit půjde, až ho doplníš' + (mode === 'update' ? '; počet, který věc už má, zůstane' : '') + '): řádek ' + B.bad.slice(0, 6).join(', řádek ') + (B.bad.length > 6 ? ' a další (' + B.bad.length + ')' : '') + '.');
      if (B.badType.length) lines.push('Typ položky ve skladu není (nové typy přidává hlavní správce v Nastavení) a nenahraje se' + (mode === 'update' ? ' (zůstane ten, co věc má)' : '') + ': řádek ' + B.badType.slice(0, 6).join(', řádek ') + (B.badType.length > 6 ? ' a další (' + B.badType.length + ')' : '') + '.');
      if (B.badPrice.length) lines.push('Cena není částka v korunách a nenahraje se' + (mode === 'update' ? ' (zůstane ta, co věc má)' : '') + ': řádek ' + B.badPrice.slice(0, 6).join(', řádek ') + (B.badPrice.length > 6 ? ' a další (' + B.badPrice.length + ')' : '') + '.');
      if (busyN) errs.push('Nahradit nejde: ' + things(busyN) + ' z ' + (targets.size > 1 ? 'těch listů' : 'toho listu') + ' ' + verb(busyN, 'je', 'jsou') + ' půjčených nebo objednaných. Vyber „Upravit věci, které už ve skladu jsou“.');
      fill(sum, errs.map(function (t) { return h('p', { class: 'msg bad' }, t); }), lines.length ? h('ul', { class: 'ilist' }, lines.map(function (t) { return h('li', null, t); })) : null,
        mode === 'replace' && gone && !busyN ? h('p', { class: 'msg warn' }, 'Nahrazení smaže i fotky těch věcí. Stav před nahráním si sklad uloží mezi zálohy' + (SUPER ? '.' : ', obnovit ho může hlavní správce.')) : null);
      var show = B.rows.slice(0, 6);
      fill(prev, show.length ? [h('b', null, 'Náhled' + (B.rows.length > show.length ? ' (prvních ' + show.length + ')' : '')),
        h('ol', { class: 'ilist pv' }, show.map(function (r) {
          var sid = sidOf(r), old = !r.n && r.id ? itemBy.get(r.id) : null;
          var sname = secBy.has(sid) ? secBy.get(sid).name : (B.lists.find(function (l) { return l.key === sid; }) || {}).name;
          var iq = 'q' in r ? importQty(r.q, r.u) : null;
          var hash = function (a) { return a.map(function (t) { return '#' + t.replace(/^#+/, ''); }).join(' '); };
          var bits = ['q' in r ? IMP_LABEL.q + ': ' + (iq ? fmtQ(iq.n, iq.u) : (r.q ? '„' + r.q + '“ (není číslo)' : 'prázdný')) : '', r.p1 ? r.p1 + (r.p2 ? ' › ' + r.p2 : '') : '', r.cat && r.cat.length ? r.cat.join(', ') : '',
            r.tg && r.tg.length ? hash(r.tg) : '', r.tga && r.tga.length ? 'přidat ' + hash(r.tga) : '', r.kw || '', r.note || ''].filter(Boolean);
          return h('li', null, h('b', null, r.n || (old ? old.n : 'kód ' + r.id + ' (ve skladu není)')), h('small', null, ' · ' + (r.n || old ? sname : '') + (bits.length ? (r.n || old ? ' · ' : '') + bits.join(' · ') : '')));
        }))] : null);
      var doing = mode === 'update' ? upd + add : B.rows.length;
      go.disabled = !!errs.length || !doing;
      go.textContent = mode === 'replace' ? 'Nahradit a nahrát' : 'Nahrát ' + things(doing);
    }
    dialog({
      title: 'Nahrát věci z tabulky', novalidate: true,
      body: [h('p', { class: 'sm-hint' }, 'Nahraj soubor CSV, nebo sem vlož buňky zkopírované z tabulky. Sloupce se přiřadí podle záhlaví a před nahráním uvidíš náhled.'),
        h('div', { class: 'acts' }, h('button', { type: 'button', class: 'btn sm', onclick: function () { fileIn.click(); } }, icon('up'), 'Vybrat soubor'), fileIn, sampleBtn),
        sample,
        h('div', { class: 'fld' }, h('span', null, 'nebo vlož zkopírované buňky'), paste,
          h('div', { class: 'acts' }, h('button', { type: 'button', class: 'btn sm', onclick: function () { if (paste.value.trim()) load(paste.value, 'vložený text'); else { toast('Nejdřív sem něco vlož.', true); paste.focus(); } } }, 'Načíst vložený text'))),
        step2],
      actions: function (close) { return [h('button', { type: 'button', class: 'btn', onclick: close }, 'Zrušit'), go]; },
      onsubmit: function (close, form) {
        if (!T) return;
        var B = build();
        var send = function () {
          busy(form, true);
          change('items.import', { mode: mode, rows: B.rows, lists: B.lists, labels: B.labels, def: defSec.value }, function (r) {
            var x = r.result, bits = [];
            if (x.added) bits.push('přidáno ' + x.added);
            if (x.updated) bits.push('upraveno ' + x.updated);
            if (x.removed) bits.push('nahrazeno ' + x.removed);
            if (x.skipped) bits.push('přeskočeno ' + x.skipped);
            return 'Nahráno: ' + bits.join(', ') + '.' + (x.lists.length ? ' Nové listy: ' + x.lists.join(', ') + '.' : '')
              + (x.kept ? ' U ' + things(x.kept) + ' v souboru počet číslem nebyl, zůstal ten ze skladu.' : '') + (x.nocount ? ' Bez počtu je teď ' + things(x.nocount) + '.' : '');
          }).then(function () { close(); }, function (e) { busy(form, false); fail(e); });
        };
        if (mode === 'replace') confirmBox('Nahradit věci v listu?', 'Věci, které teď v listu jsou, se smažou a nahradí věcmi ze souboru. Stav před tím bude v zálohách' + (SUPER ? '.' : ', obnovit ho může hlavní správce.'), 'Nahradit', send, true);
        else send();
      }
    });
  }

  /* ───────── tabulka ke stažení ───────── */

  function exportCsv() {
    var rows = [];
    var name;
    if (S.tab === 'wish') {
      var L = D.wish.labels;
      rows.push(WKEYS.map(function (k) { return L[k]; }));
      wishes.filter(wishOk).forEach(function (w) { rows.push(WKEYS.map(function (k) { return typeof w[k] === 'boolean' ? (w[k] ? 'ano' : 'ne') : w[k]; })); });
      name = D.wish.name;
    } else if (S.tab === 'lent') {
      // výpůjčky: nejdřív objednávky (co si kdo kdy vyzvedne), pak to, co je půjčené teď
      rows.push(['stav', 'kdo', 'vyzvedne', 'v kolik', 'vrátit do', 'objednávka', 'věc', 'kolik', 'list', 'místo', 'v čem', 'poznámka k objednávce', 'kód']);
      visibleOrders().forEach(function (o) {
        o.items.forEach(function (l) {
          var it = itemBy.get(l.i), sec = secBy.get(it.s);
          rows.push([l.w ? 'objednáno, čeká, až se uvolní' : 'objednáno', o.who, fmtDate(o.from), o.at, fmtDate(o.due), o.no ? 'č. ' + o.no : '', it.n, l.q === null ? '' : fmtQ(l.q, it.u),
            sec.name, 'p1' in sec.f ? it.p1 : '', 'p2' in sec.f ? it.p2 : '', o.note, it.id]);
        });
      });
      lentLoans().forEach(function (x) {
        var it = x.it, l = x.l, sec = secBy.get(it.s);
        rows.push(['půjčeno', l.who, fmtDate(l.since), '', fmtDate(l.due), l.no ? 'č. ' + l.no : '', it.n, l.q === null ? '' : fmtQ(l.q, it.u), sec.name, 'p1' in sec.f ? it.p1 : '', 'p2' in sec.f ? it.p2 : '', '', it.id]);
      });
      missingItems().forEach(function (it) {
        var sec = secBy.get(it.s);
        rows.push(['chybí', '', '', '', '', '', it.n, '', sec.name, 'p1' in sec.f ? it.p1 : '', 'p2' in sec.f ? it.p2 : '', '', it.id]);
      });
      name = 'vypujcky';
    } else {
      // počet, jednotka, ceny a typ položky jdou nahrát zpátky; „volno“ a „půjčeno“ jsou jen pro čtení; pořizovací cena a typ položky jen ze správy
      rows.push(['list', 'věc', 'počet', 'jednotka', 'volno', 've skladu', 'půjčeno', 'výpůjční cena', 'za co'].concat(ADMIN ? ['pořizovací cena', 'typ položky'] : [],
        ['místo', 'v čem', 'označení', 'čí', 'kategorie', 'tagy', 'popis', 'foto', 'poznámka', 'kód']));
      var vis = visibleItems();
      D.sections.forEach(function (sec) {
        vis.filter(function (it) { return it.s === sec.id; }).forEach(function (it) {
          var g = function (k, v) { return k in sec.f ? v : ''; };
          var lent = it.l.map(function (l) { return l.who + ' ' + (l.q === null ? '(nezapsáno kolik)' : fmtQ(l.q, it.u)) + (l.due ? ' do ' + fmtDate(l.due) : ''); }).join(', ');
          rows.push([sec.name, it.n, it.q !== null ? it.q : it.qt, it.q !== null ? it.u : '', it.free !== null ? it.free : '', g('ok', it.ok === null ? '' : (it.ok ? 'ano' : 'ne')), lent,
            inPrice(it.rc), it.rc ? it.rn : ''].concat(ADMIN ? [inPrice(it.pc), it.ty && typeBy.has(it.ty) ? typeBy.get(it.ty).n : ''] : [], [g('p1', it.p1), g('p2', it.p2), g('mk', it.mk.join(', ')), g('own', it.own),
            g('cat', it.cat.join(', ')), it.tg.join(', '), it.kw, it.ph.map(function (p) { return p.u || 'nahraná fotka'; }).join(' '), g('note', it.note), it.id]));
        });
      });
      name = S.tab === 'all' ? 'sklad' : secBy.get(S.tab).name;
    }
    downloadCsv(rows, name + '-' + today());
  }

  /* ───────── adresa ───────── */

  var lastHash = '';
  function syncHash() {
    var p = [];
    if (S.tab !== 'all') p.push('t=' + encodeURIComponent(S.tab));
    if (S.q) p.push('q=' + encodeURIComponent(S.q));
    if (S.sort !== 'orig') p.push('s=' + S.sort);
    if (S.out) p.push('o=1');
    if (S.nocount) p.push('c=1');
    lastHash = p.length ? '#' + p.join('&') : '';
    if (location.hash !== lastHash) { try { history.replaceState(null, '', location.pathname + location.search + lastHash); } catch (e) {} }
  }
  function readHash() {
    var p = new URLSearchParams(location.hash.replace(/^#/, ''));
    var t = p.get('t') || 'all';
    S.tab = (t === 'all' || t === 'lent' || (t === 'wish' && D.wish.on) || secBy.has(t) || (t === 'set' && ADMIN)) ? t : 'all';
    S.q = p.get('q') || '';
    var s = p.get('s');
    S.sort = s === 'az' || s === 'place' ? s : 'orig';
    S.out = p.get('o') === '1';
    S.nocount = p.get('c') === '1';
    if (S.out || S.nocount) S.filt = true;
  }

  /* ───────── start ───────── */

  index();
  readHash();
  build();
  refresh();
  window.addEventListener('hashchange', function () { if (location.hash !== lastHash) { readHash(); el.search.value = S.q; el.clr.hidden = !S.q; refresh(); } });
  var skip = document.querySelector('.skip');
  if (skip) skip.addEventListener('click', function (e) { e.preventDefault(); el.list.tabIndex = -1; el.list.focus(); });     // bez změny adresy: ta nese list a hledání
  if (BOOT.tick) setTimeout(tick, 2500);
  document.addEventListener('visibilitychange', function () { resync(); botCheck(); });
  window.addEventListener('focus', function () { resync(); botCheck(); });
  window.addEventListener('pageshow', function (e) { if (e.persisted) { lastSync = 0; resync(); } });
  document.addEventListener('keydown', function (e) {
    if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey && !document.querySelector('dialog[open]') && !/^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName)) { e.preventDefault(); el.search.focus(); el.search.select(); }
  });
})();
#SK:seed
{
"v":4,
"site":{"title":"Sklad","fonts":true},
"marks":[],
"places":[],
"sections":[
{"id":"veci","name":"Věci","upd":"","note":"","nl":"věc","f":{"q":"počet","ok":"Je na místě?","p1":"místo","p2":"v čem","cat":"kategorie","kw":"popis","ph":"fotky","note":"poznámka"}}
],
"wish":{"on":false,"name":"Přání","upd":"","note":"","hint":"Napiš v pár slovech, k čemu to bude a proč se to hodí. Pomůže to při rozhodování i při žádostech o dotace."},
"items":[],
"wishes":[]
}
#SK:znacka
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 42" aria-hidden="true" focusable="false"><path d="M3 37V10h3.2l2.4-7h11.8l2.4 7H40v27z" fill="#e9d7a6"/><path d="M3 37V10h3.2l2.4-7h11.8l2.4 7H40" fill="none" stroke="#b89b59" stroke-width="1.6" stroke-linejoin="round"/><rect x="8.25" y="15.25" width="37.5" height="24.5" fill="#fff" stroke="#1c2430" stroke-width="2.5"/><path d="M12.5 21.5h29" stroke="#d9645a" stroke-width="2"/><path d="M12.5 27.5h29M12.5 33h19" stroke="#9fb5d1" stroke-width="2"/></svg>
#SK:pisma
{"pisma":[{"name":"archivo-400","family":"Archivo","weight":400,"data":"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"},{"name":"archivo-700","family":"Archivo","weight":700,"data":"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"},{"name":"zilla-slab-700","family":"Zilla Slab","weight":700,"data":"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"}],"licence":"Archivo: Copyright 2020 The Archivo Project Authors (https://github.com/Omnibus-Type/Archivo). Zilla Slab: Copyright 2017, The Mozilla Foundation. Obě písma jsou pod licencí SIL Open Font License 1.1 (OFL-Archivo.txt, OFL-ZillaSlab.txt); tady jsou jen znaky pro češtinu a okolní jazyky."}
