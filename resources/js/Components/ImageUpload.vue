<script setup lang="ts">
import { ref, watch  } from "vue";

interface ImageStyle {
    backgroundImage: string;
}

const props = withDefaults(
    defineProps<{
        image?: ImageStyle;
    }>(),
    {
        image: () => ({ backgroundImage: "" }),
    },
);

const emit = defineEmits<{
    imageFile: [file: File];
}>();

const isInvalidImage = ref(false);
const previewImage = ref<ImageStyle>(props.image);

watch(
    () => props.image,
    (newImage) => {
        previewImage.value = newImage;
        isInvalidImage.value = false;
    },
);

const uploadImage = (e: Event): void => {
    const target = e.target as HTMLInputElement;
    const image = target.files?.[0];

    isInvalidImage.value = false;

    if (!image) return;

    const allowed = ["image/png", "image/jpeg"];

    if (!allowed.includes(image.type)) {
        isInvalidImage.value = true;
        return;
    }

    previewImage.value = {
        backgroundImage: `url(${URL.createObjectURL(image)})`,
    };

    emit("imageFile", image);
};
</script>

<template>
    <div class="flex flex-col gap-1">
        <div
            class="border border-dashed border-gray-400 rounded-sm h-[250px] md:h-[350px] flex justify-center items-center bg-contain bg-center bg-no-repeat"
            :style="previewImage"
        >
            <div
                class="p-1 border border-dashed border-gray-400 rounded-md bg-black text-gray-500 opacity-70"
            >
                <label
                    class="flex justify-center items-center gap-2 cursor-pointer"
                    for="image"
                >
                    <font-awesome-icon
                        class="text-3xl"
                        icon="image"
                    ></font-awesome-icon>
                    <span>Upload a Image</span>
                </label>
            </div>
            <input
                id="image"
                class="hidden"
                type="file"
                name="image"
                accept="image/*"
                @change="uploadImage"
            />
        </div>
        <span class="text-red-600 italic" v-if="isInvalidImage"
            >Please upload a valid image!</span
        >
    </div>
</template>
