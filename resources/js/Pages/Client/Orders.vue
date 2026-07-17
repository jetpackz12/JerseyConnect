<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import type {
    Order,
    OrderStatus,
    ShippingZone,
    CourierReceipt,
} from "@/types/orders.ts";
import {
    SHIPPING_ZONES,
    computeShippingFee,
    isLocalZone,
    formatCurrency,
} from "@/Composables/shipping";
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
            zone: "metro_iloilo",
            is_default: true,
        },
        shipping_fee: 0,
        status: "in_production",
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
            zone: "other_visayas",
            is_default: true,
        },
        shipping_fee: computeShippingFee("other_visayas", 20),
        status: "shipped",
        courier_receipt: {
            id: 1,
            courier_name: "J&T Express",
            transaction_number: "JT-88213764521",
            tracking_url:
                "https://www.jtexpress.ph/tracking?billcode=JT-88213764521",
            date_shipped: "2026-07-12T14:00:00Z",
            remarks: "3 boxes",
        },
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
            zone: "luzon",
            is_default: true,
        },
        shipping_fee: computeShippingFee("luzon", 15),
        status: "delivered",
        courier_receipt: {
            id: 2,
            courier_name: "LBC Express",
            transaction_number: "LBC-100294837",
            tracking_url:
                "https://www.lbcexpress.com/track/?tracking_no=LBC-100294837",
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
    { label: "Cancelled", value: "cancelled" },
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
    cancelled: { label: "Cancelled", class: "bg-red-100 text-red-700" },
};

// Statuses can only move forward, in this order.
const statusFlow: OrderStatus[] = [
    "processing",
    "in_production",
    "ready_for_delivery",
    "shipped",
    "delivered",
    "completed",
];

function nextAllowedStatuses(current: OrderStatus): OrderStatus[] {
    const idx = statusFlow.indexOf(current);
    if (idx === -1) return [];
    return statusFlow.slice(idx + 1);
}

const columns = [
    { key: "template_name", label: "Design", slot: "template" },
    { key: "team_name", label: "Team" },
    { key: "quantity", label: "Qty" },
    { key: "zone", label: "Destination", slot: "zone" },
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

// ---------------------------------------------------------------------------
// Modal state
// ---------------------------------------------------------------------------
const modal = useModal();
const selectedOrder = ref<Order | null>(null);

function orderTotal(order: Order): number {
    return order.quantity * order.unit_price + order.shipping_fee;
}

function viewOrder(order: Order) {
    selectedOrder.value = order;
    modal.title.value = "Order Details";
    modal.type.value = "View";
    modal.icon.value = "fa-solid fa-shirt";
    modal.openModal();
}

// --- Advance status / attach courier receipt ---
const statusForm = reactive<{
    status: OrderStatus | null;
    courier_name: string;
    transaction_number: string;
    tracking_url: string;
    remarks: string;
}>({
    status: null,
    courier_name: "",
    transaction_number: "",
    tracking_url: "",
    remarks: "",
});

const courierOptions = [
    "LBC Express",
    "J&T Express",
    "Grab Express",
    "Lalamove",
    "2GO Express",
];

const needsCourierReceipt = computed(() => statusForm.status === "shipped");

function openStatusModal(order: Order) {
    selectedOrder.value = order;
    statusForm.status = nextAllowedStatuses(order.status)[0] ?? null;
    statusForm.courier_name = order.courier_receipt?.courier_name ?? "";
    statusForm.transaction_number =
        order.courier_receipt?.transaction_number ?? "";
    statusForm.tracking_url = order.courier_receipt?.tracking_url ?? "";
    statusForm.remarks = "";
    modal.title.value = "Update Order Status";
    modal.type.value = "UpdateStatus";
    modal.icon.value = "fa-solid fa-truck";
    modal.openModal();
}

function submitStatusUpdate() {
    if (!selectedOrder.value || !statusForm.status) return;

    // TODO: replace with a real request, e.g.
    // router.patch(route('admin.orders.updateStatus', selectedOrder.value.id), { ...statusForm })
    selectedOrder.value.status = statusForm.status;
    if (statusForm.status === "shipped") {
        const receipt: CourierReceipt = {
            courier_name: statusForm.courier_name,
            transaction_number: statusForm.transaction_number,
            tracking_url: statusForm.tracking_url,
            date_shipped: new Date().toISOString(),
            remarks: statusForm.remarks || null,
        };
        selectedOrder.value.courier_receipt = receipt;
    }
    closeModal();
}

const isCourierFormValid = computed(() => {
    if (!needsCourierReceipt.value) return true;
    return (
        statusForm.courier_name.trim() !== "" &&
        statusForm.transaction_number.trim() !== "" &&
        statusForm.tracking_url.trim() !== ""
    );
});

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
    zone: "metro_iloilo" as ShippingZone,
});

