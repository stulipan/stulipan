<template>
    <div class="row mb-1">
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
        <template v-else>
            <div class="col-12">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <label class="col-form-label required">Válassz megyét:</label>
                        <multiselect
                                v-model="provinceInForm"
                                :options="provinces"
                                :custom-label="showProvinceName"
                                placeholder="Válassz..."
                                track-by="province"
                                :showLabels="false"
                                :loading="dataIsLoading"
                                class="mb-2"
                        >
                            <span slot="noResult">Upsz! Nincs ilyen megye vagy elírtad...</span>
                        </multiselect>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label class="col-form-label required">Mozgasd át a településeket balról jobbra, majd add meg a szállítási díjat:</label>
                    </div>
                    <div class="col-12">
                        <div class="d-flex flex-row flex-grow-1X justify-content-between align-items-stretch">
                            <div class="col-6X w-100 d-flex justify-content-lg-start mb-2">
                                <select
                                        v-model="selectedCities"
                                        id="whereTo" name="whereTo"
                                        class="form-control form-control-sm"
                                        required="required"
                                        multiple
                                        size="11"
                                >
                                    <template v-for="item in cities" track-by="id">
                                        <option :value="item.id">{{item.city}} - {{item.zip}} {{item.district ? ' - ' + item.district + ' kerület' : ''}} {{ item.price ? ' - ' + item.price.price.value + ' Ft' : ''}}</option>
                                    </template>
                                </select>
                            </div>
                            <div class="d-flex justify-content-lg-center align-self-center px-2">
                                <button @click.prevent="addCities(selectedCities)" class="btn btn-dark" formnovalidate="">
                                    <!--type="submit" -->
                                    <i class="fas fa-long-arrow-alt-right mr-0"></i>
                                </button>
                            </div>
                            <div class="col-6X w-100 d-flex flex-column mb-2">
                                <form>
                                    <div class="w-100 mb-2">
                                        <!--<input v-model="citiesWithPrice" type="hidden">-->
                                        <select
                                                id="whereTo" name="whereTo"
                                                class="form-control form-control-sm"
                                                required="required"
                                                multiple
                                                size="6"
                                        >
                                            <template v-for="item in addedCities" track-by="id">
                                                <option :value="item.id">{{item.city}} - {{item.zip}}</option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="mb-2X">
                                        <div class="form-group row">
                                            <div class="col-sm-12 col-md-8">
                                                <label class="col-form-label required" for="price">Szállítási díj</label>
                                                <input v-model="price" type="number" id="price" required="required" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2X">
                                        <div class="form-group row mb-0">
                                            <div class="col-sm-12">
                                                <button @click.prevent="saveForm(addedCities, price, $event)" class="btn btn-info mr-1" formnovalidate="">{{ price ? 'Mentés' : 'Hozzáadás' }}</button>
<!--                                                @click.prevent="onCancelClicked"-->
                                                <a v-if="addedCities" :href="backUrl" class="btn btn-secondary">Mégse</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect'

    const initialData = () => {
        return {
            dataIsLoading: true,
            selectedCities: [],
            cities: [],
            addedCities: [],
            formErrors: [],
            resetForm: false,  // If true will hide form errors and uploaded images preview
            price: null,
            citiesWithPrice: {
                cities: [],
                price: null,
            },
            provinceInForm: '',
            provinces: [],
            i: 0,
        }
    };

    export default {
        components: {
            Multiselect,
        },
        props: [
            'backUrl',
        ],
        data: initialData,
        watch: {
            provinceInForm: {
                handler: function () {
                    this.getCityList(this.provinceInForm.province)
                }
            }
        },
        methods: {
            showProvinceName (provinceObj) {
                return `${provinceObj.province}`
            },
            addCities (selCities) {
                let i = '';
                for (i in selCities) {
                    const index = this.cities.findIndex((c) => c.id === selCities[i]);
                    // if found
                    if (index !== -1) {
                        let cityObj = this.cities[index];
                        if (!this.addedCities.includes(cityObj)) {
                            // includes() is not supported Edge13 and earlier
                            this.addedCities.push(cityObj);
                        }
                    } else {
                        console.log ('City not found!')
                    }
                }
            },
            saveForm (addedCities, price, e) {
                const citiesWithPrice = {};
                citiesWithPrice.cities = addedCities;
                citiesWithPrice.price = price;
                this.$http.post('/hu/admin/api/geoplace/price/', citiesWithPrice)
                            .then(
                                response => {
                                    this.resetDataInForm();
                                    this.showToast('Szállítási díjak rögzítve!', 'success');
                                    this.getCityList(this.provinceInForm.province);
                                },
                                response => {
                                    if (response.status === 422) {
                                        for (let value of Object.values(response.body.errors)) {
                                            console.log(response.body.errors);
                                            this.formErrors.push(value.message);
                                            this.showToast(value.message, 'danger');
                                        }
                                    }
                                    if (!this.formErrors.length) {
                                        this.resetDataInForm()
                                    }
                                });
            },
            resetDataInForm () {
                this.selectedCities = initialData().selectedCities;
                this.addedCities = initialData().addedCities;
                this.price = initialData().price;
                this.citiesWithPrice = initialData().citiesWithPrice;
                this.formErrors = initialData().formErrors;
                this.resetForm = true;  // azert vettem ki, mert ugyis a kategoria valtozast figyeli
            },
            getCityList (province) {
                this.$http.get(`/hu/admin/api/geoplace/cities/?province=${province}`)
                    .then(response => {
                        this.cities = response.data.cities;
                        this.dataIsLoading = false;
                    }, response => {
                        if (response.status === 422) {
                            for (let value of Object.values(response.body.errors)) {
                                console.log(value.message);
                                this.showToast(value.message, 'danger');
                            }
                        }
                    });
            },
            onCancelClicked () {
                this.$emit('click');
            },
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
            },
            getProvinceList () {
                this.$http.get(`/hu/admin/api/geoplace/provinces/`)
                    .then(response => {
                        this.provinces = response.data.provinces;
                        this.dataIsLoading = false;
                    }, response => {
                        if (response.status === 422) {
                            for (let value of Object.values(response.body.errors)) {
//                            this.formErrors.push(value);
                                console.log(value);
                            }
                        }
                    });
            },
        },
        created () {
            this.getProvinceList();
        },
    }
</script>

<!--<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>-->
<style scoped>
    select option {
        padding: .45rem 0.125rem !important;
        border-bottom: 1px solid #ccd4da;
    }
</style>

