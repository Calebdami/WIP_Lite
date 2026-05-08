<script setup>
import { ref, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
  employees: Object,
  campaigns: Array,
  managers: Object,
  positions: Array,
  filters: Object,
})

const page = usePage()
const toast = useToast()
const success = computed(() => page.props.flash?.success)

// Afficher les notifications PrimeVue si succès
watch(success, (newSuccess) => {
  if (newSuccess) {
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: newSuccess,
      life: 5000
    })
  }
})

// État du formulaire
const selectedEmployees = ref([])
const assignmentType = ref('campaign') // 'campaign' ou 'manager'
const selectedCampaigns = ref([])
const selectedManager = ref('')
const selectedPosition = ref('')
const selectedPositionFilter = ref('') // Pour le filtrage
const startDate = ref(new Date().toISOString().split('T')[0])
const search = ref(props.filters.search || '')

// État pour les checkboxes
const selectAll = ref(false)
const availableManagers = ref([])

// Computed
const filteredEmployees = computed(() => {
  let employees = props.employees.data
  
  // Filtrage par recherche
  if (search.value) {
    employees = employees.filter(employee => 
      employee.first_name.toLowerCase().includes(search.value.toLowerCase()) ||
      employee.last_name.toLowerCase().includes(search.value.toLowerCase()) ||
      employee.matricule.toLowerCase().includes(search.value.toLowerCase())
    )
  }
  
  // Filtrage par position - MAINTIENT la logique de disponibilité
  if (selectedPositionFilter.value) {
    // Récupérer la position sélectionnée pour connaître son code
    const selectedPosition = props.positions.find(p => p.id === selectedPositionFilter.value)
    
    if (selectedPosition) {
      employees = employees.filter(employee => {
        // Vérifier si l'employé correspond à la position filtrée
        if (employee.position_id !== selectedPositionFilter.value) {
          return false
        }
        
        // Appliquer les règles de disponibilité selon le type de position
        if (selectedPosition.code === 'CP') {
          // Les CP peuvent avoir des affectations multiples (multi-campagnes)
          return true
        } else {
          // Pour SUP et TC : doivent être non affectés
          return !employee.assignments || employee.assignments.length === 0
        }
      })
    }
  }
  
  return employees
})

const canSubmit = computed(() => {
  return selectedEmployees.value.length > 0 && 
         selectedPosition.value && 
         startDate.value &&
         (assignmentType.value === 'campaign' ? selectedCampaigns.value.length > 0 : selectedManager.value)
})

// Watchers
watch(assignmentType, () => {
  selectedCampaigns.value = []
  selectedManager.value = ''
})

watch(selectedPosition, (newPosition) => {
  if (assignmentType.value === 'manager' && newPosition) {
    loadAvailableManagers(newPosition)
  }
})

// Méthodes
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedEmployees.value = filteredEmployees.value.map(emp => emp.id)
  } else {
    selectedEmployees.value = []
  }
}

const toggleEmployee = (employeeId) => {
  const index = selectedEmployees.value.indexOf(employeeId)
  if (index > -1) {
    selectedEmployees.value.splice(index, 1)
  } else {
    selectedEmployees.value.push(employeeId)
  }
}

const isEmployeeSelected = (employeeId) => {
  return selectedEmployees.value.includes(employeeId)
}

const loadAvailableManagers = (positionId) => {
  const position = props.positions.find(p => p.id === positionId)
  if (!position) return

  const positionCode = position.code
  let managers = []

  if (positionCode === 'SUP') {
    // Les SUP peuvent être affectés à des CP
    managers = props.managers.CP || []
  } else if (positionCode === 'TC') {
    // Les TC peuvent être affectés à des SUP
    managers = props.managers.SUP || []
  }

  availableManagers.value = managers
}

const submitForm = () => {
  const formData = {
    employee_ids: selectedEmployees.value,
    assignment_type: assignmentType.value,
    position_id: selectedPosition.value,
    start_date: startDate.value,
  }

  if (assignmentType.value === 'campaign') {
    formData.campaign_ids = selectedCampaigns.value
  } else {
    formData.manager_assignment_id = selectedManager.value
  }

  router.post(route('admin.assignments.bulk-assign.store'), formData, {
    preserveScroll: true,
    onSuccess: () => {
      // Réinitialiser le formulaire
      selectedEmployees.value = []
      selectAll.value = false
      selectedCampaigns.value = []
      selectedManager.value = ''
    }
  })
}

const getPositionName = (positionId) => {
  const position = props.positions.find(p => p.id === positionId)
  return position ? position.name : ''
}

