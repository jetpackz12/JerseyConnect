<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import SelectInput from "@/Components/SelectInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import ImageUpload from "@/Components/ImageUpload.vue";
import { useModal } from "@/Composables/useModal";
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, computed, onMounted, onUnmounted } from "vue";

interface JerseyTemplates {
    id: number;
    name: string;
    image: string;
    price: number;
    badge?: "New" | "Bestseller" | "Hot";
    primary_color: string;
    secondary_color: string;
    accent_color: string;
    sport: "Basketball" | "Soccer" | "Baseball" | "Volleyball" | "Esports";
    status: "active" | "inactive";
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    data?: JerseyTemplates[];
}>();

interface ImageStyle {
    backgroundImage: string;
}

type JerseyTemplatesStatus = "active" | "inactive";

const jerseyTemplates = computed(() => props.data ?? []);

const statusBadge: Record<
    JerseyTemplatesStatus,
    { label: string; class: string }
> = {
    active: {
        label: "Active",
        class: "bg-green-100 text-green-700",
    },
    inactive: {
        label: "Inactive",
        class: "bg-red-100 text-red-700",
    },
};

const badgeOptions = ["New", "Bestseller", "Hot"];
const sportOptions = [
    "Basketball",
    "Soccer",
    "Baseball",
    "Volleyball",
    "Esports",
];
const jerseyStatus = [
    {
        value: "active",
        label: "Active",
    },
    {
        value: "inactive",
        label: "Inactive",
    },
];

const columns = [
    { key: "name", label: "Design", slot: "template" },
    { key: "price", label: "Price" },
    { key: "badge", label: "Badge" },
    { key: "colors", label: "Colors", slot: "colors" },
    { key: "sport", label: "Sport" },
    { key: "status", label: "Status", slot: "status" },
    { key: "created_at", label: "Submitted", slot: "date" },
    { key: "actions", label: "Action", slot: "actions" },
];

const modal = useModal();

function closeModal() {
    modal.closeModal();
    addForm.reset();
    addForm.clearErrors();
    editForm.reset();
    editForm.clearErrors();
    addImageStyle.value = { backgroundImage: "" };
    editImageStyle.value = { backgroundImage: "" };
    selectedJersey.value = null;
}

