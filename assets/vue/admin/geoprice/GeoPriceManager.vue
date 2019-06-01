<template>
    <div class="row mb-1">
        <div v-if="dataIsLoading" class="col-lg-6" style="width: 400px;">
            <list-loader
                            :speed="2"
                            :animate="true"
                            :width="400"
                            :height="460"
                            :preserveAspectRatio="'xMidYMid meet'"
            ></list-loader>
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
                        <div class="d-flex flex-row flex-grow-1X justify-content-between align-items-start w-100">
                            <div class="col-6X w-100 d-flex justify-content-lg-start mb-2">
                                <select
                                        v-model="selectedCities"
                                        id="whereTo" name="whereTo"
                                        class="form-control d-inline-flex text-sm pt-1X"
                                        required="required"
                                        multiple
                                        size="18"
                                >
                                    <template v-for="item in cities" track-by="id">
                                        <option :value="item.id">{{item.city}} - {{item.zip}} {{item.district ? ' - ' + item.district + ' kerület' : ''}} {{ item.price ? ' - ' + item.price.price.value + ' Ft' : ''}}</option>
                                    </template>
                                </select>
                            </div>
                            <div class="d-flex justify-content-lg-center align-self-center px-2">
                                <button @click.prevent="addCities(selectedCities)" class="btn btn-primary" formnovalidate="">
                                    <!--type="submit" -->
                                    <i class="fas fa-long-arrow-alt-right"></i>
                                </button>
                            </div>
                            <div class="col-6X w-100 d-flex justify-content-end flex-column mb-2">
                                <form>
                                    <div class="w-100 mb-2">
                                        <!--<input v-model="citiesWithPrice" type="hidden">-->
                                        <select
                                                id="whereTo" name="whereTo"
                                                class="form-control d-inline-flex text-sm pt-1X"
                                                required="required"
                                                multiple
                                                size="5"
                                        >
                                            <!--<option value=""> - Település: Budakalász, vagy Irányítószám: 2011 - </option>-->
                                            <option disabled value="">Please select one</option>
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
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <button @click.prevent="saveForm(addedCities, price, $event)" class="btn btn-primary mr-1" formnovalidate="">{{ price ? 'Mentés' : 'Hozzáadás' }}</button>
                                                <a href="#" v-if="addedCities" @click.prevent="onCancelClicked" class="btn btn-outline-secondary">Mégse</a>
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
    import { ListLoader } from 'vue-content-loader'

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
    }

    export default {
        components: {
            Multiselect,
            ListLoader,
        },
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
                this.$http.post('/admin/api/geoplace/price/', citiesWithPrice)
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
                this.$http.get(`/admin/api/geoplace/cities/?province=${province}`)
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
                this.$http.get(`/admin/api/geoplace/provinces/`)
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

