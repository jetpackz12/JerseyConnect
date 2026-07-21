<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import type {
    MessageThread,
    ThreadMessage,
    ConversationStage,
} from "@/types/messages";

// ── Props ────────────────────────────────────────────────────────────────────
// Arriving from a "Message" button on the Design Request or Order tables:
//   route('admin.messages', { design_request_id: row.id })              // from Design Requests
//   route('admin.messages', { design_request_id: row.design_request_id }) // from Orders
// Both resolve to the SAME thread because a thread is keyed by design_request_id.
const props = withDefaults(
    defineProps<{
        threads?: MessageThread[];
        design_request_id?: number | string | null;
    }>(),
    {
        threads: () => [],
        design_request_id: null,
    },
);

// ── Static mock data (swap for props.threads once the backend is wired up) ──
const mockThreads = ref<MessageThread[]>([
    {
        id: 1,
        design_request_id: 2,
        design_request_ref: "DR-2026-0002",
        order_id: null,
        order_ref: null,
        stage: "design",
        status_key: "in_discussion",
        status_label: "In Discussion",
        status_class: "bg-blue-100 text-blue-700",
        team_name: "Molo Warriors",
        template_name: "Vortex Fade",
        template_image: "/images/image2.png",
        client_name: "Renz Villaflor",
        read: true,
        updated_at: "2026-07-18T14:45:00Z",
        closed: false,
        messages: [
            {
                id: "m1",
                from: "client",
                name: "Renz Villaflor",
                body: "Hi! Can we make the fade go from green to yellow instead of green to navy?",
                time: "Jul 18, 2:10 PM",
            },
            {
                id: "m2",
                from: "admin",
                name: "Captain Rodel",
                body: "Sure, that works well with your accent color. I'll have the artist update the mockup and send it over.",
                time: "Jul 18, 2:30 PM",
            },
            {
                id: "m3",
                from: "admin",
                name: "Captain Rodel",
                body: "Updated mockup attached — let us know if the gradient balance looks right.",
                time: "Jul 18, 2:45 PM",
                attachment_url: "/images/image2.png",
                attachment_name: "vortex-fade-mockup-v2.png",
            },
        ],
    },
    {
        id: 2,
        design_request_id: 3,
        design_request_ref: "DR-2026-0003",
        order_id: null,
        order_ref: null,
        stage: "design",
        status_key: "revision_requested",
        status_label: "Revision Requested",
        status_class: "bg-orange-100 text-orange-700",
        team_name: "Jaro Falcons",
        template_name: "Retro Stripe",
        template_image: "/images/image3.png",
        client_name: "Carlo Reyes",
        read: false,
        updated_at: "2026-07-20T09:05:00Z",
        closed: false,
        messages: [
            {
                id: "m1",
                from: "client",
                name: "Carlo Reyes",
                body: "The sponsor logo is too close to the number on the back. Can it move down, below the number instead?",
                time: "Jul 20, 9:05 AM",
            },
        ],
    },
    {
        id: 3,
        design_request_id: 4,
        design_request_ref: "DR-2026-0004",
        order_id: 4,
        order_ref: "ORD-2026-0004",
        stage: "order",
        status_key: "shipped",
        status_label: "Shipped",
        status_class: "bg-indigo-100 text-indigo-700",
        team_name: "Arevalo Titans",
        template_name: "Minimalist Crest",
        template_image: "/images/image4.png",
        client_name: "Marco Villanueva",
        read: false,
        updated_at: "2026-07-19T14:05:00Z",
        closed: false,
        messages: [
            {
                id: "m1",
                from: "admin",
                name: "System",
                body: "Design approved. This conversation now follows your order through production and delivery.",
                time: "Jul 05, 10:00 AM",
            },
            {
                id: "m2",
                from: "admin",
                name: "Captain Rodel",
                body: "Your order has shipped via J&T Express. Tracking number: JT-88213764521.",
                time: "Jul 19, 2:00 PM",
            },
            {
                id: "m3",
                from: "client",
                name: "Marco Villanueva",
                body: "Thank you! How many days until it arrives in Iloilo?",
                time: "Jul 19, 2:05 PM",
            },
        ],
    },
    {
        id: 4,
        design_request_id: 9,
        design_request_ref: "DR-2026-0009",
        order_id: 5,
        order_ref: "ORD-2026-0005",
        stage: "order",
        status_key: "delivered",
        status_label: "Delivered",
        status_class: "bg-teal-100 text-teal-700",
        team_name: "Cebu Coastal Runners",
        template_name: "Vortex Fade",
        template_image: "/images/image2.png",
        client_name: "Anna Bautista",
        read: true,
        updated_at: "2026-07-14T10:00:00Z",
        closed: false,
        messages: [
            {
                id: "m1",
                from: "admin",
                name: "System",
                body: "Your order has been delivered. Let us know if everything checks out!",
                time: "Jul 14, 10:00 AM",
            },
            {
                id: "m2",
                from: "client",
                name: "Anna Bautista",
                body: "Received, everything looks great. Thank you!",
                time: "Jul 14, 11:20 AM",
            },
        ],
    },
]);

