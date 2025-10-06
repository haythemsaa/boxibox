import { ref, watch, onMounted } from 'vue';

const isDarkMode = ref(false);

export function useDarkMode() {
    onMounted(() => {
        // Récupérer la préférence depuis localStorage
        const savedMode = localStorage.getItem('darkMode');
        if (savedMode !== null) {
            isDarkMode.value = savedMode === 'true';
        } else {
            // Utiliser la préférence système par défaut
            isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }

        // Appliquer le mode
        applyDarkMode();
    });

    watch(isDarkMode, () => {
        applyDarkMode();
        localStorage.setItem('darkMode', isDarkMode.value.toString());
    });

    function applyDarkMode() {
        if (isDarkMode.value) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            document.body.classList.add('dark-mode');
        } else {
            document.documentElement.setAttribute('data-bs-theme', 'light');
            document.body.classList.remove('dark-mode');
        }
    }

    function toggleDarkMode() {
        isDarkMode.value = !isDarkMode.value;
    }

    return {
        isDarkMode,
        toggleDarkMode
    };
}
