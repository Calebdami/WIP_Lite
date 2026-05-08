<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import Button from 'primevue/button'
import Badge from 'primevue/badge'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Checkbox from 'primevue/checkbox'
import InputSwitch from 'primevue/inputswitch'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import { useConfirm } from 'primevue/useconfirm'

const confirm = useConfirm()

// Reactive data
const loading = ref({
  cache: false,
  backup: false,
  manualBackup: false,
  cleanTemp: false,
  reindex: false,
  removeOrphans: false,
  anonymize: false,
  checkAccess: false,
  scan2FA: false,
  purgeTemp: false,
  purgeReports: false,
  clearAppCache: false,
  clearRouteCache: false,
  clearConfigCache: false,
  retryJobs: false,
  saveEnv: false,
  sendNotification: false,
  sendTestMail: false
})

const systemHealth = ref({
  cpu: 0,
  ram: 0,
  disk: 0,
  version: '1.0.0',
  services: []
})

const errorLogs = ref([])
const backups = ref([])
const migrations = ref([])
const anonymizeEnabled = ref(false)
const activeSessions = ref([])
const auditLogs = ref([])
const maintenanceMode = ref({
  enabled: false,
  message: 'Le système est en maintenance. Veuillez réessayer plus tard.'
})
const permissionsCheck = ref({ results: [] })
const queueJobs = ref([])
const envVars = ref([
  { key: 'MAIL_HOST', value: '', type: 'text' },
  { key: 'MAIL_PORT', value: '', type: 'number' },
  { key: 'MAIL_USERNAME', value: '', type: 'text' },
  { key: 'MAIL_PASSWORD', value: '', type: 'password' }
])
const systemNotification = ref({
  message: '',
  type: 'info'
})
const notificationTypes = ref([
  { label: 'Information', value: 'info' },
  { label: 'Avertissement', value: 'warning' },
  { label: 'Erreur', value: 'error' },
  { label: 'Succès', value: 'success' }
])
const mailTest = ref({
  email: '',
  result: '',
  success: false
})

// Methods
const clearCache = async () => {
  loading.value.cache = true
  try {
    await axios.post('/admin/maintenance/clear-cache')
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    loading.value.cache = false
  }
}

const backupDatabase = async () => {
  loading.value.backup = true
  try {
    await axios.post('/admin/maintenance/backup-database')
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    loading.value.backup = false
  }
}

const createBackup = async () => {
  loading.value.manualBackup = true
  try {
    await axios.post('/admin/maintenance/database/backup')
    loadBackups()
  } catch (error) {
    // Handle error
  } finally {
    loading.value.manualBackup = false
  }
}

const downloadBackup = async (backup) => {
  window.open(`/admin/maintenance/database/backup/${backup.id}/download`)
}

const restoreBackup = async (backup) => {
  confirm.require({
    message: 'Êtes-vous sûr de vouloir restaurer cette sauvegarde ?',
    header: 'Confirmation de restauration',
    icon: 'pi-exclamation-triangle',
    acceptClass: 'p-button-warning',
    rejectClass: 'p-button-secondary p-button-outlined',
    accept: async () => {
      try {
        await axios.post(`/admin/maintenance/database/backup/${backup.id}/restore`)
        // Show success message
      } catch (error) {
        // Handle error
      }
    }
  })
}

const cleanTempTables = async () => {
  loading.value.cleanTemp = true
  try {
    await axios.post('/admin/maintenance/database/clean-temp')
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    loading.value.cleanTemp = false
  }
}

const reindexTables = async () => {
  loading.value.reindex = true
  try {
    await axios.post('/admin/maintenance/database/reindex')
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    loading.value.reindex = false
  }
}

const removeOrphans = async () => {
  loading.value.removeOrphans = true
  try {
    await axios.post('/admin/maintenance/database/remove-orphans')
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    loading.value.removeOrphans = false
  }
}

const anonymizeData = async () => {
  confirm.require({
    message: 'Cette action va anonymiser les données sensibles. Continuer ?',
    header: 'Confirmation d\'anonymisation',
    icon: 'pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    rejectClass: 'p-button-secondary p-button-outlined',
    accept: async () => {
      loading.value.anonymize = true
      try {
        await axios.post('/admin/maintenance/database/anonymize')
        // Show success message
      } catch (error) {
        // Handle error
      } finally {
        loading.value.anonymize = false
      }
    }
  })
}

