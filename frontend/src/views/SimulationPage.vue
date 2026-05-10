<script setup lang="ts">
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import type { PredictionDisplayRow } from '../types/league'
import { useLeagueFlowStore } from '../stores/leagueFlow'
import LeagueStandingsTable from '../components/LeagueStandingsTable.vue'
import WeekFixturesPanel from '../components/WeekFixturesPanel.vue'
import ChampionshipPredictionsTable from '../components/ChampionshipPredictionsTable.vue'
import MatchResultsByWeek from '../components/MatchResultsByWeek.vue'

const store = useLeagueFlowStore()
const { leagueState, loading, statusMessage } = storeToRefs(store)

const predictionRows = computed<PredictionDisplayRow[]>(() => {
  const s = leagueState.value
  if (!s) {
    return []
  }
  if (s.predictions.length > 0) {
    return s.predictions.map((p) => ({
      name: p.name,
      percentage: p.percentage,
    }))
  }
  return s.table.map((row) => ({
    name: row.name,
    percentage: 0,
  }))
})

const hasAnyPlayedMatch = computed<boolean>(() => {
  const s = leagueState.value
  if (!s) {
    return false
  }
  return s.fixturesByWeek.some((week) =>
    week.some((f) => f.home_goals !== null && f.away_goals !== null),
  )
})

async function handleUpdate(payload: {
  fixtureId: number
  homeGoals: number
  awayGoals: number
}): Promise<void> {
  await store.updateFixtureScore(payload.fixtureId, payload.homeGoals, payload.awayGoals)
}
</script>

<template>
  <main class="layout">
    <header class="header center">
      <h1>Simulation</h1>
      <p>Run weeks one by one or all at once. Edit any score to recompute the table.</p>
    </header>
    <section class="sim-grid">
      <article class="sim-panel">
        <LeagueStandingsTable :rows="leagueState!.table" />
      </article>
      <WeekFixturesPanel
        :week-number="store.viewWeek"
        :fixtures="store.weekFixtures"
      />
      <article class="sim-panel">
        <ChampionshipPredictionsTable :rows="predictionRows" />
      </article>
    </section>
    <section class="actions sim-actions">
      <button
        type="button"
        class="btn-ghost"
        :disabled="loading"
        :aria-busy="loading"
        @click="store.goToFixtures()"
      >
        Back
      </button>
      <div class="sim-actions-main">
        <button
          type="button"
          class="btn-teal"
          :disabled="leagueState!.meta.isFinished || loading"
          :aria-busy="loading"
          @click="store.playAllWeeks()"
        >
          Play All Weeks
        </button>
        <button
          type="button"
          class="btn-teal"
          :disabled="leagueState!.meta.isFinished || loading"
          :aria-busy="loading"
          @click="store.playNextWeek()"
        >
          Play Next Week
        </button>
        <button
          type="button"
          class="btn-red"
          :disabled="loading"
          :aria-busy="loading"
          @click="store.resetLeagueData()"
        >
          Reset Data
        </button>
      </div>
    </section>
    <p class="status">{{ statusMessage }}</p>

    <MatchResultsByWeek
      v-if="hasAnyPlayedMatch"
      :fixtures-by-week="leagueState!.fixturesByWeek"
      :loading="loading"
      @update="handleUpdate"
    />
  </main>
</template>
