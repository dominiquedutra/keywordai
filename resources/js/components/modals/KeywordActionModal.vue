<template>
  <Dialog :open="isOpen" @update:open="updateOpen">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>{{ modalTitle }}</DialogTitle>
        <DialogDescription>
          {{ modalDescription }}
        </DialogDescription>
      </DialogHeader>

      <!-- Informações do Grupo de Anúncios (se disponível) -->
      <div v-if="termData.ad_group_name" class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          <strong class="font-semibold text-gray-700 dark:text-gray-300">Grupo de Anúncios:</strong> 
          {{ termData.ad_group_name }} {{ termData.ad_group_id ? `(ID: ${termData.ad_group_id})` : '' }}
        </p>
        <p v-if="termData.campaign_name" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          <strong class="font-semibold text-gray-700 dark:text-gray-300">Campanha:</strong> 
          {{ termData.campaign_name }}
        </p>
        <p v-if="termData.keyword_text" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          <strong class="font-semibold text-gray-700 dark:text-gray-300">Palavra-Chave Original:</strong> 
          {{ termData.keyword_text }}
        </p>
      </div>

      <!-- Mensagens de erro -->
      <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-400 rounded-md">
        <strong class="font-bold">Erro!</strong>
        <span class="block">{{ errorMessage }}</span>
      </div>

      <!-- Formulário -->
      <form @submit.prevent="submitForm" class="space-y-4">
        <!-- Campo de termo (editável) -->
        <div>
          <label for="term" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Termo de Pesquisa (Editável):
          </label>
          <Input 
            id="term" 
            v-model="formData.term" 
            type="text" 
            required 
            class="w-full"
          />
        </div>

        <!-- Campo de tipo de correspondência -->
        <div>
          <label for="match_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ actionType === 'negate' ? 'Tipo de Correspondência para Negativação:' : 'Tipo de Correspondência:' }}
          </label>
          <select 
            id="match_type" 
            v-model="formData.match_type" 
            required 
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          >
            <option value="broad">Ampla (Broad)</option>
            <option value="phrase">Frase (Phrase)</option>
            <option value="exact">Exata (Exact)</option>
          </select>
        </div>

        <!-- Campo de motivo (apenas para negativação) -->
        <div v-if="actionType === 'negate'">
          <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Motivo da Negativação:
          </label>
          <textarea
            id="reason"
            v-model="formData.reason"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="Informe o motivo para adicionar esta palavra-chave negativa"
          ></textarea>
        </div>

        <!-- Campos ocultos -->
        <input v-if="actionType === 'negate'" type="hidden" v-model="formData.list_id">
        <input v-if="actionType === 'add'" type="hidden" v-model="formData.ad_group_id">
        <input v-if="actionType === 'add'" type="hidden" v-model="formData.ad_group_name">

        <DialogFooter class="flex justify-between">
          <Button type="button" variant="outline" @click="closeModal">
            Cancelar
          </Button>
          <Button 
            type="submit" 
            :disabled="isSubmitting"
            :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
          >
            <span v-if="isSubmitting" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Processando...
            </span>
            <span v-else>
              {{ actionType === 'negate' ? 'Salvar Negativação' : 'Adicionar Palavra-Chave' }}
            </span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

// Props
const props = defineProps({
  actionType: {
    type: String,
    required: true,
    validator: (value) => ['add', 'negate'].includes(value)
  },
  termData: {
    type: Object,
    required: true,
    default: () => ({})
  },
  isOpen: {
    type: Boolean,
    default: true
  }
});

// Emits
const emit = defineEmits(['update:isOpen', 'actionComplete']);

// Estado local
const formData = ref({
  term: props.termData.term || '',
  match_type: props.termData.match_type || (props.actionType === 'negate' ? 'phrase' : 'exact'),
  list_id: props.termData.list_id || '',
  ad_group_id: props.termData.ad_group_id || '',
  ad_group_name: props.termData.ad_group_name || '',
  reason: ''
});

const isSubmitting = ref(false);
const errorMessage = ref('');

// Computed properties
const modalTitle = computed(() => {
  return props.actionType === 'negate' 
    ? 'Adicionar Palavra-Chave Negativa' 
    : 'Adicionar Palavra-Chave';
});

const modalDescription = computed(() => {
  return props.actionType === 'negate'
    ? 'Adicione este termo como uma palavra-chave negativa para evitar que seus anúncios sejam exibidos para esta pesquisa.'
    : 'Adicione este termo como uma palavra-chave para direcionar seus anúncios para esta pesquisa.';
});

const formEndpoint = computed(() => {
  return props.actionType === 'negate'
    ? '/negative-keyword/add' // Rota para negativar
    : '/keyword/add'; // Rota para adicionar
});

// Métodos
const updateOpen = (value) => {
  emit('update:isOpen', value);
};

const closeModal = () => {
  emit('update:isOpen', false);
};

const submitForm = async () => {
  try {
    isSubmitting.value = true;
    errorMessage.value = '';

    // Obter o token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Preparar os dados do formulário
    const postData = new FormData();
    
    if (props.actionType === 'negate') {
      postData.append('term', formData.value.term);
      postData.append('match_type', formData.value.match_type);
      postData.append('list_id', formData.value.list_id);
      postData.append('reason', formData.value.reason);
    } else {
      postData.append('search_term', formData.value.term);
      postData.append('match_type', formData.value.match_type);
      postData.append('ad_group_id', formData.value.ad_group_id);
      postData.append('ad_group_name', formData.value.ad_group_name);
    }

    // Adicionar token CSRF
    postData.append('_token', csrfToken);

    // Enviar requisição
    const response = await fetch(formEndpoint.value, {
      method: 'POST',
      body: postData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });

    // Processar resposta
    if (response.ok) {
      // Emitir evento de conclusão
      emit('actionComplete', {
        success: true,
        message: props.actionType === 'negate' 
          ? 'Palavra-chave negativa adicionada com sucesso!' 
          : 'Palavra-chave adicionada com sucesso!',
        term: formData.value.term,
        actionType: props.actionType
      });
      
      // Fechar o modal
      closeModal();
    } else {
      // Tentar obter mensagem de erro da resposta
      const responseData = await response.json().catch(() => null);
      
      if (responseData && responseData.message) {
        errorMessage.value = responseData.message;
      } else {
        errorMessage.value = 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.';
      }
    }
  } catch (error) {
    console.error('Erro ao submeter formulário:', error);
    errorMessage.value = 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.';
  } finally {
    isSubmitting.value = false;
  }
};

// Lifecycle hooks
onMounted(() => {
  // Inicializar com os dados recebidos
  formData.value = {
    term: props.termData.term || '',
    match_type: props.termData.match_type || (props.actionType === 'negate' ? 'phrase' : 'exact'),
    list_id: props.termData.list_id || '',
    ad_group_id: props.termData.ad_group_id || '',
    ad_group_name: props.termData.ad_group_name || '',
    reason: ''
  };
});
</script>
