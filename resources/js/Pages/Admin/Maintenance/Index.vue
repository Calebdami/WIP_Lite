<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-900">Maintenance Système</h1>
      <div class="flex space-x-2">
        <Button label="Vider le cache" icon="pi pi-refresh" @click="clearCache" :loading="loading.cache" />
        <Button label="Sauvegarde Base de Données" icon="pi pi-save" @click="backupDatabase" :loading="loading.backup" severity="success" />
      </div>
    </div>

    <!-- 1. État de Santé du Système -->
    <Card>
      <template #title>
        <div class="flex items-center space-x-2">
          <i class="pi pi-heart text-green-500"></i>
          <span>État de Santé du Système</span>
        </div>
      </template>
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600">CPU</div>
            <div class="text-2xl font-bold text-blue-900">{{ systemHealth.cpu }}%</div>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-green-600">RAM</div>
            <div class="text-2xl font-bold text-green-900">{{ systemHealth.ram }}%</div>
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-sm text-yellow-600">Disque</div>
            <div class="text-2xl font-bold text-yellow-900">{{ systemHealth.disk }}%</div>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-sm text-purple-600">Version</div>
            <div class="text-lg font-bold text-purple-900">{{ systemHealth.version }}</div>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <h4 class="font-semibold mb-2">Statut des Services</h4>
            <div class="space-y-2">
              <div v-for="service in systemHealth.services" :key="service.name" class="flex items-center justify-between p-2 border rounded">
                <span>{{ service.name }}</span>
                <Badge :value="service.status" :severity="service.status === 'OK' ? 'success' : 'danger'" />
              </div>
            </div>
          </div>

          <div>
            <h4 class="font-semibold mb-2">Logs d'Erreurs Récentes</h4>
            <DataTable :value="errorLogs" class="p-datatable-sm">
              <Column field="level" header="Niveau" style="width: 100px">
                <template #body="slotProps">
                  <Badge :value="slotProps.data.level" :severity="getSeverity(slotProps.data.level)" />
                </template>
              </Column>
              <Column field="message" header="Message"></Column>
              <Column field="created_at" header="Date" style="width: 150px"></Column>
            </DataTable>
          </div>
        </div>
      </template>
    </Card>

    <!-- 2. Gestion de la Base de Données -->
    <Card>
      <template #title>
        <div class="flex items-center space-x-2">
          <i class="pi pi-database text-blue-500"></i>
          <span>Gestion de la Base de Données</span>
        </div>
      </template>
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 class="font-semibold mb-3">Sauvegardes</h4>
            <div class="space-y-2">
              <Button label="Créer Sauvegarde Manuelle" icon="pi pi-plus" @click="createBackup" :loading="loading.manualBackup" />
              <div class="border rounded p-3">
                <h5 class="font-medium mb-2">Sauvegardes Automatiques</h5>
                <div v-for="backup in backups" :key="backup.id" class="flex items-center justify-between py-1">
                  <span>{{ backup.filename }}</span>
                  <div class="space-x-2">
                    <Button icon="pi pi-download" size="small" @click="downloadBackup(backup)" />
                    <Button icon="pi pi-refresh" size="small" severity="success" @click="restoreBackup(backup)" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <h4 class="font-semibold mb-3">Optimisation</h4>
            <div class="space-y-2">
              <Button label="Nettoyer Tables Temporaires" icon="pi pi-trash" @click="cleanTempTables" :loading="loading.cleanTemp" severity="warning" />
              <Button label="Réindexer Tables" icon="pi pi-wrench" @click="reindexTables" :loading="loading.reindex" />
              <Button label="Supprimer Entrées Orphelines" icon="pi pi-minus-circle" @click="removeOrphans" :loading="loading.removeOrphans" severity="danger" />
            </div>
          </div>
        </div>

        <div class="mt-6">
          <h4 class="font-semibold mb-3">Historique des Migrations</h4>
          <DataTable :value="migrations" class="p-datatable-sm">
            <Column field="migration" header="Migration"></Column>
            <Column field="batch" header="Batch" style="width: 80px"></Column>
            <Column field="created_at" header="Date" style="width: 150px"></Column>
          </DataTable>
        </div>

        <div class="mt-6">
          <h4 class="font-semibold mb-3">Anonymisation des Données</h4>
          <div class="flex items-center space-x-4">
            <Checkbox v-model="anonymizeEnabled" inputId="anonymize" />
            <label for="anonymize">Activer l'anonymisation automatique</label>
            <Button label="Anonymiser Maintenant" icon="pi pi-shield" @click="anonymizeData" :loading="loading.anonymize" severity="danger" />
          </div>
        </div>
      </template>
    </Card>

    <!-- 3. Contrôle des Utilisateurs et Sécurité -->
    <Card>
      <template #title>
        <div class="flex items-center space-x-2">
          <i class="pi pi-shield text-red-500"></i>
          <span>Contrôle des Utilisateurs et Sécurité</span>
        </div>
      </template>
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 class="font-semibold mb-3">Sessions Actives</h4>
            <DataTable :value="activeSessions" class="p-datatable-sm">
              <Column field="user.name" header="Utilisateur"></Column>
              <Column field="ip_address" header="IP"></Column>
              <Column field="last_activity" header="Dernière Activité" style="width: 150px"></Column>
              <Column header="Actions" style="width: 100px">
                <template #body="slotProps">
                  <Button icon="pi pi-times" size="small" severity="danger" @click="forceLogout(slotProps.data)" />
                </template>
              </Column>
            </DataTable>
          </div>

          <div>
            <h4 class="font-semibold mb-3">Audit Log</h4>
            <DataTable :value="auditLogs" class="p-datatable-sm" scrollHeight="300px">
              <Column field="user.name" header="Utilisateur"></Column>
              <Column field="action" header="Action"></Column>
              <Column field="resource" header="Ressource"></Column>
              <Column field="created_at" header="Date" style="width: 150px"></Column>
            </DataTable>
          </div>
        </div>

        <div class="mt-6 space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <label for="maintenance-mode">Mode Maintenance</label>
              <InputSwitch v-model="maintenanceMode.enabled" inputId="maintenance-mode" @change="toggleMaintenanceMode" />
            </div>
            <div v-if="maintenanceMode.enabled" class="flex-1 ml-4">
              <InputText v-model="maintenanceMode.message" placeholder="Message de maintenance" class="w-full" />
            </div>
          </div>

          <div>
            <h4 class="font-semibold mb-3">Gestion des Permissions</h4>
            <div class="space-y-2">
              <Button label="Vérifier Accès Critiques" icon="pi pi-check-circle" @click="checkCriticalAccess" :loading="loading.checkAccess" />
              <Button label="Scanner 2FA" icon="pi pi-mobile" @click="scan2FA" :loading="loading.scan2FA" />
            </div>
            <div class="mt-3">
              <div v-if="permissionsCheck.results.length > 0" class="space-y-1">
                <div v-for="result in permissionsCheck.results" :key="result.id" class="flex items-center justify-between p-2 border rounded">
                  <span>{{ result.user }} - {{ result.issue }}</span>
                  <Badge :value="result.severity" :severity="result.severity === 'critical' ? 'danger' : 'warning'" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </Card>

    <!-- 4. Maintenance Applicative & Métier -->
    <Card>
      <template #title>
        <div class="flex items-center space-x-2">
          <i class="pi pi-cog text-orange-500"></i>
          <span>Maintenance Applicative & Métier</span>
        </div>
      </template>
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div>
            <h4 class="font-semibold mb-3">Purge des Fichiers</h4>
            <div class="space-y-2">
              <Button label="Nettoyer Fichiers Temporaires" icon="pi pi-trash" @click="purgeTempFiles" :loading="loading.purgeTemp" severity="warning" />
              <Button label="Supprimer Anciens Rapports" icon="pi pi-file-pdf" @click="purgeOldReports" :loading="loading.purgeReports" />
            </div>
          </div>

          <div>
            <h4 class="font-semibold mb-3">Gestion du Cache</h4>
            <div class="space-y-2">
              <Button label="Vider Cache Application" icon="pi pi-refresh" @click="clearAppCache" :loading="loading.clearAppCache" />
              <Button label="Vider Cache Routes" icon="pi pi-directions" @click="clearRouteCache" :loading="loading.clearRouteCache" />
              <Button label="Vider Cache Config" icon="pi pi-sliders-h" @click="clearConfigCache" :loading="loading.clearConfigCache" />
            </div>
          </div>

          <div>
            <h4 class="font-semibold mb-3">File d'Attente (Queue Jobs)</h4>
            <div class="space-y-2">
              <Button label="Voir Tâches en Cours" icon="pi pi-list" @click="viewQueueJobs" />
              <Button label="Relancer Tâches Échouées" icon="pi pi-replay" @click="retryFailedJobs" :loading="loading.retryJobs" severity="warning" />
            </div>
            <div class="mt-3">
              <div v-if="queueJobs.length > 0" class="border rounded p-3 max-h-40 overflow-y-auto">
                <div v-for="job in queueJobs" :key="job.id" class="flex items-center justify-between py-1">
                  <span>{{ job.name }}</span>
                  <Badge :value="job.status" :severity="getJobSeverity(job.status)" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6">
          <h4 class="font-semibold mb-3">Configuration des Variables d'Environnement</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="envVar in envVars" :key="envVar.key" class="border rounded p-3">
              <label class="block text-sm font-medium mb-1">{{ envVar.key }}</label>
              <InputText v-model="envVar.value" class="w-full" :type="envVar.type" />
            </div>
          </div>
          <div class="mt-3">
            <Button label="Sauvegarder Configuration" icon="pi pi-save" @click="saveEnvConfig" :loading="loading.saveEnv" severity="success" />
          </div>
        </div>
      </template>
    </Card>

    <!-- 5. Communication & Support -->
    <Card>
      <template #title>
        <div class="flex items-center space-x-2">
          <i class="pi pi-send text-indigo-500"></i>
          <span>Communication & Support</span>
        </div>
      </template>
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 class="font-semibold mb-3">Notifications Système</h4>
            <div class="space-y-3">
              <Textarea v-model="systemNotification.message" placeholder="Message à envoyer à tous les utilisateurs" rows="3" class="w-full" />
              <div class="flex space-x-2">
                <Dropdown v-model="systemNotification.type" :options="notificationTypes" optionLabel="label" optionValue="value" placeholder="Type de notification" />
                <Button label="Envoyer" icon="pi pi-send" @click="sendSystemNotification" :loading="loading.sendNotification" severity="info" />
              </div>
            </div>
          </div>

          <div>
            <h4 class="font-semibold mb-3">Diagnostic Mail</h4>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium mb-1">Email de test</label>
                <InputText v-model="mailTest.email" placeholder="test@example.com" class="w-full" />
              </div>
              <Button label="Envoyer Mail de Test" icon="pi pi-envelope" @click="sendTestMail" :loading="loading.sendTestMail" />
              <div v-if="mailTest.result" class="mt-3 p-3 border rounded" :class="mailTest.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                <div class="flex items-center space-x-2">
                  <i :class="mailTest.success ? 'pi pi-check-circle text-green-500' : 'pi pi-times-circle text-red-500'"></i>
                  <span>{{ mailTest.result }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Badge from 'primevue/badge'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Checkbox from 'primevue/checkbox'
import InputSwitch from 'primevue/inputswitch'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'

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
  if (confirm('Êtes-vous sûr de vouloir restaurer cette sauvegarde ?')) {
    try {
      await axios.post(`/admin/maintenance/database/backup/${backup.id}/restore`)
      // Show success message
    } catch (error) {
      // Handle error
    }
  }
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
  if (confirm('Cette action va anonymiser les données sensibles. Continuer ?')) {
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

<style scoped>
/* Custom styles if needed */
</style>