import { Controller } from 'stimulus';
// import Swal from 'sweetalert2';
import Confirm from '../../assets/js/alerts/confirm';
import axios from 'axios'

export default class extends Controller {
    static values = {
        title: String,
        text: String,
        icon: String,
        iconHtml: String,
        confirmButtonText: String,
        cancelButtonText: String,
        isAjaxCall: Boolean,
    }

    submitForm(e) {
        e.preventDefault();
        Confirm.fire({
            title: this.titleValue || null,
            text: this.textValue || null,
            icon: this.iconValue || null,
            iconHtml: this.iconHtmlValue || null,
            confirmButtonText: this.confirmButtonTextValue || 'Remove',
            cancelButtonText: this.cancelButtonTextValue || 'Cancel',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return this.submitForm_();
            }
        });
    }

    submitForm_() {
        if (!this.isAjaxCallValue) {
            return;
        }
        return axios.get(this.element.dataset.url);
    }

}
