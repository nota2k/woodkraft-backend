<template>
    <div>
        <div class="mb-6">
            <router-link to="/admin/products" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                ← Retour à la liste
            </router-link>
            <h1 class="text-3xl font-bold">{{ isEdit ? 'Modifier le produit' : 'Nouveau produit' }}</h1>
        </div>

        <form @submit.prevent="submitForm" class="bg-white rounded-lg shadow p-6 space-y-6">
            <!-- Message d'erreur -->
            <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ error }}
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                <input
                    v-model="form.title"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prix *</label>
                    <input
                        v-model.number="form.price"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantité *</label>
                    <input
                        v-model.number="form.quantity"
                        type="number"
                        min="0"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Référence *</label>
                <input
                    v-model="form.reference"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                <textarea
                    v-model="form.description"
                    rows="4"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Matériaux</label>
                    <input
                        v-model="form.materials"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dimensions</label>
                    <input
                        v-model="form.dimensions"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </div>

            <!-- Upload d'images -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Images du produit</label>
                <p class="text-xs text-gray-500 mb-2">La première image sera l'image par défaut</p>
                
                <!-- Zone d'upload -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center mb-4">
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        multiple
                        @change="handleFileSelect"
                        class="hidden"
                    />
                    <button
                        type="button"
                        @click="triggerFileInput"
                        class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg transition-colors"
                    >
                        + Ajouter des images
                    </button>
                </div>

                <!-- Prévisualisation des images -->
                <div v-if="images.length > 0" class="grid grid-cols-4 gap-4">
                    <div
                        v-for="(image, index) in images"
                        :key="index"
                        class="relative group"
                    >
                        <div class="relative">
                            <img
                                :src="image.url || image.image_path"
                                :alt="`Image ${index + 1}`"
                                class="w-full h-32 object-cover rounded-lg border-2"
                                :class="index === 0 ? 'border-blue-500' : 'border-gray-300'"
                            />
                            <div
                                v-if="index === 0"
                                class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded"
                            >
                                Par défaut
                            </div>
                            <button
                                type="button"
                                @click="removeImage(index)"
                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                ×
                            </button>
                        </div>
                        <div class="mt-2 flex gap-2">
                            <button
                                v-if="index > 0"
                                type="button"
                                @click="moveImage(index, -1)"
                                class="text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded"
                            >
                                ↑
                            </button>
                            <button
                                v-if="index < images.length - 1"
                                type="button"
                                @click="moveImage(index, 1)"
                                class="text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded"
                            >
                                ↓
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <button
                    type="submit"
                    :disabled="loading"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                >
                    {{ loading ? 'Enregistrement...' : 'Enregistrer' }}
                </button>
                <router-link
                    to="/admin/products"
                    class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                >
                    Annuler
                </router-link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { productService, imageService } from '../../services/api';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const uploadingImages = ref(false);
const isEdit = computed(() => !!route.params.id);
const fileInput = ref(null);

const form = ref({
    title: '',
    price: 0,
    description: '',
    reference: '',
    materials: '',
    dimensions: '',
    quantity: 0,
});

const images = ref([]);

const loadProduct = async () => {
    if (isEdit.value) {
        try {
            const response = await productService.getById(route.params.id);
            const product = response.data;
            form.value = {
                title: product.title,
                price: product.price,
                description: product.description,
                reference: product.reference,
                materials: product.materials || '',
                dimensions: product.dimensions || '',
                quantity: product.quantity,
            };
            // Charger les images existantes
            if (product.images && product.images.length > 0) {
                images.value = product.images.map(img => ({
                    url: img.image_path,
                    image_path: img.image_path,
                }));
            }
        } catch (error) {
            console.error('Erreur lors du chargement du produit:', error);
        }
    }
};

const triggerFileInput = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};

const handleFileSelect = async (event) => {
    const files = Array.from(event.target.files);
    uploadingImages.value = true;
    
    try {
        for (const file of files) {
            const response = await imageService.upload(file);
            images.value.push({
                url: response.data.url,
                image_path: response.data.url,
            });
        }
    } catch (error) {
        console.error('Erreur lors de l\'upload:', error);
        error.value = 'Erreur lors de l\'upload des images';
    } finally {
        uploadingImages.value = false;
        // Réinitialiser l'input
        if (fileInput.value) {
            fileInput.value.value = '';
        }
    }
};

const removeImage = (index) => {
    images.value.splice(index, 1);
};

const moveImage = (index, direction) => {
    const newIndex = index + direction;
    if (newIndex >= 0 && newIndex < images.value.length) {
        const temp = images.value[index];
        images.value[index] = images.value[newIndex];
        images.value[newIndex] = temp;
    }
};

const error = ref(null);

const submitForm = async () => {
    loading.value = true;
    error.value = null;
    try {
        // Préparer les données avec les images
        const data = {
            ...form.value,
            images: images.value.map(img => img.image_path || img.url),
        };
        
        console.log('Soumission du formulaire:', data);
        if (isEdit.value) {
            const response = await productService.update(route.params.id, data);
            console.log('Produit mis à jour:', response);
        } else {
            const response = await productService.create(data);
            console.log('Produit créé:', response);
        }
        router.push('/admin/products');
    } catch (error) {
        console.error('Erreur lors de l\'enregistrement:', error);
        if (error.response && error.response.data) {
            const errors = error.response.data.errors || error.response.data.message;
            error.value = typeof errors === 'string' ? errors : 'Erreur de validation. Vérifiez les champs.';
        } else {
            error.value = 'Erreur lors de l\'enregistrement du produit. Vérifiez la console pour plus de détails.';
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadProduct();
});
</script>

