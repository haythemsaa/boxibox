<template>
    <div class="skeleton-loader" :class="type">
        <!-- Skeleton pour Table -->
        <div v-if="type === 'table'" class="skeleton-table">
            <div class="skeleton-table-header">
                <div v-for="i in columns" :key="i" class="skeleton-th"></div>
            </div>
            <div v-for="row in rows" :key="row" class="skeleton-table-row">
                <div v-for="col in columns" :key="col" class="skeleton-td"></div>
            </div>
        </div>

        <!-- Skeleton pour Card -->
        <div v-else-if="type === 'card'" class="skeleton-card">
            <div class="skeleton-card-header"></div>
            <div class="skeleton-card-body">
                <div class="skeleton-line"></div>
                <div class="skeleton-line"></div>
                <div class="skeleton-line short"></div>
            </div>
        </div>

        <!-- Skeleton pour Liste -->
        <div v-else-if="type === 'list'" class="skeleton-list">
            <div v-for="i in rows" :key="i" class="skeleton-list-item">
                <div class="skeleton-avatar"></div>
                <div class="skeleton-list-content">
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line short"></div>
                </div>
            </div>
        </div>

        <!-- Skeleton pour Texte -->
        <div v-else-if="type === 'text'" class="skeleton-text">
            <div v-for="i in rows" :key="i" class="skeleton-line" :class="{ short: i === rows }"></div>
        </div>

        <!-- Skeleton pour Dashboard Cards -->
        <div v-else-if="type === 'dashboard'" class="skeleton-dashboard">
            <div class="skeleton-stat-card" v-for="i in 4" :key="i">
                <div class="skeleton-stat-icon"></div>
                <div class="skeleton-stat-content">
                    <div class="skeleton-line short"></div>
                    <div class="skeleton-stat-number"></div>
                </div>
            </div>
        </div>

        <!-- Skeleton par défaut (Texte) -->
        <div v-else class="skeleton-text">
            <div v-for="i in rows" :key="i" class="skeleton-line"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SkeletonLoader',

    props: {
        type: {
            type: String,
            default: 'text',
            validator: (value) => ['table', 'card', 'list', 'text', 'dashboard'].includes(value)
        },
        rows: {
            type: Number,
            default: 3
        },
        columns: {
            type: Number,
            default: 4
        }
    }
};
</script>

<style scoped>
/* Animation de pulsation */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.skeleton-loader {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Éléments de base */
.skeleton-line {
    height: 16px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    border-radius: 4px;
    margin-bottom: 12px;
    animation: shimmer 2s infinite;
}

.skeleton-line.short {
    width: 60%;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

/* Mode sombre */
.dark-mode .skeleton-line {
    background: linear-gradient(90deg, #2b3035 25%, #343a40 50%, #2b3035 75%);
    background-size: 200% 100%;
}

/* Skeleton Table */
.skeleton-table {
    width: 100%;
}

.skeleton-table-header {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}

.skeleton-th {
    flex: 1;
    height: 40px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    border-radius: 4px;
    animation: shimmer 2s infinite;
}

.skeleton-table-row {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
}

.skeleton-td {
    flex: 1;
    height: 32px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    border-radius: 4px;
    animation: shimmer 2s infinite;
}

.dark-mode .skeleton-th,
.dark-mode .skeleton-td {
    background: linear-gradient(90deg, #2b3035 25%, #343a40 50%, #2b3035 75%);
    background-size: 200% 100%;
}

/* Skeleton Card */
.skeleton-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.skeleton-card-header {
    height: 50px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}

.skeleton-card-body {
    padding: 20px;
}

.dark-mode .skeleton-card {
    border-color: #495057;
}

.dark-mode .skeleton-card-header {
    background: linear-gradient(90deg, #2b3035 25%, #343a40 50%, #2b3035 75%);
    background-size: 200% 100%;
}

/* Skeleton List */
.skeleton-list-item {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
    align-items: center;
}

.skeleton-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    flex-shrink: 0;
    animation: shimmer 2s infinite;
}

.skeleton-list-content {
    flex: 1;
}

.dark-mode .skeleton-avatar {
    background: linear-gradient(90deg, #2b3035 25%, #343a40 50%, #2b3035 75%);
    background-size: 200% 100%;
}

/* Skeleton Dashboard */
.skeleton-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.skeleton-stat-card {
    display: flex;
    gap: 16px;
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    align-items: center;
}

.skeleton-stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    flex-shrink: 0;
    animation: shimmer 2s infinite;
}

.skeleton-stat-content {
    flex: 1;
}

.skeleton-stat-number {
    height: 32px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    border-radius: 4px;
    margin-top: 8px;
    animation: shimmer 2s infinite;
}

.dark-mode .skeleton-stat-card {
    border-color: #495057;
}

.dark-mode .skeleton-stat-icon,
.dark-mode .skeleton-stat-number {
    background: linear-gradient(90deg, #2b3035 25%, #343a40 50%, #2b3035 75%);
    background-size: 200% 100%;
}
</style>
