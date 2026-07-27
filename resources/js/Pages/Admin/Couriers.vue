<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue";
import SelectInput from "@/Components/SelectInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { useModal } from "@/Composables/useModal";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

type CourierStatus = "active" | "inactive";

interface Courier {
    id: number;
    name: string;
    site: string | null;
    status: CourierStatus;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    couriers: Courier[];
}>();

const courierStatus: Record<CourierStatus, { label: string; class: string }> = {
    active: {
        label: "Active",
        class: "bg-green-100 text-green-700",
    },
    inactive: {
        label: "Inactive",
        class: "bg-red-100 text-red-700",
    },
};

const statusOptions = [
    { value: "active", label: "Active" },
    { value: "inactive", label: "Inactive" },
];

const columns = [
    { key: "name", label: "Courier" },
    { key: "site", label: "Site", slot: "site" },
    { key: "status", label: "Status", slot: "status" },
    { key: "created_at", label: "Created", slot: "date" },
    { key: "actions", label: "Action", slot: "actions" },
];

const modal = useModal();

function closeModal() {
    modal.closeModal();
    addForm.reset();
    addForm.clearErrors();
    editForm.reset();
    editForm.clearErrors();
    courierToDelete.value = null;
}

function formatDate(value: string) {
    return new Date(value).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

/* ---------------- ADD ---------------- */

const addForm = useForm({
    name: "",
    site: "",
    status: "active" as CourierStatus,
});

function openAddModal() {
    addForm.reset();
    addForm.clearErrors();
    modal.title.value = "Add Courier";
    modal.type.value = "Add";
    modal.openModal();
}

function submitAdd() {
    addForm.post(route("admin.couriers.store"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
}

/* ---------------- EDIT ---------------- */

const editForm = useForm({
    id: null as number | null,
    name: "",
    site: "",
    status: "active" as CourierStatus,
});

function openEditModal(row: Courier) {
    editForm.reset();
    editForm.clearErrors();

    editForm.id = row.id;
    editForm.name = row.name;
    editForm.site = row.site ?? "";
    editForm.status = row.status;

    modal.title.value = "Edit Courier";
    modal.type.value = "Edit";
    modal.openModal();
}

function submitEdit() {
    if (!editForm.id) return;

    editForm
        .transform((data) => ({
            ...data,
            _method: "put",
        }))
        .post(route("admin.couriers.update", editForm.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
}

/* ---------------- DELETE ---------------- */

const courierToDelete = ref<Courier | null>(null);

const deleteForm = useForm({});

function openDeleteModal(row: Courier) {
    courierToDelete.value = row;
    modal.title.value = "Delete Courier";
    modal.type.value = "Delete";
    modal.openModal();
}

function submitDelete() {
    if (!courierToDelete.value) return;

    deleteForm.delete(
        route("admin.couriers.destroy", courierToDelete.value.id),
        {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        },
    );
}
</script>

<template>
    <Head title="Couriers" />

    <AdminLayout>
        <div class="bg-white p-4 rounded-lg">
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <h1 class="text-lg font-semibold text-ink">
                    <font-awesome-icon icon="fa-solid fa-truck-fast" />
                    Couriers
                </h1>

                <div class="flex flex-wrap gap-2">
                    <PrimaryButton
                        class="flex items-center justify-center gap-1 w-full sm:w-auto"
                        @click="openAddModal"
                    >
                        <font-awesome-icon icon="fa-solid fa-plus-circle" />
                        Add Courier
                    </PrimaryButton>
                </div>
            </div>
            <hr class="mb-4 mt-2" />
            <Table
                :data="props.couriers"
                :columns="columns"
                date-key="created_at"
            >
                <template #site="{ row }">
                    <a
                        v-if="row.site"
                        :href="row.site"
                        target="_blank"
                        class="text-blue-600 underline"
                    >
                        <font-awesome-icon icon="fa-solid fa-link" />
                        {{ row.name }}
                    </a>
                    <span v-else class="text-gray-400">—</span>
                </template>

                <template #status="{ row }">
                    <span
                        class="inline-block rounded-full px-2.5 py-1 text-xs font-medium"
                        :class="courierStatus[row.status].class"
                    >
                        {{ courierStatus[row.status].label }}
                    </span>
                </template>

                <template #date="{ row }">
                    {{ formatDate(row.created_at) }}
                </template>

                <template #actions="{ row }">
                    <div class="flex items-center justify-center gap-2">
                        <button
                            type="button"
                            class="text-xs font-medium bg-gray-600 text-white rounded-md px-2 py-2 transition-colors hover:bg-gray-500"
                            @click="openEditModal(row)"
                        >
                            <font-awesome-icon icon="fa-solid fa-edit" />
                            Edit
                        </button>
                        <button
                            type="button"
                            class="text-xs font-medium bg-red-600 text-white rounded-md px-2 py-2 transition-colors hover:bg-red-500"
                            @click="openDeleteModal(row)"
                        >
                            <font-awesome-icon icon="fa-solid fa-trash" />
                            Delete
                        </button>
                    </div>
                </template>
            </Table>
        </div>

        <!-- Add Modal -->
        <Modal
            :show="modal.type.value === 'Add'"
            @close="closeModal()"
            :maxWidth="'md'"
        >
            <form @submit.prevent="submitAdd" class="px-4 pt-5 pb-4 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    <font-awesome-icon icon="fa-solid fa-plus-circle" />
                    {{ modal.title.value }}
                </h2>
                <hr class="my-2" />
                <div class="flex flex-col gap-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            v-model="addForm.name"
                            class="mt-1 block w-full"
                            id="name"
                            required
                            placeholder="e.g. DHL"
                        />
                        <InputError
                            :message="addForm.errors.name"
                            class="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel for="site" value="Site" />
                        <TextAreaInput
                            v-model="addForm.site"
                            class="mt-1 block w-full"
                            id="site"
                            rows="3"
                            placeholder="e.g. https://example.com/track?..."
                        />
                        <InputError
                            :message="addForm.errors.site"
                            class="mt-2"
                        />
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

        <!-- Edit Modal -->
        <Modal
            :show="modal.type.value === 'Edit'"
            @close="closeModal()"
            :maxWidth="'md'"
        >
            <form @submit.prevent="submitEdit" class="px-4 pt-5 pb-4 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    <font-awesome-icon icon="fa-solid fa-edit" />
                    {{ modal.title.value }}
                </h2>
                <hr class="my-2" />

                <div class="flex flex-col gap-4">
                    <div>
                        <InputLabel for="edit-name" value="Name" />
                        <TextInput
                            v-model="editForm.name"
                            class="mt-1 block w-full"
                            id="edit-name"
                            required
                            placeholder="e.g. DHL"
                        />
                        <InputError
                            :message="editForm.errors.name"
                            class="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel for="edit-site" value="Site" />
                        <TextAreaInput
                            v-model="editForm.site"
                            class="mt-1 block w-full"
                            id="edit-site"
                            rows="3"
                            placeholder="e.g. https://example.com/track?..."
                        />
                        <InputError
                            :message="editForm.errors.site"
                            class="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel for="edit-status" value="Status" />
                        <SelectInput
                            v-model="editForm.status"
                            :options="statusOptions"
                            class="mt-1 block w-full"
                            id="edit-status"
                            required
                        />
                        <InputError
                            :message="editForm.errors.status"
                            class="mt-2"
                        />
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
        <!-- Delete Modal -->
        <Modal
            :show="modal.type.value === 'Delete'"
            @close="closeModal()"
            :maxWidth="'md'"
        >
            <div class="px-4 pt-5 pb-4 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    <font-awesome-icon icon="fa-solid fa-trash" />
                    {{ modal.title.value }}
                </h2>
                <hr class="my-2" />

                <p class="text-sm text-gray-600">
                    Are you sure you want to delete
                    <span class="font-semibold text-gray-900">{{
                        courierToDelete?.name
                    }}</span
                    >? This action cannot be undone.
                </p>

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
                        type="button"
                        class="flex items-center justify-center gap-1 !bg-red-600 hover:!bg-red-500"
                        :disabled="deleteForm.processing"
                        :class="{ 'opacity-25': deleteForm.processing }"
                        @click="submitDelete"
                    >
                        <div class="text-sm" v-if="deleteForm.processing">
                            <font-awesome-icon
                                icon="fa-solid fa-spinner"
                                spin
                            />
                        </div>
                        Delete
                        <font-awesome-icon icon="fa-solid fa-trash" />
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