const forceLogout = async (session) => {
  try {
    await axios.delete(`/admin/maintenance/sessions/${session.id}`)
    loadActiveSessions()
  } catch (error) {
    // Handle error
  }
}

const toggleMaintenanceMode = async () => {
  try {
    await axios.post('/admin/maintenance/maintenance-mode', {
      enabled: maintenanceMode.value.enabled,
      message: maintenanceMode.value.message
    })
  } catch (error) {
    // Handle error
  }
}

const checkCriticalAccess = async () => {
  loading.value.checkAccess = true
  try {
    const response = await axios.get('/admin/maintenance/permissions/check')
    permissionsCheck.value.results = response.data
  } catch (error) {
    // Handle error
  } finally {
    loading.value.checkAccess = false
  }
}

const scan2FA = async () => {
  loading.value.scan2FA = true
  try {
    const response = await axios.get('/admin/maintenance/2fa/scan')
    permissionsCheck.value.results = response.data
  } catch (error) {
    // Handle error
  } finally {
    loading.value.scan2FA = false
  }
}

const purgeTempFiles = async () => {
  loading.value.purgeTemp = true
  try {
    await axios.post('/admin/maintenance/files/purge-temp')
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    loading.value.purgeTemp = false
  }
}

const purgeOldReports = async () => {
  loading.value.purgeReports = true
  try {
    await axios.post('/admin/maintenance/files/purge-reports')
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    loading.value.purgeReports = false
  }
}

const clearAppCache = async () => {
  loading.value.clearAppCache = true
  try {
    await axios.post('/admin/maintenance/cache/clear-app')
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    loading.value.clearAppCache = false
  }
}

const clearRouteCache = async () => {
  loading.value.clearRouteCache = true
  try {
    await axios.post('/admin/maintenance/cache/clear-routes')
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    loading.value.clearRouteCache = false
  }
}

const clearConfigCache = async () => {
  loading.value.clearConfigCache = true
  try {
    await axios.post('/admin/maintenance/cache/clear-config')
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    loading.value.clearConfigCache = false
  }
}

const viewQueueJobs = async () => {
  try {
    const response = await axios.get('/admin/maintenance/queue/jobs')
    queueJobs.value = response.data
  } catch (error) {
    // Handle error
  }
}

const retryFailedJobs = async () => {
  loading.value.retryJobs = true
  try {
    await axios.post('/admin/maintenance/queue/retry-failed')
    viewQueueJobs()
  } catch (error) {
    // Handle error
  } finally {
    loading.value.retryJobs = false
  }
}

const saveEnvConfig = async () => {
  loading.value.saveEnv = true
  try {
    await axios.post('/admin/maintenance/config/env', { vars: envVars.value })
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    loading.value.saveEnv = false
  }
}

const sendSystemNotification = async () => {
  loading.value.sendNotification = true
  try {
    await axios.post('/admin/maintenance/notifications/send', systemNotification.value)
    systemNotification.value.message = ''
  } catch (error) {
    // Handle error
  } finally {
    loading.value.sendNotification = false
  }
}

const sendTestMail = async () => {
  loading.value.sendTestMail = true
  try {
    const response = await axios.post('/admin/maintenance/mail/test', { email: mailTest.value.email })
    mailTest.value.result = response.data.message
    mailTest.value.success = response.data.success
  } catch (error) {
    mailTest.value.result = 'Erreur lors de l\'envoi du mail'
    mailTest.value.success = false
  } finally {
    loading.value.sendTestMail = false
  }
}

// Helper methods
const getSeverity = (level) => {
  const severities = {
    'emergency': 'danger',
    'alert': 'danger',
    'critical': 'danger',
    'error': 'danger',
    'warning': 'warning',
    'notice': 'info',
    'info': 'info',
    'debug': 'info'
  }
  return severities[level] || 'info'
}

const getJobSeverity = (status) => {
  const severities = {
    'pending': 'warning',
    'processing': 'info',
    'completed': 'success',
    'failed': 'danger'
  }
  return severities[status] || 'info'
}

