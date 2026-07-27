<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import LocationPicker from "@/Components/LocationPicker.vue";
import type { Order, OrderStatus } from "@/types/orders.ts";
import { getCourierById } from "@/types/couriers";
import { formatCurrency } from "@/Composables/shipping";
import { useModal } from "@/Composables/useModal";
import { Head, Link, useForm, usePoll } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps<{
    orders?: Order[];
}>();

usePoll(5000, {
    only: ["orders"],
});

const orders = computed<Order[]>(() => props.orders ?? []);

// ---------------------------------------------------------------------------
// Status filters + badges
// ---------------------------------------------------------------------------
const statusFilters: { label: string; value: OrderStatus | "All" }[] = [
    { label: "All", value: "All" },
    { label: "Processing", value: "processing" },
    { label: "In Production", value: "in_production" },
    { label: "Ready for Delivery", value: "ready_for_delivery" },
    { label: "Shipped", value: "shipped" },
    { label: "Delivered", value: "delivered" },
    { label: "Completed", value: "completed" },
];

const activeStatus = ref<OrderStatus | "All">("All");

const statusBadge: Record<OrderStatus, { label: string; class: string }> = {
    processing: { label: "Processing", class: "bg-yellow-100 text-yellow-700" },
    in_production: {
        label: "In Production",
        class: "bg-blue-100 text-blue-700",
    },
    ready_for_delivery: {
        label: "Ready for Delivery",
        class: "bg-purple-100 text-purple-700",
    },
    shipped: { label: "Shipped", class: "bg-indigo-100 text-indigo-700" },
    delivered: { label: "Delivered", class: "bg-teal-100 text-teal-700" },
    completed: { label: "Completed", class: "bg-green-100 text-green-700" },
};

const columns = [
    { key: "template_name", label: "Design", slot: "template" },
    { key: "team_name", label: "Team" },
    { key: "quantity", label: "Qty" },
    { key: "destination", label: "Destination", slot: "destination" },
    { key: "total", label: "Total", slot: "total" },
    { key: "status", label: "Status", slot: "status" },
    { key: "tracking", label: "Tracking", slot: "tracking" },
    { key: "created_at", label: "Placed", slot: "date" },
    { key: "actions", label: "Action", slot: "actions" },
];

const filteredByStatus = computed<Order[]>(() => {
    if (activeStatus.value === "All") return orders.value;
    return orders.value.filter((o) => o.status === activeStatus.value);
});

/** Convenience accessor: the Courier record tied to an order's receipt. */
function courierFor(order: Order) {
    if (!order.courier_receipt) return undefined;
    return getCourierById(order.courier_receipt.courier_id);
}

// ---------------------------------------------------------------------------
// Modal state
// ---------------------------------------------------------------------------
const modal = useModal();
const selectedOrder = ref<Order | null>(null);

/** Returns null while the shipping fee is still unknown (pre-shipment). */
function orderTotal(order: Order): number | null {
    if (order.shipping_fee === null) return null;
    return order.quantity * order.unit_price + order.shipping_fee;
}

function viewOrder(order: Order) {
    selectedOrder.value = order;
    modal.title.value = "Order Details";
    modal.type.value = "View";
    modal.icon.value = "fa-solid fa-shirt";
    modal.openModal();
}

// --- Address management ---
const addressForm = useForm({
    recipient_name: "",
    contact_number: "",
    line1: "",
    barangay: "",
    city: "",
    province: "",
    postal_code: "",
    latitude: null as number | null,
    longitude: null as number | null,
});

function handleLocationUpdate(lat: number, lng: number) {
    addressForm.latitude = lat;
    addressForm.longitude = lng;
}

