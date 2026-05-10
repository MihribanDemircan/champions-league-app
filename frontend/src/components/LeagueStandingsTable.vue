<script setup lang="ts">
import type { TableRow } from '../types/league'

defineProps<{
  rows: TableRow[]
}>()

function badgeClass(index: number): Record<string, boolean> {
  return {
    'position-badge': true,
    'is-first': index === 0,
    'is-second': index === 1,
    'is-third': index === 2,
  }
}
</script>

<template>
  <div class="panel-header">League Standings</div>
  <table class="sim-table">
    <thead>
      <tr>
        <th scope="col">Team</th>
        <th scope="col" class="num-col">P</th>
        <th scope="col" class="num-col">W</th>
        <th scope="col" class="num-col">D</th>
        <th scope="col" class="num-col">L</th>
        <th scope="col" class="num-col">GD</th>
        <th scope="col" class="num-col">Pts</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(row, index) in rows" :key="row.teamId">
        <td class="team-cell">
          <span :class="badgeClass(index)">{{ index + 1 }}</span>
          <span>{{ row.name }}</span>
        </td>
        <td class="num-col">{{ row.played }}</td>
        <td class="num-col">{{ row.won }}</td>
        <td class="num-col">{{ row.drawn }}</td>
        <td class="num-col">{{ row.lost }}</td>
        <td class="num-col">{{ row.goalDifference }}</td>
        <td class="num-col"><strong>{{ row.points }}</strong></td>
      </tr>
    </tbody>
  </table>
</template>
