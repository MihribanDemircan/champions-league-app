<script setup lang="ts">
import { computed } from 'vue'
import type { PredictionDisplayRow } from '../types/league'

const props = defineProps<{
  rows: PredictionDisplayRow[]
}>()

const maxPct = computed<number>(() => {
  const max = Math.max(0, ...props.rows.map((r) => r.percentage))
  return max <= 0 ? 100 : Math.max(max, 1)
})

function formatPct(p: number): string {
  return Number.isInteger(p) ? String(p) : p.toFixed(2)
}
</script>

<template>
  <div class="panel-header">Championship Predictions</div>
  <div>
    <div
      v-for="(row, index) in rows"
      :key="`${row.name}-${index}`"
      class="prediction-row"
    >
      <div class="prediction-cell">
        <div class="prediction-name">
          <span>{{ row.name }}</span>
        </div>
        <div class="prediction-bar-wrap">
          <div
            class="prediction-bar"
            :style="{ width: `${(row.percentage / maxPct) * 100}%` }"
          />
        </div>
      </div>
      <span class="prediction-pct">{{ formatPct(row.percentage) }}%</span>
    </div>
  </div>
</template>
