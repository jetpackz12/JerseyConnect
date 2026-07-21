<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LocationPicker from "@/Components/LocationPicker.vue";
import type { Order, OrderStatus } from "@/types/orders.ts";
import { getCourierById } from "@/types/couriers";
import { formatCurrency } from "@/Composables/shipping";
import { useModal } from "@/Composables/useModal";
import { Head, Link } from "@inertiajs/vue3";
import { ref, computed, reactive } from "vue";

const props = defineProps<{
    orders?: Order[];
    /** Set to true on the client-facing route; hides admin-only controls */
    readOnly?: boolean;
}>();

// ---------------------------------------------------------------------------
// Mock data — replace with props.orders once the backend endpoint is wired up
// ---------------------------------------------------------------------------
const mockOrders: Order[] = [
    {
        id: 1,
        order_number: "ORD-2026-0001",
        design_request_id: 4,
        template_name: "Minimalist Crest",
        template_image: "/images/image4.png",
        team_name: "Arevalo Titans",
        primary_color: "#1F618D",
        secondary_color: "#FFFFFF",
        accent_color: "#1F618D",
        font_style: "Sans Condensed",
        quantity: 12,
        unit_price: 249,
        address: {
            id: 1,
            recipient_name: "Marco Villanueva",
            contact_number: "0917 123 4567",
            line1: "Blk 4 Lot 12 Rizal St.",
            barangay: "Arevalo",
            city: "Iloilo City",
            province: "Iloilo",
            postal_code: "5000",
            is_default: true,
        },
        shipping_fee: null,
        status: "processing",
        courier_receipt: null,
        created_at: "2026-06-21T09:00:00Z",
        updated_at: "2026-07-05T10:00:00Z",
    },
    {
        id: 2,
        order_number: "ORD-2026-0002",
        design_request_id: 9,
        template_name: "Vortex Fade",
        template_image: "/images/image2.png",
        team_name: "Cebu Coastal Runners",
        primary_color: "#2E7D4F",
        secondary_color: "#F5C518",
        accent_color: "#14202B",
        font_style: "Slab Serif",
        quantity: 20,
        unit_price: 249,
        address: {
            id: 2,
            recipient_name: "Anna Bautista",
            contact_number: "0918 555 2211",
            line1: "88 Salinas Drive",
            city: "Cebu City",
            province: "Cebu",
            postal_code: "6000",
            is_default: true,
        },
        shipping_fee: null,
        status: "in_production",
        courier_receipt: null,
        created_at: "2026-06-10T08:30:00Z",
        updated_at: "2026-07-12T14:00:00Z",
    },
    {
        id: 3,
        order_number: "ORD-2026-0003",
        design_request_id: 11,
        template_name: "Retro Stripe",
        template_image: "/images/image3.png",
        team_name: "Manila Bay Sharks",
        primary_color: "#C0392B",
        secondary_color: "#FFFFFF",
        accent_color: "#F5C518",
        font_style: "Script",
        quantity: 15,
        unit_price: 249,
        address: {
            id: 3,
            recipient_name: "Carlo Reyes",
            contact_number: "0920 444 7890",
            line1: "212 Katipunan Ave",
            city: "Quezon City",
            province: "Metro Manila",
            postal_code: "1108",
            is_default: true,
        },
        shipping_fee: null,
        status: "ready_for_delivery",
        courier_receipt: null,
        created_at: "2026-06-05T07:00:00Z",
        updated_at: "2026-07-02T16:00:00Z",
    },
    {
        id: 4,
        order_number: "ORD-2026-0004",
        design_request_id: 4,
        template_name: "Minimalist Crest",
        template_image: "/images/image4.png",
        team_name: "Arevalo Titans",
        primary_color: "#1F618D",
        secondary_color: "#FFFFFF",
        accent_color: "#1F618D",
        font_style: "Sans Condensed",
        quantity: 12,
        unit_price: 249,
        address: {
            id: 1,
            recipient_name: "Marco Villanueva",
            contact_number: "0917 123 4567",
            line1: "Blk 4 Lot 12 Rizal St.",
            barangay: "Arevalo",
            city: "Iloilo City",
            province: "Iloilo",
            postal_code: "5000",
            is_default: true,
        },
        shipping_fee: 380,
        status: "shipped",
        courier_receipt: {
            id: 1,
            courier_id: 1,
            transaction_number: "JT-88213764521",
            shipping_fee: 380,
            date_shipped: "2026-07-12T14:00:00Z",
            remarks: "3 boxes",
        },
        created_at: "2026-06-21T09:00:00Z",
        updated_at: "2026-07-05T10:00:00Z",
    },
    {
        id: 5,
        order_number: "ORD-2026-0005",
        design_request_id: 9,
        template_name: "Vortex Fade",
        template_image: "/images/image2.png",
        team_name: "Cebu Coastal Runners",
        primary_color: "#2E7D4F",
        secondary_color: "#F5C518",
        accent_color: "#14202B",
        font_style: "Slab Serif",
        quantity: 20,
        unit_price: 249,
        address: {
            id: 2,
            recipient_name: "Anna Bautista",
            contact_number: "0918 555 2211",
            line1: "88 Salinas Drive",
            city: "Cebu City",
            province: "Cebu",
            postal_code: "6000",
            is_default: true,
        },
        shipping_fee: 320,
        status: "delivered",
        courier_receipt: {
            id: 1,
            courier_id: 1,
            transaction_number: "JT-88213764521",
            shipping_fee: 320,
            date_shipped: "2026-07-12T14:00:00Z",
            remarks: "3 boxes",
        },
        created_at: "2026-06-10T08:30:00Z",
        updated_at: "2026-07-12T14:00:00Z",
    },
    {
        id: 6,
        order_number: "ORD-2026-0006",
        design_request_id: 11,
        template_name: "Retro Stripe",
        template_image: "/images/image3.png",
        team_name: "Manila Bay Sharks",
        primary_color: "#C0392B",
        secondary_color: "#FFFFFF",
        accent_color: "#F5C518",
        font_style: "Script",
        quantity: 15,
        unit_price: 249,
        address: {
            id: 3,
            recipient_name: "Carlo Reyes",
            contact_number: "0920 444 7890",
            line1: "212 Katipunan Ave",
            city: "Quezon City",
            province: "Metro Manila",
            postal_code: "1108",
            is_default: true,
        },
        shipping_fee: 350,
        status: "completed",
        courier_receipt: {
            id: 2,
            courier_id: 2,
            transaction_number: "LBC-100294837",
            shipping_fee: 350,
            date_shipped: "2026-06-25T09:00:00Z",
            remarks: null,
        },
        created_at: "2026-06-05T07:00:00Z",
        updated_at: "2026-07-02T16:00:00Z",
    },
];

