<?php

add_action('admin_menu', 'dcAdminPage');
// Add a new top level menu link to the ACP
function dcAdminPage() {
  add_menu_page(
    'Digicalculator Admin', // Title of the page
    'Digicalculator', // Text to show on the menu link
    'manage_options', // Capability requirement to see the link
    'digicalculator_admin',
    'digicalculator_adminPage',
    'dashicons-printer'
  );
};

function digicalculator_adminPage() {
  require_once plugin_dir_path(__FILE__) . '../pages/mfp-first-acp-page.php';
}