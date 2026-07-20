<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';

// Static placeholder data — swap for Inertia page props once the backend is wired up
const gcashDetails = reactive({
    account_name: 'Juan Dela Cruz',
    account_number: '0917 123 4567',
    instructions:
        'Send your payment to the GCash account above, then upload a screenshot of your receipt as proof of payment.',
});

const gcashDetailsDraft = reactive({ ...gcashDetails });

const qr = reactive({
    image_url: 'https://placehold.co/400x400?text=GCash+QR',
});

const isEditingDetails = ref(false);
const isEditingQr = ref(false);
const qrPreview = ref<string | null>(null);
const qrFile = ref<File | null>(null);

function startEditDetails() {
    Object.assign(gcashDetailsDraft, gcashDetails);
    isEditingDetails.value = true;
}

function cancelEditDetails() {
    isEditingDetails.value = false;
}

function saveDetails() {
    // TODO: replace with Inertia form submission, e.g.
    // router.put(route('admin.gcash.details.update'), gcashDetailsDraft)
    Object.assign(gcashDetails, gcashDetailsDraft);
    isEditingDetails.value = false;
}

function startEditQr() {
    qrPreview.value = null;
    qrFile.value = null;
    isEditingQr.value = true;
}

function cancelEditQr() {
    qrPreview.value = null;
    qrFile.value = null;
    isEditingQr.value = false;
}

function onQrFileChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    qrFile.value = file;
    qrPreview.value = URL.createObjectURL(file);
}

function saveQr() {
    // TODO: replace with Inertia form submission (multipart/form-data), e.g.
    // const form = useForm({ qr_image: qrFile.value })
    // form.post(route('admin.gcash.qr.update'))
    if (qrPreview.value) {
        qr.image_url = qrPreview.value;
    }
    isEditingQr.value = false;
}
</script>

<template>
    <Head title="Gcash" />

    <AdminLayout>
            <div class="mx-auto bg-white p-4 rounded-lg">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">
                        <font-awesome-icon icon="fa-solid fa-money-bill-wave" />
                        GCash
                    </h1>
                    <p class="mt-1  text-gray-500">
                        Manage the GCash account details and QR code shown to customers at checkout.
                    </p>
                </div>
                <hr class="my-2">
                <div class="grid grid-cols-1 gap-2">
                    <!-- GCash Details Card -->
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-200 bg-ink/5 px-4 py-2">
                            <h2 class="font-semibold text-gray-900">GCash Details</h2>

                            <button
                                v-if="!isEditingDetails"
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-md px-2 py-1  font-medium border border-blue-600 text-blue-600 hover:bg-blue-50"
                                @click="startEditDetails"
                            >
                                <font-awesome-icon icon="fa-solid fa-edit" />
                                Edit
                            </button>

                            <div v-else class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-md px-2 py-1  font-medium text-gray-500 hover:bg-gray-50"
                                    @click="cancelEditDetails"
                                >
                                    <font-awesome-icon icon="fa-solid fa-xmark" />
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-2 py-1  font-medium text-white hover:bg-blue-700"
                                    @click="saveDetails"
                                >
                                    <font-awesome-icon icon="fa-solid fa-check" />
                                    Save
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4 p-4">
                            <!-- Read-only view -->
                            <template v-if="!isEditingDetails">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                        Account Name
                                    </p>
                                    <p class="mt-1  text-gray-900">{{ gcashDetails.account_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                        Account Number
                                    </p>
                                    <p class="mt-1  text-gray-900">{{ gcashDetails.account_number }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                        Instructions
                                    </p>
                                    <p class="mt-1  leading-relaxed text-gray-600">
                                        {{ gcashDetails.instructions }}
                                    </p>
                                </div>
                            </template>

                            <!-- Edit form -->
                            <template v-else>
                                <div>
                                    <label class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                        Account Name
                                    </label>
                                    <input
                                        v-model="gcashDetailsDraft.account_name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300  shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                        Account Number
                                    </label>
                                    <input
                                        v-model="gcashDetailsDraft.account_number"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300  shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                        Instructions
                                    </label>
                                    <textarea
                                        v-model="gcashDetailsDraft.instructions"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300  shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    ></textarea>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- GCash QR Scanner Card -->
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-200 bg-ink/5 px-4 py-2">
                            <h2 class=" font-semibold text-gray-900">GCash QR Scanner</h2>

                            <button
                                v-if="!isEditingQr"
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-md px-2 py-1  font-medium border border-blue-600 text-blue-600 hover:bg-blue-50"
                                @click="startEditQr"
                            >
                                <font-awesome-icon icon="fa-solid fa-edit" />
                                Edit
                            </button>

                            <div v-else class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-md px-2 py-1  font-medium text-gray-500 hover:bg-gray-50"
                                    @click="cancelEditQr"
                                >
                                    <font-awesome-icon icon="fa-solid fa-xmark" />
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    :disabled="!qrPreview"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-2 py-1  font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-blue-300"
                                    @click="saveQr"
                                >
                                    <font-awesome-icon icon="fa-solid fa-check" />
                                    Save
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col items-center gap-4 p-4">
                            <div
                                class="flex h-auto w-60 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50"
                            >
                                <img
                                    v-if="qrPreview || qr.image_url"
                                    :src="qrPreview ?? qr.image_url"
                                    alt="GCash QR code"
                                    class="h-full w-full object-contain"
                                />
                                <i v-else class="fa-regular fa-image text-3xl text-gray-300"></i>
                            </div>

                            <label
                                v-if="isEditingQr"
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-gray-300 px-3 py-1.5  font-medium text-gray-700 hover:bg-gray-50"
                            >
                                <i class="fa-solid fa-upload text-xs"></i>
                                Choose image
                                <input type="file" accept="image/*" class="hidden" @change="onQrFileChange" />
                            </label>

                            <p class="text-center text-xs text-gray-500">
                                This QR code is shown to customers.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
    </AdminLayout>
</template>