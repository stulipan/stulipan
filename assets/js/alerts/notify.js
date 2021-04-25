import Swal from 'sweetalert2/src/sweetalert2'

const Notify = (function() {
    const swal = Swal.mixin({
        toast: true,
        position: 'bottom',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: false,
        // padding: '1rem',
        showCloseButton: true,
        buttonsStyling: false,
    });

    function success(message, icon = false) {
        const alert = swal.mixin({
            customClass: {
                popup: '--alert-success',
                content: '--alert-content',
            }
        });
        alert.fire({
            icon: icon ? 'success' : '',
            title: message
        });
    }

    function error(message, icon = false) {
        const alert = swal.mixin({
            customClass: {
                popup: '--alert-danger',
                content: '--alert-content',
            }
        });
        alert.fire({
            icon: icon ? 'error' : '',
            title: message
        });
    }

    function warning(message, icon = false) {
        const alert = swal.mixin({
            customClass: {
                popup: '--alert-warning',
                content: '--alert-content',
            }
        });
        alert.fire({
            icon: icon ? 'warning' : '',
            title: message
        });
    }

    function info(message, icon = false) {
        const alert = swal.mixin({
            customClass: {
                popup: '--alert-info',
                content: '--alert-content',
            }
        });
        alert.fire({
            icon: icon ? 'info' : '',
            title: message
        });
    }

    return {
        success: success,
        error: error,
        warning: warning,
        info: info,
    };
})();

export default Notify;