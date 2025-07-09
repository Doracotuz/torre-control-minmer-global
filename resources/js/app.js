import './bootstrap';

import Alpine from 'alpinejs';

// ¡Asegúrate de que jQuery esté disponible globalmente antes de importar OrgChart!
// Esto es CRÍTICO para los plugins de jQuery.
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

import OrgChart from 'orgchart';

window.Alpine = Alpine;

Alpine.start();

window.OrgChart = OrgChart; // Esto hace global el objeto OrgChart, no el plugin de jQuery.