<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Search, Ban, ClipboardList, Settings, ChevronLeft, ChevronRight, Terminal } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Referência para o elemento canvas do gráfico
const chartCanvas = ref<HTMLCanvasElement | null>(null);
// Instância do gráfico
let chart: Chart | null = null;

// Estado para controle do mês/ano
const currentMonth = ref(new Date().getMonth() + 1); // 1-12
const currentYear = ref(new Date().getFullYear());
const monthName = ref('');
const isLoading = ref(false);

// Função para formatar o nome do mês em português
function getMonthName(month: number): string {
    const months = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];
    return months[month - 1];
}

// Função para navegar para o mês anterior
function previousMonth() {
    if (currentMonth.value === 1) {
        currentMonth.value = 12;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
}

// Função para navegar para o próximo mês
function nextMonth() {
    if (currentMonth.value === 12) {
        currentMonth.value = 1;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
}

// Função para buscar os dados do gráfico
async function fetchChartData() {
    isLoading.value = true;
    try {
        const response = await fetch(`/api/dashboard/new-terms-chart?month=${currentMonth.value}&year=${currentYear.value}`);
        if (!response.ok) {
            throw new Error('Erro ao buscar dados do gráfico');
        }
        const data = await response.json();
        monthName.value = data.monthName;
        renderChart(data);
    } catch (error) {
        console.error('Erro ao buscar dados do gráfico:', error);
    } finally {
        isLoading.value = false;
    }
}

// Função para renderizar o gráfico
function renderChart(data: any) {
    if (!chartCanvas.value) return;
    
    // Destruir o gráfico existente se houver
    if (chart) {
        chart.destroy();
    }
    
    // Criar novo gráfico
    chart = new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: data.datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: `Novos Termos por Dia - ${data.monthName} ${data.year}`
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Dia do Mês'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantidade de Termos'
                    }
                }
            }
        }
    });
}

// Observar mudanças no mês/ano e atualizar o gráfico
watch([currentMonth, currentYear], () => {
    fetchChartData();
});

// Inicializar o gráfico quando o componente for montado
onMounted(() => {
    fetchChartData();
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-bold mb-4">Bem-vindo ao KeywordAI</h1>
            <p class="mb-6">Selecione uma das opções abaixo para começar:</p>
            
            <div class="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-4">
                <a :href="route('search-terms.index')" class="flex flex-col items-center justify-center p-6 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <Search class="w-12 h-12 mb-4 text-blue-500" />
                    <h2 class="text-xl font-semibold">Termos de Pesquisa</h2>
                    <p class="text-center text-gray-600 dark:text-gray-300 mt-2">Gerencie e analise termos de pesquisa</p>
                </a>
                
                <a :href="route('negative-keywords.index')" class="flex flex-col items-center justify-center p-6 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <Ban class="w-12 h-12 mb-4 text-red-500" />
                    <h2 class="text-xl font-semibold">Palavras-chave Negativas</h2>
                    <p class="text-center text-gray-600 dark:text-gray-300 mt-2">Gerencie palavras-chave negativas</p>
                </a>
                
                <a :href="route('activity-logs.index')" class="flex flex-col items-center justify-center p-6 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <ClipboardList class="w-12 h-12 mb-4 text-green-500" />
                    <h2 class="text-xl font-semibold">Log de Atividades</h2>
                    <p class="text-center text-gray-600 dark:text-gray-300 mt-2">Visualize o histórico de atividades</p>
                </a>
                <a :href="route('settings.global.index')" class="flex flex-col items-center justify-center p-6 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <Settings class="w-12 h-12 mb-4 text-purple-500" />
                    <h2 class="text-xl font-semibold">Configurações Globais</h2>
                    <p class="text-center text-gray-600 dark:text-gray-300 mt-2">Gerencie as configurações do sistema</p>
                </a>
                
                <a :href="route('queue-commands.index')" class="flex flex-col items-center justify-center p-6 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <Terminal class="w-12 h-12 mb-4 text-amber-500" />
                    <h2 class="text-xl font-semibold">Fila e Comandos</h2>
                    <p class="text-center text-gray-600 dark:text-gray-300 mt-2">Monitore a fila e execute comandos</p>
                </a>
            </div>
            
            <div class="relative mt-8 p-6 flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min bg-white dark:bg-gray-800">
                <h2 class="text-xl font-semibold mb-4">Visão Geral</h2>
                
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium">Novos Termos de Pesquisa por Dia</h3>
                        <div class="flex items-center space-x-4">
                            <button 
                                @click="previousMonth" 
                                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                :disabled="isLoading"
                                :class="{ 'opacity-50 cursor-not-allowed': isLoading }"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </button>
                            <span class="text-sm font-medium min-w-[120px] text-center">{{ monthName }} {{ currentYear }}</span>
                            <button 
                                @click="nextMonth" 
                                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                :disabled="isLoading"
                                :class="{ 'opacity-50 cursor-not-allowed': isLoading }"
                            >
                                <ChevronRight class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                    
                    <div class="relative h-[400px] w-full">
                        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-800/50 z-10">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                        </div>
                        <canvas ref="chartCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
