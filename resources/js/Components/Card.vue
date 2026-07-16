<script setup lang="ts">
import type { JerseyTemplate } from "@/types/jersey";

const props = defineProps<{ template: JerseyTemplate }>();

const emit = defineEmits<{ (e: "select", id: number): void }>();

const badgeStyles: Record<string, string> = {
    New: "bg-green-500 text-white",
    Bestseller: "bg-yellow-500 text-white",
    Hot: "bg-red-500 text-white",
};
</script>

<template>
    <article
        class="group relative flex flex-col overflow-hidden rounded-lg border-2 border-dashed border-ink/15 bg-paper transition-all duration-200 hover:-translate-y-1 hover:border-ink hover:shadow-lg"
    >
        <!-- Thumbnail -->
        <div
            class="relative flex h-44 items-center justify-center overflow-hidden bg-ink transition-colors duration-200 group-hover:bg-ink/5 py-2"
        >
            <img
                class="h-full w-full object-contain rounded"
                :src="template.imagePath"
                alt="t-shirt image"
            />
            <span
                v-if="template.badge"
                :class="badgeStyles[template.badge]"
                class="absolute left-2 top-2 rounded px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide"
            >
                {{ template.badge }}
            </span>

            <span
                class="absolute right-2 top-2 rounded-full px-2 py-0.5 text-[11px] font-medium bg-paper text-ink/90 group-hover:bg-ink group-hover:text-paper transition-colors duration-200"
            >
                {{ template.sport }}
            </span>
        </div>

        <!-- Body -->
        <div class="flex flex-1 flex-col gap-1.5 p-3">
            <div class="flex items-start gap-1.5">
                <div class="flex flex-1 flex-col gap-1.5">
                    <h3
                        class="truncate text-xl tracking-wide text-ink/90 group-hover:text-ink/80"
                    >
                        {{ template.name }}
                    </h3>
                </div>
                <span class="text-lg font-medium text-ink">
                    ₱{{ template.price }}
                </span>
            </div>
            <button
                type="button"
                class="mt-2 w-full rounded bg-ink/90 py-2 text-sm font-semibold text-white transition-colors group-hover:bg-ink"
                @click="emit('select', template.id)"
            >
                Select Template
            </button>
        </div>
    </article>
</template>
