import './bootstrap';

import {
    Chart, BarController, LineController, BarElement, LineElement,
    PointElement, CategoryScale, LinearScale, Tooltip, Legend,
} from 'chart.js';

Chart.register(
    BarController, LineController, BarElement, LineElement,
    PointElement, CategoryScale, LinearScale, Tooltip, Legend,
);  

const pln = new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN' });