// ── State ────────────────────────────────────────────────────────────────────
type TabKey = "all" | "design" | "order";
const activeTab = ref<TabKey>("all");
const activeThread = ref<MessageThread | null>(null);
const replyText = ref<string>("");
const replyImage = ref<File | null>(null);
const replyImagePreview = ref<string | null>(null);
const senderName = ref<string>("Captain Rodel");
const threadBody = ref<HTMLElement | null>(null);

const tabs: { key: TabKey; label: string }[] = [
    { key: "all", label: "All" },
    { key: "design", label: "In Design" },
    { key: "order", label: "In Production / Delivery" },
];

// ── Computed ─────────────────────────────────────────────────────────────────
const threads = computed<MessageThread[]>(() =>
    props.threads?.length ? props.threads : mockThreads.value,
);

const unreadCount = computed<number>(
    () => threads.value.filter((t) => !t.read).length,
);

const sorted = computed<MessageThread[]>(() =>
    [...threads.value].sort(
        (a, b) =>
            new Date(b.updated_at).getTime() - new Date(a.updated_at).getTime(),
    ),
);

const filtered = computed<MessageThread[]>(() => {
    if (activeTab.value === "design")
        return sorted.value.filter((t) => t.stage === "design");
    if (activeTab.value === "order")
        return sorted.value.filter((t) => t.stage === "order");
    return sorted.value;
});