function formatDate(value: string) {
    return new Date(value).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

/* ---------------- RATE LIMIT ---------------- */
const showLimitModal = ref(false);
const retryAfterSeconds = ref<number | null>(null);
let countdownTimer: ReturnType<typeof setInterval> | null = null;
let removeInvalidListener: (() => void) | undefined;

function startCountdown(seconds: number | null) {
    stopCountdown();
    if (!seconds || seconds <= 0) return;

    retryAfterSeconds.value = seconds;
    countdownTimer = setInterval(() => {
        if (retryAfterSeconds.value === null) return;
        if (retryAfterSeconds.value <= 1) {
            retryAfterSeconds.value = 0;
            stopCountdown();
            return;
        }
        retryAfterSeconds.value -= 1;
    }, 1000);
}

function stopCountdown() {
    if (countdownTimer) {
        clearInterval(countdownTimer);
        countdownTimer = null;
    }
}

function closeLimitModal() {
    showLimitModal.value = false;
    stopCountdown();
    retryAfterSeconds.value = null;
}

onMounted(() => {
    removeInvalidListener = router.on("invalid", (event) => {
        const status = event.detail.response?.status;

        if (status === 429) {
            // Stop Inertia from rendering the default error page.
            event.preventDefault();

            const retryAfterHeader =
                event.detail.response?.headers?.["retry-after"];
            const seconds = retryAfterHeader
                ? Number(retryAfterHeader)
                : null;

            startCountdown(seconds);
            showLimitModal.value = true;
        }
    });
});

onUnmounted(() => {
    removeInvalidListener?.();
    stopCountdown();
});

/* ---------------- ADD ---------------- */

const addImageStyle = ref<ImageStyle>({ backgroundImage: "" });

const addForm = useForm({
    name: "",
    price: "",
    badge: "" as "" | (typeof badgeOptions)[number],
    primary_color: "#14202B",
    secondary_color: "#FFFFFF",
    accent_color: "#2E7D4F",
    sport: "Basketball" as (typeof sportOptions)[number],
    image: null as File | null,
});

function openAddModal() {
    addForm.reset();
    addForm.clearErrors();
    addImageStyle.value = { backgroundImage: "" };
    modal.title.value = "Add Jersey Template";
    modal.type.value = "Add";
    modal.openModal();
}

function onAddImageChange(file: File) {
    addForm.image = file;
}

function submitAdd() {
    addForm.post(route("admin.jersey.store"), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
}

/* ---------------- VIEW ---------------- */

const selectedJersey = ref<JerseyTemplates | null>(null);

function openViewModal(row: JerseyTemplates) {
    selectedJersey.value = row;
    modal.title.value = "View Jersey Template";
    modal.type.value = "View";
    modal.openModal();
}

/* ---------------- EDIT ---------------- */

const editImageStyle = ref<ImageStyle>({ backgroundImage: "" });

const editForm = useForm({
    id: null as number | null,
    name: "",
    price: "",
    badge: "" as "" | (typeof badgeOptions)[number],
    primary_color: "#14202B",
    secondary_color: "#FFFFFF",
    accent_color: "#2E7D4F",
    sport: "Basketball" as (typeof sportOptions)[number],
    status: "active" as JerseyTemplatesStatus,
    image: null as File | null,
});

function openEditModal(row: JerseyTemplates) {
    editForm.reset();
    editForm.clearErrors();

    editForm.id = row.id;
    editForm.name = row.name;
    editForm.price = String(row.price);
    editForm.badge = row.badge ?? "";
    editForm.primary_color = row.primary_color;
    editForm.secondary_color = row.secondary_color;
    editForm.accent_color = row.accent_color;
    editForm.sport = row.sport;
    editForm.status = row.status;

    editImageStyle.value = { backgroundImage: `url(${row.image})` };

    modal.title.value = "Edit Jersey Template";
    modal.type.value = "Edit";
    modal.openModal();
}

function onEditImageChange(file: File) {
    editForm.image = file;
}

function submitEdit() {
    if (!editForm.id) return;

    editForm
        .transform((data) => ({
            ...data,
            _method: "put",
        }))
        .post(route("admin.jersey.update", editForm.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
}
</script>

<template>
    <Head title="Jersey Templates" />

    <AdminLayout>
        <div class="bg-white p-4 rounded-lg">
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <h1 class="text-lg font-semibold text-[#14202B]">
                    <font-awesome-icon icon="fa-solid fa-tshirt" />
                    Jersey Templates
                </h1>

                <div class="flex flex-wrap gap-2">
                    <PrimaryButton
                        class="flex items-center justify-center gap-1 w-full sm:w-auto"
                        @click="openAddModal"
                    >
                        <font-awesome-icon icon="fa-solid fa-plus-circle" />
                        Add Jersey
                    </PrimaryButton>
                </div>
            </div>
            <hr class="mb-4 mt-2" />
            <Table
                :data="jerseyTemplates"
                :columns="columns"
                date-key="created_at"
            >
                <template #template="{ row }">
                    <div class="flex items-center gap-2 text-left">
                        <img
                            :src="row.image"
                            :alt="row.name"
                            class="h-8 w-8 flex-shrink-0 rounded object-contain bg-[#14202B]/5 p-1"
                        />
                        <span class="font-medium text-[#14202B]">{{
                            row.name
                        }}</span>
                    </div>
                </template>

                <template #colors="{ row }">
                    <div class="flex items-center justify-center gap-1">
                        <span
                            class="h-4 w-4 rounded-full border border-[#14202B]/15"
                            :style="{ backgroundColor: row.primary_color }"
                        />
                        <span
                            class="h-4 w-4 rounded-full border border-[#14202B]/15"
                            :style="{ backgroundColor: row.secondary_color }"
                        />
                        <span
                            class="h-4 w-4 rounded-full border border-[#14202B]/15"
                            :style="{ backgroundColor: row.accent_color }"
                        />
                    </div>
                </template>

                <template #status="{ row }">
                    <span
                        class="inline-block rounded-full px-2.5 py-1 text-xs font-medium"
                        :class="statusBadge[row.status].class"
                    >
                        {{ statusBadge[row.status].label }}
                    </span>
                </template>

                <template #date="{ row }">
                    {{ formatDate(row.created_at) }}
                </template>

                <template #actions="{ row }">
                    <div class="flex items-center justify-center gap-1">
                        <button
                            type="button"
                            class="text-xs font-medium bg-blue-600 text-white rounded-md px-2 py-2 transition-colors hover:bg-blue-500"
                            @click="openViewModal(row)"
                        >
                            <font-awesome-icon icon="fa-solid fa-eye" />
                            View
                        </button>
                        <button
                            type="button"
                            class="text-xs font-medium bg-gray-600 text-white rounded-md px-2 py-2 transition-colors hover:bg-gray-500"
                            @click="openEditModal(row)"
                        >
                            <font-awesome-icon icon="fa-solid fa-edit" />
                            Edit
                        </button>
                    </div>
                </template>
            </Table>
        </div>

        <!-- Add Modal -->
        <Modal
            :show="modal.type.value === 'Add'"
            @close="closeModal()"
            :maxWidth="'5xl'"
        >
            <form @submit.prevent="submitAdd" class="px-4 pt-5 pb-4 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    <font-awesome-icon icon="fa-solid fa-plus-circle" />
                    {{ modal.title.value }}
                </h2>
                <hr class="my-2" />
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block font-medium text-gray-700"
                            >Jersey Image</label
                        >
                        <ImageUpload
                            class="mt-1"
                            :image="addImageStyle"
                            @imageFile="onAddImageChange"
                        />
                        <InputError
                            :message="addForm.errors.image"
                            class="mt-2"
                        />
                    </div>

                    <div class="flex flex-col gap-4">
                        <div>
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                v-model="addForm.name"
                                class="mt-1 block w-full"
                                id="name"
                                required
                            />
                            <InputError
                                :message="addForm.errors.name"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel for="price" value="Price" />
                            <TextInput
                                v-model="addForm.price"
                                class="mt-1 block w-full"
                                id="price"
                                required
                            />
                            <InputError
                                :message="addForm.errors.price"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel for="badge" value="Badge" />
                            <SelectInput
                                v-model="addForm.badge"
                                :options="badgeOptions"
                                class="mt-1 block w-full"
                                id="badge"
                                required
                            />
                            <InputError
                                :message="addForm.errors.badge"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel for="sport" value="Sport" />
                            <SelectInput
                                v-model="addForm.sport"
                                :options="sportOptions"
                                class="mt-1 block w-full"
                                id="sport"
                                required
                            />
                            <InputError
                                :message="addForm.errors.sport"
                                class="mt-2"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <InputLabel
                                    for="primary"
                                    value="Primary Color"
                                />
                                <div class="mt-1 flex items-center gap-1">
                                    <input
                                        type="color"
                                        v-model="addForm.primary_color"
                                        class="h-10 w-12 rounded border border-gray-300"
                                        id="primary"
                                    />
                                    <TextInput
                                        v-model="addForm.primary_color"
                                        class="block w-full"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Secondary Color</label
                                >
                                <div class="mt-1 flex items-center gap-1">
                                    <input
                                        type="color"
                                        v-model="addForm.secondary_color"
                                        class="h-10 w-12 rounded border border-gray-300"
                                    />
                                    <TextInput
                                        v-model="addForm.secondary_color"
                                        class="block w-full"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Accent Color</label
                                >
                                <div class="mt-1 flex items-center gap-1">
                                    <input
                                        type="color"
                                        v-model="addForm.accent_color"
                                        class="h-10 w-12 rounded border border-gray-300"
                                    />
                                    <TextInput
                                        v-model="addForm.accent_color"
                                        class="block w-full"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="mt-4" />
                <div
                    class="mt-2 flex flex-col-reverse gap-2 sm:flex-row sm:justify-between"
                >
                    <SecondaryButton
                        type="button"
                        class="flex items-center justify-center"
                        @click="closeModal()"
                    >
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton
                        type="submit"
                        class="flex items-center justify-center gap-1"
                        :disabled="addForm.processing"
                        :class="{ 'opacity-25': addForm.processing }"
                    >
                        <div class="text-sm" v-if="addForm.processing">
                            <font-awesome-icon
                                icon="fa-solid fa-spinner"
                                spin
                            />
                        </div>
                        Save
                        <font-awesome-icon icon="fa-solid fa-circle-down" />
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- View Modal -->
        <Modal
            :show="modal.type.value === 'View'"
            @close="closeModal()"
            :maxWidth="'lg'"
        >
            <div v-if="selectedJersey" class="px-4 pt-5 pb-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">
                        <font-awesome-icon icon="fa-solid fa-eye" />
                        {{ modal.title.value }}
                    </h2>
                    <SecondaryButton @click="closeModal()">
                        <font-awesome-icon icon="fa-solid fa-xmark" />
                    </SecondaryButton>
                </div>
                <hr class="my-2" />
                <div class="mt-2 flex items-center gap-3">
                    <img
                        :src="selectedJersey.image"
                        :alt="selectedJersey.name"
                        class="mx-auto max-h-80 w-full rounded object-contain bg-[#14202B]/5 p-4"
                    />
                </div>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal
            :show="modal.type.value === 'Edit'"
            @close="closeModal()"
            :maxWidth="'5xl'"
        >
            <form @submit.prevent="submitEdit" class="px-4 pt-5 pb-4 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    <font-awesome-icon icon="fa-solid fa-edit" />
                    {{ modal.title.value }}
                </h2>
                <hr class="my-2" />
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Jersey Image</label
                        >
                        <ImageUpload
                            class="mt-1"
                            :image="editImageStyle"
                            @imageFile="onEditImageChange"
                        />
                        <InputError
                            :message="editForm.errors.image"
                            class="mt-2"
                        />
                    </div>

                    <div class="flex flex-col gap-4">
                        <div>
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                v-model="editForm.name"
                                class="mt-1 block w-full"
                                id="name"
                                required
                            />
                            <InputError
                                :message="editForm.errors.name"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel for="price" value="Price" />
                            <TextInput
                                v-model="editForm.price"
                                class="mt-1 block w-full"
                                id="price"
                                required
                            />
                            <InputError
                                :message="editForm.errors.price"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel for="badge" value="Badge" />
                            <SelectInput
                                v-model="editForm.badge"
                                :options="badgeOptions"
                                class="mt-1 block w-full"
                                id="badge"
                                required
                            />
                            <InputError
                                :message="editForm.errors.badge"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel for="sport" value="Sport" />
                            <SelectInput
                                v-model="editForm.sport"
                                :options="sportOptions"
                                class="mt-1 block w-full"
                                id="sport"
                                required
                            />
                            <InputError
                                :message="editForm.errors.sport"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel for="status" value="Status" />
                            <SelectInput
                                v-model="editForm.status"
                                :options="jerseyStatus"
                                class="mt-1 block w-full"
                                id="status"
                                required
                            />
                            <InputError
                                :message="editForm.errors.status"
                                class="mt-2"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <InputLabel
                                    for="primary"
                                    value="Primary Color"
                                />
                                <div class="mt-1 flex items-center gap-1">
                                    <input
                                        type="color"
                                        v-model="editForm.primary_color"
                                        class="h-10 w-12 rounded border border-gray-300"
                                        id="primary"
                                    />
                                    <TextInput
                                        v-model="editForm.primary_color"
                                        class="block w-full"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Secondary Color</label
                                >
                                <div class="mt-1 flex items-center gap-1">
                                    <input
                                        type="color"
                                        v-model="editForm.secondary_color"
                                        class="h-10 w-12 rounded border border-gray-300"
                                    />
                                    <TextInput
                                        v-model="editForm.secondary_color"
                                        class="block w-full"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Accent Color</label
                                >
                                <div class="mt-1 flex items-center gap-1">
                                    <input
                                        type="color"
                                        v-model="editForm.accent_color"
                                        class="h-10 w-12 rounded border border-gray-300"
                                    />
                                    <TextInput
                                        v-model="editForm.accent_color"
                                        class="block w-full"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="mt-4" />
                <div
                    class="mt-2 flex flex-col-reverse gap-2 sm:flex-row sm:justify-between"
                >
                    <SecondaryButton
                        type="button"
                        class="flex items-center justify-center"
                        @click="closeModal()"
                    >
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton
                        type="submit"
                        class="flex items-center justify-center gap-1"
                        :disabled="editForm.processing"
                        :class="{ 'opacity-25': editForm.processing }"
                    >
                        <div class="text-sm" v-if="editForm.processing">
                            <font-awesome-icon
                                icon="fa-solid fa-spinner"
                                spin
                            />
                        </div>
                        Save
                        <font-awesome-icon icon="fa-solid fa-circle-down" />
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Rate Limit Modal -->
        <Modal :show="showLimitModal" @close="closeLimitModal()" :maxWidth="'md'">
            <div class="px-4 pt-6 pb-5 sm:p-6 text-center">
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100"
                >
                    <font-awesome-icon
                        icon="fa-solid fa-hourglass-half"
                        class="text-amber-600 text-lg"
                    />
                </div>

                <h2 class="mt-4 text-lg font-medium text-gray-900">
                    Whoa, slow down a bit!
                </h2>

                <p class="mt-2 text-sm text-gray-600">
                    You've made too many requests in a short time. Please
                    wait a moment before trying again.
                </p>

                <p
                    v-if="retryAfterSeconds !== null && retryAfterSeconds > 0"
                    class="mt-3 text-sm font-medium text-[#14202B]"
                >
                    You can try again in
                    <span class="text-amber-600">{{ retryAfterSeconds }}s</span>
                </p>

                <div class="mt-6 flex justify-center">
                    <PrimaryButton
                        type="button"
                        :disabled="
                            retryAfterSeconds !== null && retryAfterSeconds > 0
                        "
                        :class="{
                            'opacity-40 cursor-not-allowed':
                                retryAfterSeconds !== null &&
                                retryAfterSeconds > 0,
                        }"
                        @click="closeLimitModal()"
                    >
                        Got it
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>