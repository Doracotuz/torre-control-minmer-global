import './bootstrap';

import Alpine from 'alpinejs';

import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

import OrgChart from 'orgchart';

window.Alpine = Alpine;

Alpine.start();

window.OrgChart = OrgChart;