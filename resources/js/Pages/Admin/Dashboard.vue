<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Table from "@/Components/Table.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

interface StatBlock {
    value: number;
    change: number;
}

interface Stats {
    totalOrders: StatBlock;
    revenue: StatBlock;
    pendingDesignRequests: StatBlock;
    pendingGcash: StatBlock;
    unreadMessages: StatBlock;
    newUsers: StatBlock;
}

interface BestSellingTemplate {
    name: string;
    sold: number;
}

interface RecentOrder {
    id: string;
    customer: string;
    template: string;
    amount: number;
    status: string;
    courier: string;
    date: string;
}

interface NeedsAttentionItem {
    label: string;
    count: number;
    icon: string;
}

interface DesignRequestItem {
    customer: string;
    status: string;
    date: string;
}

interface GcashTransaction {
    ref: string;
    customer: string;
    amount: number;
    status: string;
}

interface RecentMessage {
    customer: string;
    preview: string;
    time: string;
}

const props = defineProps<{
    stats: Stats;
    bestSellingTemplates: BestSellingTemplate[];
    recentOrders: RecentOrder[];
    needsAttention: NeedsAttentionItem[];
    designRequests: DesignRequestItem[];
    gcashTransactions: GcashTransaction[];
    recentMessages: RecentMessage[];
}>();

const maxSold = computed(() =>
    Math.max(...props.bestSellingTemplates.map((t) => t.sold), 1)
);

const orderColumns = [
    { key: "id", label: "Order" },
    { key: "customer", label: "Customer" },
    { key: "amount", label: "Amount", slot: "amount" },
    { key: "status", label: "Status", slot: "status" },
    { key: "courier", label: "Courier" },
    { key: "date", label: "Date" },
];

function statusColor(status: string) {
    const map: Record<string, string> = {
        // Order statuses
        Processing: "bg-blue-100 text-blue-700",
        "In Production": "bg-indigo-100 text-indigo-700",
        "Ready for Delivery": "bg-cyan-100 text-cyan-700",
        Shipped: "bg-purple-100 text-purple-700",
        Delivered: "bg-emerald-100 text-emerald-700",
        Completed: "bg-emerald-100 text-emerald-700",

        // Design request statuses
        New: "bg-blue-100 text-blue-700",
        "In Discussion": "bg-sky-100 text-sky-700",
        "Revision Requested": "bg-orange-100 text-orange-700",
        "Waiting for Payment": "bg-amber-100 text-amber-700",
        "In Review": "bg-amber-100 text-amber-700",
        Approved: "bg-emerald-100 text-emerald-700",
        Rejected: "bg-rose-100 text-rose-700",

        // GCash statuses
        Pending: "bg-amber-100 text-amber-700",
        Verified: "bg-emerald-100 text-emerald-700",
    };
    return map[status] ?? "bg-gray-100 text-gray-700";
}

