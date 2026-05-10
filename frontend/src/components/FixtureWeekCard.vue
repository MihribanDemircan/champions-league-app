<script setup lang="ts">
import type { Fixture } from '../types/league'

defineProps<{
  week: number
  fixtures: Fixture[]
}>()
</script>

<template>
  <article class="fixture-preview-card">
    <div class="fixture-preview-week">Week {{ week }}</div>
    <div
      v-for="f in fixtures"
      :key="f.id"
      class="fixture-preview-row"
    >
      <span>{{ f.home_team.name }}</span>
      <span>-</span>
      <span>{{ f.away_team.name }}</span>
    </div>
  </article>
</template>
