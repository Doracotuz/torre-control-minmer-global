import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

import OrgChart from 'orgchart';
Alpine.plugin(collapse);
window.Alpine = Alpine;

Alpine.start();

window.OrgChart = OrgChart;