const getEmployeeStatus = (employee) => {
  if (!employee.assignments || employee.assignments.length === 0) {
    return 'Disponible'
  }
  
  // Si l'employé a des affectations
  if (employee.position.code === 'CP') {
    return 'Disponible (multi-campagnes)'
  } else {
    return 'Non disponible'
  }
}

const getEmployeeStatusClass = (employee) => {
  if (!employee.assignments || employee.assignments.length === 0) {
    return 'text-green-600'
  }
  
  // Si l'employé a des affectations
  if (employee.position.code === 'CP') {
    return 'text-yellow-600'
  } else {
    return 'text-red-600'
  }
}
</script>

<template>
  <AdminLayout>
    <Head title="Affectation Multiple" />
    <Toast />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Affectation Multiple</h1>
              <p class="mt-1 text-xs text-charcoal-400">
                Sélectionnez plusieurs employés et affectez-les simultanément à des campagnes ou à des managers
              </p>
            </div>
            <Link 
              :href="route('admin.assignments.structure')" 
              class="inline-flex items-center px-4 py-2 border border-pearl-200 rounded-xl shadow-sm text-xs font-medium text-charcoal-700 bg-white hover:bg-pearl-50 transition-all"
            >
              <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Retour à la structure
            </Link>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Liste des employés -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-pearl-200 overflow-hidden shadow-sm">
              <!-- Header de la liste -->
              <div class="px-6 py-4 bg-charcoal-900 border-b border-charcoal-800">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-black text-white uppercase tracking-wider">Employés Disponibles</h3>
                  <span class="text-[10px] text-pearl-400 font-bold uppercase">{{ filteredEmployees.length }} trouvé(s)</span>
                </div>
              </div>
              
              <div class="p-6">
                <!-- Filtres -->
                <div class="mb-6 space-y-4">
                  <!-- Barre de recherche -->
                  <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-charcoal-400">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                      </svg>
                    </span>
                    <input
                      type="text"
                      v-model="search"
                      class="block w-full pl-10 pr-3 py-2 border border-pearl-200 rounded-xl text-xs focus:ring-gold-500 focus:border-gold-500 bg-white"
                      placeholder="Rechercher par nom, prénom ou matricule..."
                    />
                  </div>
                  
                  <!-- Filtre par position -->
                  <div>
                    <label class="block text-xs font-bold text-charcoal-700 mb-2">Filtrer par position</label>
                    <select
                      v-model="selectedPositionFilter"
                      class="block w-full border border-pearl-200 rounded-xl text-xs focus:ring-gold-500 focus:border-gold-500 bg-white"
                    >
                      <option value="">Toutes les positions</option>
                      <option
                        v-for="position in positions"
                        :key="position.id"
                        :value="position.id"
                      >
                        {{ position.name }}
                      </option>
                    </select>
                  </div>
                  
                  <!-- Sélection tout -->
                  <div class="flex items-center justify-between p-3 bg-pearl-50 rounded-xl border border-pearl-100">
                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        v-model="selectAll"
                        @change="toggleSelectAll"
                        class="h-4 w-4 text-gold-600 focus:ring-gold-500 border-pearl-300 rounded"
                      />
                      <label class="ml-3 block text-xs font-bold text-charcoal-700">
                        Sélectionner tout ({{ filteredEmployees.length }} employés)
                      </label>
                    </div>
                    <div class="text-xs font-black text-gold-600">
                      {{ selectedEmployees.length }} sélectionné(s)
                    </div>
                  </div>
                </div>

                <!-- Liste des employés -->
                <div class="space-y-2 max-h-96 overflow-y-auto">
                  <div
                    v-for="employee in filteredEmployees"
                    :key="employee.id"
                    class="flex items-center justify-between p-4 border border-pearl-100 rounded-xl hover:bg-pearl-50 hover:border-gold-200 transition-all group"
                  >
                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        :value="employee.id"
                        :checked="isEmployeeSelected(employee.id)"
                        @change="toggleEmployee(employee.id)"
                        class="h-4 w-4 text-gold-600 focus:ring-gold-500 border-pearl-300 rounded"
                      />
                      <div class="ml-3">
                        <div class="text-sm font-black text-charcoal-800">
                          {{ employee.first_name }} {{ employee.last_name }}
                        </div>
                        <div class="text-[9px] text-charcoal-400 font-bold uppercase tracking-tighter">
                          {{ employee.matricule }} • {{ getPositionName(employee.position_id) }}
                        </div>
                      </div>
                    </div>
                    <div class="text-right">
                      <div :class="getEmployeeStatusClass(employee)" class="text-xs font-bold uppercase tracking-wider">
                        {{ getEmployeeStatus(employee) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Formulaire d'affectation -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-pearl-200 overflow-hidden shadow-sm">
              <!-- Header du formulaire -->
              <div class="px-6 py-4 bg-gold-gradient">
                <h3 class="text-sm font-black text-white uppercase tracking-wider">Configuration de l'affectation</h3>
              </div>
              
              <div class="p-6 space-y-4">
                <!-- Résumé de la sélection -->
                <div class="p-3 bg-pearl-50 rounded-xl border border-pearl-100">
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-charcoal-700">Employés sélectionnés</span>
                    <span class="text-sm font-black text-gold-600">{{ selectedEmployees.length }}</span>
                  </div>
                </div>

                <!-- Type d'affectation -->
                <div>
                  <label class="block text-xs font-bold text-charcoal-700 mb-3">Type d'affectation</label>
                  <div class="space-y-2">
                    <label class="flex items-center p-3 border border-pearl-100 rounded-xl cursor-pointer hover:bg-pearl-50 transition-all">
                      <input
                        type="radio"
                        v-model="assignmentType"
                        value="campaign"
                        class="h-4 w-4 text-gold-600 focus:ring-gold-500 border-pearl-300"
                      />
                      <span class="ml-3 text-xs font-black text-charcoal-800">Affecter à des campagnes</span>
                    </label>
                    <label class="flex items-center p-3 border border-pearl-100 rounded-xl cursor-pointer hover:bg-pearl-50 transition-all">
                      <input
                        type="radio"
                        v-model="assignmentType"
                        value="manager"
                        class="h-4 w-4 text-gold-600 focus:ring-gold-500 border-pearl-300"
                      />
                      <span class="ml-3 text-xs font-black text-charcoal-800">Affecter à un manager</span>
                    </label>
                  </div>
                </div>

                <!-- Position -->
                <div>
                  <label class="block text-xs font-bold text-charcoal-700 mb-2">
                    Position *
                  </label>
                  <select
                    v-model="selectedPosition"
                    class="block w-full border border-pearl-200 rounded-xl text-xs focus:ring-gold-500 focus:border-gold-500 bg-white"
                  >
                    <option value="">Sélectionner une position</option>
                    <option
                      v-for="position in positions"
                      :key="position.id"
                      :value="position.id"
                    >
                      {{ position.name }}
                    </option>
                  </select>
                </div>

                <!-- Campagnes (si affectation à des campagnes) -->
                <div v-if="assignmentType === 'campaign'">
                  <label class="block text-xs font-bold text-charcoal-700 mb-2">
                    Campagnes *
                  </label>
                  <div class="space-y-2 max-h-48 overflow-y-auto border border-pearl-100 rounded-xl p-3 bg-pearl-50">
                    <label
                      v-for="campaign in campaigns"
                      :key="campaign.id"
                      class="flex items-center p-2 hover:bg-white rounded-lg transition-all cursor-pointer"
                    >
                      <input
                        type="checkbox"
                        :value="campaign.id"
                        v-model="selectedCampaigns"
                        class="h-4 w-4 text-gold-600 focus:ring-gold-500 border-pearl-300 rounded"
                      />
                      <span class="ml-2 text-xs font-black text-charcoal-800">{{ campaign.name }}</span>
                    </label>
                  </div>
                </div>

                <!-- Manager (si affectation à un manager) -->
                <div v-if="assignmentType === 'manager'">
                  <label class="block text-xs font-bold text-charcoal-700 mb-2">
                    Manager *
                  </label>
                  <select
                    v-model="selectedManager"
                    class="block w-full border border-pearl-200 rounded-xl text-xs focus:ring-gold-500 focus:border-gold-500 bg-white"
                    :disabled="!selectedPosition"
                  >
                    <option value="">Sélectionner d'abord une position</option>
                    <option
                      v-for="manager in availableManagers"
                      :key="manager.id"
                      :value="manager.id"
                    >
                      {{ manager.employee.first_name }} {{ manager.employee.last_name }} 
                      ({{ manager.campaign.name }})
                    </option>
                  </select>
                </div>

                <!-- Date de début -->
                <div>
                  <label class="block text-xs font-bold text-charcoal-700 mb-2">
                    Date de début *
                  </label>
                  <input
                    type="date"
                    v-model="startDate"
                    class="block w-full border border-pearl-200 rounded-xl text-xs focus:ring-gold-500 focus:border-gold-500 bg-white"
                  />
                </div>

                <!-- Bouton de soumission -->
                <div class="pt-4">
                  <button
                    @click="submitForm"
                    :disabled="!canSubmit"
                    class="w-full flex justify-center py-3 px-4 bg-gold-gradient text-white text-xs font-black uppercase tracking-wider rounded-xl hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Valider l'affectation ({{ selectedEmployees.length }} employé(s))
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
