<script setup lang="ts">
import type { Fixture } from '../types/league'

defineProps<{
  weekNumber: number
  fixtures: Fixture[]
}>()

function scoreText(home: number | null, away: number | null): string {
  const h = home === null ? '-' : home
  const a = away === null ? '-' : away
  return `${h} - ${a}`
}
</script>

<template>
  <article class="sim-panel">
    <div class="panel-header">Match Week {{ weekNumber }}</div>
    <div
      v-for="fixture in fixtures"
      :key="fixture.id"
      class="fixture-row simple"
    >
      <span>{{ fixture.home_team.name }}</span>
      <span class="score">{{ scoreText(fixture.home_goals, fixture.away_goals) }}</span>
      <span>{{ fixture.away_team.name }}</span>
    </div>
  </article>
</template>