function openAddressModal(order: Order) {
    selectedOrder.value = order;
    Object.assign(addressForm, {
        ...order.address,
        line2: order.address.line2 ?? "",
        barangay: order.address.barangay ?? "",
    });
    modal.title.value = "Delivery Address";
    modal.type.value = "Address";
    modal.icon.value = "fa-solid fa-location-dot";
    modal.openModal();
}

const previewShippingFee = computed(() =>
    selectedOrder.value
        ? computeShippingFee(addressForm.zone, selectedOrder.value.quantity)
        : 0,
);

function submitAddressUpdate() {
    if (!selectedOrder.value) return;
    // TODO: replace with a real request, e.g.
    // router.patch(route('client.orders.updateAddress', selectedOrder.value.id), addressForm)
    selectedOrder.value.address = {
        ...selectedOrder.value.address,
        ...addressForm,
        line2: addressForm.line2 || null,
        barangay: addressForm.barangay || null,
    };
    selectedOrder.value.shipping_fee = previewShippingFee.value;
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

                <template #zone="{ row }">
                    <div class="flex flex-col items-center text-xs">
                        <span class="font-medium text-[#14202B]">{{
                            SHIPPING_ZONES[row.address.zone].label
                        }}</span>
                        <span class="text-[#14202B]/50">{{
                            row.address.city
                        }}</span>
                    </div>
                </template>

                <template #total="{ row }">
                    <div class="flex flex-col items-center text-xs">
                        <span class="font-semibold text-[#14202B]">{{
                            formatCurrency(orderTotal(row))
                        }}</span>
                        <span class="text-[#14202B]/40">
                            incl.
                            {{
                                row.shipping_fee > 0
                                    ? formatCurrency(row.shipping_fee) +
                                      " shipping"
                                    : "free local delivery"
                            }}
                        </span>
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

                <template #tracking="{ row }">
                    <span
                        v-if="row.courier_receipt"
                        class="inline-flex items-center gap-1 font-medium text-blue-600"
                    >
                        {{ row.courier_receipt.transaction_number }}
                    </span>
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
                                !props.readOnly &&
                                row.status !== 'completed' &&
                                row.status !== 'cancelled'
                            "
                            type="button"
                            class="text-xs font-medium bg-gray-600 text-white rounded-md px-2 py-2 transition-colors hover:bg-gray-500"
                            @click="openStatusModal(row)"
                        >
                            <font-awesome-icon icon="fa-solid fa-edit" />
                            Update
                        </button>

                        <button
                            v-if="!props.readOnly && row.status === 'processing'"
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
                            class="text-xs font-medium bg-orange-600 text-white rounded-md px-2 py-2 transition-colors hover:bg-orange-500"
                            :href="route('client.chat.index')"
                        >
                            <font-awesome-icon icon="fa-solid fa-message" />
                            Message
                        </Link>

                        <a
                            class="text-xs font-medium bg-ink text-white rounded-md px-2 py-2 transition-colors hover:bg-ink/90"
                            v-if="row.courier_receipt"
                            :href="row.courier_receipt.tracking_url"
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
                            <p class="text-xs text-[#14202B]/50 mt-1">
                                {{
                                    SHIPPING_ZONES[selectedOrder.address.zone]
                                        .label
                                }}
                                •
                                {{
                                    SHIPPING_ZONES[selectedOrder.address.zone]
                                        .eta
                                }}
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
                                    selectedOrder.shipping_fee > 0
                                        ? formatCurrency(
                                              selectedOrder.shipping_fee,
                                          )
                                        : "Free (local)"
                                }}</span>
                            </div>
                            <hr class="my-2" />
                            <div
                                class="flex justify-between text-sm font-semibold text-[#14202B]"
                            >
                                <span>Total</span>
                                <span>{{
                                    formatCurrency(orderTotal(selectedOrder))
                                }}</span>
                            </div>
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
                                        selectedOrder.courier_receipt
                                            .courier_name
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
                                    :href="
                                        selectedOrder.courier_receipt
                                            .tracking_url
                                    "
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

        <!-- Update Status / Courier Receipt Modal -->
        <Modal
            :show="modal.type.value === 'UpdateStatus'"
            @close="closeModal"
            :maxWidth="'md'"
        >
            <div class="px-4 pt-5 pb-4 sm:p-6" v-if="selectedOrder">
                <h2 class="text-lg font-medium text-gray-900">
                    <font-awesome-icon :icon="modal.icon.value" />
                    {{ modal.title.value }}
                </h2>
                <hr class="my-3" />

                <label class="block text-sm font-medium text-[#14202B] mb-1"
                    >Move to</label
                >
                <select
                    v-model="statusForm.status"
                    class="w-full rounded-md border border-gray-300 text-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
                    <option
                        v-for="s in nextAllowedStatuses(selectedOrder.status)"
                        :key="s"
                        :value="s"
                    >
                        {{ statusBadge[s].label }}
                    </option>
                </select>

                <div
                    v-if="needsCourierReceipt"
                    class="mt-4 border-t border-[#14202B]/10 pt-4 space-y-3"
                >
                    <p class="text-xs text-[#14202B]/60">
                        No courier API is connected — paste the
                        transaction/waybill number and the tracking link the
                        courier gave you at drop-off. The customer will see this
                        as a "Track Package" link.
                    </p>

                    <div>
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Courier</label
                        >
                        <select
                            v-model="statusForm.courier_name"
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="" disabled>Select courier</option>
                            <option
                                v-for="c in courierOptions"
                                :key="c"
                                :value="c"
                            >
                                {{ c }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Transaction / Waybill No.</label
                        >
                        <input
                            v-model="statusForm.transaction_number"
                            type="text"
                            placeholder="e.g. JT-88213764521"
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Tracking Link</label
                        >
                        <input
                            v-model="statusForm.tracking_url"
                            type="url"
                            placeholder="https://courier-site.com/track?..."
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Remarks (optional)</label
                        >
                        <input
                            v-model="statusForm.remarks"
                            type="text"
                            placeholder="e.g. 3 boxes"
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-between">
                    <SecondaryButton @click="closeModal">Close</SecondaryButton>
                    <PrimaryButton
                        :disabled="!statusForm.status || !isCourierFormValid"
                        @click="submitStatusUpdate"
                    >
                        Save
                    </PrimaryButton>
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
                    <div>
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
                    <div>
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
                    <div>
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
                    <div>
                        <label
                            class="block text-sm font-medium text-[#14202B] mb-1"
                            >Shipping Zone</label
                        >
                        <select
                            v-model="addressForm.zone"
                            class="w-full rounded-md border border-gray-300 text-sm py-2 px-3"
                        >
                            <option
                                v-for="(cfg, key) in SHIPPING_ZONES"
                                :key="key"
                                :value="key"
                            >
                                {{ cfg.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div
                    class="mt-4 flex items-center justify-between rounded-md bg-[#14202B]/5 px-3 py-2"
                >
                    <span class="text-sm text-[#14202B]/70"
                        >Estimated shipping fee</span
                    >
                    <span class="text-sm font-semibold text-[#14202B]">
                        {{
                            previewShippingFee > 0
                                ? formatCurrency(previewShippingFee)
                                : "Free (local)"
                        }}
                    </span>
                </div>

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
