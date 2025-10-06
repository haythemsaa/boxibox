<template>
    <div class="stats-card card shadow-sm h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="stats-label text-muted mb-2">
                        {{ label }}
                    </div>
                    <div class="stats-value mb-2">
                        <AnimatedNumber :value="value" :format="format" />
                    </div>
                    <div v-if="trend !== null" class="stats-trend">
                        <i class="fas" :class="trendIcon"></i>
                        <span :class="trendClass">{{ trendText }}</span>
                    </div>
                </div>
                <div class="stats-icon" :style="{ background: gradient }">
                    <i class="fas" :class="icon"></i>
                </div>
            </div>
        </div>
        <div v-if="showProgress" class="card-footer bg-transparent border-0 pt-0">
            <div class="progress" style="height: 6px;">
                <div
                    class="progress-bar"
                    :class="progressColor"
                    :style="{ width: progressPercentage + '%' }"
                    role="progressbar"
                ></div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed } from 'vue';
import AnimatedNumber from './AnimatedNumber.vue';

export default {
    components: {
        AnimatedNumber
    },

    props: {
        label: {
            type: String,
            required: true
        },
        value: {
            type: [Number, String],
            required: true
        },
        icon: {
            type: String,
            required: true
        },
        format: {
            type: String,
            default: 'number' // 'number', 'currency', 'percent'
        },
        gradient: {
            type: String,
            default: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
        },
        trend: {
            type: Number,
            default: null // Positive or negative percentage
        },
        showProgress: {
            type: Boolean,
            default: false
        },
        progressValue: {
            type: Number,
            default: 0
        },
        progressMax: {
            type: Number,
            default: 100
        },
        progressColor: {
            type: String,
            default: 'bg-primary'
        }
    },

    setup(props) {
        const trendIcon = computed(() => {
            if (props.trend === null) return '';
            return props.trend > 0 ? 'fa-arrow-up' : 'fa-arrow-down';
        });

        const trendClass = computed(() => {
            if (props.trend === null) return '';
            return props.trend > 0 ? 'text-success' : 'text-danger';
        });

        const trendText = computed(() => {
            if (props.trend === null) return '';
            const absValue = Math.abs(props.trend);
            return `${absValue}% ${props.trend > 0 ? 'vs mois dernier' : 'vs mois dernier'}`;
        });

        const progressPercentage = computed(() => {
            if (!props.showProgress) return 0;
            return Math.min(100, (props.progressValue / props.progressMax) * 100);
        });

        return {
            trendIcon,
            trendClass,
            trendText,
            progressPercentage
        };
    }
};
</script>

<style scoped>
.stats-card {
    border: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
}

.stats-label {
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-value {
    font-size: 2rem;
    font-weight: 700;
    color: #212529;
}

.stats-trend {
    font-size: 0.85rem;
    font-weight: 500;
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

/* Dark mode */
.dark-mode .stats-card {
    background: #2b3035;
}

.dark-mode .stats-value {
    color: #f8f9fa;
}

.dark-mode .stats-label {
    color: #adb5bd;
}
</style>
