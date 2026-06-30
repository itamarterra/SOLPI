<?php

define('PLUGIN_SOLPI_VERSION', '1.0.0');

function plugin_version_solpi() {
   return [
      'name'         => 'SOLPI',
      'version'      => PLUGIN_SOLPI_VERSION,
      'author'       => 'Itamar Terra',
      'license'      => 'GPLv2+',
      'homepage'     => '',
      'requirements' => [
         'glpi' => [
            'min' => '11.0.0'
         ]
      ]
   ];
}

function plugin_init_solpi() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['solpi'] = true;
}

function plugin_solpi_check_prerequisites() {
   return true;
}

function plugin_solpi_check_config() {
   return true;
}

function plugin_solpi_install() {
   return true;
}

function plugin_solpi_uninstall() {
   return true;
}