<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-[url('/images/login-bg.png')] bg-cover bg-center pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/" class="flex items-center gap-2.5">
                <div class="py-1 rounded bg-white">
                    <img
                        class="h-8 w-auto"
                        src="/images/printcode.png"
                        alt="logo"
                    />
                </div>
                <span
                    class="font-semibold text-[15px] tracking-tight text-paper"
                    >PrintCode</span
                >
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
