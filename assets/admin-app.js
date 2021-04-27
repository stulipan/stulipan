/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';


import Notify from "./js/alerts/notify";
import Sidebar from "./js/sidebar";
import FloatingInput from "./js/floating-input";
import AdaptiveTabs from "./js/adaptive-tabs";

if (AlertMessages.SUCCESS) {
    Notify.success(AlertMessages.SUCCESS);
}
if (AlertMessages.ERROR) {
    Notify.error(AlertMessages.ERROR);
}
if (AlertMessages.WARNING) {
    Notify.warning(AlertMessages.WARNING);
}
if (AlertMessages.INFO) {
    Notify.info(AlertMessages.INFO);
}