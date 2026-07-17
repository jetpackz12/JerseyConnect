<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Table from "@/Components/Table.vue";
import { Head } from "@inertiajs/vue3";

const stats = {
    totalOrders: { value: 128, change: 12.4 },
    revenue: { value: 84250, change: 8.1 },
    pendingDesignRequests: { value: 5, change: -2 },
    pendingGcash: { value: 3, change: 0 },
    unreadMessages: { value: 7, change: 4 },
    newUsers: { value: 21, change: 15.6 },
};

const bestSellingTemplates = [
    { name: "Classic Home Jersey", sold: 42 },
    { name: "Away Kit 2026", sold: 35 },
    { name: "Retro Edition", sold: 27 },
    { name: "Training Jersey", sold: 19 },
];
const maxSold = Math.max(...bestSellingTemplates.map((t) => t.sold));

const recentOrders = [
    {
        id: "JC-1042",
        customer: "Mark Villanueva",
        template: "Classic Home Jersey",
        amount: 950,
        status: "Processing",
        courier: "J&T Express",
        date: "Jul 17, 2026",
    },
    {
        id: "JC-1041",
        customer: "Aira Santos",
        template: "Away Kit 2026",
        amount: 1050,
        status: "Pending",
        courier: "-",
        date: "Jul 17, 2026",
    },
    {
        id: "JC-1040",
        customer: "Josh Reyes",
        template: "Retro Edition",
        amount: 890,
        status: "Shipped",
        courier: "LBC",
        date: "Jul 16, 2026",
    },
    {
        id: "JC-1039",
        customer: "Cyra Gomez",
        template: "Training Jersey",
        amount: 750,
        status: "Delivered",
        courier: "Ninja Van",
        date: "Jul 16, 2026",
    },
    {
        id: "JC-1038",
        customer: "Dan Ilagan",
        template: "Classic Home Jersey",
        amount: 950,
        status: "Cancelled",
        courier: "-",
        date: "Jul 15, 2026",
    },
];

const needsAttention = [
    {
        label: "GCash payments awaiting verification",
        count: 3,
        icon: "fa-solid fa-wallet",
    },
    {
        label: "Design requests pending review",
        count: 5,
        icon: "fa-solid fa-spray-can-sparkles",
    },
    {
        label: "Orders stuck in Processing 3+ days",
        count: 2,
        icon: "fa-solid fa-triangle-exclamation",
    },
];

const designRequests = [
    { customer: "Mark Villanueva", status: "New", date: "Jul 17, 2026" },
    { customer: "Bea Fernandez", status: "In Review", date: "Jul 16, 2026" },
    { customer: "Kim Uy", status: "Approved", date: "Jul 15, 2026" },
];

const gcashTransactions = [
    {
        ref: "GC-88213",
        customer: "Aira Santos",
        amount: 1050,
        status: "Pending",
    },
    {
        ref: "GC-88190",
        customer: "Josh Reyes",
        amount: 890,
        status: "Verified",
    },
    {
        ref: "GC-88177",
        customer: "Cyra Gomez",
        amount: 750,
        status: "Verified",
    },
];

const recentMessages = [
    {
        customer: "Mark Villanueva",
        preview: "Hi, can I still change the jersey size after ordering?",
        time: "10:24 AM",
    },
    {
        customer: "Bea Fernandez",
        preview: "Is the design request for team logo approved?",
        time: "Yesterday",
    },
    {
        customer: "Josh Reyes",
        preview: "My order says shipped, do you have the tracking no?",
        time: "Yesterday",
    },
];

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
        Pending: "bg-amber-100 text-amber-700",
        Processing: "bg-blue-100 text-blue-700",
        "For Shipping": "bg-indigo-100 text-indigo-700",
        Shipped: "bg-purple-100 text-purple-700",
        Delivered: "bg-emerald-100 text-emerald-700",
        Cancelled: "bg-rose-100 text-rose-700",
        New: "bg-blue-100 text-blue-700",
        "In Review": "bg-amber-100 text-amber-700",
        Approved: "bg-emerald-100 text-emerald-700",
        Rejected: "bg-rose-100 text-rose-700",
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
                        <p class="mt-1 text-xs text-gray-400">from customers</p>
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

                <!-- Order status + Best selling templates -->
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div
                        class="rounded-lg bg-white p-6 shadow-sm lg:col-span-2"
                    >
                        <h3 class="font-semibold text-gray-900">
                            Best-Selling Templates
                        </h3>
                        <hr class="mb-3 mt-2" />
                        <div class="mt-4 space-y-3">
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

                <!-- Recent Orders + Needs Attention -->
                <div class="grid grid-cols-1 gap-4">
                    <div
                        class="overflow-hidden rounded-lg bg-white shadow-sm p-4"
                    >
                        <div class="flex items-center justify-between pb-2">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Recent Orders
                            </h3>
                            <a
                                href="#"
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
                                href="#"
                                class="text-xs font-medium border border-ink bg-ink/20 px-2 py-1 text-ink hover:bg-ink hover:text-white rounded-md transition-colors"
                            >
                                View all
                                <font-awesome-icon
                                    icon="fa-solid fa-arrow-right"
                                />
                            </a>
                        </div>
                        <hr class="mb-3 mt-2" />
                        <ul class="mt-4 divide-y divide-gray-50">
                            <li
                                v-for="d in designRequests"
                                :key="d.customer"
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
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">
                                GCash Transactions
                            </h3>
                            <a
                                href="#"
                                class="text-xs font-medium border border-ink bg-ink/20 px-2 py-1 text-ink hover:bg-ink hover:text-white rounded-md transition-colors"
                            >
                                View all
                                <font-awesome-icon
                                    icon="fa-solid fa-arrow-right"
                                />
                            </a>
                        </div>
                        <hr class="mb-3 mt-2" />
                        <ul class="mt-4 divide-y divide-gray-50">
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
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Recent Messages
                            </h3>
                            <a
                                href="#"
                                class="text-xs font-medium border border-ink bg-ink/20 px-2 py-1 text-ink hover:bg-ink hover:text-white rounded-md transition-colors"
                            >
                                View all
                                <font-awesome-icon
                                    icon="fa-solid fa-arrow-right"
                                />
                            </a>
                        </div>
                        <hr class="mb-3 mt-2" />
                        <ul class="mt-4 divide-y divide-gray-50">
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
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
