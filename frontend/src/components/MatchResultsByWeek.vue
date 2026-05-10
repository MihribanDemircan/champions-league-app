<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { Fixture } from '../types/league'

const props = defineProps<{
  fixturesByWeek: Fixture[][]
  loading: boolean
}>()

const emit = defineEmits<{
  (
    e: 'update',
    payload: { fixtureId: number; homeGoals: number; awayGoals: number },
  ): void
}>()

const editingId = ref<number | null>(null)
const draft = reactive<{ home: number; away: number }>({ home: 0, away: 0 })

function isPlayed(f: Fixture): boolean {
  return f.home_goals !== null && f.away_goals !== null
}

function startEdit(f: Fixture): void {
  if (props.loading) {
    return
  }
  editingId.value = f.id
  draft.home = f.home_goals ?? 0
  draft.away = f.away_goals ?? 0
}

function cancelEdit(): void {
  editingId.value = null
}

function saveEdit(f: Fixture): void {
  const home = clamp(Number(draft.home))
  const away = clamp(Number(draft.away))
  emit('update', { fixtureId: f.id, homeGoals: home, awayGoals: away })
  editingId.value = null
}

function clamp(value: number): number {
  if (Number.isNaN(value) || value < 0) {
    return 0
  }
  if (value > 20) {
    return 20
  }
  return Math.floor(value)
}
</script>

<template>
  <section class="results-by-week">
    <header class="results-header">
      <h2>Match Results by Week</h2>
      <p class="results-hint">
        Click any score to edit it. Standings are recalculated automatically when you save.
      </p>
    </header>

    <div class="results-grid">
      <article
        v-for="group in fixturesByWeek"
        :key="group[0]?.week ?? 0"
        class="results-card"
      >
        <div class="panel-header">Week {{ group[0]?.week ?? 0 }}</div>
        <div
          v-for="f in group"
          :key="f.id"
          class="result-row"
        >
          <span class="team-name home">{{ f.home_team.name }}</span>

          <template v-if="editingId === f.id">
            <span class="score-edit">
              <input
                v-model.number="draft.home"
                type="number"
                min="0"
                max="20"
                class="score-input"
                aria-label="Home goals"
              />
              <span class="dash">-</span>
              <input
                v-model.number="draft.away"
                type="number"
                min="0"
                max="20"
                class="score-input"
                aria-label="Away goals"
              />
            </span>
            <span class="row-actions">
              <button
                type="button"
                class="btn-mini btn-save"
                :disabled="loading"
                @click="saveEdit(f)"
              >
                Save
              </button>
              <button
                type="button"
                class="btn-mini btn-cancel"
                :disabled="loading"
                @click="cancelEdit"
              >
                Cancel
              </button>
            </span>
          </template>

          <template v-else>
            <button
              type="button"
              class="score-button"
              :class="{ 'score-empty': !isPlayed(f) }"
              :disabled="loading || !isPlayed(f)"
              :title="isPlayed(f) ? 'Edit score' : 'Not played yet'"
              @click="startEdit(f)"
            >
              {{ isPlayed(f) ? `${f.home_goals} - ${f.away_goals}` : '- : -' }}
            </button>
            <span class="row-actions" />
          </template>

          <span class="team-name away">{{ f.away_team.name }}</span>
        </div>
      </article>
    </div>
  </section>
</template>
