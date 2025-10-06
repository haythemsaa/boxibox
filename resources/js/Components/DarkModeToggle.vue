<template>
    <div class="dark-mode-toggle">
        <button
            @click="toggleDarkMode"
            class="btn btn-sm"
            :class="isDarkMode ? 'btn-outline-light' : 'btn-outline-dark'"
            :title="isDarkMode ? 'Mode clair' : 'Mode sombre'"
        >
            <i class="fas" :class="isDarkMode ? 'fa-sun' : 'fa-moon'"></i>
        </button>
    </div>
</template>

<script>
import { useDarkMode } from '@/composables/useDarkMode';

export default {
    name: 'DarkModeToggle',

    setup() {
        const { isDarkMode, toggleDarkMode } = useDarkMode();

        return {
            isDarkMode,
            toggleDarkMode
        };
    }
};
</script>

<style scoped>
.dark-mode-toggle {
    display: inline-block;
}

.dark-mode-toggle .btn {
    border-radius: 50%;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.dark-mode-toggle .btn:hover {
    transform: rotate(180deg);
}
</style>
