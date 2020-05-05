<template>
    <div>
        <div v-if="isDataLoading" class="d-flex justify-content-center w-100 h-100" style="z-index: 15000; opacity: 1;">
            <div class="d-flex justify-content-center h-100 mt-5 align-items-center">
                <div>
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div class="d-flex justify-content-center mt-2">Tartalom betöltése...</div>
                </div>
            </div>
        </div>
        <multiselect v-else
                     v-model="cityInForm"
                     :options="cities"
                     :custom-label="cityWithZip"
                     placeholder=""
                     track-by="id"
                     :showLabels="false"
                     :loading="isDataLoading"
                     class="mb-2"
        >
            <span slot="noResult">Upsz! Nincs ilyen település vagy elírtad...</span>
            <span slot="noOptions">Upsz! {{ dataLoadingErrors[0] }}...</span>
        </multiselect>
        <span v-if="cityInForm.city" class="text-sm">Szállítási díj: <em>{{ cityInForm.price.value }} Ft</em></span>
    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect'

    const initialData = () => {
        return {
            isDataLoading: true,
            dataLoadingErrors: [],
            cityInForm: {},
            cities: [],
        }
    };

    export default {
        components: {
            Multiselect,
        },
        data: initialData,
        methods: {
            cityWithZip(cityObj) {
                if (!cityObj.city) {
                    return 'Település vagy Irányítószám'
                } else return `[${cityObj.city.zip}] ${cityObj.city.city}`
//                return '[' + zip + ']  ' + city  // ugyanaz mint: return `[${zip}] ${city}`
            },
        },
        created () {
            this.$http.get('/hu/admin/api/geoplace/prices/')
                .then(response => {
                    this.cities = response.data.cities;
                    this.isDataLoading = false;
                }, response => {
                    if (response.status === 422) {
                        for (let item of Object.values(response.body.errors)) {
                            this.dataLoadingErrors.push(item.message);
                        }
                    }
                    this.isDataLoading = false;
                });
        },
    }
</script>

<!--<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>-->
<style scoped>
</style>

