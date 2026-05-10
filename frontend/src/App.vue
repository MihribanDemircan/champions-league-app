<script setup lang="ts">
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useLeagueFlowStore } from './stores/leagueFlow'
import TournamentTeamsPage from './views/TournamentTeamsPage.vue'
import GeneratedFixturesPage from './views/GeneratedFixturesPage.vue'
import SimulationPage from './views/SimulationPage.vue'

const store = useLeagueFlowStore()
const { step, leagueState, loading, error } = storeToRefs(store)

type StepKey = 'teams' | 'fixtures' | 'simulation'

const stepDefs: { key: StepKey; label: string }[] = [
  { key: 'teams', label: 'Teams' },
  { key: 'fixtures', label: 'Fixtures' },
  { key: 'simulation', label: 'Simulation' },
]

function stepClass(key: StepKey): Record<string, boolean> {
  const order: StepKey[] = ['teams', 'fixtures', 'simulation']
  const currentIdx = order.indexOf(step.value)
  const idx = order.indexOf(key)
  return {
    'step-item': true,
    'is-active': idx === currentIdx,
    'is-done': idx < currentIdx,
  }
}

const metaText = computed<string>(() => {
  const s = leagueState.value
  if (!s) {
    return 'Setup'
  }
  if (s.meta.isFinished) {
    return 'Season finished'
  }
  return `Week ${Math.min(s.meta.currentWeek, s.meta.totalWeeks)} / ${s.meta.totalWeeks}`
})
</script>

<template>
  <div class="app-shell">
    <header class="topbar">
      <div class="topbar-inner">
        <div class="brand">
          <div class="brand-mark">CL</div>
          <div>
            <div class="brand-title">Champions League</div>
            <div class="brand-subtitle">Mini Simulator</div>
          </div>
        </div>

        <ol class="steps">
          <li
            v-for="(s, i) in stepDefs"
            :key="s.key"
            :class="stepClass(s.key)"
          >
            <span class="step-index">{{ i + 1 }}</span>
            <span>{{ s.label }}</span>
          </li>
        </ol>

        <div class="topbar-meta">{{ metaText }}</div>
      </div>
    </header>

    <div
      v-if="error"
      class="app-error"
      role="alert"
    >
      {{ error }}
    </div>
    <div
      v-if="loading"
      class="app-loading"
      role="status"
      aria-live="polite"
    >
      Loading...
    </div>

    <TournamentTeamsPage v-if="step === 'teams'" />
    <GeneratedFixturesPage v-else-if="step === 'fixtures' && leagueState" />
    <SimulationPage v-else-if="step === 'simulation' && leagueState" />
  </div>
</template>
