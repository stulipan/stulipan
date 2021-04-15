// import Swal from 'sweetalert2';
import Swal from 'sweetalert2/src/sweetalert2'

class Toast {
    constructor() {
        this.swal = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        return this.swal;
    }
}

export default new Toast();