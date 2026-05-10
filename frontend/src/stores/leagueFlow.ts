import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import type { LeagueState, TeamSeed } from '../types/league'
import { leagueRequest } from '../api/leagueClient'

export type FlowStep = 'teams' | 'fixtures' | 'simulation'

export const useLeagueFlowStore = defineStore('leagueFlow', () => {
  const step = ref<FlowStep>('teams')
  const leagueState = ref<LeagueState | null>(null)
  const teamsPayload = ref<TeamSeed[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const statusMessage = ref('')

  const viewWeek = computed(() => {
    if (!leagueState.value) {
      return 1
    }
    const meta = leagueState.value.meta
    return meta.currentWeek > meta.totalWeeks ? meta.totalWeeks : meta.currentWeek
  })

  const weekFixtures = computed(
    () =>
      leagueState.value?.fixturesByWeek.find((g) => g[0]?.week === viewWeek.value) ?? [],
  )

  async function runWithLoading<T>(message: string, fn: () => Promise<T>): Promise<T> {
    loading.value = true
    error.value = null
    statusMessage.value = message
    try {
      return await fn()
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Unknown error'
      throw e
    } finally {
      loading.value = false
    }
  }

  function syncTeamsFromState(state: LeagueState): void {
    teamsPayload.value = state.teams.map((t) => ({ name: t.name, power: t.power }))
  }

  async function createLeagueFromTeams(teams: TeamSeed[]): Promise<void> {
    const state = await runWithLoading('Generating fixtures...', () =>
      leagueRequest<LeagueState>('/reset', {
        method: 'POST',
        body: JSON.stringify({ teams }),
      }),
    )
    leagueState.value = state
    syncTeamsFromState(state)
    step.value = 'fixtures'
    statusMessage.value = ''
  }

  function goToTeams(): void {
    step.value = 'teams'
    statusMessage.value = ''
    error.value = null
  }

  function goToFixtures(): void {
    step.value = 'fixtures'
    statusMessage.value = ''
    error.value = null
  }

  function startSimulation(): void {
    step.value = 'simulation'
  }

  function setLeagueState(next: LeagueState): void {
    leagueState.value = next
    syncTeamsFromState(next)
  }

  async function loadStateFromBackend(): Promise<void> {
    try {
      const state = await leagueRequest<LeagueState>('/state')
      leagueState.value = state
      syncTeamsFromState(state)
    } catch {
      // ignore: ilk acilista backend hazir degilse default seed kullanilir
    }
  }

  async function playNextWeek(): Promise<void> {
    await runWithLoading('Playing week...', async () => {
      const next = await leagueRequest<LeagueState>('/simulate/week', { method: 'POST' })
      setLeagueState(next)
    })
    statusMessage.value = 'Done.'
  }

  async function playAllWeeks(): Promise<void> {
    await runWithLoading('Playing season...', async () => {
      const next = await leagueRequest<LeagueState>('/simulate/all', { method: 'POST' })
      setLeagueState(next)
    })
    statusMessage.value = 'Done.'
  }

  async function resetLeagueData(): Promise<void> {
    await runWithLoading('Resetting...', async () => {
      const next = await leagueRequest<LeagueState>('/reset', {
        method: 'POST',
        body: JSON.stringify({ teams: teamsPayload.value }),
      })
      setLeagueState(next)
    })
    statusMessage.value = 'Done.'
  }

  async function updateFixtureScore(
    fixtureId: number,
    homeGoals: number,
    awayGoals: number,
  ): Promise<void> {
    await runWithLoading('Updating score...', async () => {
      const next = await leagueRequest<LeagueState>(`/fixtures/${fixtureId}`, {
        method: 'PUT',
        body: JSON.stringify({ homeGoals, awayGoals }),
      })
      setLeagueState(next)
    })
    statusMessage.value = 'Score updated, standings recalculated.'
  }

  return {
    step,
    leagueState,
    teamsPayload,
    loading,
    error,
    statusMessage,
    viewWeek,
    weekFixtures,
    createLeagueFromTeams,
    goToTeams,
    goToFixtures,
    startSimulation,
    playNextWeek,
    playAllWeeks,
    resetLeagueData,
    setLeagueState,
    updateFixtureScore,
    loadStateFromBackend,
  }
})