function lastMessagePreview(thread: MessageThread): string {
    const last = thread.messages[thread.messages.length - 1];
    return last ? last.body : "No messages yet";
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function stagePillClass(stage: ConversationStage): string {
    return stage === "design"
        ? "bg-purple-100 text-purple-700"
        : "bg-emerald-100 text-emerald-700";
}

function stagePillLabel(stage: ConversationStage): string {
    return stage === "design" ? "Design Request" : "Order";
}

// ── Actions ──────────────────────────────────────────────────────────────────
function openThread(thread: MessageThread): void {
    thread.read = true;
    activeThread.value = thread;
    replyText.value = "";
    // TODO: axios.patch(route('admin.messages.markRead', thread.id))
    scrollThread();
}

function scrollThread(): void {
    setTimeout(() => {
        if (threadBody.value)
            threadBody.value.scrollTop = threadBody.value.scrollHeight;
    }, 50);
}

function handleImageSelect(e: Event): void {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;
    replyImage.value = file;
    replyImagePreview.value = URL.createObjectURL(file);
    target.value = ""; // allow re-selecting the same file later
}

function removeReplyImage(): void {
    replyImage.value = null;
    replyImagePreview.value = null;
}

function openAttachment(url: string): void {
    window.open(url, "_blank");
}

function sendReply(thread: MessageThread): void {
    if ((!replyText.value.trim() && !replyImage.value) || thread.closed)
        return;
    const msg: ThreadMessage = {
        id: `${thread.id}-${Date.now()}`,
        from: "admin",
        name: senderName.value,
        body: replyText.value.trim(),
        time: "Just now",
        attachment_url: replyImagePreview.value,
        attachment_name: replyImage.value?.name ?? null,
    };
    thread.messages.push(msg);
    thread.updated_at = new Date().toISOString();
    replyText.value = "";
    removeReplyImage();
    scrollThread();
    // TODO: axios.post(route('admin.messages.reply', thread.id), formData)
    // where formData carries { body, sender, attachment } — use
    // forceFormData / useForm() once this is wired to a real endpoint,
    // the same way the client's GCash payment-proof upload works.
}

/**
 * Finds (or, for a brand-new design request with no admin reply yet, creates
 * a stub for) the thread tied to a given design_request_id, and opens it.
 * This is what makes the "Message" button on the Design Request / Order
 * tables land on the right conversation.
 */
function resolveAndOpenByDesignRequestId(designRequestId: number): void {
    const existing = threads.value.find(
        (t) => t.design_request_id === designRequestId,
    );
    if (existing) {
        openThread(existing);
        return;
    }

    // No conversation yet — create an empty stub. In production this data
    // (team name, template, ref, status) would come from the backend
    // record instead of being guessed here.
    const stub: MessageThread = {
        id: Date.now(),
        design_request_id: designRequestId,
        design_request_ref: `DR-2026-${String(designRequestId).padStart(4, "0")}`,
        order_id: null,
        order_ref: null,
        stage: "design",
        status_key: "pending_review",
        status_label: "Pending Review",
        status_class: "bg-yellow-100 text-yellow-700",
        team_name: "—",
        template_name: "—",
        template_image: "/images/image1.png",
        client_name: "—",
        read: true,
        updated_at: new Date().toISOString(),
        closed: false,
        messages: [],
    };
    mockThreads.value.unshift(stub);
    openThread(stub);
}

onMounted(() => {
    if (props.design_request_id) {
        resolveAndOpenByDesignRequestId(Number(props.design_request_id));
    }
});
</script>

<template>
    <Head title="Messages" />

    <AdminLayout>
        <div>
            <div
                class="flex h-[calc(100dvh-140px)] md:h-[80dvh-40px] rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden"
            >
                <aside
                    class="w-full md:w-80 flex-shrink-0 border-r border-gray-200 flex-col"
                    :class="activeThread ? 'hidden md:flex' : 'flex'"
                >
                    <!-- Header -->
                    <div class="p-4 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <font-awesome-icon
                                icon="fa-solid fa-comments"
                                class="text-ink"
                            />
                            <span class="font-semibold text-slate-900 text-sm"
                                >Messages</span
                            >
                            <span
                                v-if="unreadCount"
                                class="ml-auto rounded-full bg-warn px-2 py-0.5 text-[10px] font-bold text-ink"
                            >
                                {{ unreadCount }}
                            </span>
                        </div>
                        <p class="mt-1 text-[11px] text-gray-400">
                            Conversations are opened from a Design Request or
                            Order.
                        </p>
                    </div>

                    <!-- Tabs -->
                    <div class="flex border-b border-gray-100">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            class="flex-1 py-2.5 text-xs font-medium border-b-2 transition"
                            :class="
                                activeTab === tab.key
                                    ? 'border-warn text-slate-900'
                                    : 'border-transparent text-gray-500 hover:text-slate-900'
                            "
                            @click="activeTab = tab.key"
                        >
                            {{ tab.label }}
                        </button>
                    </div>

                    <!-- Thread list -->
                    <div class="flex-1 overflow-y-auto">
                        <div
                            v-for="thread in filtered"
                            :key="thread.id"
                            class="relative flex cursor-pointer gap-2.5 border-b border-gray-100 px-4 py-3 transition hover:bg-gray-50"
                            :class="{
                                'bg-blue-50 hover:bg-blue-50':
                                    activeThread?.id === thread.id,
                                'border-l-2 border-l-warn': !thread.read,
                            }"
                            @click="openThread(thread)"
                        >
                            <img
                                :src="thread.template_image"
                                :alt="thread.template_name"
                                class="h-9 w-9 flex-shrink-0 rounded object-contain bg-gray-100 p-1"
                            />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="truncate text-xs font-medium text-slate-800"
                                        :class="{
                                            'font-semibold': !thread.read,
                                        }"
                                    >
                                        {{ thread.team_name }}
                                    </span>
                                    <span
                                        v-if="!thread.read"
                                        class="h-1.5 w-1.5 flex-shrink-0 rounded-full bg-warn"
                                    />
                                </div>
                                <div
                                    class="mt-1 flex flex-wrap items-center gap-1"
                                >
                                    <span
                                        class="rounded-full px-1.5 py-0.5 text-[9px] font-semibold"
                                        :class="stagePillClass(thread.stage)"
                                    >
                                        {{ stagePillLabel(thread.stage) }}
                                    </span>
                                    <span
                                        class="rounded-full px-1.5 py-0.5 text-[9px] font-semibold"
                                        :class="thread.status_class"
                                    >
                                        {{ thread.status_label }}
                                    </span>
                                    <span
                                        class="font-mono text-[9px] text-gray-400 bg-gray-100 rounded px-1"
                                        >{{
                                            thread.order_ref ??
                                            thread.design_request_ref
                                        }}</span
                                    >
                                </div>
                                <p
                                    class="mt-1 truncate text-xs text-gray-500"
                                >
                                    {{ lastMessagePreview(thread) }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="filtered.length === 0"
                            class="flex flex-col items-center gap-2 py-16 text-center text-gray-400"
                        >
                            <span class="text-3xl">📭</span>
                            <p class="text-sm">No conversations here</p>
                        </div>
                    </div>
                </aside>

                <main
                    class="flex-1 flex-col overflow-hidden"
                    :class="activeThread ? 'flex' : 'hidden md:flex'"
                >
                    <!-- Empty state -->
                    <div
                        v-if="!activeThread"
                        class="flex flex-1 flex-col items-center justify-center text-gray-400 gap-3"
                    >
                        <span class="text-5xl">
                            <font-awesome-icon icon="fa-solid fa-inbox" />
                        </span>
                        <p class="font-medium text-gray-500">
                            Select a conversation
                        </p>
                        <p class="text-sm">
                            Or open one from a Design Request or Order
                        </p>
                    </div>

                    <!-- Thread view -->
                    <template v-else>
                        <!-- Thread header -->
                        <div
                            class="flex flex-wrap items-start justify-between gap-2 border-b border-gray-100 px-4 sm:px-6 py-4"
                        >
                            <div class="flex items-start gap-3 min-w-0">
                                <button
                                    class="md:hidden mt-0.5 -ml-1 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-50 transition"
                                    aria-label="Back to messages"
                                    @click="activeThread = null"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15.75 19.5 8.25 12l7.5-7.5"
                                        />
                                    </svg>
                                </button>
                                <img
                                    :src="activeThread.template_image"
                                    :alt="activeThread.template_name"
                                    class="h-10 w-10 flex-shrink-0 rounded object-contain bg-gray-100 p-1"
                                />
                                <div class="min-w-0">
                                    <h3
                                        class="text-sm font-semibold text-slate-900 truncate"
                                    >
                                        {{ activeThread.team_name }} —
                                        {{ activeThread.template_name }}
                                    </h3>
                                    <div
                                        class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500"
                                    >
                                        <span>{{
                                            activeThread.client_name
                                        }}</span>
                                        <span
                                            class="font-mono bg-gray-100 rounded px-1"
                                            >{{
                                                activeThread.order_ref ??
                                                activeThread.design_request_ref
                                            }}</span
                                        >
                                        <span
                                            class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                            :class="
                                                stagePillClass(
                                                    activeThread.stage,
                                                )
                                            "
                                        >
                                            {{
                                                stagePillLabel(
                                                    activeThread.stage,
                                                )
                                            }}
                                        </span>
                                        <span
                                            class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                            :class="activeThread.status_class"
                                        >
                                            {{ activeThread.status_label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2 flex-shrink-0">
                                <Link
                                    v-if="activeThread.stage === 'order'"
                                    :href="
                                        route(
                                            'admin.orders',
                                        )
                                    "
                                    class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                                >
                                    <font-awesome-icon
                                        icon="fa-solid fa-box"
                                    />
                                    View order
                                </Link>
                                <Link
                                    v-else
                                    :href="
                                        route(
                                            'admin.design',
                                            activeThread.design_request_id,
                                        )
                                    "
                                    class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                                >
                                    <font-awesome-icon
                                        icon="fa-solid fa-tshirt"
                                    />
                                    View design request
                                </Link>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div
                            ref="threadBody"
                            class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 space-y-3"
                        >
                            <div
                                v-if="activeThread.messages.length === 0"
                                class="flex h-full flex-col items-center justify-center gap-2 text-center text-gray-400"
                            >
                                <span class="text-3xl">💬</span>
                                <p class="text-sm">
                                    No messages yet — say hello to
                                    {{ activeThread.client_name }} to start
                                    the design discussion.
                                </p>
                            </div>
                            <div
                                v-for="msg in activeThread.messages"
                                :key="msg.id"
                                class="flex"
                                :class="
                                    msg.from === 'client'
                                        ? 'justify-end'
                                        : 'justify-start'
                                "
                            >
                                <div
                                    class="max-w-[85%] sm:max-w-[72%] rounded-xl px-4 py-2.5 text-sm leading-relaxed"
                                    :class="
                                        msg.from === 'client'
                                            ? 'rounded-br-sm bg-ink text-white'
                                            : 'rounded-bl-sm border border-gray-200 bg-gray-50 text-gray-800'
                                    "
                                >
                                    <p
                                        class="mb-0.5 text-[10px] font-bold uppercase tracking-wide opacity-60"
                                    >
                                        {{ msg.name }}
                                    </p>
                                    <img
                                        v-if="msg.attachment_url"
                                        :src="msg.attachment_url"
                                        :alt="msg.attachment_name ?? 'Attached image'"
                                        class="mb-1.5 max-h-56 w-full rounded-lg border border-black/10 object-cover cursor-pointer"
                                        @click="
                                            openAttachment(msg.attachment_url!)
                                        "
                                    />
                                    <p v-if="msg.body">{{ msg.body }}</p>
                                    <p
                                        class="mt-1 text-right text-[10px] opacity-80"
                                    >
                                        {{ msg.time }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Reply box -->
                        <div
                            class="border-t border-gray-100 px-4 sm:px-6 py-4"
                        >
                            <div
                                v-if="activeThread.closed"
                                class="rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-500"
                            >
                                <font-awesome-icon
                                    icon="fa-solid fa-lock"
                                />
                                This order is completed. The conversation is
                                now read-only.
                            </div>
                            <template v-else>
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="text-xs text-gray-500"
                                        >Reply as:</span
                                    >
                                    <select
                                        v-model="senderName"
                                        class="rounded-md border border-gray-200 py-1 text-xs text-gray-700 focus:border-blue-900 focus:outline-none"
                                    >
                                        <option>Captain Rodel</option>
                                        <option
                                            >The Butal Ship Hauz Team</option
                                        >
                                    </select>
                                </div>
                                <div
                                    v-if="replyImagePreview"
                                    class="relative mb-2 inline-block"
                                >
                                    <img
                                        :src="replyImagePreview"
                                        alt="Attachment preview"
                                        class="max-h-32 rounded-lg border border-gray-200 object-cover"
                                    />
                                    <button
                                        type="button"
                                        class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] text-white hover:bg-red-500"
                                        @click="removeReplyImage"
                                    >
                                        <font-awesome-icon
                                            icon="fa-solid fa-xmark"
                                        />
                                    </button>
                                </div>
                                <textarea
                                    v-model="replyText"
                                    rows="3"
                                    placeholder="Type your reply here…"
                                    class="w-full resize-none rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-900/10"
                                    @keydown.ctrl.enter="
                                        sendReply(activeThread)
                                    "
                                />
                                <div
                                    class="mt-2 flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-2">
                                        <label
                                            class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition"
                                        >
                                            <font-awesome-icon
                                                icon="fa-solid fa-image"
                                            />
                                            Attach image
                                            <input
                                                type="file"
                                                accept="image/*"
                                                class="hidden"
                                                @change="handleImageSelect"
                                            />
                                        </label>
                                        <span
                                            class="hidden text-[11px] text-gray-400 sm:inline"
                                            >Ctrl + Enter to send</span
                                        >
                                    </div>
                                    <button
                                        :disabled="
                                            !replyText.trim() && !replyImage
                                        "
                                        class="flex items-center gap-2 rounded-lg bg-ink px-4 py-1.5 text-sm font-semibold text-white hover:bg-ink/90 transition disabled:cursor-not-allowed disabled:opacity-40"
                                        @click="sendReply(activeThread)"
                                    >
                                        Send
                                        <font-awesome-icon
                                            icon="fa-solid fa-paper-plane"
                                        />
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </main>
            </div>
        </div>
    </AdminLayout>
</template>