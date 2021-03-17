<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     report_allbackups
 * @category    string
 * @copyright   2021 ARNES
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allbackups:delete'] = 'Izbriši varnostne kopije';
$string['allbackups:view'] = 'Ogled poročila vseh varnostnih kopij';
$string['areyousurebulk'] = 'Ali ste prepričani, da želite izbrisati izbrane varnostne kopije? Število izbranih varnostnih kopij: {$a}';
$string['autobackup'] = 'Samodejno izdelane varnostne kopije, shranjene v navedeni mapi na strežniku';
$string['autobackup_description'] = 'To poročilo prikaže vse datoteke s končnico .mbz (Moodle varnostne kopije), shranjene v mapi, ki je določena v nastavitvah samodejnih varnostnih kopij.';
$string['autobackupnotset'] = 'Ciljna mapa samodejnih varnostnih kopij ni nastavljena - te funkcionalnosti ne morete uporabljati.';
$string['component'] = 'Komponenta';
$string['couldnotdeletefile'] = 'Datoteke z ID: {$a} ni bilo mogoče najti.';
$string['coursecategory'] = 'Kategorija predmeta';
$string['deleteselectedfiles'] = 'Izbriši izbrane datoteke';
$string['eventautobackupdeleted'] = 'Samodejno ustvarjena varnostna kopija je bila izbrisana.';
$string['eventbackupdeleted'] = 'Varnostna kopija je bila izbrisana.';
$string['eventreportdownloaded'] = 'Poročilo o vseh varnostnih kopijah prenešeno';
$string['eventreportviewed'] = 'Poročilo o vseh varnostnih kopijah ogledano';
$string['filearea'] = 'Območje datoteke';
$string['filename'] = 'Ime datoteke';
$string['filesdeleted'] = 'Število izbrisanih datotek: {$a}';
$string['plugindescription'] = 'Poročilo prikaže vse datoteke *.mbz (Moodle varnostne kopije) na strani, prosimo, upoštevajte, da lahko po izbrisu datoteke le-ta obstaja na Moodle strani še nekaj dni, preden se dokončno izbriše iz datotečnega sistema..';
$string['pluginname'] = 'Vse varnostne kopije';
$string['privacy:metadata'] = 'Vtičnik za poročilo o vseh varnostnih kopijah ne hrani nobenih osebnih podatkov.';
$string['standardbackups'] = 'Standardne varnostne kopije';
$string['settings_categorybackupmgmt'] = 'Način upravljanja varnostnih kopij po kategorijah';
$string['categorybackupmgmtmode'] = 'Način upravljanja varnostnih kopij po kategorijah?';
$string['categorybackupmgmtmode_desc'] = 'Upravljanje z načinom delovanja vtičnika. Obkljukajte to možnost za omogočanje delegacije upravljanja z varnostnimi kopijami na nivoju kategorije. Funkcionalnost bo omogočena uporabnikom z vlogo, katera ima kot arhetip vlogo <b>Upravitelj</b>.';
$string['mdlbkponly'] = 'Samo varnostne kopije te Moodle strani';
$string['mdlbkponly_desc'] = 'Vključi samo varnostne kopije, ustvarjene na tej Moodle instanci';
$string['enableactivities'] = 'V seznam vključi tudi varnostne kopije dejavnosti';
$string['enableactivities_desc'] = 'V seznamu vseh varnostnih kopij vključi tudi varnostne kopije posameznih dejavnosti. Za vključitev te možnosti, mora biti omogočena nastavitev <i>"Samo varnostne kopije te Moodle strani"</i>.';
$string['error:categorybackupmgmtmodedisabled'] = 'Način upravljanja varnostnih kopij po kategorijah je onemogočen.';
