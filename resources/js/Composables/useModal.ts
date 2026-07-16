import { ref } from "vue";

export function useModal() {
    const open = ref(false);
    const title = ref("");
    const type = ref("");
    const icon = ref("");

    const openModal = () => {
        open.value = true;
    };

    const closeModal = () => {
        open.value = false;
        title.value = "";
        type.value = "";
        icon.value = "";
    };

    return { open, title, type, icon, openModal, closeModal };
}