// Load data methods
const loadSystemHealth = async () => {
  try {
    const response = await axios.get('/admin/maintenance/health')
    systemHealth.value = response.data
  } catch (error) {
    // Handle error
  }
}

const loadErrorLogs = async () => {
  try {
    const response = await axios.get('/admin/maintenance/logs/errors')
    errorLogs.value = response.data
  } catch (error) {
    // Handle error
  }
}

const loadBackups = async () => {
  try {
    const response = await axios.get('/admin/maintenance/database/backups')
    backups.value = response.data
  } catch (error) {
    // Handle error
  }
}

const loadMigrations = async () => {
  try {
    const response = await axios.get('/admin/maintenance/database/migrations')
    migrations.value = response.data
  } catch (error) {
    // Handle error
  }
}

const loadActiveSessions = async () => {
  try {
    const response = await axios.get('/admin/maintenance/sessions')
    activeSessions.value = response.data
  } catch (error) {
    // Handle error
  }
}

const loadAuditLogs = async () => {
  try {
    const response = await axios.get('/admin/maintenance/audit-logs')
    auditLogs.value = response.data
  } catch (error) {
    // Handle error
  }
}

const loadMaintenanceMode = async () => {
  try {
    const response = await axios.get('/admin/maintenance/maintenance-mode')
    maintenanceMode.value = response.data
  } catch (error) {
    // Handle error
  }
}

const loadEnvVars = async () => {
  try {
    const response = await axios.get('/admin/maintenance/config/env')
    envVars.value = response.data
  } catch (error) {
    // Handle error
  }
}

// Initialize
onMounted(() => {
  loadSystemHealth()
  loadErrorLogs()
  loadBackups()
  loadMigrations()
  loadActiveSessions()
  loadAuditLogs()
  loadMaintenanceMode()
  loadEnvVars()
})
</script>

