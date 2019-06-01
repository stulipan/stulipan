<template>
</template>

<script>
    export default {
        methods: {
            showToast (message, type) {
                let title = '';
                switch (type) {
                    case 'success':
                        title = 'SIKERES MŰVELET';
                        break;
                    case 'danger':
                        title = 'HIBA!';
                        break;
                    case 'warning':
                        title = 'FIGYELEM!';
                        break;
                    default:
                        title = 'FIGYELEM!';
                        break;
                }
                this.$bvToast.toast(message, {
                    title: title,
                    autoHideDelay: 5000,
                    appendToast: true,
                    variant: type,
                    toaster: "b-toaster-bottom-left",
                });
            }
        }
    }
</script>