function openAddressModal(order: Order) {
    selectedOrder.value = order;
    addressForm.clearErrors();
    addressForm.recipient_name = order.address.recipient_name ?? "";
    addressForm.contact_number = order.address.contact_number ?? "";
    addressForm.line1 = order.address.line1 ?? "";
    addressForm.barangay = order.address.barangay ?? "";
    addressForm.city = order.address.city ?? "";
    addressForm.province = order.address.province ?? "";
    addressForm.postal_code = order.address.postal_code ?? "";
    addressForm.latitude = order.address.latitude ?? null;
    addressForm.longitude = order.address.longitude ?? null;
    modal.title.value = "Delivery Address";
    modal.type.value = "Address";
    modal.icon.value = "fa-solid fa-location-dot";
    modal.openModal();
}

function submitAddressUpdate() {
    if (!selectedOrder.value) return;
    addressForm.patch(
        route("client.orders.update-address", selectedOrder.value.id),
        { onSuccess: () => closeModal() },
    );
}

function closeModal() {
    selectedOrder.value = null;
    addressForm.clearErrors();
    modal.closeModal();
}

function formatDate(value: string) {
    return new Date(value).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}
</script>

<template>
    <Head title="Orders" />

    <AuthenticatedLayout>
        <div class="bg-white p-6">
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <h1 class="text-lg font-semibold text-[#14202B]">Orders</h1>

                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="filter in statusFilters"
                        :key="filter.value"
                        type="button"
                        class="rounded-full px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="
                            activeStatus === filter.value
                                ? 'bg-[#2E7D4F] text-white'
                                : 'bg-[#14202B]/5 text-[#14202B]/70 hover:bg-[#14202B]/10'
                        "
                        @click="activeStatus = filter.value"
                    >
                        {{ filter.label }}
                    </button>
                </div>
            </div>
            <hr class="mb-4 mt-2" />

            <Table
                :data="filteredByStatus"
                :columns="columns"
                date-key="created_at"
                :empty-state-message="'No orders yet. Orders appear here once a design request is approved.'"
            >
                <template #template="{ row }">
                    <div class="flex items-center gap-2 text-left">
                        <img
                            :src="row.template_image"
                            :alt="row.template_name"
                            class="h-8 w-8 flex-shrink-0 rounded object-contain bg-[#14202B]/5 p-1"
                        />
                        <div class="flex flex-col">
                            <span class="font-medium text-[#14202B]">{{
                                row.team_name
                            }}</span>
                            <span class="text-xs text-[#14202B]/50">{{
                                row.order_number
                            }}</span>
                        </div>
                    </div>
                </template>

                <template #destination="{ row }">
                    <div class="flex flex-col items-center text-xs">
                        <span class="font-medium text-[#14202B]">{{
                            row.address.province
                        }}</span>
                        <span class="text-[#14202B]/50">{{
                            row.address.city
                        }}</span>
                    </div>
                </template>

                <template #total="{ row }">
                    <div
                        v-if="orderTotal(row) !== null"
                        class="flex flex-col items-center text-xs"
                    >
                        <span class="font-semibold text-[#14202B]">{{
                            formatCurrency(orderTotal(row)!)
                        }}</span>
                        <span class="text-[#14202B]/40">
                            incl. {{ formatCurrency(row.shipping_fee!) }}
                            shipping
                        </span>
                    </div>
                    <span v-else class="text-xs text-[#14202B]/40">
                        Pending shipping fee
                    </span>
                </template>

                <template #status="{ row }">
                    <span
                        class="inline-block rounded-full px-2.5 py-1 text-xs font-medium"
                        :class="statusBadge[row.status].class"
                    >
                        {{ statusBadge[row.status].label }}
                    </span>
                </template>

                <template #tracking="{ row }">
                    <div
                        v-if="row.courier_receipt"
                        class="flex flex-col text-xs"
                    >
                        <span class="font-medium text-blue-600">{{
                            row.courier_receipt.transaction_number
                        }}</span>
                        <span class="text-[#14202B]/40">{{
                            courierFor(row)?.name ?? "Unknown courier"
                        }}</span>
                    </div>
                    <span v-else class="text-xs text-[#14202B]/40">—</span>
                </template>

                <template #date="{ row }">
                    {{ formatDate(row.created_at) }}
                </template>

                <template #actions="{ row }">
                    <div class="flex items-center justify-center gap-1">
                        <button
                            type="button"
                            class="text-xs font-medium bg-blue-600 text-white rounded-md px-2 py-2 transition-colors hover:bg-blue-500"
                            @click="viewOrder(row)"
                        >
                            <font-awesome-icon icon="fa-solid fa-eye" />
                            View
                        </button>

                        <button
                            v-if="
                                row.status === 'processing' ||
                                row.status === 'in_production' ||
                                row.status === 'ready_for_delivery'
                            "
                            type="button"
                            class="text-xs font-medium bg-green-600 text-white rounded-md px-2 py-2 transition-colors hover:bg-green-500"
                            @click="openAddressModal(row)"
                        >
                            <font-awesome-icon
                                icon="fa-solid fa-location-dot"
                            />
                            Address
                        </button>

                        <Link
                            v-if="row.status !== 'completed'"
                            class="text-xs font-medium bg-orange-600 text-white rounded-md px-2 py-2 transition-colors hover:bg-orange-500"
                            :href="route('client.chat.index')"
                        >
                            <font-awesome-icon icon="fa-solid fa-message" />
                            Message
                        </Link>

                        <a
                            class="text-xs font-medium bg-ink text-white rounded-md px-2 py-2 transition-colors hover:bg-ink/90"
                            v-if="
                                row.courier_receipt &&
                                row.status === 'shipped' &&
                                courierFor(row)
                            "
                            :href="courierFor(row)!.site"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <font-awesome-icon icon="fa-solid fa-truck" />
                            Track
                        </a>
                    </div>
                </template>
            </Table>
        </div>

        <!-- View Order Modal -->
        <Modal
            :show="modal.type.value === 'View'"
            @close="closeModal"
            :maxWidth="'5xl'"
        >
            <div
                class="overflow-y-auto max-h-[90vh] px-4 pt-5 pb-4 sm:p-6"
                v-if="selectedOrder"
            >
                <div class="flex items-center justify-between gap-2">
                    <h2
                        class="text-base sm:text-lg font-medium text-gray-900 truncate"
                    >
                        <font-awesome-icon :icon="modal.icon.value" />
                        {{ modal.title.value }} —
                        {{ selectedOrder.order_number }}
                    </h2>
                    <SecondaryButton @click="closeModal" class="flex-shrink-0">
                        <font-awesome-icon icon="fa-solid fa-xmark" />
                    </SecondaryButton>
                </div>
                <hr class="my-3" />

                <div class="flex flex-col gap-4 sm:flex-row">
                    <!-- Jersey preview -->
                    <div
                        class="flex flex-col gap-3 border border-[#14202B]/10 px-3 py-3 rounded w-full sm:w-1/3"
                    >
                        <p class="text-sm font-bold text-[#14202B] text-center">
                            {{ selectedOrder.template_name }}
                        </p>
                        <div
                            class="flex aspect-square w-full items-center justify-center overflow-hidden rounded-lg border border-[#14202B]/10 bg-[#14202B]/5"
                        >
                            <img
                                :src="selectedOrder.template_image"
                                :alt="selectedOrder.template_name"
                                class="h-full w-full object-contain p-4"
                            />
                        </div>
                        <div class="flex items-center justify-center gap-1">
                            <span
                                class="h-4 w-4 rounded-full border border-[#14202B]/15"
                                :style="{
                                    backgroundColor:
                                        selectedOrder.primary_color,
                                }"
                            />
                            <span
                                class="h-4 w-4 rounded-full border border-[#14202B]/15"
                                :style="{
                                    backgroundColor:
                                        selectedOrder.secondary_color,
                                }"
                            />
                            <span
                                class="h-4 w-4 rounded-full border border-[#14202B]/15"
                                :style="{
                                    backgroundColor: selectedOrder.accent_color,
                                }"
                            />
                        </div>
                        <p class="text-xs text-center text-[#14202B]/50">
                            {{ selectedOrder.font_style }} • Qty
                            {{ selectedOrder.quantity }}
                        </p>
                    </div>

                    <!-- Delivery + cost -->
                    <div class="flex flex-col gap-4 w-full sm:w-2/3">
                        <div class="border border-[#14202B]/10 rounded p-3">
                            <p class="text-sm font-bold text-[#14202B] mb-2">
                                <font-awesome-icon
                                    icon="fa-solid fa-location-dot"
                                />
                                Delivery Address
                            </p>
                            <p class="text-sm text-[#14202B]/80">
                                {{ selectedOrder.address.recipient_name }} •
                                {{ selectedOrder.address.contact_number }}
                            </p>
                            <p class="text-sm text-[#14202B]/80">
                                {{ selectedOrder.address.line1
                                }}<span v-if="selectedOrder.address.barangay"
                                    >,
                                    {{ selectedOrder.address.barangay }}</span
                                >, {{ selectedOrder.address.city }},
                                {{ selectedOrder.address.province }}
                                {{ selectedOrder.address.postal_code }}
                            </p>
                        </div>

                        <div class="border border-[#14202B]/10 rounded p-3">
                            <p class="text-sm font-bold text-[#14202B] mb-2">
                                <font-awesome-icon icon="fa-solid fa-receipt" />
                                Cost Breakdown
                            </p>
                            <div
                                class="flex justify-between text-sm text-[#14202B]/80"
                            >
                                <span
                                    >{{ selectedOrder.quantity }} ×
                                    {{
                                        formatCurrency(selectedOrder.unit_price)
                                    }}</span
                                >
                                <span>{{
                                    formatCurrency(
                                        selectedOrder.quantity *
                                            selectedOrder.unit_price,
                                    )
                                }}</span>
                            </div>
                            <div
                                class="flex justify-between text-sm text-[#14202B]/80"
                            >
                                <span>Shipping fee</span>
                                <span>{{
                                    selectedOrder.shipping_fee !== null
                                        ? formatCurrency(
                                              selectedOrder.shipping_fee,
                                          )
                                        : "To be determined"
                                }}</span>
                            </div>
                            <hr class="my-2" />
                            <div
                                class="flex justify-between text-sm font-semibold text-[#14202B]"
                            >
                                <span>Total</span>
                                <span>{{
                                    orderTotal(selectedOrder) !== null
                                        ? formatCurrency(
                                              orderTotal(selectedOrder)!,
                                          )
                                        : "Pending shipping fee"
                                }}</span>
                            </div>
                            <p
                                v-if="selectedOrder.shipping_fee === null"
                                class="mt-1 text-xs text-[#14202B]/40"
                            >
                                The shipping fee is confirmed once your order
                                ships and we get the courier's receipt.
                            </p>
                        </div>

                        <div class="border border-[#14202B]/10 rounded p-3">
                            <p class="text-sm font-bold text-[#14202B] mb-2">
                                <font-awesome-icon icon="fa-solid fa-truck" />
                                Shipping Status
                            </p>
                            <span
                                class="inline-block rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="statusBadge[selectedOrder.status].class"
                            >
                                {{ statusBadge[selectedOrder.status].label }}
                            </span>

                            <div
                                v-if="selectedOrder.courier_receipt"
                                class="mt-2 text-sm text-[#14202B]/80"
                            >
                                <p>
                                    Courier:
                                    {{
                                        courierFor(selectedOrder)?.name ??
                                        "Unknown courier"
                                    }}
                                </p>
                                <p>
                                    Transaction #:
                                    {{
                                        selectedOrder.courier_receipt
                                            .transaction_number
                                    }}
                                </p>
                                <p>
                                    Shipped:
                                    {{
                                        formatDate(
                                            selectedOrder.courier_receipt
                                                .date_shipped,
                                        )
                                    }}
                                </p>
                                <a
                                    v-if="courierFor(selectedOrder)"
                                    :href="courierFor(selectedOrder)!.site"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 mt-1 text-blue-600 hover:underline"
                                >
                                    <font-awesome-icon
                                        icon="fa-solid fa-arrow-up-right-from-square"
                                    />
                                    Track Package
                                </a>
                            </div>
                            <p v-else class="mt-2 text-xs text-[#14202B]/40">
                                Tracking details will appear here once the order
                                ships.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Address Management Modal -->
        <Modal
            :show="modal.type.value === 'Address'"
            @close="closeModal"
            :maxWidth="'lg'"
        >
            <div class="px-4 pt-5 pb-4 sm:p-6" v-if="selectedOrder">
                <h2 class="text-lg font-medium text-gray-900">
                    <font-awesome-icon :icon="modal.icon.value" />
                    {{ modal.title.value }}
                </h2>
                <hr class="my-3" />

                <div class="flex flex-col gap-3">
                    <div>
                        <InputLabel for="recipient_name" value="Recipient Name" />
                        <TextInput
                            v-model="addressForm.recipient_name"
                            class="mt-1 block w-full"
                            id="recipient_name"
                            required
                        />
                        <InputError
                            :message="addressForm.errors.recipient_name"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel for="contact_number" value="Contact Number" />
                        <TextInput
                            v-model="addressForm.contact_number"
                            class="mt-1 block w-full"
                            id="contact_number"
                            required
                        />
                        <InputError
                            :message="addressForm.errors.contact_number"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel for="postal_code" value="Postal Code" />
                        <TextInput
                            v-model="addressForm.postal_code"
                            class="mt-1 block w-full"
                            id="postal_code"
                            required
                        />
                        <InputError
                            :message="addressForm.errors.postal_code"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel for="line1" value="Address Line 1" />
                        <TextInput
                            v-model="addressForm.line1"
                            class="mt-1 block w-full"
                            id="line1"
                            required
                        />
                        <InputError
                            :message="addressForm.errors.line1"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel for="barangay" value="Barangay (optional)" />
                        <TextInput
                            v-model="addressForm.barangay"
                            class="mt-1 block w-full"
                            id="barangay"
                            required
                        />
                        <InputError
                            :message="addressForm.errors.barangay"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel for="city" value="City / Municipality" />
                        <TextInput
                            v-model="addressForm.city"
                            class="mt-1 block w-full"
                            id="city"
                            required
                        />
                        <InputError
                            :message="addressForm.errors.city"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel for="province" value="Province" />
                        <TextInput
                            v-model="addressForm.province"
                            class="mt-1 block w-full"
                            id="province"
                            required
                        />
                        <InputError
                            :message="addressForm.errors.province"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Pin Location</label
                        >
                        <LocationPicker
                            :model-lat="addressForm.latitude"
                            :model-lng="addressForm.longitude"
                            @update:location="handleLocationUpdate"
                        />
                        <p
                            v-if="addressForm.latitude && addressForm.longitude"
                            class="mt-1 text-xs text-[#14202B]/50"
                        >
                            {{ addressForm.latitude.toFixed(6) }},
                            {{ addressForm.longitude.toFixed(6) }}
                        </p>
                    </div>
                </div>

                <p class="mt-4 text-xs text-[#14202B]/50">
                    The shipping fee for this order will be confirmed once it
                    ships and isn't affected by this address form.
                </p>

                <div class="mt-6 flex justify-between">
                    <SecondaryButton @click="closeModal">Close</SecondaryButton>
                    <PrimaryButton
                        class="flex items-center justify-center gap-1"
                        :disabled="addressForm.processing"
                        @click="submitAddressUpdate"
                        :class="{ 'opacity-25': addressForm.processing }"
                    >
                        <div class="text-sm" v-if="addressForm.processing">
                            <font-awesome-icon
                                icon="fa-solid fa-spinner"
                                spin
                            />
                        </div>
                        Save Address
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