<template>
    <Head title="Maintenance Système — Administration" />
    <AdminLayout>
        <!-- ── Header ────────────────────────────────────────────────────────── -->
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-charcoal-700 tracking-tight">Maintenance Système</h1>
                    <p class="text-xs text-charcoal-400 mt-0.5">Outils de gestion et monitoring du système</p>
                </div>
                <div class="flex gap-2">
                    <Button class="px-4 py-2 bg-gold-gradient text-charcoal-900 rounded-lg text-xs font-bold shadow-gold" label="Vider le cache" icon="pi pi-refresh" @click="clearCache" :loading="loading.cache" severity="secondary" />
                    <Button class="px-4 py-2 bg-gold-gradient text-charcoal-900 rounded-lg text-xs font-bold shadow-gold" label="Sauvegarde BD" icon="pi pi-save" @click="backupDatabase" :loading="loading.backup" severity="success" />
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- 1. État de Santé du Système -->
            <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4 border-b border-pearl-200">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.109 3.5a2.5 2.5 0 110 5 2.5 2.5 0 010-5zM9.109 15.5a6.471 6.471 0 00-6.475 6.5H2a8 8 0 1 0 14.219 0h-.633a6.479 6.479 0 00-6.477-6.5z"/></svg>
                    <h2 class="text-sm font-bold text-charcoal-700">État de Santé du Système</h2>
                </div>
                <div class="p-5">
                    <!-- Indicateurs -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
                        <div class="bg-pearl-50 rounded-lg border border-pearl-100 p-4">
                            <p class="text-xs text-charcoal-400 font-medium mb-1">CPU</p>
                            <p class="text-2xl font-black text-charcoal-700">{{ systemHealth.cpu }}%</p>
                        </div>
                        <div class="bg-pearl-50 rounded-lg border border-pearl-100 p-4">
                            <p class="text-xs text-charcoal-400 font-medium mb-1">Mémoire</p>
                            <p class="text-2xl font-black text-charcoal-700">{{ systemHealth.ram }}%</p>
                        </div>
                        <div class="bg-pearl-50 rounded-lg border border-pearl-100 p-4">
                            <p class="text-xs text-charcoal-400 font-medium mb-1">Disque</p>
                            <p class="text-2xl font-black text-charcoal-700">{{ systemHealth.disk }}%</p>
                        </div>
                        <div class="bg-pearl-50 rounded-lg border border-pearl-100 p-4">
                            <p class="text-xs text-charcoal-400 font-medium mb-1">Version</p>
                            <p class="text-lg font-bold text-charcoal-700">{{ systemHealth.version }}</p>
                        </div>
                    </div>

                    <!-- Statut des Services -->
                    <div class="mb-4">
                        <h3 class="text-xs font-bold text-charcoal-700 mb-3">Statut des Services</h3>
                        <div class="space-y-2">
                            <div v-for="service in systemHealth.services" :key="service.name" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-pearl-50 transition-colors">
                                <span class="text-xs text-charcoal-600">{{ service.name }}</span>
                                <Badge :value="service.status" :severity="service.status === 'OK' ? 'success' : 'danger'" />
                            </div>
                        </div>
                    </div>

                    <!-- Logs d'Erreurs -->
                    <div>
                        <h3 class="text-xs font-bold text-charcoal-700 mb-3">Logs d'Erreurs Récentes</h3>
                        <DataTable :value="errorLogs" class="p-datatable-sm">
                            <Column field="level" header="Niveau" style="width: 80px">
                                <template #body="slotProps">
                                    <Badge :value="slotProps.data.level" :severity="getSeverity(slotProps.data.level)" />
                                </template>
                            </Column>
                            <Column field="message" header="Message"></Column>
                            <Column field="created_at" header="Date" style="width: 130px"></Column>
                        </DataTable>
                    </div>
                </div>
            </div>

            <!-- 2. Gestion de la Base de Données -->
            <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4 border-b border-pearl-200">
                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 6a1 1 0 011-1h12a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zm0 8a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z"/></svg>
                    <h2 class="text-sm font-bold text-charcoal-700">Gestion de la Base de Données</h2>
                </div>
                <div class="p-5 space-y-4">
                    <!-- Sauvegardes et Optimisation -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-xs font-bold text-charcoal-700 mb-2">Sauvegardes</h3>
                            <div class="space-y-2">
                                <Button label="Créer Sauvegarde" icon="pi pi-plus" @click="createBackup" :loading="loading.manualBackup" size="small" />
                                <div v-if="backups.length > 0" class="border border-pearl-200 rounded-lg p-3 max-h-48 overflow-y-auto">
                                    <div v-for="backup in backups" :key="backup.id" class="flex items-center justify-between py-1.5 border-b border-pearl-100 last:border-0">
                                        <span class="text-[11px] text-charcoal-600">{{ backup.filename }}</span>
                                        <div class="flex gap-1">
                                            <Button icon="pi pi-download" size="small" severity="secondary" @click="downloadBackup(backup)" />
                                            <Button icon="pi pi-refresh" size="small" severity="success" @click="restoreBackup(backup)" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-charcoal-700 mb-2">Optimisation</h3>
                            <div class="space-y-2">
                                <Button label="Nettoyer Temp" icon="pi pi-trash" @click="cleanTempTables" :loading="loading.cleanTemp" size="small" severity="warning" />
                                <Button label="Réindexer" icon="pi pi-wrench" @click="reindexTables" :loading="loading.reindex" size="small" />
                                <Button label="Nettoyer Orphelins" icon="pi pi-minus-circle" @click="removeOrphans" :loading="loading.removeOrphans" size="small" severity="danger" />
                            </div>
                        </div>
                    </div>

                    <!-- Migrations et Anonymisation -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-xs font-bold text-charcoal-700 mb-2">Historique Migrations</h3>
                            <DataTable :value="migrations" class="p-datatable-sm">
                                <Column field="migration" header="Migration" style="width: 60%"></Column>
                                <Column field="batch" header="Batch" style="width: 20%; text-align: center"></Column>
                            </DataTable>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-charcoal-700 mb-2">Anonymisation</h3>
                            <div class="flex items-center gap-3 px-3 py-2 bg-pearl-50 rounded-lg border border-pearl-100">
                                <Checkbox v-model="anonymizeEnabled" inputId="anonymize" />
                                <label for="anonymize" class="text-xs text-charcoal-600">Activer anonymisation</label>
                                <Button label="Exécuter" icon="pi pi-shield" @click="anonymizeData" :loading="loading.anonymize" size="small" severity="danger" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- 3. Contrôle des Utilisateurs et Sécurité -->
            <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4 border-b border-pearl-200">
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    <h2 class="text-sm font-bold text-charcoal-700">Contrôle des Utilisateurs et Sécurité</h2>
                </div>
                <div class="p-5 space-y-4">
                    <!-- Sessions et Audit Log -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-xs font-bold text-charcoal-700 mb-2">Sessions Actives</h3>
                            <DataTable :value="activeSessions" class="p-datatable-sm" style="font-size: 12px">
                                <Column field="name" header="Utilisateur"></Column>
                                <Column field="ip_address" header="IP" style="width: 100px"></Column>
                                <Column header="Actions" style="width: 60px; text-align: center">
                                    <template #body="slotProps">
                                        <Button icon="pi pi-times" size="small" severity="danger" @click="forceLogout(slotProps.data)" />
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-charcoal-700 mb-2">Audit Log</h3>
                            <DataTable :value="auditLogs.slice(0, 5)" class="p-datatable-sm" style="font-size: 12px">
                                <Column field="action" header="Action" style="width: 40%"></Column>
                                <Column field="description" header="Description"></Column>
                            </DataTable>
                        </div>
                    </div>

                    <!-- Mode Maintenance et Permissions -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 px-3 py-2 bg-pearl-50 rounded-lg border border-pearl-100">
                            <InputSwitch v-model="maintenanceMode.enabled" @change="toggleMaintenanceMode" />
                            <label class="text-xs text-charcoal-600 flex-1">Mode Maintenance</label>
                            <span class="text-[10px] font-mono text-charcoal-400">{{ maintenanceMode.enabled ? 'ACTIF' : 'INACTIF' }}</span>
                        </div>
                        <div v-if="maintenanceMode.enabled" class="px-3">
                            <InputText v-model="maintenanceMode.message" placeholder="Message de maintenance" class="w-full text-xs" />
                        </div>

                        <div class="flex gap-2">
                            <Button label="Vérifier Accès" icon="pi pi-check-circle" @click="checkCriticalAccess" :loading="loading.checkAccess" size="small" />
                            <Button label="Scanner 2FA" icon="pi pi-mobile" @click="scan2FA" :loading="loading.scan2FA" size="small" />
                        </div>

                        <div v-if="permissionsCheck.results.length > 0" class="border border-pearl-200 rounded-lg p-3 max-h-40 overflow-y-auto">
                            <div v-for="result in permissionsCheck.results" :key="result.id" class="flex items-center justify-between py-1.5 border-b border-pearl-100 last:border-0">
                                <span class="text-[11px] text-charcoal-600">{{ result.user }}</span>
                                <Badge :value="result.severity" :severity="result.severity === 'critical' ? 'danger' : 'warning'" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- 4. Maintenance Applicative & Métier -->
            <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4 border-b border-pearl-200">
                    <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-1.286 1.313c-1.565.29-1.566 2.694 0 2.984.566.105 1.06.605 1.286 1.313.38 1.56 2.6 1.56 2.98 0 .226-.708.72-1.208 1.286-1.313 1.565-.29 1.566-2.694 0-2.984a1.532 1.532 0 01-1.286-1.313zm-5.002 9.002a1 1 0 100 2 1 1 0 000-2zM3 15a1 1 0 100 2 1 1 0 000-2zm2 2a1 1 0 100 2 1 1 0 000-2zm2-4a1 1 0 100 2 1 1 0 000-2zM1 7a1 1 0 100 2 1 1 0 000-2z"/></svg>
                    <h2 class="text-sm font-bold text-charcoal-700">Maintenance Applicative & Métier</h2>
                </div>
                <div class="p-5 space-y-4">
                    <!-- Purge, Cache, Queue -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <h3 class="text-xs font-bold text-charcoal-700 mb-2">Purge Fichiers</h3>
                            <div class="space-y-1.5">
                                <Button label="Fichiers Temp" icon="pi pi-trash" @click="purgeTempFiles" :loading="loading.purgeTemp" size="small" severity="warning" />
                                <Button label="Anciens Rapports" icon="pi pi-file-pdf" @click="purgeOldReports" :loading="loading.purgeReports" size="small" />
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-charcoal-700 mb-2">Cache</h3>
                            <div class="space-y-1.5">
                                <Button label="Cache App" icon="pi pi-refresh" @click="clearAppCache" :loading="loading.clearAppCache" size="small" />
                                <Button label="Routes" icon="pi pi-directions" @click="clearRouteCache" :loading="loading.clearRouteCache" size="small" />
                                <Button label="Config" icon="pi pi-sliders-h" @click="clearConfigCache" :loading="loading.clearConfigCache" size="small" />
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-charcoal-700 mb-2">Queue Jobs</h3>
                            <div class="space-y-1.5">
                                <Button label="Voir Tâches" icon="pi pi-list" @click="viewQueueJobs" size="small" />
                                <Button label="Relancer" icon="pi pi-replay" @click="retryFailedJobs" :loading="loading.retryJobs" size="small" severity="warning" />
                            </div>
                        </div>
                    </div>

                    <!-- Tâches en Cours -->
                    <div v-if="queueJobs.length > 0" class="border border-pearl-200 rounded-lg p-3">
                        <h3 class="text-xs font-bold text-charcoal-700 mb-2">Tâches en Cours</h3>
                        <div class="space-y-1 max-h-32 overflow-y-auto">
                            <div v-for="job in queueJobs" :key="job.id" class="flex items-center justify-between py-1">
                                <span class="text-[11px] text-charcoal-600">{{ job.name }}</span>
                                <Badge :value="job.status" :severity="getJobSeverity(job.status)" />
                            </div>
                        </div>
                    </div>

                    <!-- Configuration d'Environnement -->
                    <div>
                        <h3 class="text-xs font-bold text-charcoal-700 mb-2">Configuration .env</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div v-for="envVar in envVars" :key="envVar.key" class="flex flex-col gap-1">
                                <label class="text-[11px] font-medium text-charcoal-600">{{ envVar.key }}</label>
                                <InputText v-model="envVar.value" :type="envVar.type" class="text-xs" style="padding: 0.5rem" />
                            </div>
                        </div>
                        <Button label="Sauvegarder" icon="pi pi-save" @click="saveEnvConfig" :loading="loading.saveEnv" severity="success" class="mt-2" size="small" />
                    </div>
                </div>
            </div>


            <!-- 5. Communication & Support -->
            <div class="bg-white rounded-xl border border-pearl-200 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4 border-b border-pearl-200">
                    <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    <h2 class="text-sm font-bold text-charcoal-700">Communication & Support</h2>
                </div>
                <div class="p-5 space-y-4">
                    <!-- Notifications Système et Diagnostic Mail -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-xs font-bold text-charcoal-700 mb-2">Notifications Système</h3>
                            <div class="space-y-2">
                                <Textarea v-model="systemNotification.message" placeholder="Message pour tous les utilisateurs..." rows="3" class="w-full text-xs" />
                                <div class="flex gap-2">
                                    <Dropdown v-model="systemNotification.type" :options="notificationTypes" optionLabel="label" optionValue="value" class="flex-1" />
                                    <Button label="Envoyer" icon="pi pi-send" @click="sendSystemNotification" :loading="loading.sendNotification" severity="info" size="small" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-charcoal-700 mb-2">Diagnostic Mail SMTP</h3>
                            <div class="space-y-2">
                                <InputText v-model="mailTest.email" placeholder="Email de test..." class="w-full text-xs" />
                                <Button label="Envoyer Test" icon="pi pi-envelope" @click="sendTestMail" :loading="loading.sendTestMail" severity="success" size="small" class="w-full" />
                                <div v-if="mailTest.result" class="p-2 rounded-lg text-xs" :class="mailTest.success ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-red-50 border border-red-200 text-red-700'">
                                    <div class="flex items-center gap-2">
                                        <i :class="mailTest.success ? 'pi pi-check-circle' : 'pi pi-times-circle'"></i>
                                        <span>{{ mailTest.result }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Custom styles if needed */
</style>