const orders = computed<Order[]>(() => props.orders ?? mockOrders);

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
const addressForm = reactive({
    recipient_name: "",
    contact_number: "",
    line1: "",
    line2: "",
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
    Object.assign(addressForm, {
        ...order.address,
        line2: order.address.line2 ?? "",
        barangay: order.address.barangay ?? "",
        latitude: order.address.latitude ?? null,
        longitude: order.address.longitude ?? null,
    });
    modal.title.value = "Delivery Address";
    modal.type.value = "Address";
    modal.icon.value = "fa-solid fa-location-dot";
    modal.openModal();
}

function submitAddressUpdate() {
    if (!selectedOrder.value) return;
    // TODO: replace with a real request, e.g.
    // router.patch(route('client.orders.updateAddress', selectedOrder.value.id), addressForm)
    selectedOrder.value.address = {
        ...selectedOrder.value.address,
        ...addressForm,
        line2: addressForm.line2 || null,
        barangay: addressForm.barangay || null,
        latitude: addressForm.latitude,
        longitude: addressForm.longitude,
    };
    closeModal();
}

function closeModal() {
    selectedOrder.value = null;
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Recipient Name</label
                        >
                        <input
                            v-model="addressForm.recipient_name"
                            type="text"
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Contact Number</label
                        >
                        <input
                            v-model="addressForm.contact_number"
                            type="text"
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Postal Code</label
                        >
                        <input
                            v-model="addressForm.postal_code"
                            type="text"
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Address Line 1</label
                        >
                        <input
                            v-model="addressForm.line1"
                            type="text"
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Barangay (optional)</label
                        >
                        <input
                            v-model="addressForm.barangay"
                            type="text"
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >City / Municipality</label
                        >
                        <input
                            v-model="addressForm.city"
                            type="text"
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Province</label
                        >
                        <input
                            v-model="addressForm.province"
                            type="text"
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3"
                        />
                    </div>
                    <div class="sm:col-span-2">
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
                    <PrimaryButton @click="submitAddressUpdate"
                        >Save Address</PrimaryButton
                    >
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>