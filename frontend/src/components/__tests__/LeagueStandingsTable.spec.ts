import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import LeagueStandingsTable from '../LeagueStandingsTable.vue'
import type { TableRow } from '../../types/league'

describe('LeagueStandingsTable', () => {
  it('renders team rows', () => {
    const rows: TableRow[] = [
      {
        teamId: 1,
        name: 'Liverpool',
        power: 90,
        played: 0,
        won: 0,
        drawn: 0,
        lost: 0,
        goalsFor: 0,
        goalsAgainst: 0,
        goalDifference: 0,
        points: 0,
      },
    ]

    const wrapper = mount(LeagueStandingsTable, {
      props: { rows },
    })

    expect(wrapper.text()).toContain('Liverpool')
    expect(wrapper.find('th[scope="col"]').exists()).toBe(true)
  })
})