function formatCurrency(value: number) {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(value);
}
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <div>
            <div class="mx-auto space-y-6">
                <!-- KPI Cards -->
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6"
                >
                    <div class="rounded-lg bg-ink p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Total Orders</p>
                        <p class="mt-1 text-2xl font-semibold text-paper">
                            {{ stats.totalOrders.value }}
                        </p>
                        <p
                            class="mt-1 text-xs"
                            :class="
                                stats.totalOrders.change >= 0
                                    ? 'text-emerald-600'
                                    : 'text-rose-600'
                            "
                        >
                            {{ stats.totalOrders.change >= 0 ? "+" : ""
                            }}{{ stats.totalOrders.change }}% this week
                        </p>
                    </div>
                    <div class="rounded-lg bg-ink p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Revenue</p>
                        <p class="mt-1 text-2xl font-semibold text-paper">
                            {{ formatCurrency(stats.revenue.value) }}
                        </p>
                        <p
                            class="mt-1 text-xs"
                            :class="
                                stats.revenue.change >= 0
                                    ? 'text-emerald-600'
                                    : 'text-rose-600'
                            "
                        >
                            {{ stats.revenue.change >= 0 ? "+" : ""
                            }}{{ stats.revenue.change }}% this week
                        </p>
                    </div>
                    <div class="rounded-lg bg-ink p-5 shadow-sm">
                        <p class="text-sm text-gray-500">
                            Pending Design Requests
                        </p>
                        <p class="mt-1 text-2xl font-semibold text-paper">
                            {{ stats.pendingDesignRequests.value }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">needs review</p>
                    </div>
                    <div class="rounded-lg bg-ink p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Pending GCash</p>
                        <p class="mt-1 text-2xl font-semibold text-paper">
                            {{ stats.pendingGcash.value }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">to verify</p>
                    </div>
                    <div class="rounded-lg bg-ink p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Unread Messages</p>
                        <p class="mt-1 text-2xl font-semibold text-paper">
                            {{ stats.unreadMessages.value }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            from customers
                        </p>
                    </div>
                    <div class="rounded-lg bg-ink p-5 shadow-sm">
                        <p class="text-sm text-gray-500">New Users</p>
                        <p class="mt-1 text-2xl font-semibold text-paper">
                            {{ stats.newUsers.value }}
                        </p>
                        <p class="mt-1 text-xs text-emerald-600">
                            +{{ stats.newUsers.change }}% this week
                        </p>
                    </div>
                </div>

                <!-- Best selling templates + Needs Attention -->
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div
                        class="rounded-lg bg-white p-6 shadow-sm lg:col-span-2"
                    >
                        <h3 class="font-semibold text-gray-900">
                            Best-Selling Templates
                        </h3>
                        <hr class="mb-3 mt-2" />
                        <div
                            v-if="bestSellingTemplates.length"
                            class="mt-4 space-y-3"
                        >
                            <div
                                v-for="t in bestSellingTemplates"
                                :key="t.name"
                            >
                                <div class="flex justify-between">
                                    <span class="text-gray-700">{{
                                        t.name
                                    }}</span>
                                    <span class="font-medium text-gray-900">{{
                                        t.sold
                                    }}</span>
                                </div>
                                <div
                                    class="mt-1 h-2 w-full rounded-full bg-gray-100"
                                >
                                    <div
                                        class="h-2 rounded-full bg-indigo-500"
                                        :style="{
                                            width:
                                                (t.sold / maxSold) * 100 + '%',
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="mt-4 text-sm text-gray-400">
                            No orders yet.
                        </p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="font-semibold text-gray-900">
                            Needs Attention
                        </h3>
                        <hr class="mb-3 mt-2" />
                        <ul class="mt-4 space-y-3">
                            <li
                                v-for="n in needsAttention"
                                :key="n.label"
                                class="flex items-start gap-3"
                            >
                                <span
                                    class="mt-0.5 flex h-8 w-8 flex-none items-center justify-center rounded-full bg-amber-50 text-warn"
                                >
                                    <font-awesome-icon
                                        :icon="n.icon"
                                        class="text-xs"
                                    ></font-awesome-icon>
                                </span>
                                <div>
                                    <p class="text-gray-700">
                                        {{ n.label }}
                                    </p>
                                    <p
                                        class="text-xs font-medium text-gray-900"
                                    >
                                        {{ n.count }} item(s)
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="grid grid-cols-1 gap-4">
                    <div
                        class="overflow-hidden rounded-lg bg-white shadow-sm p-4"
                    >
                        <div class="flex items-center justify-between pb-2">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Recent Orders
                            </h3>
                            <a
                                :href="route('admin.orders.index')"
                                class="text-xs font-medium border border-ink bg-ink/20 px-2 py-1 text-ink hover:bg-ink hover:text-white rounded-md transition-colors"
                            >
                                View all
                                <font-awesome-icon
                                    icon="fa-solid fa-arrow-right"
                                />
                            </a>
                        </div>
                        <hr class="mb-3" />
                        <Table
                            :data="recentOrders"
                            :columns="orderColumns"
                            date-key="date"
                            :actions="{
                                isDateFilterShow: false,
                                isPerPageShow: false,
                                isSearchShow: false,
                            }"
                        >
                            <template #amount="{ value }">
                                {{ formatCurrency(value as number) }}
                            </template>

                            <template #status="{ value }">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="statusColor(value as string)"
                                >
                                    {{ value }}
                                </span>
                            </template>
                        </Table>
                    </div>
                </div>

                <!-- Design Requests / GCash / Messages -->
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Design Requests
                            </h3>
                            <a
                                :href="route('admin.design.index')"
                                class="text-xs font-medium border border-ink bg-ink/20 px-2 py-1 text-ink hover:bg-ink hover:text-white rounded-md transition-colors"
                            >
                                View all
                                <font-awesome-icon
                                    icon="fa-solid fa-arrow-right"
                                />
                            </a>
                        </div>
                        <hr class="mb-3 mt-2" />
                        <ul
                            v-if="designRequests.length"
                            class="mt-4 divide-y divide-gray-50"
                        >
                            <li
                                v-for="d in designRequests"
                                :key="d.customer + d.date"
                                class="flex items-center justify-between py-2 text-sm"
                            >
                                <div>
                                    <p class="text-gray-800">
                                        {{ d.customer }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ d.date }}
                                    </p>
                                </div>
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="statusColor(d.status)"
                                    >{{ d.status }}</span
                                >
                            </li>
                        </ul>
                        <p v-else class="mt-4 text-sm text-gray-400">
                            No design requests yet.
                        </p>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">
                                GCash Transactions
                            </h3>
                            <a
                                :href="route('admin.gcash.index')"
                                class="text-xs font-medium border border-ink bg-ink/20 px-2 py-1 text-ink hover:bg-ink hover:text-white rounded-md transition-colors"
                            >
                                View all
                                <font-awesome-icon
                                    icon="fa-solid fa-arrow-right"
                                />
                            </a>
                        </div>
                        <hr class="mb-3 mt-2" />
                        <ul
                            v-if="gcashTransactions.length"
                            class="mt-4 divide-y divide-gray-50"
                        >
                            <li
                                v-for="g in gcashTransactions"
                                :key="g.ref"
                                class="flex items-center justify-between py-2 text-sm"
                            >
                                <div>
                                    <p class="text-gray-800">
                                        {{ g.customer }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ g.ref }} &middot;
                                        {{ formatCurrency(g.amount) }}
                                    </p>
                                </div>
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="statusColor(g.status)"
                                    >{{ g.status }}</span
                                >
                            </li>
                        </ul>
                        <p v-else class="mt-4 text-sm text-gray-400">
                            No GCash transactions yet.
                        </p>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Recent Messages
                            </h3>
                            <a
                                :href="route('admin.messages.index')"
                                class="text-xs font-medium border border-ink bg-ink/20 px-2 py-1 text-ink hover:bg-ink hover:text-white rounded-md transition-colors"
                            >
                                View all
                                <font-awesome-icon
                                    icon="fa-solid fa-arrow-right"
                                />
                            </a>
                        </div>
                        <hr class="mb-3 mt-2" />
                        <ul
                            v-if="recentMessages.length"
                            class="mt-4 divide-y divide-gray-50"
                        >
                            <li
                                v-for="m in recentMessages"
                                :key="m.customer + m.time"
                                class="py-2 text-sm"
                            >
                                <div class="flex items-center justify-between">
                                    <p class="font-medium text-gray-800">
                                        {{ m.customer }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ m.time }}
                                    </p>
                                </div>
                                <p
                                    class="mt-0.5 truncate text-xs text-gray-500"
                                >
                                    {{ m.preview }}
                                </p>
                            </li>
                        </ul>
                        <p v-else class="mt-4 text-sm text-gray-400">
                            No messages yet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>