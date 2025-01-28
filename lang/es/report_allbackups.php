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
 * @copyright   2020 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allbackups:delete'] = 'Eliminar copias de seguridad';
$string['allbackups:view'] = 'Ver informe de todas las copias de seguridad';
$string['areyousurebulk'] = '¿Está seguro de que desea eliminar los {$a} archivo(s) seleccionados?';
$string['autobackup'] = 'Copias de seguridad automáticas almacenadas en el directorio especificado del servidor';
$string['autobackup_description'] = 'Este informe muestra todos los archivos *.mbz (archivos de copia de seguridad de Moodle) almacenados en el directorio especificado en la configuración de copias de seguridad automáticas.';
$string['autobackupnotset'] = 'El destino de la copia de seguridad automática no está configurado - no puede usar esta función';
$string['component'] = 'Componente';
$string['couldnotdeletefile'] = 'No se pudo encontrar el archivo con id: {$a}';
$string['couldnotdownloadfile'] = 'No se pueden descargar todos los archivos de copia de seguridad';
$string['downloadallselectedfiles'] = 'Descargar archivos seleccionados';
$string['coursecategory'] = 'Categoría del curso';
$string['deleteselectedfiles'] = 'Eliminar archivos seleccionados';
$string['eventautobackupdeleted'] = 'Se eliminó un archivo de copia de seguridad automática';
$string['eventbackupdeleted'] = 'Se eliminó un archivo de copia de seguridad';
$string['eventreportdownloaded'] = 'Informe de todas las copias de seguridad descargado';
$string['eventreportviewed'] = 'Informe de todas las copias de seguridad visto';
$string['filearea'] = 'Área de archivos';
$string['filename'] = 'Nombre del archivo';
$string['filesdeleted'] = 'Se eliminaron {$a} archivo(s)';
$string['plugindescription'] = 'Este informe muestra todos los archivos *.mbz (archivos de copia de seguridad de Moodle) en su sitio, tenga en cuenta que después de eliminar un archivo, Moodle puede tardar hasta 4 días en eliminar el archivo del almacenamiento en disco.';
$string['pluginname'] = 'Todas las copias de seguridad';
$string['privacy:metadata'] = 'El plugin de informe de todas las copias de seguridad no almacena ningún dato personal';
$string['standardbackups'] = 'Copias de seguridad estándar';
$string['recordsperpage'] = 'Registros por página';