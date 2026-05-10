<script setup lang="ts">
import { onMounted, ref } from 'vue'
import type { TeamSeed } from '../types/league'
import { useLeagueFlowStore } from '../stores/leagueFlow'

const store = useLeagueFlowStore()

const defaultSeed: TeamSeed[] = [
  { name: 'Liverpool', power: 90 },
  { name: 'Manchester City', power: 95 },
  { name: 'Chelsea', power: 84 },
  { name: 'Arsenal', power: 87 },
]

function cloneSeeds(source: TeamSeed[]): TeamSeed[] {
  return source.map((t) => ({ ...t }))
}

function applyRowsFromStore(): void {
  const teamCount = store.teamsPayload.length
  const tableLen = store.leagueState?.table?.length ?? 0
  const hasPersistedLeague =
    teamCount === 4 && tableLen > 0 && (store.leagueState?.teams?.length ?? 0) === 4

  if (hasPersistedLeague) {
    rows.value = cloneSeeds(store.teamsPayload)
    return
  }

  rows.value = cloneSeeds(defaultSeed)
}

const rows = ref<TeamSeed[]>(
  store.teamsPayload.length === 4 &&
    (store.leagueState?.teams?.length ?? 0) === 4 &&
    (store.leagueState?.table?.length ?? 0) > 0
    ? cloneSeeds(store.teamsPayload)
    : cloneSeeds(defaultSeed),
)
const localStatus = ref('')

onMounted(async () => {
  if (
    store.teamsPayload.length === 4 &&
    (store.leagueState?.teams?.length ?? 0) === 4 &&
    (store.leagueState?.table?.length ?? 0) > 0
  ) {
    applyRowsFromStore()
    return
  }
  await store.loadStateFromBackend()
  applyRowsFromStore()
})

async function generateFixtures(): Promise<void> {
  const teams = rows.value.map((r) => ({
    name: r.name.trim(),
    power: Number(r.power),
  }))

  const invalid = teams.some(
    (t) => !t.name || !Number.isFinite(t.power) || t.power < 1 || t.power > 100,
  )
  if (invalid) {
    localStatus.value = 'All team names are required and power must be between 1 and 100.'
    return
  }

  localStatus.value = ''
  try {
    await store.createLeagueFromTeams(teams)
  } catch {
    localStatus.value = store.error ?? 'Request failed'
  }
}
</script>

<template>
  <main class="setup-layout">
    <section class="setup-card">
      <h1>Tournament Teams</h1>
      <p class="subtitle">
        Define your four contenders and their power ratings. The simulator
        weighs power into match probabilities.
      </p>

      <div class="setup-header-row">
        <span>Team name</span>
        <span style="text-align:center">Power</span>
      </div>
      <div class="setup-list">
        <div
          v-for="(row, index) in rows"
          :key="index"
          class="setup-row"
        >
          <input
            v-model="row.name"
            class="team-input"
            type="text"
            placeholder="Team name"
            :aria-label="`Team name ${index + 1}`"
          >
          <input
            v-model.number="row.power"
            class="power-input"
            type="number"
            min="1"
            max="100"
            placeholder="0-100"
            :aria-label="`Team power ${index + 1}`"
          >
        </div>
      </div>

      <div class="actions">
        <button
          type="button"
          class="btn-primary"
          :disabled="store.loading"
          :aria-busy="store.loading"
          @click="generateFixtures"
        >
          Generate Fixtures
        </button>
      </div>

      <p class="status">{{ localStatus }}</p>
    </section>
  </main>
</template>
