import Swal from 'sweetalert2/src/sweetalert2'

class Confirm {
    constructor() {
        this.swal = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-primary mr-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        });
        return this.swal;
    }
}

export default new Confirm();