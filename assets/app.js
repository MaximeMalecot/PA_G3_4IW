/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// If you need Materialize's JS features you can import them here
import {Toast} from 'bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    const toastElList = document.querySelectorAll('.toast');
    const toastList = [...toastElList].map(toastEl => new Toast(toastEl, {}));
    toastList.forEach(toast => toast.show());
});

// import M from "materialize-css";
// window.M = M;
//
// document.addEventListener('DOMContentLoaded', function() {
//     const selects = document.querySelectorAll('select');
//     M.FormSelect.init(selects, null, {});
// });