<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useLeagueFlowStore } from '../stores/leagueFlow'
import FixtureWeekCard from '../components/FixtureWeekCard.vue'

const store = useLeagueFlowStore()
const { leagueState } = storeToRefs(store)
</script>

<template>
  <main class="layout">
    <header class="header center">
      <h1>Generated Fixtures</h1>
      <p>Round-robin schedule, randomly drawn each time you reset.</p>
    </header>
    <section class="fixture-preview-grid">
      <FixtureWeekCard
        v-for="group in leagueState!.fixturesByWeek"
        :key="group[0]?.week ?? 0"
        :week="group[0]?.week ?? 0"
        :fixtures="group"
      />
    </section>
    <section class="actions">
      <button
        type="button"
        class="btn-ghost"
        :disabled="store.loading"
        :aria-busy="store.loading"
        @click="store.goToTeams()"
      >
        Back
      </button>
      <button
        type="button"
        class="btn-primary"
        :disabled="store.loading"
        :aria-busy="store.loading"
        @click="store.startSimulation()"
      >
        Start Simulation
      </button>
    </section>
  </main>
</template>
