<template>
        <div class="row">
            <div class="col-lg-12">
                <div class="cart bg-white shadow-none">
                    <div class="cart-wrapper">
                        <div class="checkout-block-header px-2 px-md-3">
                            <h4>A virág mellé válassz ajándékot</h4>
                        </div>
                        <div class="block-body px-2 px-md-4">
                            <div class="row">
                                <div class="col-lg-12 mb-0">
                                </div>
                            </div>
                            <div v-if="gifts" class="row row-narrow">
                                <template v-for="gift in gifts">
                                    <div class="col-xl-2 col-lg-2 col-md-3 col-4 col-narrow">
                                        <div class="product"
                                             @click.prevent="addGiftToCart(gift)"
                                        >
                                            <div class="product-image">
                                                <img :src="gift.images[0].imageUrl" :alt="gift.name" class="img-fluid">
                                                <a href="#" class="tile-link product-hover-overlay-link">
                                                </a>
                                            </div>
                                            <div class="py-2 text-center">
                                                <p class="productList--name mb-1"><a href="#" class="text-dark">{{ gift.name }}</a></p>
                                                <span class="productList--price-gross">{{ gift.price.numericValue }} Ft</span>
                                            </div>
                                            <div class="text-center">
                                                <a href="#" class="btn btn-sm btn-outline-secondary" title="Kiválaszt">
                                                    Kiválaszt
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
</template>

<script>
    const initialData = () => {
        return {
        }
    };

    export default {
        props: [
            'gifts',
        ],
        components: {
        },
        data: initialData,
        methods: {
            addGiftToCart(gift) {
                this.$emit('selectGift', gift);
            }
        },
        created () {
        },
    }
</script>

<style scoped>
</style>

