<script setup lang="ts">
import { onMounted, ref } from "vue";

type SelectOption = {
    value: string | number;
    label: string;
    disabled?: boolean;
};

type OptionItem = SelectOption | string | number;

const model = defineModel<string>({
    required: true,
});

const props = defineProps<{
    options: OptionItem[];
}>();

const input = ref<HTMLSelectElement | null>(null);

function isOptionObject(option: OptionItem): option is SelectOption {
    return typeof option === "object";
}

function getValue(option: OptionItem) {
    return isOptionObject(option) ? option.value : option;
}

function getLabel(option: OptionItem) {
    return isOptionObject(option) ? option.label : option;
}

function isDisabled(option: OptionItem) {
    return isOptionObject(option) ? Boolean(option.disabled) : false;
}

onMounted(() => {
    if (input.value?.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <select
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        v-model="model"
        ref="input"
    >
        <option
            v-for="option in options"
            :key="getValue(option)"
            :value="getValue(option)"
            :disabled="isDisabled(option)"
        >
            {{ getLabel(option) }}
        </option>
    </select>
</template>
