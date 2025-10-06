<template>
    <span>{{ displayValue }}</span>
</template>

<script>
import { ref, watch, onMounted } from 'vue';

export default {
    props: {
        value: {
            type: [Number, String],
            required: true
        },
        format: {
            type: String,
            default: 'number' // 'number', 'currency', 'percent'
        },
        duration: {
            type: Number,
            default: 1000 // Animation duration in ms
        }
    },

    setup(props) {
        const displayValue = ref(formatValue(0, props.format));
        let animationFrame = null;

        function formatValue(val, format) {
            const numValue = typeof val === 'string' ? parseFloat(val) : val;

            switch (format) {
                case 'currency':
                    return new Intl.NumberFormat('fr-FR', {
                        style: 'currency',
                        currency: 'EUR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(numValue);
                case 'percent':
                    return `${numValue.toFixed(1)}%`;
                default:
                    return new Intl.NumberFormat('fr-FR').format(numValue);
            }
        }

        function animateValue(start, end, duration) {
            const startTime = Date.now();
            const diff = end - start;

            function update() {
                const now = Date.now();
                const progress = Math.min((now - startTime) / duration, 1);

                // Ease out cubic
                const easeProgress = 1 - Math.pow(1 - progress, 3);

                const currentValue = start + (diff * easeProgress);
                displayValue.value = formatValue(currentValue, props.format);

                if (progress < 1) {
                    animationFrame = requestAnimationFrame(update);
                }
            }

            update();
        }

        function updateValue(newValue) {
            if (animationFrame) {
                cancelAnimationFrame(animationFrame);
            }

            const currentNum = typeof displayValue.value === 'string'
                ? parseFloat(displayValue.value.replace(/[^\d.-]/g, ''))
                : displayValue.value;
            const newNum = typeof newValue === 'string' ? parseFloat(newValue) : newValue;

            animateValue(currentNum || 0, newNum, props.duration);
        }

        watch(() => props.value, (newValue) => {
            updateValue(newValue);
        });

        onMounted(() => {
            updateValue(props.value);
        });

        return {
            displayValue
        };
    }
};
</script>
