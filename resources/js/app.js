import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';
import './alerts.js';

import axios from 'axios';

import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { Spanish } from 'flatpickr/dist/l10n/es.js';

window.TomSelect = TomSelect;

window.axios = axios;

window.flatpickr = flatpickr;
window.flatpickrLocales = { Spanish };

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
