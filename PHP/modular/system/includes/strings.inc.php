<?php

	/***********************************************\
	 * N2F Yverdon v0                              *
	 * Copyright (c) 2009 Zibings Incorporated     *
	 *                                             *
	 * You should have received a copy of the      *
	 * Microsoft Reciprocal License along with     *
	 * this program.  If not, see:                 *
	 * <http://opensource.org/licenses/ms-rl.html> *
	\***********************************************/

	/*
	 * $Id: strings.inc.php 162 2011-07-12 19:00:59Z amale@EPSILON $
	 */

	// Our global variable
	global $strings;

	// English strings
	$strings['en'] = array(
		// Error strings
		'N2F_ERROR_NO_LANGUAGE_SET'				=> "There is no language set for the system.",
		'N2F_ERROR_MODULE_FAILURE'				=> "The '_%1%_' module is not properly setup.",

		// Notice strings
		'N2F_NOTICE_N2FCLASS_LOADED'				=> "The N2 Framework's main class has been loaded.",
		'N2F_NOTICE_EXTENSION_LOADED'				=> "The '_%1%_' extension was loaded succesfully.",
		'N2F_NOTICE_MODULES_LOADED'				=> "The N2 Framework module extensions have been loaded.",
		'N2F_NOTICE_CORE_LOADED'					=> "The N2 Framework core has been loaded.",
		'N2F_NOTICE_MODULE_LOADED'				=> "The current module has been loaded and run.",
		'N2F_NOTICE_EVENT_ADDED'					=> "The '_%1%_' event was added to the \$n2f object.",
		'N2F_NOTICE_EVENT_TOUCHED'				=> "The '_%1%_' event was touched.",
		'N2F_NOTICE_LANG_KEY_SET'				=> "The '_%1%_' key has been added to the system's language set.",

		// Warning strings
		'N2F_WARN_EXTENSION_LOAD_FAILED'			=> "Failed to load the '_%1%_' extension.",
		'N2F_WARN_LANG_KEY_MISSING'				=> "The '_%1%_' key is missing from the system's language set.",
		'N2F_WARN_LANG_KEY_EXISTS'				=> "The '_%1%_' key already exists in the system's language set.",
		'N2F_WARN_EXISTING_EVENT'				=> "The '_%1%_' event already exists within the \$n2f object.",
		'N2F_WARN_NONEXISTANT_EVENT'				=> "The '_%1%_' event does not exist within the \$n2f object.",

		// Error code strings
		'N2F_ERRCODE_MODULE_FAILURE'				=> "The module you requested (<em>_%1%_</em>) is not installed or is prohibited from view.  Please check your link and try again.",
	);

	// German strings
	$strings['de'] = array(
		// Error strings
		'N2F_ERROR_NO_LANGUAGE_SET'				=> "Es wurde keine Sprache für das System ausgewählt.",
		'N2F_ERROR_MODULE_FAILURE'				=> "Das Modul '_%1%_' wurde nicht richtig eingerichtet.",

		// Notice strings
		'N2F_NOTICE_N2FCLASS_LOADED'				=> "Die N2 Framework-Hauptklasse wurde geladen.",
		'N2F_NOTICE_EXTENSION_LOADED'				=> "Die Erweiterung '_%1%_' wurde erfolgreich geladen.",
		'N2F_NOTICE_MODULES_LOADED'				=> "Die N2 Framework-Modulerweiterungen wurden geladen.",
		'N2F_NOTICE_CORE_LOADED'					=> "Der N2 Framework-Kern wurde geladen.",
		'N2F_NOTICE_MODULE_LOADED'				=> "Das aktuelle Modul wurde geladen und ausgeführt.",

		// Warning strings
		'N2F_WARN_EXTENSION_LOAD_FAILED'			=> "Laden der Erweiterung '_%1%_' fehlgeschlagen.",
		'N2F_WARN_LANG_KEY_MISSING'				=> "Der Schlüssel '_%1%_' fehlt im Sprachsatz des Systems.",

		// Error code strings
		'N2F_ERRCODE_MODULE_FAILURE'				=> "The module you requested (<em>_%1%_</em>) is not installed or is prohibited from view.  Please check your link and try again.",
	);

	// Spanish strings
	$strings['es'] = array(
		// Error strings
		'N2F_ERROR_NO_LANGUAGE_SET'				=> "No hay un lenguaje establecido para el sistema.",
		'N2F_ERROR_MODULE_FAILURE'				=> "El módulo '_%1%_' no está configurado correctamente.",

		// Notice strings
		'N2F_NOTICE_N2FCLASS_LOADED'				=> "La clase principal del N2 Framework ha sido cargada.",
		'N2F_NOTICE_EXTENSION_LOADED'				=> "La extensión '_%1%_' fue cargada con éxito.",
		'N2F_NOTICE_MODULES_LOADED'				=> "Las extensiones de módulo del N2 Framework han sido cargadas.",
		'N2F_NOTICE_CORE_LOADED'					=> "El núcleo del N2 Framework ha sido cargado.",
		'N2F_NOTICE_MODULE_LOADED'				=> "Se ha cargado y ejecutado el módulo actual.",

		// Warning strings
		'N2F_WARN_EXTENSION_LOAD_FAILED'			=> "Fallo al cargar la extensión '_%1%_'.",
		'N2F_WARN_LANG_KEY_MISSING'				=> "No se encuentra la llave '_%1%_' para el idioma del sistema.",

		// Error code strings
		'N2F_ERRCODE_MODULE_FAILURE'				=> "The module you requested (<em>_%1%_</em>) is not installed or is prohibited from view.  Please check your link and try again.",
	);

?>