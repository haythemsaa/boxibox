<template>
    <div>
        <Head :title="title" />

        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
            <div class="container-fluid">
                <a :href="route('client.dashboard')" class="navbar-brand">
                    <i class="fas fa-box-open me-2"></i>BOXIBOX
                </a>
                <span class="navbar-text text-white me-3">
                    Espace Client
                </span>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item me-2">
                            <DarkModeToggle />
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                {{ $page.props.auth?.user?.name || 'Client' }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form @submit.prevent="logout">
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-2 p-0 sidebar">
                    <nav class="nav flex-column py-3">
                        <a :href="route('client.dashboard')"
                           class="nav-link"
                           :class="{ active: $page.url === '/client/dashboard' }">
                            <i class="fas fa-home"></i>Tableau de bord
                        </a>

                        <div class="nav-section-title">Mes Services</div>

                        <a :href="route('client.contrats')"
                           class="nav-link"
                           :class="{ active: $page.url.startsWith('/client/contrats') }">
                            <i class="fas fa-file-contract"></i>Mes contrats
                        </a>
                        <a :href="route('client.boxplan')"
                           class="nav-link"
                           :class="{ active: $page.url.startsWith('/client/box-plan') }">
                            <i class="fas fa-map"></i>Plan des boxes
                        </a>

                        <div class="nav-section-title">Finances</div>

                        <a :href="route('client.factures')"
                           class="nav-link"
                           :class="{ active: $page.url.startsWith('/client/factures') }">
                            <i class="fas fa-file-invoice-dollar"></i>Mes factures
                        </a>
                        <a :href="route('client.reglements')"
                           class="nav-link"
                           :class="{ active: $page.url.startsWith('/client/reglements') }">
                            <i class="fas fa-money-bill-wave"></i>Mes règlements
                        </a>
                        <a :href="route('client.relances')"
                           class="nav-link"
                           :class="{ active: $page.url.startsWith('/client/relances') }">
                            <i class="fas fa-bell"></i>Mes relances
                        </a>
                        <a :href="route('client.sepa')"
                           class="nav-link"
                           :class="{ active: $page.url.startsWith('/client/sepa') }">
                            <i class="fas fa-university"></i>Mandats SEPA
                        </a>

                        <div class="nav-section-title">Documents</div>

                        <a :href="route('client.documents')"
                           class="nav-link"
                           :class="{ active: $page.url.startsWith('/client/documents') }">
                            <i class="fas fa-folder-open"></i>Mes fichiers
                        </a>
                        <a :href="route('client.suivi')"
                           class="nav-link"
                           :class="{ active: $page.url.startsWith('/client/suivi') }">
                            <i class="fas fa-history"></i>Suivi d'activité
                        </a>

                        <div class="nav-section-title">Compte</div>

                        <a :href="route('client.profil')"
                           class="nav-link"
                           :class="{ active: $page.url === '/client/profil' }">
                            <i class="fas fa-user"></i>Mon profil
                        </a>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="col-md-10">
                    <div class="main-content">
                        <!-- Flash Messages -->
                        <div v-if="$page.props.flash?.success" class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ $page.props.flash.success }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <div v-if="$page.props.flash?.error" class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ $page.props.flash.error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <slot />
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <Toast />
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';

export default {
    components: {
        Head,
        Toast,
        DarkModeToggle
    },

    props: {
        title: {
            type: String,
            default: 'Espace Client'
        }
    },

    methods: {
        logout() {
            router.post(route('logout'));
        }
    }
};
</script>

<style scoped>
.sidebar {
    min-height: calc(100vh - 56px);
    background: white;
    box-shadow: 2px 0 5px rgba(0,0,0,0.05);
}

.nav-section-title {
    padding: 1rem 1.5rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #6c757d;
    letter-spacing: 0.5px;
    margin-top: 0.5rem;
}

.sidebar .nav-link {
    color: #495057;
    padding: 0.8rem 1.5rem;
    border-left: 3px solid transparent;
    transition: all 0.2s;
}

.sidebar .nav-link:hover {
    background-color: #f8f9fa;
    border-left-color: #0d6efd;
    color: #0d6efd;
}

.sidebar .nav-link.active {
    background-color: #e7f1ff;
    border-left-color: #0d6efd;
    color: #0d6efd;
    font-weight: 600;
}

.sidebar .nav-link i {
    width: 20px;
    margin-right: 10px;
}

.main-content {
    padding: 2rem;
}

.card {
    border: none;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    border-radius: 10px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
</style>
